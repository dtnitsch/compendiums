<?php

function audit_logins($user_id) {
	$schema = "";
	$datetime = time();
	if(uses_schema()) {
		$schema = '"audits".';
		$datetime = "now()";
	}
	$q = "insert into ". $schema ."logins (user_id,created) values ('". $user_id ."',". $datetime .")";
	db_query($q, "audit: login");
	return true;
}

function audit_page_hits($options) {
	$path = $options['path'];
	$params = $options['params'];
	$schema = "";
	$datetime = time();
	if(uses_schema()) {
		$schema = '"audits".';
		$datetime = "now()";
	}
	$user_id = (!empty($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 0);
	if(preg_match('/(?:css|js|ico)$/',$path)) { return false; }
	$q = "insert into ". $schema ."page_hits (user_id,path,session_id,params,created) values ('". (int)$user_id ."','". $path ."','". session_id() ."','". $params ."',". $datetime .")";
	db_query($q, "audit: page hit");
	return true;
}

function audit_failed_login_attempts($login) {
	$schema = "";
	$datetime = time();
	if(uses_schema()) {
		$schema = '"audits".';
		$datetime = "now()";
	}
	$ip = $_SERVER['REMOTE_ADDR'];
	$ipv4 = $ipv6 = 0;
	if(strpos($ip,":") !== false && strpos($ip,".") === false) {
		$ipv6 = $ip;
	} else {
		$ipv4 = $ip;
	}
	$q = "insert into ". $schema ."failed_login_attempts (login_input,ipv4,ipv6,created) values 
		('". db_prep_sql($login) ."','". $ipv4 ."','". $ipv6 ."',". $datetime .")
	";
	post_post_transaction_hook($q, "audit: failed login attempt");
	// $res = db_query();
	// if(db_is_error($res)) { return false; }
	return true;
}



function audit_table_insert(&$table_info) {
	$user_id = (!empty($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 0);
	if(empty($table_info['audit_table'])) { $table_info['audit_table'] = $table_info['table_schema'].'_table_log'; }

	$schema = "";
	$datetime = time();
	$pk_table = $table_info['audit_table'];
	if(uses_schema()) {
		$schema = (!empty($table_info['audit_schema']) ? $table_info['audit_schema'] : '"audits"');
		$datetime = "now()";
		$pk_table = '"'. $schema .'"."'. $table_info['audit_table'] .'"';
	}

	// $pk_table = $table_info['audit_table'];
	// $field_table = $table_info['audit_schema'].".".str_replace('_table_log','_field_log',$table_info['audit_table']);
	$field_table = str_replace('_table_log','_field_log',$pk_table);

	foreach($table_info['table_columns'] as $k => $row) {

		$q = "insert into ". $pk_table ." (schema_name, table_name, primary_key_id, user_id, created) values 
			('". $schema ."','". $table_info['table_name'] ."','". $table_info['primary_key_value'] ."','". $user_id ."',". $datetime .")
		";
		$res = db_query($q,'audit: insert into table log');
		$last_insert_id = db_insert_id($res);
		if(empty($last_insert_id)) {
			error_message('Error inserting into table log');
			return false;
		}

		# inserting into the field log table.. get all the rows and insert them.
		$id = $last_insert_id;

		$q = '';
		foreach($row as $col => $val) {
			if(in_array($col,array('date_created','date_modified','datetime','created','modified')) || $col == $table_info['primary_key']) { continue; }
			$q .= "(". $id .",'". $col ."','','". $val ."',". $datetime ."),";
		}
		$q = "insert into ". $field_table ." (table_log_id,column_name,old_value,new_value,created) values ". substr($q,0,-1);
		$res = db_query($q,'audit: insert into field log');
		if(db_is_error($res)) {
			error_message('Error inserting into field log');
			return false;
		}

	}
	
}


function audit_table_update(&$table_info) {
	$user_id = (!empty($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 0);
	if(empty($table_info['audit_table'])) { $table_info['audit_table'] = $table_info['table_schema'].'_table_log'; }
	if(empty($table_info['original_values'])) { return true; }

	$schema = "";
	$datetime = time();
	$pk_table = $table_info['audit_table'];
	if(uses_schema()) {
		$schema = (!empty($table_info['audit_schema']) ? $table_info['audit_schema'] : '"audits"');
		$datetime = "now()";
		$pk_table = '"'. $schema .'"."'. $table_info['audit_table'] .'"';
	}

	// $field_table = $table_info['audit_schema'].".".str_replace('_table_log','_field_log',$table_info['audit_table']);
	$field_table = str_replace('_table_log','_field_log',$pk_table);

	foreach($table_info['table_columns'] as $k => $row) {
	
		$q = "insert into ". $pk_table ." (schema_name, table_name, primary_key_id, user_id,created) values 
			('". $schema ."','". $table_info['table_name'] ."','". $table_info['primary_key_value'] ."','". $user_id ."',". $datetime .")
		";
		$res = db_query($q,'audit: insert into table log');
		$last_insert_id = db_insert_id($res);
		if(empty($last_insert_id)) {
			error_message('Error inserting into table log');
			return false;
		}

		# inserting into the field log table.. get all the rows and insert them.
		$id = $last_insert_id;

		$q = '';
		foreach($row as $col => $val) {
			if(in_array($col,array('date_created','date_modified','datetime','created','modified')) || $col == $table_info['primary_key']) { continue; }
			if($table_info['original_values'][$col] != $val) {
				$q .= "(". $id .",'". $col ."','". db_prep_sql($table_info['original_values'][$col]) ."','". db_prep_sql($val) ."',". $datetime ."),";
			}
		}
		if(empty($q)) { return true; }
		$q = "insert into ". $field_table ." (table_log_id,column_name,old_value,new_value,created) values ". substr($q,0,-1);
		$res = db_query($q,'audit: insert into field log');
		if(db_is_error($res)) {
			error_message('Error inserting into field log');
			return false;
		}
		
		if(!empty($table_info['additional_fields'])) {
			$q = '';
			foreach($table_info['additional_fields'] as $column => $row) {
				$q .= "(". $id .",'". $column ."','". $row['old'] ."','". $row['new'] ."',". $datetime ."),";	
			}
			$q = "insert into ". $field_table ." (table_log_id,column_name,old_value,new_value,created) values ". substr($q,0,-1);
			$res = db_query($q,'audit: insert additional columns into field log');
			if(db_is_error($res)) {
				error_message('Error inserting into field log');
				return false;
			}
		}
	}

}

?>