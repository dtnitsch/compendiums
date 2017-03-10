<?php
if(!empty($_POST) && !error_message()) {
	library("validation.php");
	library("membership.php");

	// if(!validate($_POST["firstname"],["required","string_length_between:1,128"],"First Name")) { error_message(get_validation_error()); }
	// if(!validate($_POST["lastname"],["required","string_length_between:1,128"],"Last Name")) { error_message(get_validation_error()); }
	// if(!validate($_POST["email"],["required","email"],"Email Address")) { error_message(get_validation_error()); }

	// if($_POST['password1'] == "" && empty($_POST['id'])) {
	// 	error_message("You must enter a Password and Confirmation Password.");
	// } else if($_POST['password1'] != $_POST['password2']) { 
	// 	error_message("Your Passwords do not match.  Please re-enter them.");
	// }

	$_POST["id"] = true;
	$json = validation_create_json_string(validation_load_file(__DIR__ ."/../validation.json"),"php");
	validate_from_json($json);
	error_message(get_all_validation_errors());
	
	if(!error_message()) {

		$table_info = array(
			"table_name" => "users"
			,"table_schema" => "system"
			,"primary_key" => "id"
			# optional
			,"audit_table" => "system_table_logs"
			,"audit_schema" => "audits"
			,"returning_value" => "id"
			,"primary_key_value" => ""
			# Key = DB column Name, Value = Post name
			,"table_columns" => array()
		);

		$table_info["table_columns"][] = array(
			"firstname" => db_prep_sql($_POST["firstname"])
			,"lastname" => db_prep_sql($_POST["lastname"])
			,"email" => db_prep_sql(strtolower($_POST["email"]))
			,"password" => db_prep_sql($_POST["password1"])
			,"is_superadmin" => (!empty($_POST["is_superadmin"]) && $_POST["is_superadmin"] == "t" ? "t" : "f")
			,'created' => 'now()'
			,'modified' => 'now()'
		);

		// $new_id = post_functions_insert($table_info)[0];
		$new_id = create_new_user($table_info["table_columns"][0]);

		$has_error = (empty($new_id) || !is_numeric($new_id) ? true : false);
		if(!$has_error) { 
			$table_info["primary_key_value"] = $new_id;
			$GLOBALS["password_id"] = $new_id;

			audit("table_insert",$table_info);

			$redirection_path = "/acu/users/";
			set_post_message("You have successfully created a new record");
			set_safe_redirect($redirection_path);

		} 
		
	}
	if($has_error) {
		error_message("An error has occurred while trying to create a new record");
	}

}