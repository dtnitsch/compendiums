<?php
##################################################
#
#	Session Database Storage
#
##################################################

# Normally this session would be used to connect to the Database.  This was already done in another function
function _session_open($path, $name) { return 1; }

# Normally this session would be used to disconnect from the Database.  This was already done in another function
function _session_close() { return 1; }

# Reading is done at the beginning of every page call.  This should be a FAST.
function _session_read($ses_id) {
	$session_sql = "
		select data
		from sessions
		where id='" . $ses_id . "'
	"; 
	$res = db_query($session_sql, '_session_read(): get session');

	# If there was an error, fail this piece and return false
	if(db_is_error($res)) { return 0; } 
	
	# Return data if there was any
	if(db_num_rows($res)) {
		$session_row = db_fetch_row($res); 
		return($session_row["data"]);
	} 
} 

# All session writes are done at the END of the script load.
function _session_write($ses_id, $data) {
	$sql = "
		update sessions set
			data='" . $data . "',
			modified=NOW()
		where id='" . $ses_id . "'
	"; 
	$res = db_query($sql, '_session_write(): update session');

	# Check for an error
	if(db_affected_rows($res) == 0) { 
		$sql = "insert into sessions (id,data,modified) values 
				('" . $ses_id . "','" . $data . "',now())"; 
		$res = db_query($sql, '_session_write(): insert session');
	} 
	return 1; 
} 

# If we know that the user is logging off or leaving the site, manually destroy the session
function _session_destroy($ses_id) {
	$sql = "
		delete from sessions 
		where id='" . $ses_id . "'
	"; 
	$session_res = db_query($sql, '_session_destroy(): destroy session'); 
	# Check for an error
	if (db_is_error($session_res)) { return 0; }	
	return 1;
} 

# Sometimes we don't know people leave. This is randomly erase all things left over without having to overload the DB with full erases too often
function _session_gc($life) {
	# One in 10,000 will erase the whole session table of old values.  
	# This is STATISTICALLY once per day if you are getting 7 hits per second.
	/*
	if(mt_rand(1, 10000) == 1) { 
		$session_sql = "
			delete from sessions
			where DATE_ADD(datemodified, INTERVAL 1 WEEK) < NOW()
		"; 
		$session_res = db_query($session_sql, '_session_gc: session garbage collection', 'system'); 
		# Check for an error
		if (db_is_error($session_res)) { return 0; }
		return 1;
	}
	*/
	return 0;
} 

?>