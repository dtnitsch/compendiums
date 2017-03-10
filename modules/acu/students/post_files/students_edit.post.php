<?php
if (!empty($_POST) && !error_message()) {

	library("validation.php");
	library("membership.php");

	$json = validation_create_json_string(validation_load_file(__DIR__."/../validation.json"),"php");
	validate_from_json($json);

	error_message(get_all_validation_errors());
	_error_debug("all validate errors",get_all_validation_errors());

	if (!error_message()) {

		$table_info = array(
			"table_name" => "students"
			,"primary_key" => "id"
			# optional
			,"audit_table" => "system_table_logs"
			,"primary_key_value" => db_prep_sql($_POST["id"])
			# Key = DB column Name, Value = Post name
			,"table_columns" => array()
		);

		if (uses_schema()) {
			$table_info["table_schema"] = "system";
			$table_info["audit_schema"] = "audits";
			$table_info["audit_table"] = "system_table_logs";
		}

		$gender_id = (empty($_POST["gender_id"]) ? 0 : $_POST["gender_id"]);

		$arr = array(
			"firstname" => $_POST["firstname"]
			,"lastname" => $_POST["lastname"]
			,"gender_id" => $gender_id
			,"grade_id" => $_POST["grade_id"]
			,"institution_id" => $_POST["institution_id"]
		);

		$table_info["table_columns"][] = $arr;

		$res = "";

		if (($original_values = post_has_changes($table_info)) !== false) {

			$table_info["original_values"] = $original_values;

			if(($res = post_functions_update($table_info)) === false) {
				error_message("An error has occured trying to update this record");
			}

		} else {
			error_message("Comparison table is incorrect");
		}


		if (!error_message() && $res != "error") {

			audit("table_update",$table_info);

			$redirection_path = "/acu/students/";
			set_post_message("The record has been successfully updated");
			set_safe_redirect($redirection_path);

		} else {
			error_message("An error has occurred while trying to update this record");
		}

	}

}
