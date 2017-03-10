<?php

if (strpos($GLOBALS["project_info"]["dns"], "api") !== false) {
	include_once($_SERVER["DOCUMENT_ROOT"]."functions/functions.php");
} else {
	include_once($_SERVER["DOCUMENT_ROOT"]."/../api/functions/functions.php");
}

/*

/xml/score2.php?world=7&parent=48299&child=140325&game=0&score=25&question=621743&reps=3&playasaclassroom=0

[3:05] 
Score Reply XML:<CMSData>
 <Result>
   <Value>true</Value>
 </Result>
</CMSData>

 */

function put_scores($values) {

	$continue = true;

	audit_function("audits.activity_scores");

	ob_start();

	// -------------------------------------------------------
	// -- TEMPORARY and needed for the app - 2016-02-28
	// -------------------------------------------------------


	if(isset($_REQUEST['game'])) {
		$game = trim($_REQUEST['game']);
		if(empty($game)) {
			$_REQUEST['game'] = "quiz_questions";
		} else if($game == "109") {
			$_REQUEST['game'] = "stepitup";
		}
	}

	if(empty($_REQUEST['user_id']) && empty($_REQUEST['parent'])) {
		$_REQUEST['user_id'] = "0";
	}
	if(!empty($_REQUEST['user_id'])) {
		$_REQUEST['user_id'] = validate_user_id($_REQUEST['user_id']);
	}

	if(!is_numeric($_REQUEST['user_id'])) {
		$_REQUEST['user_id'] = "";
	}

	// -------------------------------------------------------
	// -- TEMPORARY and needed for the app - 2016-02-28
	// -------------------------------------------------------

	if(!empty($_REQUEST['question'])) {
		$_REQUEST['content_id'] = $_REQUEST['question'];
	}

	$inputs = $_REQUEST;

	// validate the inputs (worlds, themes, genres, etc)
	if(($result = put_scores_validate($inputs)) === false) {
		$continue = false;
	}

	// Build the SQL needed for this api call
	if($continue && ($successful = put_scores_save($result)) === false) {
		$continue = false;
	}

	// Process results
	if($continue && ($xml = put_scores_build_xml($result,$successful)) === false) {
		$continue = false;
	}


	echo $xml;

	$output = ob_get_clean();

	ob_start();
	show_debug();
	$x = ob_get_clean();


	$output = str_replace("\n","",$output);
	$output = preg_replace("/\s\s+/"," ",$output);
	$output = str_replace("> <","><",$output);

	echo "<CMSData>". $output ."</CMSData>";

}

function put_scores_validate($inputs) {

	$result = array();

	// list of requirements
	if(empty($inputs['activity_map_id']) && empty($inputs['activity_bundle_id']) && empty($inputs['activity_bundle_value_id'])) {
		$needs_world_bool = true;
		if(!empty($inputs['game'])){
			if($inputs['game'] == '109' || $inputs['game'] == 'stepitup'){
				$needs_world_bool = false;
			}
		}
		$required = array();
		if($needs_world_bool == true){
			$required = array(
				"individual" => array(
					"child"
					// ,"parent"
					,"world"
					,"game"
					,"score"
				)
				,"classroom" => array(
					"world"
					,"game"
					,"score"
					,"playasaclassroom"
				)
			);
		} else {
			$required = array(
				"individual" => array(
					"child"
					// ,"parent"
					// ,"world"
					,"game"
					,"score"
				)
				,"classroom" => array(
					"game"
					// ,"world"
					,"score"
					,"playasaclassroom"
				)
			);
		}
		$type = "individual";
		if(!empty($inputs['playasaclassroom']) || !empty($inputs['classroom'])) {
			$type = "classroom";
			foreach($required as $k => $v) {
				if($v == 'child') {
					unset($required[$k]);
					break;
				}
			}
		}

		// This will clean / validate worlds, themes, grades, etc.
		$result = requirement_validation(array_flip($required[$type]),$inputs);
		$inputs['activity_map_id'] = get_activity_map_id($result);
	}

	if(empty($result['errors'])) {
		// If we have the bundle value id, but not the bundle, go fetch it real quick
		if(empty($inputs['activity_map_id']) && !empty($inputs['activity_bundle_value_id'])) {
			$tmp = get_bundle_id_from_bundle_value_id($inputs['activity_bundle_value_id']);
			$inputs['activity_bundle_id'] = $tmp['activity_bundle_id'];
			$inputs['activity_map_id'] = $tmp['activity_map_id'];
		} else if(empty($inputs['activity_map_id']) && !empty($inputs['activity_bundle_id'])) {
			$tmp = get_activity_map_id_from_bundle_id($inputs['activity_bundle_id']);
			$inputs['activity_map_id'] = $tmp['activity_map_id'];
		}

		// We should now have an activity_map_id, validate against that
		$required = array(
			"child"
			// ,"parent"
			,"score"
			,"activity_map_id"
		);
		if(!empty($inputs['playasaclassroom']) || !empty($inputs['classroom'])) {
			$type = "classroom";
			foreach($required as $k => $v) {
				if($v == 'child') {
					unset($required[$k]);
					break;
				}
			}
		}

		$result = requirement_validation(array_flip($required),$inputs);
	}
	//md5('world=18&parent=52612&child=209696&game=0&score=100&question=592250&reps=1&playasaclassroom=0&version=1.12!cck?forkids&') -- 92d3961e45854b5c0f36ce793d43beeb

	// if we have errors, spit them out and end.
	if(!empty($result["errors"])) {
		$message = "All required data must be provided.";
		$details = "Somefields are required to proceed.";
		echo xml_errors($message,$details,$result["errors"]);
		return false;
	}

	if(empty($result['activity_map_id'])) {
		$result['activity_id'] = get_activity_map_id($result);
	}

	return $result;

}

function put_scores_save($data,$item_count = 10) {

	$data['is_classroom'] = (!empty($data['playasaclassroom']) ? $data['playasaclassroom'] : 0);
	$data['is_guest'] = (!empty($data['is_guest']) ? $data['is_guest'] : 0);
	$data['attempts'] = (!empty($data['reps']) ? $data['reps'] : 1);
	$data['activity_bundle_id'] = (!empty($data['activity_bundle_id']) ? $data['activity_bundle_id'] : 0);
	$data['activity_bundle_value_id'] = (!empty($data['activity_bundle_value_id']) ? $data['activity_bundle_value_id'] : 0);
	$data['institution_id'] = (!empty($data['institution_id']) ? $data['institution_id'] : 0);

	if(empty($data['institution_id'])) {
		if(!empty($data['child'])) {
			$data['institution_id'] = get_institution_id_by_student($data['child']);
		}
	}
	/// Check a second time if needed
	if(empty($data['institution_id'])) {
		if(!empty($data['parent'])) {
			$data['institution_id'] = get_institution_id_by_user($data['parent']);
		}
	}

	$date = date('Y-m-d H:i:s');
	if ((int)$data['score'] > 0){
		$q = "
			insert into activity_scores (
				active
				,is_classroom
				,is_guest
				,attempts
				,activity_map_id
				,activity_bundle_id
				,activity_bundle_value_id
				,score_status_id
				,student_id
				,user_id
				,institution_id
				,original_score
				,calculated_score
				,multiplier
				,created
				,modified
			) values (
				't'
				,'". (int)$data['is_classroom'] ."'
				,'". (int)$data['is_guest'] ."'
				,'". (int)$data['attempts'] ."'
				,'". (int)$data['activity_map_id'] ."'
				,'". (int)$data['activity_bundle_id'] ."'
				,'". (int)$data['activity_bundle_value_id'] ."'
				,'". 3 ."'
				,'". (int)$data['child'] ."'
				,'". (int)$data['parent'] ."'
				,'". (int)$data['institution_id'] ."'
				,'". (int)$data['score'] ."'
				,'". (int)$data['score'] ."'
				,'". 1 ."'
				,'". $date ."'
				,'". $date ."'
			) returning id
		";

		$res = db_query($q,"Inserting new score");
		if(db_is_error($res)) {
			return false;
		}
		$row = db_fetch_row($res);
		return (!empty($row['id']) ? $row['id'] : false);
	} else {
		return false;
	}
}


function put_scores_build_xml($options,$successful) {

	if(empty($successful)) {
		$message = "An error has occured while trying to save this score data.";
		$details = "An error has occured while trying to save this score data.";
		echo xml_errors($message,$details);
		return false;
	}

	$full_output = "<Result>";
	$full_output .= "<Value>true</Value>";
	$full_output .= "</Result>";

	return $full_output;
}

