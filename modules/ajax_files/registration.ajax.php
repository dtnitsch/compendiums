<?php

// Enable Sessions - DB Connection might be required
session_name("clevercrazes");
session_start();

if (!empty($_POST["check_existing_email"])) {
	check_existing_email((empty((bool) $_POST["user_id"]) ? false : $_POST["user_id"]));
} else if (!empty($_POST["check_existing_username"])) {
	check_existing_username((empty((bool) $_POST["user_id"]) ? false : $_POST["user_id"]));
} else if (!empty($_POST["submit_user_registration"])) {
	submit_user_registration();
} else if (!empty($_POST["update_user_registration"])) {
	update_user_registration();
} else if (!empty($_POST["add_institution"])) {
	add_institution();
} else if (!empty($_POST["update_institution"])) {
	update_institution();
} else {
	die();
}

function check_existing_email($user_id = false) {

	$table = '"system"."users"';
	$val = "true";

	$q = "
		select
			id
		from ".$table."
		where
			lower(email) = lower('".db_prep_sql($_POST["email"])."')
			and active = 't'
			".(empty($user_id) ? "" : "and id != ".$user_id)."
	";

	$res = db_query($q, __FUNCTION__."()");

	if (db_num_rows($res)) {
		$val = "false";
	}

	echo $val;

}

function check_existing_username($user_id = false) {

	$table = '"system"."users"';
	$val = "true";

	$q = "
		select
			id
		from system.users
		where
			lower(username) = lower('".db_prep_sql($_POST["username"])."')
			and active = 't'
			".(empty($user_id) ? "" : "and id != ".$user_id)."
	";

	$res = db_query($q, __FUNCTION__."()");

	if (db_num_rows($res)) {
		$val = "false";
	}

	echo $val;

}

function submit_user_registration() {

	library("membership.php");
	library("mailer.php");

	// Get Regions/Countries Array
	$q = "select id,\"2code\",title,country_id from supplements.regions";
	$res = db_query($q, "Getting Regions");
	$regions = array();

	while ($row = db_fetch_row($res)) {
		$regions[strtolower($row["2code"])] = $row["id"];
		$regions[strtolower($row["title"])] = $row["id"];
		$regions[$row["id"]] = $row;
	}

	$q = "select id,\"2code\",\"3code\",title from supplements.countries";
	$res = db_query($q, "Getting Countries");
	$countries = array();

	while ($row = db_fetch_row($res)) {
		$countries[strtolower($row["2code"])] = $row["id"];
		$countries[strtolower($row["3code"])] = $row["id"];
		$countries[strtolower($row["title"])] = $row["id"];
	}

	$region_id = 0;
	$country_id = 0;

	if (!empty($_POST["state"])) {

		$tmp = strtolower($_POST["state"]);

		if (!empty($regions[$tmp])) {
			$region_id = $regions[$tmp];
			$country_id = $regions[$regions[$tmp]]["country_id"];
		}

	}

	if (empty($country_id) && !empty($_POST["country"])) {

		$tmp = strtolower($_POST["country"]);

		if (!empty($countries[$tmp])) {
			$country_id = $countries[$tmp];
		}

	}

	// registration type = old cms form id
	if ($_POST["registration_type"] == 2) {
		// PARENTS
		$institution_type_id = 6;
	} else if ($_POST["registration_type"] == 1) {
		// TEACHERS
		$institution_type_id = 1;
	} else if ($_POST["registration_type"] == 6) {
		// KINF
		$institution_type_id = 5;
	} else if ($_POST["registration_type"] == 4) {
		// AFTER SCHOOL
		$institution_type_id = 3;
	} else if ($_POST["registration_type"] == 5) {
		// HOME-SCHOOL
		$institution_type_id = 4;
	}  else if ($_POST["registration_type"] == 3) {
		// CLUB
		$institution_type_id = 2;
	} else {
		return false;
	}

	// Set Return Defaults
	$json = array(
		"success" => false
		,"registration_type" => $_POST["registration_type"]
		,"add_kids" => (empty($_POST["add_kids"]) ? false : true)
	);

	// Create User
	$user_id = false;

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

	$table_info["table_columns"][0] = array(
		"firstname" => $_POST["firstname"]
		,"lastname" => $_POST["lastname"]
		,"username" => strtolower($_POST["username"])
		,"email" => strtolower($_POST["email"])
		,"password" => $_POST["password"]
		,"pin" => $_POST["pin"]
		,"registration_type_id" => $_POST["registration_type"]
		,"region_id" => ($_POST["registration_type"] == 6 ? $region_id : 0)
		,"region_str" => ($_POST["registration_type"] == 6 ? $_POST["state"] : "")
		,"country_id" => ($_POST["registration_type"] == 6 ? $country_id : 0)
		,"country_str" => ($_POST["registration_type"] == 6 ? $_POST["country"] : "")
		//,"company" => $company
		,"address" => ($_POST["registration_type"] == 6 ? $_POST["address"] : "")
		,"city" => ($_POST["registration_type"] == 6 ? $_POST["city"] : "")
		,"title" => (empty($_POST["position"]) ? "" : $_POST["position"])
		,"postal_code" => ($_POST["registration_type"] == 6 ? $_POST["zip"] : "")
		,"phone1" => ($_POST["registration_type"] == 6 ? $_POST["phone"] : "")
		,"marketing_source" => $_POST["marketing"]
		//,"phone2" => $phone2
		//,"phone3" => $phone3
		,"created" => "now()"
		,"modified" => "now()"
	);

	$user_id = create_new_user($table_info["table_columns"][0]);
	$has_error = (empty($user_id) || !is_numeric($user_id) ? true : false);

	if (!$has_error) {

		audit("table_insert", $table_info);
		$json["user_id"] = $user_id;

		$_SESSION["is_guest"] = false;

		# Set the session variables that will be used in the rest of the site
		$_SESSION["user"]["id"] = $user_id;
		$_SESSION["user"]["firstname"] = $_POST["firstname"];
		$_SESSION["user"]["lastname"] = $_POST["lastname"];
		$_SESSION["user"]["email"] = strtolower($_POST["email"]);
		$_SESSION["user"]["username"] = strtolower($_POST["username"]);
		$_SESSION["user"]["pin"] = $_POST["pin"];
		$_SESSION["user"]["is_superadmin"] = 0;

		audit("logins", $user_id);

		// Create Default Player
		$student_id = false;

		$table_info = array(
			"table_name" => "students"
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

		$table_info["table_columns"][3] = array(
			"ethicspledge" => "f"
			,"gender_id" => 0
			,"user_id" => $user_id
			,"institution_id" => 0
			,"grade_id" => 18 // Default grade of other
			,"firstname" => $_POST["firstname"]
			,"created" => "now()"
			,"modified" => "now()"
		);

		$student_id = create_default_student($table_info["table_columns"][3]);
		$has_error = (empty($student_id) || !is_numeric($student_id) ? true : false);

		if (!$has_error) {
			audit("table_insert", $table_info);
			$json["student_id"] = $student_id;
		}

		// Create Institution
		$institution_id = false;

		// NOT TEACHERS REG
		if ($institution_type_id != 1 && $institution_type_id != 5) {

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

			$table_info["table_columns"][1] = array(
				"user_id" => $user_id
				,"institution_type_id" => $institution_type_id
				,"region_id" => $region_id
				,"region_str" => $_POST["state"]
				,"country_id" => $country_id
				,"country_str" => $_POST["country"]
				,"population" => (empty($_POST["class_population"]) ? 0 : $_POST["class_population"])
				,"postal_code" => (empty($_POST["zip"]) ? "" : $_POST["zip"])
				,"phone" => (empty($_POST["phone"]) ? "" : $_POST["phone"])
				,"title" => (empty($_POST["institution_name"]) ? "Parent: ".$_POST["firstname"]." ".$_POST["lastname"] : $_POST["institution_name"])
				,"city" => $_POST["city"]
				,"address1" => (empty($_POST["address"]) ? "" : $_POST["address"])
				,"site_name" => (empty($_POST["institution_site"]) ? "" : $_POST["institution_site"])
				,"marketing_source" => $_POST["marketing"]
				,"group_name" => (empty($_POST["class_name"]) ? "" : $_POST["class_name"])
				,"email" => $_POST["email"]
				,"created" => "now()"
				,"modified" => "now()"
			);

			$institution_id = create_new_institution($table_info["table_columns"][1]);
			$has_error = (empty($institution_id) || !is_numeric($institution_id) ? true : false);

			if (!$has_error) {

				audit("table_insert", $table_info);
				$json["institution_id"] = $institution_id;

				// Create Institution Map
				$user_institution_map_id = false;

				$table_info = array(
					"table_name" => "user_institution_map"
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

				$table_info["table_columns"][2] = array(
					"user_id" => $user_id
					,"institution_id" => $institution_id
					,"created" => "now()"
					,"modified" => "now()"
				);

				$user_institution_map_id = create_user_institution_map($table_info["table_columns"][2]);
				$has_error = (empty($user_institution_map_id) || !is_numeric($user_institution_map_id) ? true : false);

				if (!$has_error) {

					audit("table_insert", $table_info);
					$json["user_institution_map_id"] = $user_institution_map_id;

					// Update Player Institutions
					$update_students_institutions = update_students_institutions($user_id, $institution_id);

					if (!$update_students_institutions) { $has_error = true; }

				}

			}

		} else {

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

			$table_info["table_columns"][1] = array(
				"user_id" => $user_id
				,"institution_type_id" => $institution_type_id
				,"region_id" => 0
				,"region_str" => ""
				,"country_id" => 0
				,"country_str" => ""
				,"population" => (empty($_POST["class_population"]) ? 0 : $_POST["class_population"])
				,"postal_code" => ""
				,"phone" => ""
				,"title" => ""
				,"city" => ""
				,"address1" => ""
				,"site_name" => (empty($_POST["institution_site"]) ? "" : $_POST["institution_site"])
				,"marketing_source" => $_POST["marketing"]
				,"group_name" => (empty($_POST["class_name"]) ? "" : $_POST["class_name"])
				,"email" => ""
				,"created" => "now()"
				,"modified" => "now()"
			);

			$institution_id = create_new_institution($table_info["table_columns"][1]);
			$has_error = (empty($institution_id) || !is_numeric($institution_id) ? true : false);

			if (!$has_error) {
				audit("table_insert", $table_info);
				$json["institution_id"] = $institution_id;
			}

		}

	}

	if (!$has_error) {
		send_registration_email($_POST["email"], $_POST["firstname"]." ".$_POST["lastname"]);
	}

	$json["debug"] = ajax_debug();
	$json["success"] = ($has_error ? false : true);

	echo json_encode($json);

}

function update_user_registration() {

	library("membership.php");

	// Get Regions/Countries Array
	$q = "select id,\"2code\",title,country_id from supplements.regions";
	$res = db_query($q, "Getting Regions");
	$regions = array();

	while ($row = db_fetch_row($res)) {
		$regions[strtolower($row["2code"])] = $row["id"];
		$regions[strtolower($row["title"])] = $row["id"];
		$regions[$row["id"]] = $row;
	}

	$q = "select id,\"2code\",\"3code\",title from supplements.countries";
	$res = db_query($q, "Getting Countries");
	$countries = array();

	while ($row = db_fetch_row($res)) {
		$countries[strtolower($row["2code"])] = $row["id"];
		$countries[strtolower($row["3code"])] = $row["id"];
		$countries[strtolower($row["title"])] = $row["id"];
	}

	$region_id = 0;
	$country_id = 0;

	if (!empty($_POST["state"])) {

		$tmp = strtolower($_POST["state"]);

		if (!empty($regions[$tmp])) {
			$region_id = $regions[$tmp];
			$country_id = $regions[$regions[$tmp]]["country_id"];
		}

	}

	if (empty($country_id) && !empty($_POST["country"])) {

		$tmp = strtolower($_POST["country"]);

		if (!empty($countries[$tmp])) {
			$country_id = $countries[$tmp];
		}

	}

	// registration type = old cms form id
	if ($_POST["registration_type"] == 2) {
		// PARENTS
		$institution_type_id = 6;
	} else if ($_POST["registration_type"] == 1 || $_POST["registration_type"] == 6) {
		// TEACHERS
		$institution_type_id = 1;
	} else if ($_POST["registration_type"] == 4) {
		// AFTER SCHOOL
		$institution_type_id = 3;
	} else if ($_POST["registration_type"] == 5) {
		// HOME-SCHOOL
		$institution_type_id = 4;
	} else if ($_POST["registration_type"] == 3) {
		// CLUB
		$institution_type_id = 2;
	} else {
		return false;
	}

// echo dump($_POST);
// die();

	// Set Return Defaults
	$json = array(
		"success" => false
		,"registration_type" => $_POST["registration_type"]
	);

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

	$table_info["table_columns"][0] = array(
		"user_id" => $_POST["user_id"]
		,"firstname" => db_prep_sql($_POST["firstname"])
		,"lastname" => db_prep_sql($_POST["lastname"])
		,"username" => db_prep_sql(strtolower($_POST["username"]))
		,"email" => db_prep_sql(strtolower($_POST["email"]))
		,"password" => $_POST["password"]
		,"pin" => db_prep_sql($_POST["pin"])
		,"registration_type_id" => $_POST["registration_type"]
		,"region_id" => ($_POST["registration_type"] == 2 ? $region_id : 0)
		,"region_str" => ($_POST["registration_type"] == 2 ? db_prep_sql($_POST["state"]) : "")
		,"country_id" => ($_POST["registration_type"] == 2 ? $country_id : 0)
		,"country_str" => ($_POST["registration_type"] == 2 ? db_prep_sql($_POST["country"]) : "")
		//,"company" => $company
		,"address" => ($_POST["registration_type"] == 2 ? db_prep_sql($_POST["address"]) : "")
		,"city" => ($_POST["registration_type"] == 2 ? db_prep_sql($_POST["city"]) : "")
		,"postal_code" => ($_POST["registration_type"] == 2 ? db_prep_sql($_POST["zip"]) : "")
		,"phone1" => ($_POST["registration_type"] == 2 ? db_prep_sql($_POST["phone"]) : "")
		,"marketing_source" => $_POST["marketing"]
		//,"phone2" => $phone2
		//,"phone3" => $phone3
		,"modified" => "now()"
	);

	if (!empty($_POST["password"])) {
		$table_info["table_columns"][0]["password"] = $_POST["password"];
	}

	$user_id = update_user($table_info["table_columns"][0], $_POST["user_id"]);
	$has_error = (empty($user_id) || !is_numeric($user_id) ? true : false);

	if (!$has_error) {

		audit("table_insert", $table_info);
		$json["user_id"] = $user_id;

		$_SESSION["is_guest"] = false;

		# Set the session variables that will be used in the rest of the site
		$_SESSION["user"]["id"] = $user_id;
		$_SESSION["user"]["firstname"] = $_POST["firstname"];
		$_SESSION["user"]["lastname"] = $_POST["lastname"];
		$_SESSION["user"]["email"] = strtolower($_POST["email"]);
		$_SESSION["user"]["username"] = strtolower($_POST["username"]);
		$_SESSION["user"]["pin"] = $_POST["pin"];
		$_SESSION["user"]["is_superadmin"] = 0;

		audit("logins", $user_id);

		// NOT TEACHERS REG
		if ($institution_type_id != 1) {

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

			$table_info["table_columns"][1] = array(
				"user_id" => $user_id
				,"institution_type_id" => $institution_type_id
				,"region_id" => $region_id
				,"region_str" => db_prep_sql($_POST["state"])
				,"country_id" => $country_id
				,"country_str" => db_prep_sql($_POST["country"])
				,"population" => (empty($_POST["class_population"]) ? 0 : $_POST["class_population"])
				,"postal_code" => $_POST["zip"]
				,"phone" => $_POST["phone"]
				,"title" => (empty($_POST["institution_name"]) ? "Parent: ".$_POST["firstname"]." ".$_POST["lastname"] : $_POST["institution_name"])
				,"city" => $_POST["city"]
				,"address1" => $_POST["address"]
				,"site_name" => (empty($_POST["institution_site"]) ? "" : $_POST["institution_site"])
				,"marketing_source" => $_POST["marketing"]
				,"group_name" => (empty($_POST["class_name"]) ? "" : $_POST["class_name"])
				,"email" => $_POST["email"]
				,"created" => "now()"
				,"modified" => "now()"
			);

			$institution_id = update_user_institution($table_info["table_columns"][1], $_POST["institution_id"]);
			$has_error = (empty($institution_id) || !is_numeric($institution_id) ? true : false);

		}

	}

	$json["debug"] = ajax_debug();
	$json["success"] = ($has_error ? false : true);

	echo json_encode($json);

}

function list_institutions($institution_id) {

	$table = '"public"."institutions"';

	$q = "
		select
			*
		from ".$table." as i
		where
			i.id = ".$institution_id."
	";

	$res = db_query($q, __FUNCTION__."()");

	if (db_num_rows($res)) {

		$arr = array();

		while ($row = db_fetch_row($res)) {
			$arr[] = $row;
		}

		return $arr;

	}

	return false;

}


function submit_institution() {

	library("membership.php");

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

	$table_info["table_columns"][1] = array(
		"user_id" => $user_id
		,"institution_type_id" => $institution_type_id
		,"region_id" => $region_id
		,"region_str" => db_prep_sql($_POST["state"])
		,"country_id" => $country_id
		,"country_str" => db_prep_sql($_POST["country"])
		,"population" => (empty($_POST["class_population"]) ? 0 : $_POST["class_population"])
		,"postal_code" => $_POST["zip"]
		,"phone" => $_POST["phone"]
		,"title" => (empty($_POST["institution_name"]) ? "" : $_POST["institution_name"])
		,"city" => $_POST["city"]
		,"address1" => $_POST["address"]
		,"site_name" => (empty($_POST["institution_site"]) ? "" : $_POST["institution_site"])
		,"group_name" => (empty($_POST["class_name"]) ? "" : $_POST["class_name"])
		,"marketing_source" => $_POST["marketing"]
		,"email" => $_POST["email"]
		,"created" => "now()"
		,"modified" => "now()"
	);

	$institution_id = create_new_institution($table_info["table_columns"][1]);
	$has_error = (empty($institution_id) || !is_numeric($institution_id) ? true : false);

}

function add_institution() {

	library("membership.php");
	library("mailer.php");

	$user_id = $_SESSION["user"]["id"];

	// Get Regions/Countries Array
	$q = "select id,\"2code\",title,country_id from supplements.regions";
	$res = db_query($q, "Getting Regions");
	$regions = array();

	while ($row = db_fetch_row($res)) {
		$regions[strtolower($row["2code"])] = $row["id"];
		$regions[strtolower($row["title"])] = $row["id"];
		$regions[$row["id"]] = $row;
	}

	$q = "select id,\"2code\",\"3code\",title from supplements.countries";
	$res = db_query($q, "Getting Countries");
	$countries = array();

	while ($row = db_fetch_row($res)) {
		$countries[strtolower($row["2code"])] = $row["id"];
		$countries[strtolower($row["3code"])] = $row["id"];
		$countries[strtolower($row["title"])] = $row["id"];
	}

	$region_id = 0;
	$country_id = 0;

	if (!empty($_POST["institution_state"])) {

		$tmp = strtolower($_POST["institution_state"]);

		if (!empty($regions[$tmp])) {
			$region_id = $regions[$tmp];
			$country_id = $regions[$regions[$tmp]]["country_id"];
		}

	}

	if (empty($country_id) && !empty($_POST["institution_country"])) {

		$tmp = strtolower($_POST["institution_country"]);

		if (!empty($countries[$tmp])) {
			$country_id = $countries[$tmp];
		}

	}


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

	$table_info["table_columns"][1] = array(
		"user_id" => $user_id
		,"institution_type_id" => 1
		,"region_id" => $region_id
		,"region_str" => $_POST["institution_state"]
		,"country_id" => $country_id
		,"country_str" => $_POST["institution_country"]
		,"population" => (empty($_POST["institution_population"]) ? 0 : $_POST["institution_population"])
		,"postal_code" => (empty($_POST["institution_postal_code"]) ? "" : $_POST["institution_postal_code"])
		,"phone" => (empty($_POST["institution_phone"]) ? "" : $_POST["institution_phone"])
		,"title" => (empty($_POST["institution_name"]) ? "" : $_POST["institution_name"])
		,"city" => $_POST["institution_city"]
		,"address1" => (empty($_POST["institution_address"]) ? "" : $_POST["institution_address"])
		,"created" => "now()"
		,"modified" => "now()"
	);

	$institution_id = create_new_institution($table_info["table_columns"][1]);
	$has_error = (empty($institution_id) || !is_numeric($institution_id) ? true : false);

	if (!$has_error) {

		audit("table_insert", $table_info);
		$json["institution_id"] = $institution_id;

		// Create Institution Map
		$user_institution_map_id = false;

		$table_info = array(
			"table_name" => "user_institution_map"
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

		$table_info["table_columns"][2] = array(
			"user_id" => $user_id
			,"institution_id" => $institution_id
			,"created" => "now()"
			,"modified" => "now()"
		);

		$user_institution_map_id = create_user_institution_map($table_info["table_columns"][2]);
		$has_error = (empty($user_institution_map_id) || !is_numeric($user_institution_map_id) ? true : false);

		if (!$has_error) {

			audit("table_insert", $table_info);
			$json["user_institution_map_id"] = $user_institution_map_id;

			// Update Player Institutions
			$update_students_institutions = update_students_institutions($user_id, $institution_id);

			if (!$update_students_institutions) { $has_error = true; }

		}

	}

	$json["debug"] = ajax_debug();
	$json["success"] = ($has_error ? false : true);

	echo json_encode($json);

}

function update_institution() {

	library("membership.php");

	$json = array(
		"success" => false
	);

	// Create Institution Map
	$user_institution_map_id = false;

	$table_info = array(
		"table_name" => "user_institution_map"
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

	$table_info["table_columns"][2] = array(
		"user_id" => $_SESSION["user"]["id"]
		,"institution_id" => $_POST["institution_id"]
		,"created" => "now()"
		,"modified" => "now()"
	);

	$user_institution_map_id = create_user_institution_map($table_info["table_columns"][2]);
	$has_error = (empty($user_institution_map_id) || !is_numeric($user_institution_map_id) ? true : false);

	if (!$has_error) {
		audit("table_insert", $table_info);
		$json["user_institution_map_id"] = $user_institution_map_id;

		$update_students_institutions = update_students_institutions($_SESSION["user"]["id"], $_POST["institution_id"]);

		if (!$update_students_institutions) { $has_error = true; }

		if (!$has_error) {

			$update_child_institutions = update_child_institutions($_SESSION["user"]["id"], $_POST["institution_id"]);

			if (!$update_child_institutions) { $has_error = true; }

		}

	}

	$json["debug"] = ajax_debug();
	$json["success"] = ($has_error ? false : true);

	echo json_encode($json);

}
