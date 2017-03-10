<?php
########################################################################
#
#	PostgreSQL - No Debugging Version
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
		$GLOBALS['db_options']['connection_string'][$dbname] = db_connect($db['hostname'],$db['username'],$db['password'],$db['database'],(!empty($db['port']) ? $db['port'] : ''));
	}
}
*/
function db_postgresql_connect($host,$user,$pass,$db,$port='') {
	if(isset($host)) {
		if(($db_connection = pg_connect("host=$host dbname=$db user=$user password=$pass". ($port != "" ? " port=$port" : ''))) === false) {
			_error_debug("Error connecting to DB '$db': ". pg_last_error($db_connection));
		}
	} else {
		_error_debug("Include database conf failed");
	}
	return $db_connection;
}

function db_postgresql_query($query,$description="",$dbname="default") {
		
	$result = @pg_query($GLOBALS['db_options']['connection_string'][$dbname], $query);
	$error = pg_last_error($GLOBALS['db_options']['connection_string'][$dbname]);

	$tmp = trim($query);
	$tmp = substr($tmp,0,6);
	if(!empty($result) && strtolower($tmp) == "insert") {
		$q = "select lastval()";
		$res = db_postgresql_query($q,"getting last value");
		$row = db_postgresql_fetch_row($res,"num");
		if(!empty($row[0])) {
			$GLOBALS['db_options']['last_insert_id'][$dbname] = $row[0];
		}
	}

	if ($result === false || !empty($error)) {
		if($query != "select lastval()") {
			_error_debug("PostgreSQL ERROR: " . $description, array('Error Message' => $error, 'PostgreSQL Query' => $query), "", "", E_ERROR);	
		}
	}
		
	return array('dbname'=>$dbname,'result'=>$result);
}

function db_postgresql_fetch_row(&$results,$extra='assoc') {
	$extra = strtolower($extra);
	if($extra == 'assoc') {
		return pg_fetch_assoc($results['result']);
	} else if($extra == 'num') {
		return pg_fetch_row($results['result']);
	} else {
		return pg_fetch_array($results['result'], PGSQL_BOTH);
	}	
}

function db_postgresql_insert_id($results='',$dbname='default') {
	return $GLOBALS['db_options']['last_insert_id'][$dbname];
}

function db_postgresql_num_rows($results) {
	return pg_num_rows($results);
}

function db_postgresql_data_seek($results,$val) {
	return pg_result_seek($results,$val);
}

function db_postgresql_is_error($results) {
	return ($results['result'] === false);
}

function db_postgresql_affected_rows($results) {
	return pg_affected_rows($results);
}

function db_postgresql_transaction_start($dbname) {
	if(!empty($GLOBALS['db_in_transaction'])) {
		_error_debug("Already in the middle of transaction, can't start new one", $dbname, E_ERROR);
		return(false);
	} else {
		$result = db_query("START TRANSACTION", 'start transaction', $dbname);
		if(db_postgresql_is_error($result)) {
			return(false);
		} else {
			$GLOBALS['db_in_transaction'] = 1;
			return(true);
		}
	}
}

function db_postgresql_transaction_commit($dbname) {
	if(empty($GLOBALS['db_in_transaction'])) {
		_error_debug("There are no queries to commit", $dbname, E_ERROR);
		return(false);
	} else {
		$result = db_query("COMMIT", 'commit transaction', $dbname);
		unset($GLOBALS['db_in_transaction']);
		return(true);
	}
}

function db_postgresql_transaction_rollback($dbname) {
	if(empty($GLOBALS['db_in_transaction'])) {
		_error_debug("There are no transactions to rollback", $dbname, E_ERROR);
		return(false);
	} else {
		$result = db_query("ROLLBACK", 'rollback transaction', $dbname);
		unset($GLOBALS['db_in_transaction']);
		return(true);
	}
}

function db_postgresql_prep_sql($value,$type='') {
	if($type=='bytea') {
		return pg_escape_bytea($value);
	}
	return pg_escape_string($value);
}

?>