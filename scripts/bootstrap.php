<?php

date_default_timezone_set("America/New_York");

ob_start();

// Load the configuration file for the whole website
require("../config/development_config.php");

// Functions that are used often
include("../library/library.php");


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
	db_connect_all();
}


// Enable Sessions - DB Connection might be required
start_session();
