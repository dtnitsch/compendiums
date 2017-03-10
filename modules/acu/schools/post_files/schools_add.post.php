<?php

if (!empty($_POST) && !error_message()) {

	library("validation.php");

	validate($_POST["name"], ["required", "string_length_between: 1, 128"], "School Name");
	validate($_POST["address"], ["required", "string_length_between: 1, 128"], "Address");
	validate($_POST["city"], ["required", "string_length_between: 1, 64"], "City");
	validate($_POST["region_id"], ["required", "int"], "State");
	validate($_POST["postal_code"], ["required", "string_length_between: 1, 128"], "Postal Code");
	validate($_POST["country_id"], ["required", "int"], "Country");
	validate($_POST["phone"], ["required", "string_length_between: 1, 64"], "Phone");
	validate($_POST["population"], ["required", "int:between:1, 8"], "Population");

	if (!error_message()) {

		$table_info = array(
			"table_name" => "institutions"
			,"table_schema" => "public"
			,"primary_key" => "id"
			# optional
			,"audit_table" => "public_table_logs"
			,"audit_schema" => "audits"
			,"returning_value" => "id"
			,"primary_key_value" => ""
			# Key = DB column Name, Value = Post name
			,"table_columns" => array()
		);

		$table_info["table_columns"][] = array(
			"institution_type_id" => 1 // Force School Type
			,"user_id" => $_SESSION["user"]["id"]
			,"title" => db_prep_sql(trim($_POST["name"]))
			,"address1" => db_prep_sql(trim($_POST["address"]))
			,"city" => db_prep_sql(trim($_POST["city"]))
			,"region_id" => db_prep_sql((int) $_POST["region_id"])
			,"postal_code" => db_prep_sql(trim($_POST["postal_code"]))
			,"country_id" => db_prep_sql((int) $_POST["country_id"])
			,"phone" => db_prep_sql(trim($_POST["phone"]))
			,"population" => db_prep_sql((int) $_POST["population"])
			,"created" => "now()"
			,"modified" => "now()"
		);

		$new_id = post_functions_insert($table_info)[0];
		
		$has_error = (empty($new_id) || !is_numeric($new_id) ? true : false);

		if (!$has_error) { 

			$table_info["primary_key_value"] = $new_id;

			audit("table_insert", $table_info);

			$redirection_path = "/acu/schools/?id=".$new_id;
			set_post_message("You have successfully created a new school");
			set_safe_redirect($redirection_path);

		} else {
			$has_error = true;
		}

		if ($has_error) {
			error_message("An error has occurred while trying to create a new school");
		}

	}

}