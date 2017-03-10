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
$GLOBALS["debug_options"]["enabled"] = 0;				# Turn Debugging On - Use only in dev
$ip = $_SERVER['REMOTE_ADDR'];
if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
    $ip = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
}
if($ip == '74.83.157.2') { 
	$GLOBALS["debug_options"]["enabled"] = 0;				# Turn Debugging On - Use only in dev
}
$GLOBALS["debug_options"]["enabled_backtrace"] = 1;		# Enable full details on errors
$GLOBALS["debug_options"]["send_debug_mail"] = 0;		# Send an email with error messages - useful in production
$GLOBALS["debug_options"]["debug_email"] = "fdlkfalds@falflds.com";

#########################################################
#	Logging Options
#########################################################
$GLOBALS["logging_options"]["enabled"] = 1;
$GLOBALS["logging_options"]["database_queries"] = 0;
$GLOBALS["logging_options"]["page_hits"] = 1;

#########################################################
#	Caching Options
#########################################################
$GLOBALS["cache_options"]["enabled"] = 0;

#########################################################
#	Audit Options
#########################################################
$GLOBALS["audit_options"]["enabled"] = 0;

#########################################################
#	Session Options
#########################################################
$GLOBALS["session_options"]["use_db"] = 0;
$GLOBALS["session_options"]["session_life"] = ((60 * 60) * 24);

#########################################################
#	Security Options
#########################################################
$GLOBALS["session_options"]["use_security"] = 1;

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
		"hostname" => "localhost"
		,"username" => "turnstyle"
		,"password" => 'yWRQbu5c6^pz2h#R'
		,"database" => "clevercrazes"
		,"type" => "postgresql"
	)
);

