<?php
if(!empty($_POST) && !error_message()) {
	library("validation.php");
	
	if(!validate($_POST["id"],"required","ID")) { error_message(get_validation_error()); }

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
		$active = 0;
		if(uses_schema()) {
			$table_info["table_schema"] = "public";
			$table_info["audit_schema"] = "audits";
			$table_info["audit_table"] = "public_table_logs";
			$active = "false";
		}

		$table_info["table_columns"][] = array(
			"active" => $active
		);

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
			set_post_message("The record has been successfully deleted");
			set_safe_redirect($redirection_path);

		} else {
			error_message("An error has occurred while trying to delete this record");
		}
	}
}