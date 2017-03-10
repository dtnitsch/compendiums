<?php
/*
	name = name of the Post value or variable
	validators = int, str, email, etc.
	type = type of variable.  post is default
*/
$GLOBALS["validation_messages"] = "";
function validate($value,$validators,$label = "",$id = "",$msgs = array()) {
	if(empty($label)) { $label = $value; }
	if(is_array($validators)) {
		foreach($validators as $row) {
			if(validate_check($value,$row,$label,$id,$msgs) == false) { return false; }
		}
	} else {
		if(validate_check($value,$validators,$label,$id,$msgs) == false) { return false; }	
	}
	return true;
}
function validate_check($value,$validator,$label,$id,$msgs) {
	$params = null;
	if(strpos($validator, ":") !== false) {
		list($validator,$params) = explode(":",$validator);
	}
	$validator = strtolower($validator);
	if($validator == "equal") {
		$GLOBALS["validation_equal_check"][$id] = explode(",",$params);
	}
	if(function_exists("validate_".$validator) || $GLOBALS["validation_custom_function"][$validator]) {
		$function = (function_exists("validate_".$validator) ? "validate_".$validator : $GLOBALS["validation_custom_function"][$validator]);
		if($function($value,$params) == false) { 
			$msg = validate_messages($validator,$label,$msgs);
			if(!empty($params)) {
				if(strpos($msg,"{valx}") !== false) {
					$params = explode(",",$params);
					$params = "'". implode("', or '",$params) ."'";
					$msg = str_replace("{valx}",$params,$msg);
				} else if(strpos($msg,"{dynamic}") !== false) {
					$params = explode(",",$params);
					$val = get_validation_dynamic_variable($params[0],$params[1]);
					$msg = str_replace("{dynamic}",$val,$msg);
				} else {
					$params = explode(",",$params);
					$i = 1;
					foreach($params as $param) {
						$msg = str_replace("{val". $i++ ."}",$param,$msg);
					}	
				}
				
			}
			// ERROR_MESSAGE($msg);
			// echo $msg;
			$GLOBALS["validation_message"] = $msg;
			$GLOBALS["validation_messages"][$validator] = $msg;
			return false;			
		}
	} else {
		// ERROR_MESSAGE("Validation Failed: "". $validator ."" is not a valid validation type");
		// echo "Validation Failed: "". $validator ."" is not a valid validation type";
		$GLOBALS["validation_messages"][$validator] = "Validation Failed: '". $validator ."' is not a valid validation type";
		return false;			
	}
	return true;
}
function get_validation_dynamic_variable($type,$key) {
	if($type == 'global' && defined($key)) { return constant($key); }
	if($type == 'post' && !empty($_POST[$key])) { return $_POST[$key]; }
	if($type == 'get' && !empty($_GET[$key])) { return $_GET[$key]; }
	if($type == 'session' && !empty($_SESSION[$key])) { return $_SESSION[$key]; }
	if($type == 'server' && !empty($_SERVER[$key])) { return $_SERVER[$key]; }
	return false;
}
function get_validation_error() {
	return $GLOBALS['validation_message'];
}
function get_all_validation_errors() {
	return $GLOBALS['validation_messages'];
}
function validate_labels($arr) {
	$GLOBALS['validation']['labels'] = $arr;
}
function validate_custom_messages($arr) {
	$GLOBALS['validation']['custom_messages'] = $arr;
}
function validate_messages($validator,$label,$msgs) {
	if(!empty($msgs) && $msgs[$validator]) {
		return str_replace('{label}',$label,$msgs[$validator]);
	}
	if(!empty($GLOBALS['validation']['custom_messages'][$validator])) {
		return str_replace('{label}',$label,$GLOBALS['validation']['custom_messages'][$validator]);
	}
	$msgs = array(
		"required" => "'{label}' is a required field",
		"string_length" => "'{label}' does not match the string length of {val1}",
		"string_length_min" => "'{label}' does not meet the minimum length of {val1}",
		"string_length_max" => "'{label}' exceeds the maximum length of {val1}",
		"string_length_between" => "'{label}' must be between {val1} and {val2} characters",
		"integer" => "'{label}' is not a valid integer",
		"int" => "'{label}' is not a valid integer",
		"int_min" => "'{label}' must be greater than {val1} ",
		"int_max" => "'{label}' must be less than {val1} ",
		"int_between" => "'{label}' must be between {val1} and {val2} ",
		"integer_between" => "'{label}' must be between {val1} and {val2} ",
		"match" => "'{label}' does not match {val1}",
		"compare" => "'{label}' does not match {val1}",
		"equal" => "'{label}' does not correctly equal {dynamic}",
		"numeric" => "'{label}' is not a valid number",
		"numeric_between" => "'{label}' must be between {val1} and {val2} ",
		"string" => "'{label}' is not a valid string",
		"str" => "'{label}' is not a valid string",
		"alpha" => "'{label}' is not a valid alpha-only string",
		"alphanum" => "'{label}' is not a valid alphanumeric only string",
		"alphanumeric" => "'{label}' is not a valid alphanumeric only string",
		"array" => "'{label}' is not a valid array",
		"bool" => "'{label}' is not a valid boolean value",
		"valid_bool" => "'{label}' is not a valid boolean value",
		"datetime" => "'{label}' is not a valid datetime",
		"email" => "'{label}' is not a valid email address",
		"json" => "'{label}' is not valid JSON format",
		"in" => "'{label}' is not within accepted values {valx} ",
		"regex" => "'{label}' does not match the regular expression requirements",
		"regularexpression" => "'{label}' does not match the regular expression requirements",
		""=>"'{label}'"
	);
	return (!empty($msgs[$validator]) ? str_replace("{label}",$label,$msgs[$validator]) : "");
}

// function validate_required($value) {
// 	return (isset($value) && !is_null($value) ? true : false);
// }
function validate_required($value) {
	return (!empty($value) ? true : false);
}
function validate_string_length($value,$params) {
	$value = (string)$value;
	return (isset($value[$params-1]) && !isset($value[$params]) ? true : false);
}
function validate_string_length_min($value,$params) {
	return (!isset($value[$params-1]) ? false : true);
}
function validate_string_length_max($value,$params) {
	return (isset($value[$params]) ? false : true);
}
function validate_string_length_between($value,$params) {
	list($min,$max) = explode(",",$params);
	return (!isset($value[$min-1]) || isset($value[$max]) ? false : true);
}

function validate_int($value) { return validate_integer($value); }
function validate_integer($value) {
	#return ((!is_numeric($value) || (int)$value !== $value) ? false : true); 
	return (is_numeric($value) ? true : false); 
}
function validate_int_between($value,$params) { return validate_integer_between($value,$params); }
function validate_integer_between($value,$params) {
	list($min,$max) = explode(",",$params);
	return (($value < $min || $value > $max) ? false : true);
}

function validate_int_min($value,$params) { return validate_integer_min($value,$params); }
function validate_integer_min($value,$params) {
	return ($value >= $params ? false : true);
}

function validate_int_max($value,$params) { return validate_integer_max($value,$params); }
function validate_integer_max($value,$params) {
	return ($value <= $params ? false : true);
}

function validate_match($value, $params) { return ($value == $params ? true : false); }
function validate_compare($value, $params) { return validate_match($value, $params); }

function validate_equal($value, $params) {
	list($type,$key) = explode(",",$params);
	$val = get_validation_dynamic_variable($type,$key);
	return ($value == $val ? true : false);
}


function validate_numeric($value) { return (is_numeric($value) ? true : false);  }
function validate_numeric_between($value,$params) { 
	list($min,$max) = explode(",",$params);
	return (($value < $min || $value > $max) ? false : true);
}


function validate_str($value) { return validate_string($value); }
function validate_string($value) {
	return ((!is_string($value) || (string)$value != $value) ? false : true);
}

function validate_alpha($value) { return (ctype_alpha($value) ? true : false); }
function validate_alphanum($value) { return (ctype_alnum($value) ? true : false); }
function validate_alphanumeric($value) { return validate_alphanum($value); }

function validate_array($value) { return (is_array($value) ? true : false); }

function validate_bool($value,$params = true) {
	if(!validate_valid_bool($value)) { return false; }
	if(!empty($params)) {
		return ($value == $params ? true : false);
	}
	return true;
}
function validate_valid_bool($value) {
	$value = trim($value);
	if($value == "1" || $value == "0") {
		return true;
	} else {
		$value = strtolower($value);
		if(in_array($value,array("true","false","t","f","yes","no","y","n","on","off"))) {
			return true;
		}
	}
	return false;
}

function validate_date($value) { 
	$date = strtotime($value);
	return ($date == 0 ? false : true);
}

function validate_email($value) { 
	return (preg_match("/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i",$value) ? true : false);
}

function validate_json($value) { 
	$data = json_decode($value);
	return (json_last_error() !== JSON_ERROR_NONE ? false : true);
}

function validate_in($value,$params) {
	$params = explode(",",$params);
	return (in_array($value,$params) ? true : false);
}

function validate_datetime($value) { return (strtotime($value) ? true : false); }

function validate_regex($value,$params) {
	return (preg_match($params,$value) ? true : false);
}
function validate_regularexpression($value,$params) { 
	return validate_regex($value,$params);
}




function validation_load_file($file) {
	if(empty($file) || !is_file($file)) { 
		$GLOBALS["validation_messages"][] = "'". $file ."' is missing";
		return false;
	}
	$json = file_get_contents($file);
	$json = json_decode($json,true);
	// if(json_last_error()) { 
	// 	$GLOBALS["validation_messages"][] = "json_last_error()";
	// 	return false;
	// }
	return $json;
}
function validate_from_json($json) {
	if(empty($json)) {
		return false;
	}
	if(!is_array($json)) {
		$json = json_decode($json,true);
	}

	foreach($json as $k => $row) {
		$success = true;
		// if(!empty($row["language"])) {
		// 	$language = strtolower($row["language"]);
		// 	if(is_array($language)) {
		// 		if(!in_array($language,array('all','php'))) {
		// 			continue;
		// 		}
		// 	} else if($language != "all" && $language != "php") {
		// 		continue;
		// 	}
		// }
		if(empty($row["id"])) {
			continue;
		}
		if(empty($row["type"])) {
			$row["type"] = 'post';
			// $GLOBALS["validation_messages"][] = "'". $k ."' is missing a 'type'";
			// $success = false;
		}
		if(empty($row["validators"])) {
			$GLOBALS["validation_messages"][] = "'". $row["id"] ."' is missing 'validators' options";
			$success = false;
		}
		if($success) {
			validate_json_array($k,$row);
		}
	}
	// if(!empty($GLOBALS["validation_equal_check"])) {
	// 	validate_from_json
	// }
}
function validate_json_array($index,$row) {
	$type = (!empty($row["type"]) ? strtolower($row["type"]) : "post");
	$val = get_validation_value($row["id"],$type);
	if($val !== false) {
		if(!empty($row["optional"]) && empty($val)) {
			return true;
		}
		$label = (!empty($row['label']) ? $row['label'] : $row["id"]);
		$msgs = (!empty($row['messages']) ? $row['messages'] : array());
		validate($val,$row['validators'],$label,$row["id"],$msgs);
	}
}
function get_validation_value($k,$type) {
	if($type == "post") {
		if(!isset($_POST[$k])) {
			$GLOBALS["validation_messages"][$k] = "POST['". $k ."'] doesn't exist";
			return false;
		}
		return $_POST[$k];
	
	} else if($type == "get") {
		if(!isset($_GET[$k])) {
			$GLOBALS["validation_messages"][$k] = "GET['". $k ."'] doesn't exist";
			return false;
		}
		return $_GET[$k];
	
	} else if($type == "session") {
		if(!isset($_SESSION[$k])) {
			$GLOBALS["validation_messages"][$k] = "SESSION['". $k ."'] doesn't exist";
			return false;
		}
		return $_SESSION[$k];
	
	} else if($type == "server") {
		if(!isset($_SERVER[$k])) {
			$GLOBALS["validation_messages"][$k] = "SERVER['". $k ."'] doesn't exist";
			return false;
		}
		return $_SERVER[$k];
	
	} else if($type == "array") {
		if(!isset($GLOBALS["validation_custom_array"][$k])) {
			$GLOBALS["validation_messages"][$k] = "Array['". $k ."'] doesn't exist!";
			return false;
		}
		return $GLOBALS["validation_custom_array"][$k];
	}
	return false;
}
function validation_set_custom_array($array) {
	if(empty($array)) {
		$GLOBALS["validation_messages"][] = "Custom array was empty";
		return false;
	}
	$GLOBALS["validation_custom_array"] = $array;
	return true;
}
function validation_create_json_string($json,$type = "") {
	if(empty($json)) {
		return false;
	}
	$output = array();
	foreach($json as $k => $row) {
		$success = true;
		if(empty($row["id"]) && !empty($row["messages"])) {
			foreach($row["messages"] as $k => $v) {
				$GLOBALS['validation']['custom_messages'][$k] = $v;
			}
			// continue;
		} else if(!empty($row["language"])) {
			$language = strtolower($row["language"]);
			if(is_array($language)) {
				if(!in_array($language,array('all',$type))) {
					continue;
				}
			} else if($language != "all" && $language != $type) {
				continue;
			}
		}
		$output[] = $row;
	}
	
	return json_encode($output);
}
function validation_custom($name,$func,$msg = "") {
	$GLOBALS["validation_custom_function"][$name] = $func;
	validate_custom_messages(array($name => $msg));
}