<?php 
#########################################################
#	Project Configuration File
#########################################################
$GLOBALS = array();

$GLOBALS["project_info"]["name"] = "GM Tools";
$GLOBALS["project_info"]["alias"] = "gm_tools";
$GLOBALS["project_info"]["dns"] = "api.gm_tools.local";
$GLOBALS["project_info"]["company_name"] = "DTN";
$GLOBALS["project_info"]["default_email"] = "fdlkfalds@falflds.com";
#########################################################
#	Software Paths
#########################################################
$GLOBALS["root_path"] = str_replace("/config","",__DIR__) ."/";

#########################################################
#	Debug Options
#########################################################
$GLOBALS["debug_options"]["enabled"] = 0;				# Turn Debugging On - Use only in dev
$GLOBALS["debug_options"]["enabled_backtrace"] = 1;		# Enable full details on errors
$GLOBALS["debug_options"]["send_debug_mail"] = 1;		# Send an email with error messages - useful in production
$GLOBALS["debug_options"]["debug_email"] = 'daniel@lkaf;lds.com';

#########################################################
#	Logging Options
#########################################################
$GLOBALS["logging_options"]["enabled"] = 0;
$GLOBALS["logging_options"]["database_queries"] = 0;
$GLOBALS["logging_options"]["page_hits"] = 0;

#########################################################
#	Audit Options
#########################################################
$GLOBALS["audit_options"]["enabled"] = 0;

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

