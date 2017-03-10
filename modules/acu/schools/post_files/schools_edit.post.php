<?php
if(!empty($_POST) && !error_message()) {
	library("validation.php");
	library("membership.php");

	// define("HONEYPOT","asdfadsofjas32940d");
	// $_POST["firstname"] = "";
	// $_POST["password1"] = "1";

	// validation_custom("email_db",function() {
	// 	return false;
	// },"Email DB failed");
	// 
	$json = validation_create_json_string(validation_load_file(__DIR__ ."/../validation.json"),"php");
	validate_from_json($json);

	error_message(get_all_validation_errors());
	_error_debug("all validate errors",get_all_validation_errors());

	if(!error_message()) {

		$table_info = array(
			"table_name" => "institutions"
			,"primary_key" => "id"
			# optional
			,"audit_table" => "public_table_logs"
			,"primary_key_value" => db_prep_sql($_POST["id"])
			# Key = DB column Name, Value = Post name
			,"table_columns" => array()
		);
		if(uses_schema()) {
			$table_info["table_schema"] = "public";
			$table_info["audit_schema"] = "audits";
			$table_info["audit_table"] = "public_table_logs";
		}

		$arr = array(
			"title" => db_prep_sql(trim($_POST["title"]))
			,"institution_type_id" => db_prep_sql($_POST["institution_type_id"])
			,"address1" => db_prep_sql(trim($_POST["address1"]))
			,"address2" => db_prep_sql(trim($_POST["address2"]))
			,"site_name" => db_prep_sql(trim($_POST["site_name"]))
			,"city" => db_prep_sql(trim($_POST["city"]))
			,"region_id" => db_prep_sql((int)$_POST["region_id"])
			,"postal_code" => db_prep_sql(trim($_POST["postal_code"]))
			,"country_id" => db_prep_sql((int)$_POST["country_id"])
			,"population" => db_prep_sql((int)$_POST["population"])
			,"email" => db_prep_sql((int)$_POST["email"])
			,"phone" => db_prep_sql((int)$_POST["phone"])
		);

		$table_info["table_columns"][] = $arr;

		$res = "";
		if(($original_values = post_has_changes($table_info)) !== false) {
			$table_info["original_values"] = $original_values;

			if(($res = post_functions_update($table_info)) === false) {
				error_message("An error has occured trying to update this record");
			}
		} else {
			error_message("Comparison table is incorrect");
		}

		if(!error_message()) {
			audit("table_update",$table_info);

			$redirection_path = "/acu/schools/";
			set_post_message("The record has been successfully updated");
			set_safe_redirect($redirection_path);

		} else {
			error_message("An error has occurred while trying to update this record");
		}
	}
}