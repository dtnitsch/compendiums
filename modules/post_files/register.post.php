<?php

if(!empty($_POST)) {
	library("membership.php");


	$user_id = 0;
	$_POST['username'] = trim($_POST['username']);

	if($_POST["username"] == "") {
		error_message("You must enter a Username.");
	} else {

		$q = "
			select
				id
			from system.users
			where
				username='". db_prep_sql($_POST["username"]) ."'
		";

		$username_check = db_fetch($q, "Error Checking: username Duplication");

		if(!empty($username_check)) {
			error_message("The username '". $_POST["username"] ."' already exists.");
		}
	}

	if(!error_message()) {

		// $table_info = array(
		// 	"table_name" => "users"
		// 	,"table_schema" => "system"
		// 	,"primary_key" => "id"
		// 	# optional
		// 	,"audit_table" => "system_table_logs"
		// 	,"audit_schema" => "audits"
		// 	,"returning_value" => "id"
		// 	,"primary_key_value" => ""
		// 	# Key = DB column Name, Value = Post name
		// 	,"table_columns" => array()
		// );

		// $table_info["table_columns"][] = array(
		// 	"firstname" => db_prep_sql($_POST["firstname"])
		// 	,"lastname" => db_prep_sql($_POST["lastname"])
		// 	,"email" => db_prep_sql(strtolower($_POST["email"]))
		// 	,"password" => db_prep_sql($_POST["password1"])
		// 	,"is_superadmin" => "f"
		// 	,'created' => 'now()'
		// 	,'modified' => 'now()'
		// );

		// $new_id = post_functions_insert($table_info)[0];
		// $new_id = create_new_user($table_info["table_columns"][0]);

		// $has_error = (empty($new_id) || !is_numeric($new_id) ? true : false);
		// if(!$has_error) { 
		// 	$table_info["primary_key_value"] = $new_id;
		// 	$GLOBALS["password_id"] = $new_id;

		// 	audit("table_insert",$table_info);

			$id = create_new_user([
			    "username" => $_POST["username"]
			    ,"password" => $_POST["password"]
			    ,"is_superadmin" => "f"
			]);

			$redirection_path = "/profile/";
			set_post_message("You have successfully created a new record");
			set_safe_redirect($redirection_path);

		// } 
		
	}
	if($has_error) {
		error_message("An error has occurred while trying to create a new record");
	}

}