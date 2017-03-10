<?php

function process_controller() {

	if (!empty($_POST) && $_POST["add_quiz_question_media"] == true) {
		add_quiz_question_media();
	} else if (!empty($_POST) && $_POST["update_quiz_question_media"] == true) {
		update_quiz_question_media();
	} else {
		die();
	}

}

function add_quiz_question_media() {

	$json = array();
	$has_error = true;

	// active (can be t/f)
	// series (= sortorder starts at 1)
	// laguage_id (for multi language audio)
	// quiz_question_id (do not have question_id until save)
	// media_type_id (can be 162, 267, or 404)
	// folder (can be /audio/, /photocontent/, /soundcontent/, or /videocontent/)
	// filename (e.g. filename.mp3)

	$q = "
		insert into \"activities\".\"quiz_question_media\" (
			active
			,series
			,language_id
			,quiz_question_id
			,media_type_id
			,folder
			,filename
			,created
			,modified
		)
		values (
			't'
			,1
			,1
			,".(empty($_POST["quiz_qustion_id"]) ? 0 : db_prep_sql((int) $_POST["quiz_question_id"]))."
			,".db_prep_sql((int) $_POST["media_type_id"])."
			,'".db_prep_sql($_POST["folder"])."'
			,'".db_prep_sql($_POST["filename"])."'
			,'now()'
			,'now()'
		)
	";

	$res = db_query($q, __FUNCTION__."()");

	if (db_affected_rows($res)) {
		$has_error = false;
		$json["upload_id"] = db_insert_id($res);
	}

	$json["debug"] = ajax_debug();
	$json["success"] = ($has_error ? false : true);

	return json_encode($json);

}

function update_quiz_question_media() {

	$json = array();
	$has_error = true;

	$q = "
		update \"activities\".\"quiz_question_media\"
		set
			quiz_question_id = ".db_prep_sql((int) $_POST["quiz_question_id"])."
			".(empty($_POST["media_type_id"]) ? ",media_type_id = 0" : ",media_type_id = ".db_prep_sql((int) $_POST["media_type_id"]))."
			,modified = 'now()'
		where
			id = ".db_prep_sql((int) $_POST["upload_id"])."
	";

	$res = db_query($q, __FUNCTION__."()");

	if (!db_affected_rows($res)) {
		$has_error = false;
	}

	$json["debug"] = ajax_debug();
	$json["success"] = ($has_error ? false : true);

	return json_encode($json);

}

echo quiz_questions_process();