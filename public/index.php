<?php
date_default_timezone_set("America/New_York");

ob_start();

// Load the configuration file for the whole website
require("../config/development_config.php");

// Functions that are used often
include("../library/library.php");

// Check cache - no session or DB connections needed
// if(!empty($GLOBALS["cache_options"]["enabled"])) {
// 	// apc_clear_cache();
// 	library("cacher.php");
// 	cacher_display();
// }

// Load the error handler to be able to report all site errors
// if($GLOBALS["debug_options"]["enabled"]) {
// 	library("error_handler.php");
// 	error_handler_setup($GLOBALS["debug_options"]["enabled"]);

// 	add_css("error_handler.css",1000); 
// 	add_js("error_handler.js",1000);

// } else {
// 	// create empty function for debug messages if we are not using the debugger
// 	function _error_debug() { return false; }
// }
library("error_handler.php");
error_handler_setup($GLOBALS["debug_options"]["enabled"]);
if($GLOBALS["debug_options"]["enabled"]) {
	add_css("error_handler.css",1000); 
	add_js("error_handler.js",1000);
}



##################################################
#	Database Connections
##################################################
if(!empty($GLOBALS["db_options"])) {
	library("databases/all_in_one.php");
	// db_connect_all();
}


// Enable Sessions - DB Connection might be required
start_session();



##################################################
#	Check for path / location
##################################################
$path = get_requested_path();
$custom = 1;
if(substr($path,0,6) == '/list/') {
	$path_type = "list";
} else if(substr($path,0,12) == '/collection/') {
	$path_type = "collection";
} else {
	$custom = 0;
}
if($custom) {
	$path_data = array(
		"module_name" => $path_type
		,"folder" => ""
		,"template" => "default"
		,"path" => $path
	);
	$GLOBALS['project_info']['path_data'] = $path_data;
} else {
	$path_data = load_path_data($path);	
}
_error_debug("Path Details",$path_data);


// Check for multi_language sites
// if($GLOBALS["debug_options"]["enabled"]) {
// 	LIBRARY("multi_languages.php");
// }

// If logout is set, perform all logout goodness
// if(!empty($_GET["logout"])) {
// 	library("membership.php");
// 	$url = (strpos($path_data['path'],'acu') !== false ? '/acu/' : '/');
// 	user_logout($url);
// }

// Check to see if we need to reload their information, or if we need to log them out
check_session_timeout();

// RUN POST CHECK HERE!!!
if(!empty($_POST) && !empty($_SESSION["post_modules"])) {
	library("post_functions.php");
	perform_post_actions();

	remove_post_modules();
}

// Checks for temporary sessions and updates/removes them
// CHECK_TEMPORARY_SESSIONS();

$template = $path_data["template"];
$template = (!empty($path_data["template"]) ? $path_data["template"] : $GLOBALS["templates"]["default"]);


// Running the main chunk of the code for display
// $body = "";
// if($path_data["is_dynamic"] == "t" && !empty($path_data["dynamic_content_id"])) {
// 	$body = run_dynamic_module($path_data);
// } else {
	$body = run_module($path_data["module_name"],$path_data["folder"]);
// }

load_post_queue();
if(is_file($GLOBALS["root_path"] ."templates/". $template .".template.php")) {
	include($GLOBALS["root_path"] ."templates/". $template .".template.php");
}


$html = ob_get_clean();

echo $html;



// Add item to cache
// if(!empty($GLOBALS["cache_options"]["enabled"])) { cacher_cache($html); }
die();

##################################################
#	EOF
##################################################
