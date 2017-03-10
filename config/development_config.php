<?php 
#########################################################
#	Project Configuration File
#########################################################
$GLOBALS = array();

$GLOBALS["project_info"]["name"] = "GM Tools";
$GLOBALS["project_info"]["alias"] = "gm_tools";
$GLOBALS["project_info"]["dns"] = "gm_tools.local";
$GLOBALS["project_info"]["company_name"] = "DTN";
$GLOBALS["project_info"]["default_email"] = "fdlkfalds@falflds.com";

#########################################################
#	Software Paths
#########################################################
$GLOBALS["root_path"] = str_replace("/config","",__DIR__) ."/";

#########################################################
#	Template
#########################################################
$GLOBALS["templates"]["default"] = "";
$GLOBALS["templates"]["minification"] = 0;

#########################################################
#	Debug Options
#########################################################
$GLOBALS["debug_options"]["enabled"] = 1;				# Turn Debugging On - Use only in dev
$GLOBALS["debug_options"]["enabled_backtrace"] = 0;		# Enable full details on errors
$GLOBALS["debug_options"]["send_debug_mail"] = 0;		# Send an email with error messages - useful in production
$GLOBALS["debug_options"]["debug_email"] = '';

#########################################################
#	Routes
#########################################################
$GLOBALS["routing_options"]["db"] = 1;


#########################################################
#	Logging Options
#########################################################
$GLOBALS["logging_options"]["enabled"] = 0;
$GLOBALS["logging_options"]["database_queries"] = 0;
$GLOBALS["logging_options"]["page_hits"] = 0;

#########################################################
#	Caching Options
#########################################################
$GLOBALS["cache_options"]["enabled"] = 0;

#########################################################
#	Audit Options
#########################################################
$GLOBALS["audit_options"]["enabled"] = 1;

#########################################################
#	Session Options
#########################################################
$GLOBALS["session_options"]["use_db"] = 0;
$GLOBALS["session_options"]["db_type"] = 'mysqli';
$GLOBALS["session_options"]["session_life"] = ((60 * 60) * 24);

#########################################################
#	Security Options
#########################################################
$GLOBALS["security_options"]["enabled"] = 0;

#########################################################
#	Site Wide Notes
#########################################################
$GLOBALS["site_wide_notes"]["enabled"] = 0;

#########################################################
#	Multi Language Support
#########################################################
$GLOBALS["multi_language_support"]["enabled"] = 0;
// Use UTF-8 for all multi-byte functions
// mb_internal_encoding("UTF-8");
// mb_http_output("UTF-8");

#########################################################
#	Database Options
#########################################################
// include($GLOBALS["app_path"] ."library/databases/all_in_one.php");
$GLOBALS["db_options"]["main_connections"] = array(
	"default" => array(
		'hostname'	=> 'localhost',
		'username' 	=> 'daniel',
		'password' 	=> 'daniel',
		'database' 	=> 'gm_tools',
		'type'		=> 'postgresql'
	)
);

