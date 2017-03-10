<?php

if (!empty($_POST) && empty($_POST["password_change"])) {

	$forgot_admin = ($_SERVER["SCRIPT_URL"] == "/acu/forgot_password/" ? true : false);

	$user_id = 0;

	if ($_POST["email"] == "") {
		error_message("You must enter an Email Address.");
	} else if (preg_match("/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $_POST["email"])) {

		$q = "
			select
				id
				,is_superadmin
			from system.users
			where
				email='". db_prep_sql($_POST["email"]) ."'
		";

		$email_check = db_query($q, "Error Checking: Email Duplication");

		if (db_num_rows($email_check) == 0) {
			error_message("The email address '". $_POST["email"] ."' does not exist.");
		}

		$tmp = db_fetch_row($email_check);

		$user_id = $tmp["id"];
		$is_admin = ($forgot_admin && $tmp["is_superadmin"] == "t" ? "t" : "f");

	} else {
		error_message("The email address '". $_POST["email"] ."' is not valid.");
	}

	if (!empty($user_id)) {

		$q = "
			delete from system.forgot_password
			where
				user_id = ".db_prep_sql((int) $user_id)."
		";

		db_query($q, "clearing old forgot passwords");

	}

	if (!error_message()) {

		$table_info = array(
			"table_name" => "forgot_password"
			,"table_schema" => "system"
			,"primary_key" => "forgot_password_id"
			# optional
			#,"audit_table" => "system_table_log"
			#,"audit_schema" => "audits"
			#,"returning_value" => "user_id"
			,"primary_key_value" => db_prep_sql($user_id)
			# Key = DB column Name, Value = Post name
			,"table_columns" => array()
		);
		
		$unique_value = sha1($user_id.$_POST["email"].time());

		$table_info["table_columns"][] = array(
			"user_id" => db_prep_sql($user_id)
			,"is_admin" => $is_admin
			,"unique_value" => $unique_value
		);

		$res = post_functions_insert($table_info);

		if ($res != "error") {

			$to = trim($_POST["email"]);
			$subject = 'Forgot Password - '. $GLOBALS["project_info"]["name"];
			$message = "
				Per your request, we are sending you a link to reset your password to '". $GLOBALS["project_info"]["name"] ."'.

				Please follow the link below, to set up your new password.  You may either click the link, or copy and paste the link into your web browser.

				The link will be active for about 24 hours from the time we sent this email to you.  If the link no longer functions, please try to use the 'Forgot Password' script again.

				http://".$_SERVER["HTTP_HOST"].($is_admin == "t" ? "/acu/forgot_password/" : "/player-login/")."?u=".$unique_value."

				Sincerely,
				The team at ". $GLOBALS["project_info"]["company_name"]."
			";

			$headers = 'From: noreply@'. $_SERVER["HTTP_HOST"] . "\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			if(!mail($to, $subject, $message, $headers)) {
				error_message("An error has occured");
			}

			if(!error_message()) {
				$redirection_path = "/acu/";
				set_post_message("Password Reset Email Sent");
				set_safe_redirect($redirection_path);
			}

		}
	}
}
