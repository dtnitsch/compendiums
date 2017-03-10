<?php
########################################################################
#
#	Database Functions
#
########################################################################

# Create an array of used database groups
$GLOBALS['db_options']['db_types'] = array();

function db_connect_all() {

	# Check to see if the main connections are set
	if(empty($GLOBALS['db_options']['main_connections'])) { _error_debug('Default database connections were not set'); }

	foreach($GLOBALS['db_options']['main_connections'] as $dbname => $db) {
		$GLOBALS['db_options']['connection_string'][$dbname] = db_connect($dbname,$db);
	}
}


function db_connect($dbname,$db_options) {
	$debug = ($GLOBALS['debug_options']['enabled'] ? 1 : 0);
	# Create an array of database types so that we don't include a database type more than once
	if(!in_array($db_options['type'],$GLOBALS['db_options']['db_types'])) {
		$db_name = $db_options['type']. ($debug == 1 ? '_debug' : '') .".php";
		if(!include($GLOBALS['root_path'] .'library/databases/'.$db_name)) {
			_error_debug("Could not open database file", $dbname, __LINE__, __FILE__, E_ERROR);
			return 0;
		}
	}
	$GLOBALS['db_options']['db_types'][$dbname] = $db_options['type'];

	if(!isset($GLOBALS['db_options']['main_connections'][$dbname])) {
		_error_debug("Database connection information doesn't exist", $dbname, __LINE__, __FILE__, E_ERROR);
		return 0;
	}
	
	$db_function = 'db_'. $db_options['type'] ."_connect";
	if(!isset($db_options['port'])) {
		$db_options['port'] = "";
	}

	_error_debug("Connecting to DB: ". $dbname);
	return $db_function($db_options['hostname'],$db_options['username'],$db_options['password'],$db_options['database'],$db_options['port']);
}

function db_query($query, $description='', $dbname='default') {
	
	unset($GLOBALS['db_options']['last_insert_id'][$dbname]);
	if(!isset($GLOBALS['db_options']['main_connections'][$dbname])) {
		_error_debug("Database connection information doesn't exist", $dbname, __LINE__, __FILE__, E_ERROR);
		return 0;
	}
	if(!isset($GLOBALS['db_options']['connection_string'][$dbname])) {
		$GLOBALS['db_options']['connection_string'][$dbname] = db_connect($dbname,$GLOBALS['db_options']['main_connections'][$dbname]);
	}

	$db_type = $GLOBALS['db_options']['db_types'][$dbname];
	$db_function = 'db_'. $db_type ."_query";
	return $db_function($query, $description, $dbname);
}

function db_fetch($query, $description='', $dbname='default') {
	unset($GLOBALS['db_options']['last_insert_id'][$dbname]);
	if(!isset($GLOBALS['db_options']['main_connections'][$dbname])) {
		_error_debug("Database connection information doesn't exist", $dbname, __LINE__, __FILE__, E_ERROR);
		return 0;
	}
	if(!isset($GLOBALS['db_options']['connection_string'][$dbname])) {
		$GLOBALS['db_options']['connection_string'][$dbname] = db_connect($dbname,$GLOBALS['db_options']['main_connections'][$dbname]);
	}

	$db_type = $GLOBALS['db_options']['db_types'][$dbname];
	$db_function = 'db_'. $db_type ."_query";
	$res = $db_function($query, $description, $dbname);
	if(!db_num_rows($res)) {
		return false;
	}
	return db_fetch_row($res);
}

function db_num_rows($result) {
	if(empty($result['dbname'])) { return 0; }
	$db_type = $GLOBALS['db_options']['db_types'][$result['dbname']];

	$db_function = 'db_'. $db_type ."_num_rows";
	return $db_function($result['result']);
}

function db_data_seek(&$result, $offset=0) {
	$db_type = $GLOBALS['db_options']['db_types'][$result['dbname']];

	$db_function = 'db_'. $db_type ."_data_seek";
	return $db_function($result['result'],$offset);
}

function db_insert_id($result) {
	$db_type = $GLOBALS['db_options']['db_types'][$result['dbname']];
	$connection = $GLOBALS['db_options']['connection_string'][$result['dbname']];
	
	$db_function = 'db_'. $db_type ."_insert_id";
	return $db_function($connection,$result['dbname']);
}

function db_affected_rows($result) {
	$db_type = $GLOBALS['db_options']['db_types'][$result['dbname']];
	
	$db_function = 'db_'. $db_type ."_affected_rows";
	return $db_function($result['result']);
}

function db_fetch_row(&$result,$extra='assoc') {
	if(empty($result['dbname'])) { return 0; }
	$db_type = $GLOBALS['db_options']['db_types'][$result['dbname']];
	
	$db_function = 'db_'. $db_type ."_fetch_row";
	return $db_function($result,$extra);
}

function db_transaction_start($dbname='default') {
	$db_type = $GLOBALS['db_options']['db_types'][$dbname];

	$db_function = 'db_'. $db_type ."_transaction_start";
	return $db_function($dbname);
}

function db_transaction_commit($dbname='default') {
	$db_type = $GLOBALS['db_options']['db_types'][$dbname];

	$db_function = 'db_'. $db_type ."_transaction_commit";
	return $db_function($dbname);
}

function db_transaction_rollback($dbname='default') {
	$db_type = $GLOBALS['db_options']['db_types'][$dbname];

	$db_function = 'db_'. $db_type ."_transaction_rollback";
	return $db_function($dbname);
}

function db_is_error($result) {
	$db_type = $GLOBALS['db_options']['db_types'][$result['dbname']];

	$db_function = 'db_'. $db_type ."_is_error";
	return $db_function($result);
}

function db_prep_sql($value,$type='',$db_name='default') {
	$db_type = $GLOBALS['db_options']['db_types'][$db_name];

	$db_function = 'db_'. $db_type ."_prep_sql";
	return $db_function($value,$type,$db_name);
}

?>