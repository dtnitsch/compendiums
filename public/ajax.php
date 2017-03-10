<?php
date_default_timezone_set("America/New_York");

include("../config/ajax_config.php");
include("../library/library.php");

library("error_handler.php");
error_handler_setup($GLOBALS["debug_options"]["enabled"],"E_ALL");
// if($GLOBALS["debug_options"]["enabled"]) {
// } else {
// 	// create empty function for debug messages if we are not using the debugger
// 	function _error_debug() {}
// 	function ajax_debug() { return null; }
// }

if(!empty($GLOBALS["db_options"])) {
	library("databases/all_in_one.php");
	// db_connect_all();
}

_error_debug("Starting Secure Ajax");

$table = "paths_ajax";
if(uses_schema()) { $table = '"system"."paths_ajax"'; }

# Ajax Path ID
$apid = trim($_REQUEST["apid"]);
$q = "select folder,file,dynamic_variables from ". $table ." where uid = '". $apid ."'";
$info = db_fetch($q,"Ajax File Call");

if(empty($info["folder"])) {
	$info["folder"] = "modules/ajax_files/";
}

$output = '';
if(!empty($info["file"])) {
	$file = $GLOBALS["root_path"] . str_replace("//","/",$info["folder"] . $info["file"]);
	_error_debug("AJAX FILE:".$file);
	if(is_file($file)) {
		ob_start();
		include($file);
		echo trim(ob_get_clean());
	} else {
		ajax_error("File Not Found");
	}
} else {
	ajax_error("No Records Found");
}

function ajax_error($msg) {
	_error_debug($msg,"",__LINE__,__FILE__,E_ERROR);

	$msg = array(
		"output"=>$msg
		,"params" => $_REQUEST
	);
	if($GLOBALS["debug_options"]["enabled"]) {
		$msg["debug"] = ajax_debug();
	}
	$msg = json_encode($msg);
	echo $msg;
}