<?php

#SQLite
$db_file = $PROJECT_NAME_STRIPPED .'.sqlite.db';
if(file_exists($db_file)) {
	if(!$db_connection = sqlite_open($BF.'configurations/'. $db_file, 0666, $sqliteerror)) {
		die("SQLite Open Database Error: " .$sqliteerror);
	}
} else {
	die("Include database conf failed");
}
// clean up so that these variables aren't exposed through the debug console
unset($user, $pass, $db);

# Simple DB query, you can send the query, a name, and if you add a "1" after that, it returns only the returned value
function db_query($query,$extra='') {
	$results = sqlite_query($query);
	if($extra == '') { 
		return $results; 
	} else {
		$extra = strtolower($extra);
		if($extra == 'fetch' || $extra == 'fetch_assoc' || $extra == 'fetch_row') {
			return((sqlite_fetch_single($results));
		} else {
			return $results;
		}
	}
}

function db_fetch_row($extra='') {
	$extra = strtolower($extra);
	if($extra == '' || $extra == 'assoc') {
		return sqlite_fetch_array($results, SQLITE_ASSOC);
	} else if($extra == 'num') {
		return sqlite_fetch_array($results,, SQLITE_NUM);
	} else {
		return sqlite_fetch_array($results, SQLITE_BOTH);
	}	
}

function db_last_id($results='') {
	return sqlite_last_insert_rowid();
}

function db_num_rows($results) {
	return sqlite_num_rows($results);
}

function db_seek($results,$val) {
	return sqlite_seek($results,$val);
}

?>