<?php
if (!empty($_POST) && !error_message()) {

	library("membership.php");
	library("validation.php");

	if (!empty($_POST["acu_password_reset"]) && $_POST["acu_password_reset"] == true) {

		if ($_POST["password_acu1"] == "" && empty($_POST["id"])) {
			error_message("You must enter a Password and Confirmation Password.");
		} else if($_POST["password_acu1"] != $_POST["password_acu2"]) {
			error_message("Your Passwords do not match. Please re-enter them.");
		}

	} else if (!empty($_POST["front_password_reset"]) && $_POST["front_password_reset"] == true) {

		if ($_POST["password1"] == "" && empty($_POST["id"])) {
			error_message("You must enter a Password and Confirmation Password.");
		} else if($_POST["password1"] != $_POST["password2"]) {
			error_message("Your Passwords do not match. Please re-enter them.");
		}

	} else {

	}

	if (!error_message()) {

		$user_id = (!empty($_POST["user_id"]) ? $_POST["user_id"] : 0);

		if (empty($user_id)) {
			$user_id = (!empty($_POST["id"]) ? $_POST["id"] : 0);
		}

		if(empty($user_id)) {
			$user_id = (!empty($GLOBALS["password_id"]) ? $GLOBALS["password_id"] : 0);
		}

		if(!empty($_POST['password1'])) {

			$id = 0;
			if(!empty($_POST['id'])) { $id = $_POST['id']; }
			else if(!empty($GLOBALS['password_id'])) { $id = $GLOBALS['password_id']; }

			list($enctypted_password,$salt) = user_hash_passwords($_POST['password1']);

			$table_info = array(
				'table_name' => 'users',
				'table_schema' => 'system',
				'primary_key' => 'id',
				# optional
				// 'audit_table' => 'system_table_log',
				// 'audit_schema' => 'audits',
				'returning_value' => '',
				'primary_key_value' => $user_id,
				# Key = DB column Name, Value = Post name
				'table_columns' => array()
			);

			$table_info['table_columns'][] = array(
				'password' => db_prep_sql($enctypted_password)
				,'password_salt' => db_prep_sql($salt,"bytea")
			);

			if(($res = post_functions_update($table_info)) === false) {
				error_message("An error has occured trying to update this record");
			}
		}

		if(!empty($_POST["password_acu1"])) {

			list($enctypted_password,$salt) = user_hash_passwords($_POST["password_acu1"]);

			$table_info = array(
				'table_name' => 'users',
				'table_schema' => 'system',
				'primary_key' => 'id',
				# optional
				// 'audit_table' => 'system_table_log',
				// 'audit_schema' => 'audits',
				'returning_value' => '',
				'primary_key_value' => $user_id,
				# Key = DB column Name, Value = Post name
				'table_columns' => array()
			);

			$table_info['table_columns'][] = array(
				'password_acu' => db_prep_sql($enctypted_password)
				,'password_salt_acu' => db_prep_sql($salt,"bytea")
			);

			if(($res = post_functions_update($table_info)) === false) {
				error_message("An error has occured trying to update this record");
			}
		}

		if(!error_message()) {
			$redirection_path = "/login/";
			set_post_message("The record has been successfully updated");
			set_safe_redirect($redirection_path);
		}
	}
}
