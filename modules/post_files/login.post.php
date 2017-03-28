<?php

if(!empty($_POST) && !error_message() && !empty($_POST["login_input"])) {
	library("membership.php");

	$login = trim(!empty($_POST["login_input"]) ? $_POST["login_input"] : "");

	# Check to see if this is a submission of the login form
	if($login != "") { 
		$login = $_POST["login_input"];
		$column = "username";
		if(strpos($login,"@") !== false) { $column = "email"; }

		#################################################################################
		#	Admin User Login
		#################################################################################

		$users_table = "users";
		if(uses_schema()) { $users_table = '"system"."users"'; }

		$q = "
			select
				u.*
			from ".$users_table." as u
			where
				u.active = 't'
				and (
					u.username = '".$login."'
					or lower(u.email) = '".strtolower($login)."'
				)
			group by
				u.id
			limit
				1
		";

		$info = db_fetch($q, "Authorization Check - Verifying Login.");

		if(!empty($info["id"])) {

			if(user_compare_passwords($_POST["login_password"], $info["password"], $info["password_salt"])) {

				$_SESSION["is_guest"] = false;

				# Set the session variables that will be used in the rest of the site
				$_SESSION["user"]["id"] = $info["id"];
				$_SESSION["user"]["username"] = $info["username"];

				audit("logins",$info["id"]);
				safe_redirect("/");

			} else {
				# Nothing came back for this email address in the DB.  Generic message ensues.
				error_message("Authentication failed<!--(1)-->.");
			}
		} else {
			error_message("Authentication failed<!--(2)-->.");
		}

	} else {
		error_message("Authentication failed<!--(3)-->.");
	}

	audit("failed_login_attempts",$login);
}
?>