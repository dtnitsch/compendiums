<?php

#MYSQL
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
function db_query($query,$extra='') {
	$results = mysql_query($query);
	if($extra == '') { 
		return $results; 
	} else {
		$extra = strtolower($extra);
		if($extra == 'fetch' || $extra == 'fetch_assoc') {
			return((mysql_fetch_assoc($results));
		} else if($extra == 'fetch_row') {
			return((mysql_fetch_row($results));
		} else {
			return $results;
		}
	}
}

function db_fetch_row($extra='') {
	$extra = strtolower($extra);
	if($extra == '' || $extra == 'assoc') {
		return mysql_fetch_assoc($results, MYSQL_ASSOC);
	} else if($extra == 'num') {
		return mysql_fetch_row($results, MYSQL_NUM);
	} else {
		return mysql_fetch_array($results, MYSQL_BOTH);
	}	
}

function db_last_id($results='') {
	return mysqli_insert_id();
}

function db_num_rows($results) {
	return mysql_num_rows($results);
}

function db_seek($results,$val) {
	return mysql_data_seek($results,$val);
}

function db_mysqli_prep_sql($value,$type='',$db_name='default') {
	return mysqli_real_escape_string($GLOBALS['db_options']['connection_string'][$dbname],$value);
}

?>