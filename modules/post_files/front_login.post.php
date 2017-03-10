<?php

if (!empty($_POST) && !error_message()) {

	library("membership.php");

	if (strtolower($_POST["type"]) == "forgot") {
		forgot_password();
	} else if (!empty($_POST["password_change"])) {
		password_update();
	} else {
		login();
	}

}

function login() {

	$login = trim(!empty($_POST["login_input"]) ? $_POST["login_input"] : "");

	# Check to see if this is a submission of the login form
	if ($login != "") {

		$login = $_POST["login_input"];

		if (strpos($login, "@") !== false) { $column = "email"; }

		#################################################################################
		#	Admin User Login
		#################################################################################

		$users_table = "users";
		if (uses_schema()) { $users_table = '"system"."users"'; }

		$students_table = "students";
		if (uses_schema()) { $students_table = '"system"."students"'; }

		$institutions_table = "institutions";
		if (uses_schema()) { $institutions_table = '"public"."institutions"'; }

		$q = "
			select
				u.*
				,i.id as institution_id
				,count(s.id) as student_count
			from ".$users_table." as u
			left join ".$students_table." as s on
				s.user_id = u.id
				and s.active = 't'
			left join ".$institutions_table." as i on
				i.user_id = u.id
				and i.active = 't'
			where
				u.active = 't'
				and (
					u.username = '".$login."'
					or lower(u.email) = '".strtolower($login)."'
				)
			group by
				u.id
				,s.user_id
				,i.id
			limit
				1
		";

		$info = db_fetch($q, "Authorization Check - Verifying Login.");

		if (!empty($info["id"])) {

			$has_error = false;

			if (empty($info["student_count"])) {

				$q = "
					insert into ".$students_table." (
						ethicspledge
						,gender_id
						,user_id
						,institution_id
						,grade_id
						,firstname
						,ethicspledge_date
						,created
						,modified
					)
					values (
						't'
						,0
						,".$info["id"]."
						,".(empty($info["institution_id"]) ? 0 : $info["institution_id"])."
						,18
						,'".$info["firstname"]."'
						,'now()'
						,'now()'
						,'now()'
					)
				";

				$res = db_query($q);

				if (db_affected_rows($res)) {

					$student_id = db_insert_id($res);

					if (empty($student_id)) {
						$has_error = true;
					}

					audit("failed_login_attempts", $student_id);

				}

			}

			if (!$has_error) {

				if (user_compare_passwords($_POST["login_password"], $info["password"], $info["password_salt"])) {

					$_SESSION["is_guest"] = false;

					# Set the session variables that will be used in the rest of the site
					$_SESSION["user"]["firstname"] = $info["firstname"];
					$_SESSION["user"]["lastname"] = $info["lastname"];
					$_SESSION["user"]["email"] = $info["email"];
					$_SESSION["user"]["id"] = $info["id"];
					if(!empty($info["username"])) {
						$_SESSION["user"]["username"] = $info["username"];
					}

					$_SESSION["user"]["is_superadmin"] = 0;


					audit("logins", $info["id"]);
					safe_redirect("/myaccount/");

				} else {
					# Nothing came back for this email address in the DB.  Generic message ensues.
					error_message("Authentication failed<!--(1)-->.");
				}

			}

		} else {
			error_message("Authentication failed<!--(2)-->.");
		}

	} else {
		error_message("Authentication failed<!--(3)-->.");
	}

	audit("failed_login_attempts", $login);
}


function forgot_password() {

	$user_id = 0;
	if($_POST['forgot_password'] == "") { 
		error_message("You must enter an Email Address.");
	} else if(preg_match("/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/",$_POST['forgot_password'])) {
		$q = "select id from system.users where lower(email) = '".db_prep_sql(strtolower($_POST["forgot_password"]))."'";
		$email_check = db_query($q,'Error Checking: Email Duplication');
		if(db_num_rows($email_check) == 0) { error_message("The email address '". $_POST['forgot_password'] ."' does not exist."); }
		$tmp = db_fetch_row($email_check);
		$user_id = $tmp['id'];
	} else {
		error_message("The email address '". $_POST['forgot_password'] ."' is not valid.");
	}

	if(!empty($user_id)) {
		$q = "delete from system.forgot_password where user_id='". $user_id ."'";
		db_query($q,"clearing old forgot passwords");
	}

	if(!error_message()) {

		$table_info = array(
			'table_name' => 'forgot_password',
			'table_schema' => 'system',
			'primary_key' => 'forgot_password_id',
			# optional
			#'audit_table' => 'system_table_log',
			#'audit_schema' => 'audits',
			#'returning_value' => 'user_id',
			'primary_key_value' => db_prep_sql($user_id),
			# Key = DB column Name, Value = Post name
			'table_columns' => array()
		);

		$unique_value = sha1($user_id.$_POST['forgot_password'].time());
		$table_info['table_columns'][] = array(
			'user_id' => db_prep_sql($user_id),
			'unique_value' => $unique_value
		);

		$res = post_functions_insert($table_info);

		if($res != 'error') {

			$to      = trim($_POST['forgot_password']);
			$subject = 'Forgot Password - '. $GLOBALS['project_info']['name'];
			$message = "
Per your request, we are sending you a link to reset your password to '". $GLOBALS['project_info']['name'] ."'.

Please follow the link below, to set up your new password.  You may either click the link, or copy and paste the link into your web browser.

The link will be active for about 24 hours from the time we sent this email to you.  If the link no longer functions, please try to use the 'Forgot Password' script again.

http://". $_SERVER['HTTP_HOST'] . $GLOBALS['project_info']['path_data']['path'] ."?u=". $unique_value ."

Sincerely,
The team at ". $GLOBALS['project_info']['company_name'];

			$headers = 'From: noreply@'. $_SERVER['HTTP_HOST'] . "\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			if(!mail($to, $subject, $message, $headers)) {
				error_message('An error has occured');
			}

			if(!error_message()) {
				// $redirection_path = '/player-login/';
				// set_post_message("Password Successfully Changed");
				// set_safe_redirect($redirection_path);
			}

		}
	}


}

function password_update() {
	$user_id = (!empty($_POST["user_id"]) ? $_POST["user_id"] : 0);
	if(empty($user_id)) {
		$user_id = (!empty($_POST["id"]) ? $_POST["id"] : 0);
	}
	if(empty($user_id)) {
		$user_id = (!empty($GLOBALS["password_id"]) ? $GLOBALS["password_id"] : 0);
	}

	list($enctypted_password,$salt) = user_hash_passwords($_POST['password1']);

	$table_info = array(
		'table_name' => 'users',
		'table_schema' => 'system',
		'primary_key' => 'id',
		# optional
		'audit_table' => 'system_table_log',
		'audit_schema' => 'audits',
		'returning_value' => '',
		'primary_key_value' => $user_id,
		# Key = DB column Name, Value = Post name
		'table_columns' => array()
	);

	$table_info['table_columns'][] = array(
		'password' => db_prep_sql($enctypted_password),
		'password_salt' => db_prep_sql($salt,"bytea")
	);

	if(($res = post_functions_update($table_info)) === false) {
		error_message("An error has occured trying to update this record");
	}

	if(!error_message()) {
		$redirection_path = "/player-login/";
		set_post_message("The record has been successfully updated");
		set_safe_redirect($redirection_path);
	}

}