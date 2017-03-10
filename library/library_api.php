<?php

########################################################################
#	Useful Functions
########################################################################

function get_url_param($param) {
	return (!isset($_GET[$param]) ? $_GET[$param] : false);
}
function get_url() {
    if (!empty($_SERVER["SCRIPT_URI"])) { return $_SERVER["SCRIPT_URI"]; }
    if (!empty($_SERVER["REQUEST_URI"])) { return $_SERVER["REQUEST_URI"]; }
   	$url = $_SERVER["SCRIPT_NAME"];
   	return $url .= (!empty($_SERVER["QUERY_STRING"]) ? "?" . $_SERVER[ "QUERY_STRING" ] : "");
}
function fix_encoding($val) {
    $search = array(
		chr(130)
		,chr(132)
		,chr(133)
		,chr(145)
		,chr(146)
		,chr(147)
		,chr(148)
		,chr(151)
	); 

    $replace = array(
		"," 
		,'"' 
		,"..." 
		,"'" 
		,"'" 
		,'"' 
		,'"' 
		,'-'
	); 
    $val = str_replace($search, $replace, $val); 
	$val = iconv("UTF-8","UTF-8//TRANSLIT//IGNORE",$val);
	return $val;
}
function remove_newlines($val) {
	$val = str_replace("\n", '', $val);
	$val = str_replace("\r", '', $val);
	return str_replace("\r\n", '', $val);
}

########################################################################
#	Load from Library
########################################################################
function library($filename,$path = "",$include_once = true) {
	if(empty($filename)) { return false; }
	// only include things once, unless overwritten
	$path = trim($path);
	if(empty($path)) { $path = $GLOBALS["root_path"] ."library/"; } 
	if($include_once == true && !empty($GLOBALS["included_files"][$path.$filename])) {
		return true;
	}
	if(is_file($path . $filename)) {
		// removed the ":"include once" for performance gains
		
		if(include($path . $filename)) {
			$GLOBALS["included_files"][$path.$filename] = true;
			return true;
		}
		
	}
	return false;
}

########################################################################
#	Get Requested Path
########################################################################
function get_requested_path() {
	_error_debug(__FUNCTION__."()");

	$path = get_url();
	$strpos = strpos($path,"?");
	$params = "";
	if($strpos) {
		list($path,$params) = explode("?",$path);
	}

	# Clean the URL - Add a "/" to the END
	if(substr($path, -1, 1) != "/" && substr($path, -4, 1) != "." && substr($path, -5, 1) != "." && empty($strpos)) {
		safe_redirect($path ."/");
	}

	audit("page_hits",array("path" => $path,"params" => $params));
	return($path);
}

########################################################################
#	Audit Information
########################################################################
function audit($name,$params) {
	_error_debug("Audit: ". $name ."()");
	if(empty($GLOBALS["audit_options"]["enabled"])) { return false; }
	library("audit_functions.php");
	$function = strtolower("audit_".$name);
	return $function($params);
}


function api_generate_secret_key() {
	return trim(base64_encode(hash_hmac('sha256', 'API_KEY'.microtime(), time(), true)),'=');
}

function buildMessage($time, $id, array $data) {
	return $time . $id . implode($data);
}


function add_css($val,$priority=100,$path="") {
	$path = trim($path);
	if(empty($path)) { $path = "/css/"; } 
	$is_file = false;
	if(is_file($_SERVER["DOCUMENT_ROOT"].$path.$val)) {
#		$modified_time = get_modified_time($_SERVER["DOCUMENT_ROOT"].$path.$val);
#		$version = ($modified_time ? date("ynjGm",$modified_time) : '1');
		$version = "";
		$filename = $path.substr($val,0,-4).$version.".css";
		$GLOBALS["include_css"]["files"][$filename] = $priority;
		$is_file = true;
	}
	else { $GLOBALS["include_css"]["links"][$val] = $priority; }
	if($path == "/css/" && !$is_file && strpos($val,'//') === false) {
		_error_debug("Missing CSS File: ". $val,$val,'','',E_WARNING);
	}
}
function dump($data,$pre=1) {
	ob_start();
    if(!empty($pre)) { echo "<pre>"; }
    if(is_array($data)) { print_r($data); } else { var_dump($data); }
    if(!empty($pre)) { echo "</pre>"; }
    return ob_get_clean();
}