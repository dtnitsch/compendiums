<?php
########################################################################
#	Useful Functions
########################################################################
function get_user_id() {
	return (!empty($_SESSION["user"]["id"]) ? $_SESSION["user"]["id"] : 0);
} 
function get_page_id($param = "id") {
	$id = (int)(!empty($_GET[$param]) ? $_GET[$param] : 0);
	return ((string)$id === trim($_GET[$param]) ? $id : 0);
}
function get_url_param($param) {
	return (isset($_GET[$param]) ? $_GET[$param] : false);
}
function get_url() {
    if (!empty($_SERVER["REQUEST_URI"])) { return $_SERVER["REQUEST_URI"]; }
   	$url = $_SERVER["SCRIPT_NAME"];
   	return $url .= (!empty($_SERVER["QUERY_STRING"]) ? "?" . $_SERVER[ "QUERY_STRING" ] : "");
}
function dump($data,$pre=1) {
	ob_start();
    if(!empty($pre)) { echo "<pre>"; }
    if(is_array($data)) { print_r($data); } else { var_dump($data); }
    if(!empty($pre)) { echo "</pre>"; }
    return ob_get_clean();
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
	$val = @iconv("UTF-8","UTF-8//TRANSLIT//IGNORE",$val);
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
#	Session Functions
########################################################################
function start_session() {
	_error_debug(__FUNCTION__."()");
	if(!empty($GLOBALS["session_options"]["use_db"])) {
		$type = (!empty($GLOBALS["session_options"]["db_type"]) ? $GLOBALS["session_options"]["db_type"] : 'mysqli');
		library("sessions_". $type .".php");
		session_set_save_handler("_session_open", "_session_close","_session_read", "_session_write","_session_destroy", "_session_gc");
	}
	session_name($GLOBALS["project_info"]["alias"]);
	session_start(); 
}

########################################################################
#	Run the modules
########################################################################
function run_module($module_name,$folder="") {
	_error_debug(__FUNCTION__."(): ". $module_name);

	$folder = ($folder != "" ? rtrim($folder,"/")."/" : "modules/");
	$file = $GLOBALS["root_path"] . $folder . $module_name .".php";
	//_error_debug("FILE PATH",$file);
	if(is_file($file)) {
		ob_start();
		include($file);
		return ob_get_clean();
	} else {
		_error_debug("File does not exist",$file,__LINE__,__FILE__,E_ERROR);
	}
	return false;
}


########################################################################
#	Safe Redirecting
########################################################################
function safe_redirect($url = null, $permanent=0) {
	_error_debug(__FUNCTION__."()");

	session_write_close();
	$url = (($url == NULL) ? $_SERVER["REQUEST_URI"] : $url);

	if($permanent) { 
		header("HTTP/1.0 301 Moved Permanently");
	}
	header("Location: " . $url);
	die();
}

function set_safe_redirect($url) {
	_error_debug(__FUNCTION__."()");
	if(empty($_SESSION["post_information"]["redirect"])) { $_SESSION["post_information"]["redirect"] = $url; }
}


########################################################################
#	Loading path data from the URL
########################################################################
function load_path_data($path) {

	if(!empty($GLOBALS["routing_options"]["db"])) {	
		// $q = "select * from system.paths where path ilike '". $path ."'";
		$q = "select *,regexp_matches('". $path ."',path)::text from system.paths where '". $path ."' similar to path";
		$res = db_fetch($q,"Load Path Information");
	} else {
		include("../routes/". md5($path) .".php");
	}

	if(empty($res)) {
		header("HTTP/1.0 404 Not Found");
		include("404.php");
	}

	$GLOBALS['project_info']['title'] = (!empty($res['title']) ? $res['title'] : ''); 
	$GLOBALS['project_info']['path_data'] = $res;

	return $res;
}

function uses_schema() {
	if(isset($GLOBALS["db_options"]["main_connections"]["uses_schema"])) {
		return $GLOBALS["db_options"]["main_connections"]["uses_schema"];
	}
	$type = trim($GLOBALS["db_options"]["main_connections"]["default"]["type"]);
	$type = strtolower($type);
	if($type == "pgsql" || $type == "postgres" || $type == "postgresql") {
		$GLOBALS["db_options"]["main_connections"]["uses_schema"] = true;
		return true;
	}
	$GLOBALS["db_options"]["main_connections"]["uses_schema"] = false;
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
#	Error / Info / Debug messages to be dumped to the screen!
########################################################################
function dump_messages() {
	_error_debug(__FUNCTION__."()");

	$output = "";
	$msgs = array("debug","info","error","warning");
	foreach($msgs as $msg) {
		if(!empty($_SESSION[$msg ."_messages"])) {
			foreach($_SESSION[$msg ."_messages"] as $message) {
				$output .= show_message($msg,$message);
			}
			unset($_SESSION[$msg ."_messages"]);
		}
	}

	return $output;
}
function show_message($type,$text) {
	return '<div class="'. $type .'_message"><div class="float_right"><a href="javascript:void(0);" title="Remove This" onclick="remove_this(this);" class="remove"></a></div>'. $text .'</div>';
}
function debug_message($msg="") {
	if(is_array($msg)) { array_messages($msg,__FUNCTION__); }
	else if(empty($msg)) { return (!empty($_SESSION["debug_messages"]) ? count($_SESSION["debug_messages"]) : 0); }
	else { $_SESSION["debug_messages"][] = $msg; }
}
function info_message($msg="") {
	if(is_array($msg)) { array_messages($msg,__FUNCTION__); }
	else if(empty($msg)) { return (!empty($_SESSION["info_messages"]) ? count($_SESSION["info_messages"]) : 0); }
	else { $_SESSION["info_messages"][] = $msg; }
}
function error_message($msg="") {
	if(is_array($msg)) { array_messages($msg,__FUNCTION__); }
	else if(empty($msg)) { return (!empty($_SESSION["error_messages"]) ? count($_SESSION["error_messages"]) : 0); }
	else { $_SESSION["error_messages"][] = $msg; }
}
function highlight_message($msg="") {
	if(is_array($msg)) { array_messages($msg,__FUNCTION__); }
	else if(empty($msg)) { return (!empty($_SESSION["highlight_messages"]) ? count($_SESSION["highlight_messages"]) : 0); }
	else { $_SESSION["highlight_messages"][] = $msg; }
}
function array_messages($arr,$func) {
	if(!empty($arr) && is_array($arr)) {
		foreach($arr as $msg) {
			$func($msg);
		}
	}
}


########################################################################
#	Include/Sort CSS
########################################################################
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
function add_css_code($val,$priority=100) {
	if(empty($GLOBALS["include_css"]["code"])) { $GLOBALS["include_css"]["code"] = array(); }
	$GLOBALS["include_css"]["code"][$priority][] = $val;
}
function show_css_code() {
	if(empty($GLOBALS["include_css"]["code"])) { return; }
	$tmp = $GLOBALS["include_css"]["code"];
	asort($tmp);
	$output = "";
	foreach($tmp as $a) {
		foreach($a as $row) {
			$output .= $row;
		}
	}
	return $output;
}
function template_css() {
	$output = "";
	if(!empty($GLOBALS["include_css"]["links"])) {
		asort($GLOBALS["include_css"]["links"]);
		foreach(array_keys($GLOBALS["include_css"]["links"]) as $css) {
			$output .= "\t" . '<link href="'. $css .'" type="text/css" rel="stylesheet" media="screen" />' . "\n";
		}
	}
	if(!empty($GLOBALS["include_css"]["files"])) {
		asort($GLOBALS["include_css"]["files"]);
		foreach(array_keys($GLOBALS["include_css"]["files"]) as $css) {
			$output .= "\t" . '<link href="'. $css .'" type="text/css" rel="stylesheet" media="screen" />' . "\n";
		}
	}
	return $output;
}
########################################################################
#	Include/Sort JS
########################################################################
function add_js($val,$priority=100,$path="") {
	$path = trim($path);
	if(empty($path)) { $path = "/js/"; }
	$is_file = false;
	if(is_file($_SERVER["DOCUMENT_ROOT"].$path.$val)) {
#		$modified_time = get_modified_time($_SERVER["DOCUMENT_ROOT"].$path.$val);
#		$version = ($modified_time ? date("ynjGm",$modified_time) : '1');
		$version = "";
		$filename = $path.substr($val,0,-3).$version.".js";
		$GLOBALS["include_javascript"]["files"][$filename] = $priority;
		$is_file = true;
	}
	else { $GLOBALS["include_javascript"]["links"][$val] = $priority; }
	if($path == "/js/" && !$is_file && strpos($val,'//') === false) {
		_error_debug("Missing JS File: ". $val,$val,'','',E_WARNING);
	}
}
function add_js_code($val,$priority=100) {
	if(empty($GLOBALS["include_javascript"]["code"])) { $GLOBALS["include_javascript"]["code"] = array(); }
	$GLOBALS["include_javascript"]["code"][$priority][] = $val;
}
function show_js_code() {
	if(empty($GLOBALS["include_javascript"]["code"])) { return; }
	$tmp = $GLOBALS["include_javascript"]["code"];
	asort($tmp);
	$output = "";
	foreach($tmp as $a) {
		foreach($a as $row) {
			$output .= $row;
		}
	}
	return $output;
}
function template_js() {
	$output = "";
	if(!empty($GLOBALS["include_javascript"]["links"])) {
		$tmp =& $GLOBALS["include_javascript"]["links"];
		asort($tmp);
		foreach(array_keys($tmp) as $js) {
			$output .= "\t" . '<script src="'. $js .'" type="text/javascript"></script>' . "\n";
		}
	}
	if(!empty($GLOBALS["include_javascript"]["files"])) {
		$tmp =& $GLOBALS["include_javascript"]["files"];
		asort($tmp);
		foreach(array_keys($tmp) as $js) {
			$output .= "\t" . '<script src="'. $js .'" type="text/javascript"></script>' . "\n";
		}
	}
	return $output;
}

function get_modified_time($filename) {
	if (file_exists($filename)) {
	    return filemtime($filename);
	}
	return false;
}
########################################################################
#	On Body (Un)Load
########################################################################
function template_onload($type = "load") {
	$output = "";
	if(isset($GLOBALS["body_on".$type])) { 
		$output = " on". $type .'="';
		asort($GLOBALS["body_on". $type]);
		foreach(array_keys($GLOBALS["body_on". $type]) as $row) { $output .= $row.";"; }
		$output .= '"';
	}
	return $output;
}
function template_onunload() {
	return template_onload("unload");
}

########################################################################
#	Post Functions
########################################################################
function post_queue($module_file,$folder="") {
	if(empty($folder)) {
		$module_file = "modules/post_files/". $module_file .".post.php";
	} else {
		$folder = rtrim($folder,"/");
		$module_file = $folder ."/". $module_file .".post.php";
	}
	if(empty($_SESSION["post_information"])) { $_SESSION["post_information"] = array(); }
	if(empty($_SESSION["post_information"]["module_queue"])) { $_SESSION["post_information"]["module_queue"] = array(); }
	$_SESSION["post_information"]["module_queue"][$module_file] = 1;
}

function load_post_queue() {
	if(!empty($_SESSION["post_information"]["module_queue"])) {
		$_SESSION["post_modules"] = json_encode($_SESSION["post_information"]["module_queue"]);
		unset($_SESSION["post_information"]["module_queue"]);
	}
}
function remove_post_modules() {
	unset($_SESSION["post_information"]);
	unset($_SESSION["post_modules"]);
}

########################################################################
#	Referer Redirecting
########################################################################
function back_redirect() {
	_error_debug(__FUNCTION__."()");
die("??");
	info_message("Redirected due to security permissions.");
	if(!empty($_SERVER["HTTP_REFERER"])) {
		// if(strpos($_SERVER["HTTP_REFERER"], $GLOBALS["project_info"]["dns"]) !== false) {
			safe_redirect($_SERVER["HTTP_REFERER"]);
		// }
	}
	safe_redirect("/acu/");
	die();
}



########################################################################
#	run_dynamic_module
########################################################################
function run_dynamic_module($path_data) {
	_error_debug(__FUNCTION__."()");

	if(!empty($path_data['redirect']) && !empty($path_data['path_redirect'])) {
		safe_redirect($path_data['path_redirect'],true);
	}


	ob_start();
	include($GLOBALS["root_path"] . "modules/dynamic_web_pages.php");
	$body =  ob_get_clean();

	library('dynamic_content_replacements.php');

	$body = dynamic_replacements($body);

	return $body;
}



########################################################################
#	Session Timeout
########################################################################
function check_session_timeout() {
	_error_debug(__FUNCTION__."()");

	if(!empty($_SESSION["last_access_time"]) && !empty($GLOBALS["session_options"]["session_life"])) {
		if(
			($_SESSION["last_access_time"] + $GLOBALS["session_options"]["session_life"]) < time()
			&& !isset($_GET["logout"])
			&& !isset($_GET["login"])
			&& !empty($_SESSION["user"]["id"])
		) {

			library("membership.php");
			user_logout();

			info_message("You have been logged out due to inactivity");

			$t = $_GET;
			safe_redirect(!empty($t) ? "?". http_build_query($t) : "");
		}
	}

	$_SESSION["last_access_time"] = time();
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




########################################################################
#	Security Check, check library/security.php
########################################################################
function has_access($aliases) {
	# Security Enabled?
	if(empty($GLOBALS["security_options"]["enabled"])) {
		$pieces = explode(',',$aliases);
		if(empty($pieces[1])) { return true; }
		$results = array();
		foreach($pieces as $k) {
			$results[$k] = true;
		}
		return $results;
	}
	// if(!empty($_SESSION["user"]["is_superadmin"])) { return true; }
	library("security.php");
	return module_security(strtolower($aliases));
}

function logged_in() {
	if(!empty($_SESSION["user"]["id"])) { return true; }
	return false;
}
function is_superadmin() {
	if(!empty($_SESSION["user"]["is_superadmin"])) { return true; }
	return false;
}



########################################################################
#	Site Wide Notes
########################################################################
function site_wide_notes($val,$path,$id) {
	if(empty($GLOBALS["site_wide_notes"]["enabled"])) { return false; }
	library("site_wide_notes.php");
	$func = "site_wide_notes_".$val;
	return $func($path,$id);
}


########################################################################
#	select builder
########################################################################
function select_builder($res,$name,$options) {
	$key = (!empty($options['key']) ? $options['key'] : "id");
	$label = (!empty($options['label']) ? $options['label'] : "title");
	$display = (!empty($options['display']) ? $options['display'] : ucfirst($name));
	$selected = (!empty($options['selected']) ? $options['selected'] : "");

	$output = '<select name="'. $name .'" id="'. $name .'">';
	$output .= '<option value="">'. $display .'</option>';
	if(!empty($res['dbname'])) {
		while($row = db_fetch_row($res)) {
			$output .= '<option value="'. $row[$key] .'">'. $row[$label] .'</option>';
		}
	} else {
		foreach($res as $row) {
			$output .= '<option value="'. $row[$key] .'">'. $row[$label] .'</option>';
		}
	}
	$output .= '</select>';

	if(!empty($selected)) {
		$output = str_replace(' value="'. $selected .'"',' value="'. $selected .'" selected',$output);
	}
	return $output;
}