<?php 
date_default_timezone_set("America/New_York");

// http://mark-kirby.co.uk/2013/creating-a-true-rest-api/
// http://websec.io/2013/02/14/API-Authentication-Public-Private-Hashes.html

ob_start();

// Load the configuration file for the whole website
// require("../config/api_config.php");

// Functions that are used often
// include("../library/library_api.php");
// include_once("functions/functions.php");

// $privateKey = 'Qqlcn5Y66DqMB6k2EYsusCbggq/L7AprQYIi+anxcLI';

$request_method = (!empty($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : '');

if(empty($request_method)) {
	error("Request Method is empty");
}

$headers = getallheaders();
$path = get_url();
$url = $_SERVER['SCRIPT_URL'];
$path = str_replace('/api/','/',$path);
$url = str_replace('/api/','/',$url);

$data = '';
if ($request_method == 'get') {
	$data = [$path];
} else {
	$data = $_REQUEST;
}

// Functions that are used often
include("../api/routes.php");

	if (function_exists("db_transaction_start")) { db_transaction_start(); }

$path_exists = false;
if(!empty($routes[$request_method]['static'][$url])) {
	$function = $routes[$request_method]['static'][$url];

	// echo "<p>Static: ". $function ."()";
	if(is_file('../api/functions/'. $function .".php")) {
		include('../api/functions/'. $function .".php");
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
			// echo "<p>Dynamic: ". $function ."(". $values .")";
			// echo $_SERVER['DOCUMENT_ROOT'];
			if(is_file('../api/functions/'. $function .".php")) {
				include('../api/functions/'. $function .".php");
				$function($values);
				$path_exists = true;
			}
			break;
		}
	}
}

$output = ob_get_clean();

if(!$path_exists) {
	if (function_exists("db_transaction_rollback")) { db_transaction_rollback(); }
	die("Path didn't exist");
}
if(function_exists("db_transaction_commit")) { db_transaction_commit(); }

header('Content-type: text/xml');
echo $output;
die();