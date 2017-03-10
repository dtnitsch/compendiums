<?php

session_name("clevercrazes");
session_start();

function process_controller() {

	if (!empty($_POST) && $_POST["check_pin"] == true) {
		check_pin();
	} else {
		die();
	}

}

function check_pin() {

	$json = array();
	$has_error = true;

	$q = "
		select
			pin
		from \"system\".\"users\"
		where
			id = ".db_prep_sql((int) $_SESSION["user"]["id"])."
	";

	$res = db_query($q, __FUNCTION__."()");

	if (db_num_rows($res)) {

		$arr = array();

		while ($row = db_fetch_row($res)) {
			$arr["pin"] = $row["pin"];
		}

		if ($arr["pin"] == $_POST["pin"]) {
			$_SESSION["user"]["pin"] = $arr["pin"];
			unset($_SESSION["pin_redirect"]);
			$has_error = false;
		}

	}

	$json["debug"] = ajax_debug();
	$json["success"] = ($has_error ? false : true);

	echo json_encode($json);

}

process_controller();
