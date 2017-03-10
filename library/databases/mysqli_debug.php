<?php
########################################################################
#
#	MySQLi - Full Debugging Version
#
########################################################################
/*
function db_connect_all() {
	# Check to see if the main connections are set
	if(empty($GLOBALS['db_options']['main_connections'])) { _error_report('Default database connections were not set'); }

	foreach($GLOBALS['db_options']['main_connections'] as $dbname => $db) {
:q		if(!isset($GLOBALS['db_options']['main_connections'][$dbname])) {
			_error_debug("Database connection information doesn't exist", $dbname, E_ERROR);
			return 0;
		}
		$GLOBALS['db_options']['connection_string'][$dbname] = db_connect($db['hostname'],$db['username'],$db['password'],$db['database']);
	}
}
*/

function db_mysqli_connect($host,$user,$pass,$db) {
	if(isset($host)) {
		if($db_connection = mysqli_connect($host, $user, $pass)) {
			if(!mysqli_select_db($db_connection, $db)) {
				_error_debug("mysqli_select_db(): " . mysqli_error($db_connection));
			}
		} else {
			_error_debug("mysqli_connect(): " . mysqli_connect_error($db_connection));
		}
	} else {
		_error_debug("Include database conf failed");
	}
	return $db_connection;
}

function db_mysqli_query($query,$description="",$dbname='default') {

	$begin_time = microtime(true);
	$result = mysqli_query($GLOBALS['db_options']['connection_string'][$dbname], $query);
	$end_time = microtime(true);
	
	// $last_insert_id = 0;
	$last_insert_id = mysqli_insert_id($GLOBALS['db_options']['connection_string'][$dbname]);
	if(!empty($last_insert_id)) {
		$GLOBALS['db_options']['last_insert_id'][$dbname] = $last_insert_id;
	}
	
	$database_time = ($end_time-$begin_time);
	if(!isset($GLOBALS['debug_information'][$dbname])) { 
		$GLOBALS['debug_information'][$dbname]['total_query_time'] = 0; 
		$GLOBALS['debug_information'][$dbname]['total_queries'] = 0;
	}
	$GLOBALS['debug_information'][$dbname]['total_query_time'] += $database_time;
	$GLOBALS['debug_information'][$dbname]['total_queries']++;
	$GLOBALS['debug_information']['total_queries']++;
	$GLOBALS['debug_information']['total_query_time'] += $database_time;

	$str = 0;
	if(!is_bool($result)) {
		$num_rows = mysqli_num_rows($result);
		$str = $num_rows ." rows";
	} else {
		//$affected = mysqli_affected_rows($GLOBALS['db_options']['connection_string'][$dbname]);
		//$str = $affected ." affected";
	}
	
	if ($result === false) {
		_error_debug("MySQLi ERROR: " . $description, array('Error Message' => mysqli_error($GLOBALS['db_options']['connection_string'][$dbname]), 'MySQLi Query' => $query), '','', E_ERROR);
	} else {
		if(mysqli_warning_count($GLOBALS['db_options']['connection_string'][$dbname])/* && !$ignore_warnings*/) {
			$warnings = '';
			if($warning_result = mysqli_query($GLOBALS['db_options']['connection_string'][$dbname], "SHOW WARNINGS")) {
		        while($row = mysqli_fetch_row($warning_result)) {
			        $warnings .= $row[0] ." (". $row[1] ."): ". $row[2] ."<br>";
		        }
		        mysqli_free_result($warning_result);
			}
			if(!empty($warnings)) {
				_error_debug("MySQLi WARNING(S): " . $description, $warnings."<br>".$query, '','', E_WARNING);	
			}
			
		} else {
			_error_debug("MySQLi (" . $str . ", " . number_format($database_time,4) . " sec): " . $description, array('MySQLi Query' => $query));
		}
	}
	
	return array('dbname'=>$dbname,'result'=>$result);
}

function db_mysqli_fetch_row(&$results,$extra='assoc') {
	$extra = strtolower($extra);
	if($extra == 'assoc') {
		return mysqli_fetch_assoc($results['result']);
	} else if($extra == 'num') {
		return mysqli_fetch_row($results['result']);
	} else {
		return mysqli_fetch_array($results['result'], MYSQLI_BOTH);
	}	
}

function db_mysqli_insert_id($connection,$dbname) {
	// return mysqli_insert_id($connection);
	if(!empty($GLOBALS['db_options']['last_insert_id'][$dbname])) {
		return $GLOBALS['db_options']['last_insert_id'][$dbname];
	}
	return 0;
}

function db_mysqli_num_rows($results) {
	return mysqli_num_rows($results);
}

function db_mysqli_data_seek($results,$val) {
	return mysqli_data_seek($results,$val);
}

function db_mysqli_is_error($results) {
	return ($results['result'] === false);
}

function db_mysqli_affected_rows($results,$dbname='default') {
	$connection = $GLOBALS['db_options']['connection_string'][$dbname];
	return mysqli_affected_rows($connection);
}

function db_mysqli_transaction_start($dbname) {
	if(!empty($GLOBALS['db_in_transaction'])) {
		_error_debug("Already in the middle of transaction, can't start new one", $dbname, E_ERROR);
		return(false);
	} else {
		$result = db_query("START TRANSACTION", 'start transaction', $dbname);
		if(db_is_error($result)) {
			return(false);
		} else {
			$GLOBALS['db_in_transaction'] = 1;
			return(true);
		}
	}
}

function db_mysqli_transaction_commit($dbname) {
	if(empty($GLOBALS['db_in_transaction'])) {
		_error_debug("There are no queries to commit", $dbname, E_ERROR);
		return(false);
	} else {
		$result = db_query("COMMIT", 'commit transaction', $dbname);
		unset($GLOBALS['db_in_transaction']);
		return(true);
	}
}

function db_mysqli_transaction_rollback($dbname) {
	if(empty($GLOBALS['db_in_transaction'])) {
		_error_debug("There are no transactions to rollback", $dbname, E_ERROR);
		return(false);
	} else {
		$result = db_query("ROLLBACK", 'rollback transaction', $dbname);
		unset($GLOBALS['db_in_transaction']);
		return(true);
	}
}

function db_mysqli_prep_sql($value,$type='',$dbname='default') {
	return mysqli_real_escape_string($GLOBALS['db_options']['connection_string'][$dbname],$value);;
}

?>
