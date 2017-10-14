<?php 
date_default_timezone_set("America/New_York");

// http://mark-kirby.co.uk/2013/creating-a-true-rest-api/
// http://websec.io/2013/02/14/API-Authentication-Public-Private-Hashes.html

ob_start();

// Load the configuration file for the whole website
require("../config/api_config.php");

// Functions that are used often
include("../library/library_api.php");
include_once("functions/functions.php");

// $privateKey = 'Qqlcn5Y66DqMB6k2EYsusCbggq/L7AprQYIi+anxcLI';

$request_method = (!empty($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : '');
if(empty($request_method)) {
	error("Reqest Method is empty");
}

$headers = getallheaders();
$path = get_url();
$url = $_SERVER['SCRIPT_URL'];

$data = '';
if ($request_method == 'GET') {
	$data = [$path];
} else {
	$data = $_REQUEST;
}

// Load the error handler to be able to report all site errors
if($GLOBALS["debug_options"]["enabled"]) {
	library("error_handler.php");
	error_handler_setup($GLOBALS["debug_options"]["enabled"]);

} else {
	// create empty function for debug messages if we are not using the debugger
	function _error_debug() {}
}

// Load the database functions
if(!empty($GLOBALS["db_options"])) {
	library("databases/all_in_one.php");
	db_connect_all();
}



// $message = buildMessage((int)$headers['API_TIME'], $headers['API_UID'], $data);
// $new_hash = hash_hmac('sha256', $message, $privateKey);

// if($headers['API_HASH'] != $new_hash) {
// 	error("Hashes do not match");
// }

// Functions that are used often
include("routes.php");

$path_exists = false;
if(!empty($routes[$request_method]['static'][$url])) {
	$function = $routes[$request_method]['static'][$url];
	// echo "<p>Static: ". $function ."()";
	if(is_file('functions/'. $function .".php")) {
		include('functions/'. $function .".php");
		$function($data);
		$path_exists = true;
	}
} else {
	foreach($routes[$request_method]['dynamic'] as $route => $function) {
		$path_pieces = explode(":",$route);
		$items_to_get = array_pop($path_pieces);
		$route_path = str_replace('/','\/',$path_pieces[0]);
		if(preg_match("/^$route_path.+/",$url)) {
			$values = str_replace($path_pieces[0],'',$url);
			$values = trim(trim($values,'/'));
			// echo "<p>Dynamic: ". $function ."(". $values .")";
			// echo $_SERVER['DOCUMENT_ROOT'];
			if(is_file('functions/'. $function .".php")) {
				include('functions/'. $function .".php");
				$function($values);
				$path_exists = true;
			}
			break;
		}
	}
}

$output = ob_get_clean();

if(!$path_exists) {
	die("Path didn't exist");
}

header('Content-type: text/json');
echo $output;

// if(!empty($GLOBALS["debug_options"]["enabled"])) { echo show_debug(); }

