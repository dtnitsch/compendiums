<?php
########################################################################
#
#	MySQLi - Debugging Remove
#
########################################################################
/*
function db_connect_all() {
	# Check to see if the main connections are set
	if(empty($GLOBALS['db_options']['main_connections'])) { _error_report('Default database connections were not set'); }

	foreach($GLOBALS['db_options']['main_connections'] as $dbname => $db) {
		if(!isset($GLOBALS['db_options']['main_connections'][$dbname])) {
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

function db_mysqli_query($query,$description="",$extra='',$dbname='default') {
		
	$result = mysqli_query($GLOBALS['db_options']['connection_string'][$dbname], $query);

	$last_insert_id = mysqli_insert_id($GLOBALS['db_options']['connection_string'][$dbname]);
	if(!empty($last_insert_id)) {
		$GLOBALS['db_options']['last_insert_id'][$dbname] = $last_insert_id;
	}
		
	if ($result === false) {
		_error_debug("MySQLi ERROR: " . $description, array('Error Message' => mysqli_error($GLOBALS['db_options']['connection_string'][$dbname]), 'MySQLi Query' => $query), __LINE__, __FILE__, E_ERROR);
	}
	
	if($extra == '') {
		return array('dbname'=>$dbname,'result'=>$result);
	} else {
		$arr = array('dbname'=>$dbname,'result'=>$result);
		if($extra == '') {
			return db_mysqli_fetch_row($arr,$extra);
		} else {
			return db_mysqli_fetch_row($arr);
		}
	}
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

// function db_mysqli_insert_id($results='',$dbname='default') {
// 	return mysqli_insert_id($GLOBALS['db_connection_stings'][$dbname]);
// }

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
		return(false);
	} else {
		$result = db_query("START TRANSACTION", 'start transaction', '', $dbname);
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
		return(false);
	} else {
		$result = db_query("COMMIT", 'commit transaction', $dbname);
		unset($GLOBALS['db_in_transaction']);
		return(true);
	}
}

function db_mysqli_transaction_rollback($dbname) {
	if(empty($GLOBALS['db_in_transaction'])) {
		return(false);
	} else {
		$result = db_query("ROLLBACK", 'rollback transaction', $dbname);
		unset($GLOBALS['db_in_transaction']);
		return(true);
	}
}

function db_mysqli_prep_sql($value,$type='',$dbname='default') {
	return mysqli_real_escape_string($GLOBALS['db_options']['connection_string'][$dbname],$value);
}

?>