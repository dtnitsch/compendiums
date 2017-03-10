<?php 

if (strpos($GLOBALS["project_info"]["dns"], "api") !== false) {
	include_once($_SERVER["DOCUMENT_ROOT"]."functions/functions.php");
} else {
	include_once($_SERVER["DOCUMENT_ROOT"]."/../api/functions/functions.php");
}

function get_stepitup($values) {

	$continue = true;

	// -------------------------------------------------------
	// -- TEMPORARY and needed for the app - 2016-02-28
	// -------------------------------------------------------

	if(empty($_REQUEST['student_id']) && !empty($_REQUEST['cckid'])) {
		$_REQUEST['student_id'] = $_REQUEST['cckid'];
	}
	if(empty($_REQUEST['student_id']) && empty($_REQUEST['cckid'])) {
		$_REQUEST['student_id'] = "0";
	}
	if(!empty($_REQUEST['student_id'])) {
		$_REQUEST['student_id'] = validate_student_id($_REQUEST['student_id']);
	}

	if(!is_numeric($_REQUEST['student_id'])) {
		$_REQUEST['student_id'] = "";
	}

	// -------------------------------------------------------
	// -- TEMPORARY and needed for the app - 2016-02-28
	// -------------------------------------------------------


	// audit_function("audits.stepitup");

	ob_start();

	$inputs = $_REQUEST;
	$inputs['world'] = strtolower($_REQUEST['genre']);
	if(substr($inputs['world'],-1) == "v") {
		$inputs['world'] = substr($inputs['world'],0,-1);
	}
	$inputs['game'] = 109;

	if(!empty($inputs['questionid'])) {
		$inputs['content_id'] = $inputs['questionid'];
	}


	// validate the inputs (worlds, themes, genres, etc)
	if(($result = get_stepitup_validate($inputs)) === false) {
		$continue = false;
	}

	// Build the SQL needed for this api call
	if($continue && ($questions = get_stepitup_build_list($result)) === false) {
		$continue = false;
	}

	// Run SQL and proess results
	if($continue && ($xml = get_stepitup_build_xml($result,$questions)) === false) {
		$continue = false;
	}

	if(empty($xml)) {
		$message = "Invalid Request.";
		$details = "Please make sure your questionid and grade are correct";
		echo xml_errors($message,$details,$result["errors"]);
	}

	echo $xml;

	$output = ob_get_clean();

	ob_start();
	show_debug();
	$x = ob_get_clean();

	$output = str_replace("\n","",$output);
	$output = preg_replace("/\s\s+/"," ",$output);
	$output = str_replace("> <","><",$output);

	// header('Content-type: text/xml');

	echo $output;

}

function get_stepitup_validate($inputs) {
	// list of requirements
	$required = array(
		// "world"
		// ,"theme"
		// ,"genre"
		// ,"generation"
		"grade"
		,"student_id"
		,"game"
		,"questionid"
	);
	$result = requirement_validation_stepitup(array_flip($required),$inputs);

	// if we have errors, spit them out and end.
	if(!empty($result["errors"])) {
		$message = "All required data must be provided.";
		$details = "The fields world, theme, genre, and student id are required to proceed.";
		echo xml_errors($message,$details,$result["errors"]);
		return false;
	}

	return $result;

}

function get_stepitup_build_list($options) {

	if(empty($options['student_id'])) {
		return error_not_logged_in();
	}

	$activity_map_id = get_activity_map_id($options);

	if(empty($activity_map_id)) {
		return false;
	}

	$res = get_submissions_for_today($options,$activity_map_id);
	if($res > 2) {
		return error_number_of_submissions();
	}

	if(empty($options['action']) || $options['action'] == "stepitupentry") {
	    return get_bundle_questions($id["id"],$options);

	} else if($options['action'] == "stepitupenter") {
		return get_bundle_score($id["id"],$options);

	}

	return false;
}

function get_bundle_score($id,$options) {

	$q = "
		select count(*) as cnt
		from public.activity_map as atm
		join public.activity_scores as ats on
			ats.activity_map_id = atm.id
			and ats.created >= now()::date
		where
		    atm.activity_id = '109'
		    and atm.content_id = '".$options['questionid']."'
		    and ats.student_id = '". $options['student_id'] ."'
	";
	$repeat_res = db_fetch($q,"Getting repeat StepItUp scores");
	if ($repeat_res['cnt'] >= 2){
		return error_number_of_submissions();
	}
	$grade_for_query = strtolower("".$options['grade'][0]);
	if ($grade_for_query == "other"){
		$grade_for_query = "4";
	}
	if ($grade_for_query == "n"){
		$grade_for_query = "8";
	}
	$q = "
		select *
		from activities.step_it_up_points
		where
		    step_it_up_id = '". $options['questionid'] ."'
		    and grade_id = '". $grade_for_query ."'
	";
	$res = db_fetch($q,"Getting StepItUp point value");

	if($options['numberofreps'] > $res['max_count']) {
		return error_over_max_points();
	}

	$score = round((($res['points'] / $res['good_count']) * $options['numberofreps']) * 10);

	if($options['numberofreps'] <= $res['good_count']) {
		$bundle_score_xml = '
			<CMSData>
				<success>
					<type>stepitupsubmit</type>
					<message1>Great Job!</message1>
					<message2>You earned '. number_format($score*2) .' points!</message2>
					<points>'. $score*2 .'</points>
				</success>
			</CMSData>';
	} else {
		$bundle_score_xml = '
			<CMSData>
				<success>
					<type>stepitup_tenative_submit</type>
					<message1>Great Job!</message1>
					<message2>You earned '. number_format($score*2) .' points!</message2>
					<tenative_message1>That sounds like a lot...</tenative_message1>
					<tenative_message2>Are you sure that you entered your CORRECT reps? Entering false information can disqualify you from this contest. If these are your correct reps, press YES. If not, please press NO and correct your entry.</tenative_message2>
					<points>'. $score*2 .'</points>
				</success>
			</CMSData>';
	}

	return $bundle_score_xml;
}


function error_not_logged_in() {
	return "<CMSData>
	<error>
		<type>login</type>
		<message1>Not Logged In</message1>
		<message2>You need to login to track your exercise</message2>
	</error>
</CMSData>";
}


function error_number_of_submissions() {
	return "<CMSData>
	<error>
		<type>alreadytracked</type>
		<message1>You already tracked this exercise twice today.</message1>
		<message2>Try some other exercises now and do more of these tomorrow.</message2>
	</error>
</CMSData>";
}

function error_over_max_points() {
	/*return "<CMSData>
	<error>
		<type>toomany</type>
		<message1>Your personal certified trainer, Kimmie says that it is not possible for you to do that many reps.</message1>
		<message2>Please enter your CORRECT reps! Entering false information will DISQUALIFY YOU from this contest.</message2>
	</error>
</CMSData>";*/

	return "<CMSData>
	<error>
		<type>toomany</type>
		<message1>Too Many Reps</message1>
		<message2>Please enter your CORRECT reps! Entering false information will DISQUALIFY YOU from this contest.</message2>
	</error>
</CMSData>";
}

function get_submissions_for_today($options,$activity_map_id) {

	$q = "
		select count(*) as cnt
		from public.activity_scores
		where
		    student_id = ". $options['student_id'] ."
		    and activity_map_id = '". $activity_map_id ."'
		    and created::text like '". date('Y-m-d') ."%'
	";
	$res = db_fetch($q,"Getting repeat questions");

	if(!empty($res['cnt'])) {
		return $res['cnt'];
	}

	return 0;
}

function get_bundle_questions($id,$options) {

	// 2016-11-07 Eric Adams
	// Temporary conditional in place to stop error
	$grade_id = $options['grade'][0];
	if ($grade_id == "K")
		$grade_id = '16';
	if ($grade_id == "Other")
		$grade_id = '18';

	
	
	$q = "
		select
		    siu.title
		    ,siup.good_count
		    ,siup.max_count
		from activities.step_it_up as siu
		join activities.step_it_up_points as siup on
		    siup.step_it_up_id = siu.id
--		    and siup.grade_id = '". $options['grade'][0] ."'
		    and siup.grade_id = '". $grade_id ."'
		where siu.id = '". $options['questionid'] ."'
	";
	$res = db_fetch($q,"Getting quesiton info");

	if(empty($res['good_count'])) {
		return false;
	}

	$output = array(
		"message1" => "How many seconds did you hold for?"
		,"message2" => "Enter Your Reps!"
		,"questionid" => $options['questionid']
		,"avgreps" => $res['good_count']
		,"maxreps" => $res['max_count']
	);

	if($options['questionid'] != 418 && $options['questionid'] != 420) {
		$output['message1'] = 'How many '. $res['title'] .' did you do?';
	}

	$full_output = "
	<CMSData>
		<entry>
			<message1>". $output['message1'] ."</message1>
			<message2>". $output['message2'] ."</message2>
			<questionid>". $output['questionid'] ."</questionid>
			<avgreps>". $output['avgreps'] ."</avgreps>
			<maxreps>". $output['maxreps'] ."</maxreps>
		</entry>
	</CMSData>
	";



	return $full_output;
}

function get_stepitup_build_xml($options,$questions) {

	if(empty($questions)) {
		$message = "Unable to provide any questions matching parameters passed.";
		$details = "We were unable to find questions matching your criteria.";
		echo xml_errors($message,$details);
		return false;
	}

	return $questions;

//	return $full_output;
}

function requirement_validation_stepitup($required = array(),$inputs = array()) {

	$error_list =& get_error_list();
	$error_list['questionid'] = 'A "question id" is required';

	// Loop 1 - Check for required
	foreach(array_keys($required) as $row) {

		// Exists in the error list, but not in the given inputs... error out
		if(!isset($inputs[$row]) && isset($error_list[$row])) {
			$errors[$error_list[$row]['id']] = $error_list[$row]['message'];

		// Totally known required field
		} else if(!isset($inputs[$row]) && !isset($error_list[$row])) {
			$errors["2000"] = "Unknown value for '". $row ."' missing from input";

		// Checking for empty unknown values
		} else if(isset($inputs[$row]) && isset($error_list[$row])) {
			$value = (is_array($inputs[$row]) ? $inputs[$row][0] : $inputs[$row]);
			if(!empty($error_list[$row]['optional'])) {
				//
			} else if(!empty($error_list[$row]['required']) && empty($value))  {
				// BAD!  Erase this as soon as possible.  Needed for mobile
				if($row == "student_id" && $value == "0") {
					continue;
				}
				// game => alsd => 0 = fail
				$errors[$error_list[$row]['id']] = $error_list[$row]['invalid'];
			}
		}

	}

	// If we have errors - don't continue - return no
	if(!empty($errors)) {
		$inputs['errors'] = $errors;
		return $inputs;
	}

	// Loop 2 - Running through the inputs and seeing if we need to convert data
	foreach(array_keys($inputs) as $row) {

		// If we are returning an array of values, but it is not yet in array format...
		if(!empty($error_list[$row]) && !empty($error_list[$row]['array']) && !is_array($inputs[$row])) {
			$inputs[$row] = explode(',', $inputs[$row]);
		}

		// Additional Cleanup
		// Grades - Assume k1 and 1 are both "grade 1"
		if($row == "grades") {
			$inputs[$row] = convert_grades($row);
		}
	}

	if(!empty($inputs['game'])) {
		// $inputs['game'] = (!empty($inputs['game'][0]) ? $inputs['game'][0] : $inputs['game']);
		if(!is_numeric($inputs['game'])) {
			$inputs['game'] = get_activity_id_from_alias($inputs['game']);
		}
	} else {
		$inputs['game'] = 0;
	}

	if(!empty($inputs['grade'])) {
		// $inputs['grade'] = (!empty($inputs['grade'][0]) ? $inputs['grade'][0] : $inputs['grade']);
		if(!is_numeric($inputs['grade'])) {
			if($inputs['grade'] == 'k1' || $inputs['grade'] == 'k' || $inputs['grade'] == 'kindergarder') {
				$inputs['grade'] = 'k1';
			}
		}
	} else {
		$inputs['grade'] = 0;
	}

	// Loop 3 - Check for validity of inputs
	foreach(array_keys($required) as $row) {

		// Checking for empty unknown values
		if(isset($inputs[$row]) && isset($error_list[$row])) {
			$value = (is_array($inputs[$row]) ? $inputs[$row][0] : $inputs[$row]);
			if(!empty($error_list[$row]['required']) && empty($value))  {
				if(!empty($error_list[$row]['optional'])) {

				} else if($row == "student_id" && $value == "0") {
					// BAD!  Erase this as soon as possible.  Needed for mobile
					continue;
				}
				// echo "<br>row: $row, value: $value";
				// game => alsd => 0 = fail
				$errors[$error_list[$row]['id']] = $error_list[$row]['invalid'];
			}
		}
	}

	// If we have errors, return no
	if(!empty($errors)) {
		$inputs['errors'] = $errors;

		return $inputs;
	}

	return $inputs;
}