<?php

function process_controller() {

	if (!empty($_POST) && $_POST["students_delete_bulk"] == true) {
		students_delete_bulk();
	} else {
		die();
	}

}

function students_delete_bulk() {

	$json = array();

	$has_error = false;

	$q = "
		update \"system\".\"students\"
		set
			active = 'f'
		where
			id in(".db_prep_sql(implode(",", $_POST["data"])).");
	";

	$res = db_query($q, __FUNCTION__."()");

	if (db_is_error($res)) {
		$has_error = true;
	}

	$json["debug"] = ajax_debug();
	$json["success"] = ($has_error ? false : true);

	echo json_encode($json);

}

process_controller();