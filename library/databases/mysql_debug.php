<?php

#MYSQL_DEBUG
if(isset($host)) {
	if($db_connect = mysql_connect($host, $user, $pass)) {
		if(!mysql_select_db($db)) {
			echo "mysqli_select_db(): " . mysql_error($db_connection);
		}
	} else {
		echo "mysql_connect(): " . mysql_connect_error($db_connection);
	}
} else {
	echo "Include database conf failed";
}
// clean up so that these variables aren't exposed through the debug console
unset($host, $user, $pass, $db);

# Simple DB query, you can send the query, a name, and if you add a "1" after that, it returns only the returned value
function db_query($query,$description="",$extra='') {
	
	$begin_time = microtime(true);
	$result = mysql_query($query);
	$end_time = microtime(true);
	
	$database_time = ($end_time-$begin_time);

	if(!is_bool($result)) {
		$num_rows = mysql_num_rows($result);
		$str = $num_rows . " rows";
	} else {
		$affected = mysql_affected_rows($db_connection);
		$str = $affected . " affected";
	}
	
	if ($result === false) {
		error_debug(array('error' => mysql_error(), 'query' => $query), "MySQL ERROR: " . $description, __LINE__, __FILE__, E_ERROR);
	} else {
		error_debug(array('MySQL Query' => $query), "MySQL (" . $str . ", " . (round(($end_time-$begin_time)*1000)/1000) . " sec): " . $description, __LINE__, __FILE__);
	}
	
	if($extra == '') {
		return $result;
	} else {
		$extra = strtolower($extra);
		if($extra == 'fetch' || $extra == 'fetch_assoc') {
			return mysql_fetch_assoc($result);
		} else if($extra == 'fetch_row') {
			return mysql_fetch_row($result);
		} else {
			return $result;
		}
	}
}

function db_fetch_row($results,$extra='') {
	$extra = strtolower($extra);
	if($extra == '' || $extra == 'assoc') {
		return mysql_fetch_assoc($results);
	} else if($extra == 'num') {
		return mysql_fetch_row($results);
	} else {
		return mysql_fetch_array($results, MYSQLI_BOTH);
	}	
}

function db_last_id($results='') {
	return mysql_insert_id();
}

function db_num_rows($results) {
	return mysql_num_rows($results);
}

function db_seek($results,$val) {
	return mysql_data_seek($results,$val);
}

function db_mysql_prep_sql($value,$type='',$db_name='default') {
	return mysqli_real_escape_string($GLOBALS['db_options']['connection_string'][$dbname],$value);
}

?>