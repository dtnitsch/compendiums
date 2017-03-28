<?php
##################################################
#	User Password Creation
#
#	Return array(password,salt)
##################################################
function user_hash_passwords($password,$hash_type="sha256") {
	$hash = hash($hash_type, $password);
	$salt = openssl_random_pseudo_bytes(32);
	return array(hash($hash_type, $salt . $hash),$salt);
}

function user_compare_passwords($input,$password,$salt,$hash_type="sha256") {
	$hash = hash($hash_type, $input);
	// change this out with something less hard-codey
	if(uses_schema()) {
		$salt = pg_unescape_bytea($salt);
	}
	$password_hash = hash($hash_type, $salt . $hash);
	return ($password_hash == $password ? true : false);
}

function user_logout($url = "/") {
	// Unset all of the session variables.
	$_SESSION = array();

	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if (ini_get("session.use_cookies")) {
	    $params = session_get_cookie_params();
	    setcookie(session_name(), "", time() - 42000,
	        $params["path"], $params["domain"],
	        $params["secure"], $params["httponly"]
	    );
	}

	// Finally, destroy the session.
	session_destroy();

	if (!empty($GLOBALS["activity_info"])) {
		unset($GLOBALS["activity_info"]);
	}

	safe_redirect($url);
}

function user_login($user_id) {

	if(empty($user_id)) { return false; }

	$table = "users";
	if(uses_schema()) { $table = '"system"."users"'; }

	$q = "select id,username,firstname,lastname,email,password from ". $table ." where id=".$user_id;
	$row = db_fetch($q,"Getting login info");

	if(empty($row)) { return false; }

	list($password,$password_salt) = user_hash_passwords($_POST["password"]);
	
	if(!user_compare_passwords($_POST["password"],$password,$password_salt)) {

		# Set the session variables that will be used in the rest of the site
		$_SESSION["user"]["firstname"] = $row["firstname"];
		$_SESSION["user"]["lastname"] = $row["lastname"];
		$_SESSION["user"]["username"] = $row["username"];
		$_SESSION["user"]["email"] = $row["email"];
		$_SESSION["user"]["user_id"] = $row["user_id"];
		$_SESSION["user"]["is_superadmin"] = (!empty($row["is_superadmin"]) && $row["is_superadmin"] ? 1 : 0);
		
		return true;
	} 

	return false;
}

function create_new_user($info) {

	if(empty($info["username"])) { return false; }
	else if(empty($info["password"])) { return false; }

	list($password, $password_salt) = user_hash_passwords($info["password"]);

	$username = (!empty($info["username"]) ? $info["username"] : 0);

	$table = "users";

	// $datetime = time();

	if (uses_schema()) {
		$table = '"system"."users"';
		$datetime = "now()";
	}

	$q = "
		insert into ". $table ." (
			username
			,password
			,password_salt
			,is_superadmin
			,created
			,modified
		)
		values (
			'". db_prep_sql($username) ."'
			,'". db_prep_sql($password) ."'
			,'". db_prep_sql($password_salt,"bytea") ."'
			,'f'
			,now()
			,now()
		)
	";

	$res = db_query($q, "Creating New User");

	if (db_affected_rows($res)) {

		$id = db_insert_id($res);
		if (!empty($id)) {
			return $id;
		}
	}

	return false;
}

function update_user($info) {

	if (empty($info["user_id"])) { return false; }
	else if (empty($info["firstname"])) { return false; }
	else if(empty($info["lastname"])) { return false; }
	else if(empty($info["email"])) { return false; }

	$password_change = (!empty($info["password"]) ? true : false);

	list($password, $password_salt) = user_hash_passwords($info["password"]);

	$username = (!empty($info["username"]) ? $info["username"] : 0);

	$registration_type_id = (!empty($info["registration_type_id"]) ? $info["registration_type_id"] : 0);

	$region_id = (empty($info["region_id"]) ? 0 : $info["region_id"]);
	$region_str = (empty($info["region_str"]) ? "" : $info["region_str"]);
	$country_id = (empty($info["country_id"]) ? 0 : $info["country_id"]);
	$country_str = (empty($info["country_str"]) ? "" : $info["country_str"]);
	$pin = (!empty($info["pin"]) ? $info["pin"] : 0);

	$address = (!empty($info["address"]) ? $info["address"] : 0);
	$city = (!empty($info["city"]) ? $info["city"] : 0);
	$title = (empty($info["title"]) ? "" : $info["title"]);
	$postal_code = (!empty($info["postal_code"]) ? $info["postal_code"] : '');

	$phone1 = (!empty($info["phone1"]) ? $info["phone1"] : 0);
	$phone2 = (!empty($info["phone2"]) ? $info["phone2"] : 0);
	$phone3 = (!empty($info["phone3"]) ? $info["phone3"] : 0);

	$marketing_source = (!empty($info["marketing_source"]) ? $info["marketing_source"] : "");

	$table = "users";

	$datetime = time();

	if (uses_schema()) {
		$table = '"system"."users"';
		$datetime = "now()";
	}

	$q = "
		update ".$table."
		set
			firstname = '".db_prep_sql($info["firstname"])."'
			,lastname = '".db_prep_sql($info["lastname"])."'
			,username = '".db_prep_sql($username)."'
			,email = '".db_prep_sql($info['email'])."'
			".($password_change ? ",password = '".db_prep_sql($password)."'" : "")."
			".($password_change ? ",password_salt = '".db_prep_sql($password_salt,(uses_schema() ? "bytea" : ""))."'" : "")."
			,pin = '".db_prep_sql($pin)."'
			,city = '".db_prep_sql($city)."'
			,title = '".db_prep_sql($title)."'
			,region_id = '".db_prep_sql($region_id)."'
			,region_str = '".db_prep_sql($region_str)."'
			,country_id = '".db_prep_sql($country_id)."'
			,country_str = '".db_prep_sql($country_str)."'
			,postal_code = '".db_prep_sql($postal_code)."'
			,phone1 = '".db_prep_sql($phone1)."'
			,marketing_source = '".db_prep_sql($marketing_source)."'
			,modified = ".$datetime."
		where
			id = ".db_prep_sql((int) $info["user_id"])."
	";

	$res = db_query($q, "Updating User");

	if (!db_is_error($res)) {
		return $info["user_id"];
	}

	return false;
}

function create_new_institution($info) {

	//if (empty($info["institution_type_id"])) { return false; }

	$user_id = (empty($info["user_id"]) ? 0 : $info["user_id"]);
	$institution_type_id = (empty($info["institution_type_id"]) ? 0 : $info["institution_type_id"]);
	$region_id = (empty($info["region_id"]) ? 0 : $info["region_id"]);
	$region_str = (empty($info["region_str"]) ? "" : $info["region_str"]);
	$country_id = (empty($info["country_id"]) ? 0 : $info["country_id"]);
	$country_str = (empty($info["country_str"]) ? "" : $info["country_str"]);
	$population = (empty($info["population"]) ? 0 : $info["population"]);
	$postal_code = (empty($info["postal_code"]) ? "" : $info["postal_code"]);
	$phone = (empty($info["phone"]) ? "" : $info["phone"]);
	$title = (empty($info["title"]) ? "" : $info["title"]);
	$city = (empty($info["city"]) ? "" : $info["city"]);
	$address1 = (empty($info["address1"]) ? "" : $info["address1"]);
	$site_name = (empty($info["site_name"]) ? "" : $info["site_name"]);
	$marketing_source = (empty($info["marketing_source"]) ? "" : $info["marketing_source"]);
	$group_name = (empty($info["group_name"]) ? "" : $info["group_name"]);
	$email = (empty($info["email"]) ? "" : $info["email"]);

	$table = "institutions";

	$datetime = time();

	if (uses_schema()) {
		$table = '"public"."institutions"';
		$datetime = "now()";
	}

	$q = "
		insert into ". $table ." (
			user_id
			,institution_type_id
			,region_id
			,region_str
			,country_id
			,country_str
			,population
			,postal_code
			,phone
			,title
			,city
			,address1
			,site_name
			,marketing_source
			,group_name
			,email
			,created
			,modified
		)
		values (
			".db_prep_sql($user_id)."
			,".db_prep_sql($institution_type_id)."
			,".db_prep_sql($region_id)."
			,'".db_prep_sql($region_str)."'
			,".db_prep_sql($country_id)."
			,'".db_prep_sql($country_str)."'
			,".db_prep_sql($population)."
			,'".db_prep_sql($postal_code)."'
			,'".db_prep_sql($phone)."'
			,'".db_prep_sql($title)."'
			,'".db_prep_sql($city)."'
			,'".db_prep_sql($address1)."'
			,'".db_prep_sql($site_name)."'
			,'".db_prep_sql($marketing_source)."'
			,'".db_prep_sql($group_name)."'
			,'".db_prep_sql($email)."'
			,".$datetime."
			,".$datetime."
		)
	";

	$res = db_query($q, "Creating New Institution");

	if (db_affected_rows($res)) {

		$id = db_insert_id($res);

		if (!empty($id)) {
			return $id;
		}

	}

	return false;

}

function update_user_institution($info, $institution_id) {

	//if (empty($info["institution_type_id"])) { return false; }

	$user_id = (empty($info["user_id"]) ? 0 : $info["user_id"]);
	$institution_type_id = (empty($info["institution_type_id"]) ? 0 : $info["institution_type_id"]);
	$region_id = (empty($info["region_id"]) ? 0 : $info["region_id"]);
	$region_str = (empty($info["region_str"]) ? "" : $info["region_str"]);
	$country_id = (empty($info["country_id"]) ? 0 : $info["country_id"]);
	$country_str = (empty($info["country_str"]) ? "" : $info["country_str"]);
	$population = (empty($info["population"]) ? 0 : $info["population"]);
	$postal_code = (empty($info["postal_code"]) ? "" : $info["postal_code"]);
	$phone = (empty($info["phone"]) ? "" : $info["phone"]);
	$title = (empty($info["title"]) ? "" : $info["title"]);
	$city = (empty($info["city"]) ? "" : $info["city"]);
	$address1 = (empty($info["address1"]) ? "" : $info["address1"]);
	$site_name = (empty($info["site_name"]) ? "" : $info["site_name"]);
	$marketing_source = (empty($info["marketing_source"]) ? "" : $info["marketing_source"]);
	$group_name = (empty($info["group_name"]) ? "" : $info["group_name"]);
	$email = (empty($info["email"]) ? "" : $info["email"]);

	$table = "institutions";

	$datetime = time();

	if (uses_schema()) {
		$table = '"public"."institutions"';
		$datetime = "now()";
	}

	$q = "
		update ". $table ."
		set
			user_id = ".db_prep_sql($user_id)."
			,institution_type_id = ".db_prep_sql($institution_type_id)."
			,region_id = ".db_prep_sql($region_id)."
			,region_str = '".db_prep_sql($region_str)."'
			,country_id = ".db_prep_sql($country_id)."
			,country_str = '".db_prep_sql($country_str)."'
			,population = ".db_prep_sql($population)."
			,postal_code = '".db_prep_sql($postal_code)."'
			,phone = '".db_prep_sql($phone)."'
			,title = '".db_prep_sql($title)."'
			,city = '".db_prep_sql($city)."'
			,address1 = '".db_prep_sql($address1)."'
			,site_name = '".db_prep_sql($site_name)."'
			,marketing_source = '".db_prep_sql($marketing_source)."'
			,group_name = '".db_prep_sql($group_name)."'
			,email = '".db_prep_sql($email)."'
			,modified = ".$datetime."
		where
			id = ".$institution_id."
	";

	$res = db_query($q, "Updating User Institution");

	if (!db_is_error($res)) {

		$id = $institution_id;

		if (!empty($id)) {
			return $id;
		}

	}

	return false;

}

function create_default_student($info) {

	if (empty($info["user_id"])) { return false; }

	$gender_id = (empty($info["gender_id"]) ? 0 : $info["gender_id"]);
	$user_id = (empty($info["user_id"]) ? 0 : $info["user_id"]);
	$institution_id = (empty($info["institution_id"]) ? 0 : $info["institution_id"]);
	$grade_id = (empty($info["grade_id"]) ? 0 : $info["grade_id"]);
	$firstname = (empty($info["firstname"]) ? "Default Player" : $info["firstname"]);

	$table = "students";

	$datetime = time();

	if (uses_schema()) {
		$table = '"system"."students"';
		$datetime = "now()";
	}

	$q = "
		insert into ". $table ." (
			ethicspledge
			,ethicspledge_date
			,gender_id
			,user_id
			,institution_id
			,grade_id
			,firstname
			,created
			,modified
		)
		values (
			't'
			,".$datetime."
			,".db_prep_sql($gender_id)."
			,".db_prep_sql($user_id)."
			,".db_prep_sql($institution_id)."
			,".db_prep_sql($grade_id)."
			,'".db_prep_sql($firstname)."'
			,".$datetime."
			,".$datetime."
		)
	";

	$res = db_query($q, "Creating Default Student");

	if (db_affected_rows($res)) {

		$id = db_insert_id($res);

		if (!empty($id)) {
			return $id;
		}

	}

	return false;

}

function create_user_institution_map($info) {

	if (empty($info["user_id"])) { return false; }
	if (empty($info["institution_id"])) { return false; }

	$table = "user_institution_map";

	$datetime = time();

	if (uses_schema()) {
		$table = '"public"."user_institution_map"';
		$datetime = "now()";
	}

	$q = "
		insert into ". $table ." (
			user_id
			,institution_id
			,created
			,modified
		)
		values (
			".db_prep_sql($info["user_id"])."
			,".db_prep_sql($info["institution_id"])."
			,".$datetime."
			,".$datetime."
		)
	";

	$res = db_query($q, "Creating User Institution Map");

	if (db_affected_rows($res)) {

		$id = db_insert_id($res);

		if (!empty($id)) {
			return $id;
		}

	}

	return false;

}

function update_students_institutions($user_id, $institution_id) {

	$table = "students";

	$datetime = time();

	if (uses_schema()) {
		$table = '"system"."students"';
		$datetime = "now()";
	}

	$q = "
		update ".$table."
		set
			institution_id = ".db_prep_sql((int) $institution_id)."
			,modified = ".$datetime."
		where
			user_id = ".db_prep_sql((int) $user_id)."
	";

	$res = db_query($q, "Updating Students Institutions");

	if (!db_is_error($res)) {
		return true;
	}

	return false;

}

function update_child_institutions($user_id, $parent_id) {

	///////////////////////////////////////////////////////////////////////////////////////////////
	// WARNING: FUNCTION OVERWRITES ANY INSTITUTION WITH USER ID. USE ONLY FOR NEW REGISTRATIONS.
	///////////////////////////////////////////////////////////////////////////////////////////////

	$table = "institutions";

	$datetime = time();

	if (uses_schema()) {
		$table = '"public"."institutions"';
		$datetime = "now()";
	}

	$q = "
		update ".$table."
		set
			parent_id = ".db_prep_sql((int) $parent_id)."
			,modified = ".$datetime."
		where
			user_id = ".db_prep_sql((int) $user_id)."
	";

	$res = db_query($q, "Updating Child Institution");

	if (!db_is_error($res)) {
		return true;
	}

	return false;

}