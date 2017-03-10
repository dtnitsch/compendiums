<?php

function requirement_validation($required = array(),$inputs = array()) {

	$errors = array();

	if(!empty($inputs['playasaclassroom'])) {
		$required['playasaclassroom'] = $inputs['playasaclassroom'];
	} else if(!empty($inputs['classroom']) && !is_array($inputs['classroom'])) {
		$required['playasaclassroom'] = $inputs['classroom'];
	}

	// ---------------------------
	// ---------- TO DO ----------
	// ---------------------------
	// Error check against all available options in the DB / array below
	// Massively expand on this
	// ---------------------------
	$error_list =& get_error_list();

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

	if(!empty($inputs['world'][0])) {
		// $inputs['world'] = (!empty($inputs['world'][0]) ? $inputs['world'][0] : $inputs['world']);
		if(!is_numeric($inputs['world'][0])) {
			$inputs['world'][0] = get_world_id_from_alias($inputs['world'][0]);
		}
	} else {
		$inputs['world'][0] = 0;
	}

	if(!empty($inputs['theme'][0])) {
		// $inputs['theme'] = (!empty($inputs['theme'][0]) ? $inputs['theme'][0] : $inputs['theme']);
		foreach($inputs['theme'] as $k => $r) {
			if(!is_numeric($inputs['theme'][$k])) {
				$inputs['theme'][$k] = get_theme_id_from_alias($inputs['theme'][$k]);
			}
		}
	} else {
		$inputs['theme'][0] = 0;
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

	if(!empty($inputs['generation'])) {
		// $inputs['generation'] = (!empty($inputs['generation'][0]) ? $inputs['generation'][0] : $inputs['generation']);
		if(!is_numeric($inputs['generation'])) {
			$inputs['generation'] = 0;
		}
	} else {
		$inputs['generation'] = 0;
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

function xml_errors($message, $details, $errors = array()) {
	$output = "<Error>
		<Message>". $message ."</Message>
		<Details>". $details ."</Details>";
	if(!empty($errors)) {
		$output .= "<DetailMessages>";
		foreach($errors as $k => $v) {
			$output .= "<E". $k .">". $v ."</E". $k .">";
		}
		$output .= "</DetailMessages>";
	}
	$output .= "</Error>";

	return $output;
}

function xml_warnings($message, $details, $warnings = array()) {
	$output = "<Warning>
		<Message>". $message ."</Message>
		<Details>". $details ."</Details>";
	if(!empty($warnings)) {
		$output .= "<DetailMessages>";
		foreach($warnings as $k => $v) {
			$output .= "<W". $k .">". $v ."</W". $k .">";
		}
		$output .= "</DetailMessages>";
	}
	$output .= "</Warning>";

	return $output;
}

function xml_requests($options,$additional_options = array()) {

	$output = "<Request>";
	if(!empty($options["worlds"])) {
		$output .= "<World>". implode(", ",$options["worlds"]) ."</World>";	
	}
	if(!empty($options["themes"])) {
		$output .= "<Theme>". implode(", ",$options["themes"]) ."</Theme>";
	}
	if(!empty($options["genres"])) {
		$output .= "<Genre>". implode(", ",$options["genres"]) ."</Genre>";
	}
	if(!empty($options["grades"])) {
		$output .= "<Grade>". implode(", ",$options["grades"]) ."</Grade>";
	}
	if(!empty($options["generations"])) {
		$output .= "<Generation>". implode(", ",$options["generations"]) ."</Generation>";
	}
	if(!empty($options["student_id"])) {
		$output .= "<StudentID>". implode(", ",$options["student_id"]) ."</StudentID>";
	}

	// if(!empty($additional_options)) {
		foreach($additional_options as $k => $v) {
			$output .= "<". $k .">". $v ."</". $k .">";
		}
	// }

	$output .= "</Request>";

	return $output;
}

function get_activity_from_alias($alias) {
	$q = "select id from public.activities where alias = '". $alias ."'";
	$res = db_fetch($q,"Getting activity id by alias");
	if(!empty($res['id'])) {
		return $res['id'];
	}
	return 0;
}

function get_activity_map_id($options = array()) {

	$activity_id = (!empty($options['activity_id']) ? $options['activity_id'] : 0);
	if(empty($activity_id)) {
		if(!empty($options['game'])) {
			if(is_numeric($options['game'])) {
				$activity_id = $options['game'];
			} else {
				$activity_id = get_activity_from_alias($options['game']);
			}
		}
	}
	
	$world_id = (!empty($options['world'][0]) ? $options['world'][0] : 0);
	$theme_id = (!empty($options['theme'][0]) ? $options['theme'][0] : 0);
	$grade_id = (!empty($options['grade'][0]) ? $options['grade'][0] : 0);
	$generation_id = (!empty($options['generation']) ? $options['generation'] : 0);
	$content_id = (!empty($options['content_id']) ? $options['content_id'] : 0);

	// 2016-11-07 Eric Adams
	// Temporary conditional in place to stop error
	if (strtolower("".$grade_id) == "k"){
		$grade_id = '16';
	}
	if (strtolower("".$grade_id) == "other"){
		$grade_id = '4';
	}
	if (strtolower("".$grade_id) == "n"){
		$grade_id = '8';
	}


	$q = "
		select
			id
		from public.activity_map
		where
			activity_id = '". $activity_id ."'
			and world_id = '". $world_id ."'
			and theme_id = '". $theme_id ."'
			and grade_id = '". $grade_id ."'
			and generation_id = '". $generation_id ."'
			". (!empty($content_id) ? " and content_id = '". $content_id ."' " : '') ."
	";
	$res = db_query($q,"Getting activity id");

	// If we have more than 0 records, return that.
	if(db_num_rows($res)) {
		$row = db_fetch_row($res);
		$id = $row['id'];
		return $id;
	}

	$id = create_activity_map($activity_id,$world_id,$theme_id,$grade_id,$generation_id,$content_id);

	return $id;
}

function create_activity_map($activity_id,$world_id = 0,$theme_id = 0,$grade_id = 0,$generation_id = 0,$content_id = 0) {

	$time = date('Y-m-d H:i:s');
	$q = "
		insert into public.activity_map (
			activity_id
			,world_id
			,theme_id
			,grade_id
			,generation_id
			,content_id
			,created
			,modified
		) values (
			'". $activity_id ."'
			,'". $world_id ."'
			,'". $theme_id ."'
			,'". $grade_id ."'
			,'". $generation_id ."'
			,'". $content_id ."'
			,'". $time ."'
			,'". $time ."'
		) returning id
	";
	$res = db_query($q,"Inserting new activity");
	if(db_is_error($res)) {
		return false;
	}
	$row = db_fetch_row($res);
	return (!empty($row['id']) ? $row['id'] : false);
}

function validate_student_id($id) {
	$q = "select id from system.students where id = '". db_prep_sql(trim($id)) ."'";
	$res = db_fetch($q,__FUNCTION__);
	if(!empty($res['id'])) {
		return $res['id'];
	}
	return '-';
}

function validate_user_id($id) {
	$q = "select id from system.users where id = '". db_prep_sql(trim($id)) ."'";
	$res = db_fetch($q,__FUNCTION__);
	if(!empty($res['id'])) {
		return $res['id'];
	}
	return '-';
}

function get_world_id_from_alias($alias) {
	$q = "select id from public.worlds where alias = '". db_prep_sql(trim($alias)) ."'";
	$res = db_fetch($q,__FUNCTION__);
	if(!empty($res['id'])) {
		return $res['id'];
	}
	return 0;
}

function get_theme_id_from_alias($alias) {
	$q = "select id from public.themes where alias = '". db_prep_sql(trim($alias)) ."'";
	$res = db_fetch($q,__FUNCTION__);
	if(!empty($res['id'])) {
		return $res['id'];
	}
	return 0;
}
function get_activity_id_from_alias($alias) {
	$q = "select id from public.activities where alias = '". db_prep_sql(trim($alias)) ."'";
	$res = db_fetch($q,__FUNCTION__);
	if(!empty($res['id'])) {
		return $res['id'];
	}
	return 0;
}

function get_institution_id_by_user($user_id) {
	$q = "select id from public.activities where alias = '". db_prep_sql(trim($alias)) ."'";
	$res = db_fetch($q,__FUNCTION__);
	if(!empty($res['institution_id'])) {
		return $res['institution_id'];
	}
	return 0;
}
function get_institution_id_by_student($student_id) {
	$q = "select institution_id from system.students where id = '". db_prep_sql((int)$student_id) ."'";
	$res = db_fetch($q,__FUNCTION__);
	if(!empty($res['institution_id'])) {
		return $res['institution_id'];
	}
	return 0;
}

function convert_grades($grades) {
	foreach($grades as $k => $v) {
		$v = strtolower($v);
		if($v == "k" || $v == "1") {
			$grades[$k] == "k1";
		}
	}
	return $grades;
}

function get_bundle_id_from_bundle_value_id($activity_bundle_value_id) {
	$q = "
		SELECT
			activity_bundle_values.activity_bundle_id
			,activity_bundles.activity_map_id
		FROM activity_bundle_values
		join activity_bundles on activity_bundles.id = activity_bundle_values.activity_bundle_id
		WHERE activity_bundle_values.id = '". $activity_bundle_value_id ."'
	";
	$res = db_query($q,"Getting Bundle ID");
	if(db_is_error($res) || db_num_rows($res) == 0) {
		return false;
	}
	$row = db_fetch_row($res);
	if(empty($row['activity_bundle_id'])) { $row['activity_bundle_id'] = false; }
	if(empty($row['activity_map_id'])) { $row['activity_map_id'] = false; }
	return $row;
}

function get_activity_map_id_from_bundle_id($activity_bundle_id) {
	$q = "
		SELECT
			activity_bundles.activity_map_id
		FROM activity_bundles
		WHERE activity_bundles.id = '". $activity_bundle_id ."'
	";
	$res = db_query($q,"Getting Bundle ID");
	if(db_is_error($res) || db_num_rows($res) == 0) {
		return false;
	}
	$row = db_fetch_row($res);
	if(empty($row['activity_map_id'])) { $row['activity_map_id'] = false; }
	return $row['activity_map_id'];
}

function audit_function($table) {

	$is_classroom = (!empty($_REQUEST['playasaclassroom']) ? 't' : 'f');
	if($is_classroom == 'f') {
		$is_classroom = (!empty($_REQUEST['is_classroom']) ? 't' : 'f');
	}

	$student_id = (!empty($_REQUEST['child']) ? $_REQUEST['child'] : 0);
	if(empty($student_id)) {
		$student_id = (!empty($_REQUEST['student_id']) ? $_REQUEST['student_id'] : 0);
	}

	$user_id = (!empty($_REQUEST['parent']) ? $_REQUEST['parent'] : 0);
	if(empty($user_id)) {
		$user_id = (!empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0);
	}

	$activity_id = (!empty($options['activity_id']) ? $options['activity_id'] : 0);
	if(empty($activity_id)) {
		$activity_id = (!empty($options['game']) ? $options['game'] : 0);
	}

	$ip = (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
	$uri = (!empty($_SERVER['SCRIPT_URI']) ? $_SERVER['SCRIPT_URI'] : '');
	$referrer = (!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
	$user_agent = (!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');

	$ipv4 = 0;
	$ipv6 = 0;
	if(strpos($ip,":") !== false && strpos($ip,".") === false) {
		$ipv6 = $ip;
	} else {
		$ipv4 = $ip;
	}

	parse_str($_SERVER['QUERY_STRING'],$params);

	$q = "
		insert into ". $table ." (
			is_classroom
			,student_id
			,user_id
			,activity_id
			,ipv4
			,ipv6
			,uri
			,referrer
			,user_agent
			,params
			,server_details
		) values (
			'". $is_classroom ."'
			,'". (int)$student_id ."'
			,'". (int)$user_id ."'
			,'". (int)$activity_id ."'
			,'". db_prep_sql($ipv4) ."'
			,'". db_prep_sql($ipv6) ."'
			,'". db_prep_sql($uri) ."'
			,'". db_prep_sql($referrer) ."'
			,'". db_prep_sql($user_agent) ."'
			,'". db_prep_sql(json_encode($params)) ."'
			,'". db_prep_sql(json_encode($_SERVER)) ."'
		)
	";
	$res = db_query($q,"Auditing API call");
	if(db_is_error($res)) {
		return false;
	}
	return true;
}

function output_xml($output) {
	$output = str_replace("\n","",$output);
	$output = str_replace("\t","",$output);
	$output = preg_replace("/\s\s+/"," ",$output);
	$output = str_replace("> <","><",$output);
	echo $output;
}

function xml_prep($val) {
	// $val = htmlspecialchars($val);
	// $val = str_replace('<','&lt;',$val);
	// $val = str_replace('>','&gt;',$val);
	// $val = str_replace('"','&quot;',$val);
	// $val = str_replace("'",'&#39;',$val);
	// $val = str_replace("&",'&amp;',$val);
	return $val;
}

function get_error_list() {
	return $error_list = array(
		"world" => array(
			"id" => 1001
			,"message" => "World information is missing"
			,"invalid" => "World information is invalid"
			,"required" => true
			,'array' => true
		)
		,"theme" => array(
			"id" => 1002
			,"message" => "Theme information is missing"
			,"invalid" => "Theme information is invalid"
			,"required" => true
			,'array' => true
		)
		,"genre" => array(
			"id" => 1003
			,"message" => "Genre information is missing"
			,"invalid" => "Genre information is invalid"
			,"required" => false
			,'array' => true
		)
		,"generation" => array(
			"id" => 1004
			,"message" => "Generation information is missing"
			,"invalid" => "Generation information is invalid"
			,"required" => true
			,'array' => true
		)
		,"grade" => array(
			"id" => 1005
			,"message" => "Grade information is missing"
			,"invalid" => "Grade information is invalid"
			,"required" => true
			,'array' => true
		)
		,"game" => array(
			"id" => 1006
			,"message" => "Game information is missing"
			,"invalid" => "Game information is invalid"
			,"optional" => true
			,"required" => true
		)
		,"activity_id" => array(
			"id" => 1006
			,"message" => "Game information is missing"
			,"invalid" => "Game information is invalid"
			,"optional" => true
			,"required" => true
		)
		,"student_id" => array(
			"id" => 1010
			,"message" => "Student ID is missing"
			,"invalid" => "Student ID is invalid"
			,"required" => true
		)
		,"child" => array(
			"id" => 1010
			,"message" => "Student ID is missing"
			,"invalid" => "Student ID is invalid"
			,"required" => true
		)
		,"user_id" => array(
			"id" => 1011
			,"message" => "User ID is missing"
			,"invalid" => "User ID is invalid"
			,"optional" => true
			,"required" => true
		)
		,"parent" => array(
			"id" => 1011
			,"message" => "User ID is missing"
			,"invalid" => "User ID is invalid"
			,"optional" => true
			,"required" => true
		)
		,"score" => array(
			"id" => 1020
			,"message" => "Score is missing"
			,"invalid" => "Score is invalid"
			,"required" => true
		)
		,"question" => array(
			"id" => 1021
			,"message" => "Question ID is missing"
			,"invalid" => "Question ID is invalid"
			,"required" => true
		)
		,"question_id" => array(
			"id" => 1021
			,"message" => "Question ID is missing"
			,"invalid" => "Question ID is invalid"
			,"required" => true
		)
		,"playasaclassroom" => array(
			"id" => 1022
			,"message" => "Is Classroom information is missing"
			,"invalid" => "Is Classroom information is invalid"
			,"required" => false
		)
		,"is_classroom" => array(
			"id" => 1022
			,"message" => "Is Classroom information is missing"
			,"invalid" => "Is Classroom information is invalid"
			,"required" => false
		)
		,"reps" => array(
			"id" => 1030
			,"message" => "Reps information is missing"
			,"invalid" => "Reps information is invalid"
			,"required" => false
		)
		,"version" => array(
			"id" => 1040
			,"message" => "Version information is missing"
			,"invalid" => "Version information is invalid"
			,"required" => false
		)
		,"bundle_id" => array(
			"id" => 1050
			,"message" => "Bundle ID information is missing"
			,"invalid" => "Bundle ID information is invalid"
			,"required" => true
		)
	);

}


function error($msg = "") {
	echo "An error occured" .(!empty($msg) ? ": ". $msg : '');
}

