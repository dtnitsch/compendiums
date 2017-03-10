<?php
# Required 
#	'table_name' - The table name
#	'table_schema' => The tables schema
#	'audit_table' => The audit table
#	'audit_schema' => The audit table schema

# Optional
#	'db_connection_name' => Name of the connection to the DB (set in config.php)
#	'returning_value' => If there is a value to return:
#							postgresql - set column name to return
#							mysql: set value to true to get last_insert_id()

function post_functions_insert($arr) {
	_error_debug(__FUNCTION__."()");

	$db_columns_sql = "";
	$db_values_sql = "";
	# Multiple Inserts can occur.  Run through all inserts
	foreach($arr["table_columns"] as $k => $row) {
		$db_values = "";
		foreach($row as $column => $value) {
			if($k == 0) { $db_columns_sql .= $column .","; }
			$db_values .= ($value == "now()" ? $value : "'". $value ."'") .",";
		}
		$db_values = "(". substr($db_values,0,-1) ."),";
		$db_values_sql .= $db_values;
	}

	$q = "insert into ". (!empty($arr["table_schema"]) ? $arr["table_schema"]."." : "") . $arr["table_name"] ." (";
	$q .= substr($db_columns_sql,0,-1);
	$q .= ") values ". substr($db_values_sql,0,-1);
	
	# Checks if we are returning values - Such as last_insert_id of the table primary key
	$db_type = "";
	if(!empty($arr["returning_value"])) { 
		if(!empty($arr["db_connection_name"])) {
			$db_type = $GLOBALS["db_options"]["db_types"][$arr["db_connection_name"]];
		} else {
			$db_type = $db_type = $GLOBALS["db_options"]["db_types"]["default"];
		}
		if(strstr(strtolower($db_type),"postgresql")) {
			$q .= ' returning '. $arr["returning_value"];
		}
	}
	$res = db_query($q,"Inserting New Record: ". (!empty($arr["table_schema"]) ? $arr["table_schema"]."." : "") . $arr["table_name"]);
	if(!db_is_error($res)) {
		if(!empty($arr["returning_value"])) {
			$last_insert_id = array();
			if($db_type != "") {
				if(strstr(strtolower($db_type),"mysql")) {
					$last_insert_id[] = db_insert_id($res);
				} else {
					while($row = db_fetch_row($res)) {
						$last_insert_id[] = $row[$arr["returning_value"]];
					}
				}
			}
			return $last_insert_id;
		} else {
			return "";
		}
	} else {
		return false;
	}
}


function post_functions_update($arr,$update_modified = true) {
	_error_debug(__FUNCTION__."()");

	$escape = (uses_schema() ? '"' : '`');
	$db_values = "";
	foreach($arr["table_columns"] as $k => $row) {
		foreach($row as $column => $value) {
			if(isset($arr["original_values"][$column])) {
				if($arr["original_values"][$column] != $value) {
					$db_values .= $escape . $column . $escape ."='". $value ."',";
				}
			} else {
				$db_values .= $escape . $column . $escape ."='". $value ."',";
			}
		}
	}
	if(empty($db_values)) { return true; }
	if($update_modified) {
		$db_values .= $escape . 'modified' . $escape ."=now(),";
	}

	$update_sql = "update ". (!empty($arr["table_schema"]) ? $arr["table_schema"]."." : "") . $arr["table_name"] ." set ". substr($db_values,0,-1) ."
		where ". $arr["primary_key"] ."=". $arr["primary_key_value"];

	$res = db_query($update_sql,"Update Record: ". $arr["primary_key_value"]);
	if(db_is_error($res)) {
		return false;
	}
	return $res;
}

function post_has_changes($table_info,$column_num=0) {
	$q = "select ". implode(",",array_keys($table_info["table_columns"][$column_num])) ."
		from ". (!empty($table_info["table_schema"]) ? $table_info["table_schema"]."." : "") . $table_info["table_name"] ."
		where ". $table_info["primary_key"] ."='". $table_info["primary_key_value"] ."'
	";
	$original_values = db_fetch($q, "update: getting original values");
	// Bad table or no results
	if(empty($original_values)) { return false; }
	foreach($original_values as $k => $v) {
		#if(!isset($_POST[$k])) { continue; }
		$_POST[$k] = trim($_POST[$k]);
		if(!isset($_POST[$k]) || $_POST[$k] != $v) {
			return $original_values;
			break;
		}
	}
	return array();
}

function set_post_message($msg) {
	_error_debug(__FUNCTION__."()");
	if(empty($_SESSION["post_information"]["successful"])) { $_SESSION["post_information"]["successful"] = $msg; }
}

function perform_post_actions() {
	_error_debug(__FUNCTION__."()");

	$_SESSION["post_information"]["module_queue"] = json_decode($_SESSION["post_modules"],true);
	unset($_SESSION["post_modules"]);

	$_SESSION["error_message"] = array();
	$redirect = "";

	// Run any pre_hooks before the transactions
	run_post_transaction_hooks("pre_hook");

	$error_occured = false;
	if (function_exists("db_transaction_start")) { db_transaction_start(); }

	foreach(array_keys($_SESSION["post_information"]["module_queue"]) as $file) {
		$file = str_replace("//",'/',$GLOBALS["root_path"] . $file);
		if(!is_file($file)) {
			error_message("An error has occured");
			_error_debug("Post File Missing: ",$file,__LINE__,__FILE__,E_ERROR);
		}
		_error_debug("Including File: ". $file);
		include($file);
		if(error_message()) {
			if (function_exists("db_transaction_rollback")) { db_transaction_rollback(); }
			$error_occured = true;
			#break;
		}
	}

	if(!$error_occured) {
		if(function_exists("db_transaction_commit")) { db_transaction_commit(); }
		if(!empty($_SESSION["post_information"]["redirect"])) {
			if(!empty($_SESSION["post_information"]["successful"])) {
				info_message($_SESSION["post_information"]["successful"]);
			}
			$redirect = $_SESSION["post_information"]["redirect"];
			unset($_SESSION["post_information"]["successful"]);
			unset($_SESSION["post_information"]["redirect"]);
		}
	}

	// Run any pre_hooks before the transactions
	run_post_transaction_hooks("post_hook");

	if(!empty($redirect)) {
		safe_redirect($redirect);
	}
}

function post_set_transaction_hook($hook,$query,$label = "") {
	$GLOBALS["post_information"][$hook][] = array($query,$label);
	return true;

}
// "pre_hook" or "post_hook"
function run_post_transaction_hooks($hook) {
	if(!empty($GLOBALS["post_information"][$hook])) {
		foreach($GLOBALS["post_information"][$hook] as $row) {
			db_query($row[0],$row[1]);
		}
	}
}
function post_pre_transaction_hook($query,$label = "") {
	$query = trim($query);
	if(empty($query)) { return false; }
	return post_set_transaction_hook("pre_hook",$query,$label);
}
function post_post_transaction_hook($query,$label = "") {
	$query = trim($query);
	if(empty($query)) { return false; }
	return post_set_transaction_hook("post_hook",$query,$label);
}
