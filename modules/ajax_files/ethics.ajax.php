<?php

/*
* Session changes in script so include session_start()
*/
session_name("clevercrazes");
session_start();

$json = array();
$has_error = true;

if (!empty($_POST["update_ethics_pledge"]) && $_POST["update_ethics_pledge"] == true && !empty($_POST["student_id"])) {
	$has_error = false;
}

if (!$has_error) {

	/*
	* Reset $has_error variable for further use in function
	*/
	$has_error = true;

	/*
	* Update Student Ethics Pledge
	*/
	$q = "
		update \"system\".\"students\"
		set
			ethicspledge = 't'
			,ethicspledge_date = 'now()'
			,modified = 'now()'
		where
			id = ".db_prep_sql((int) $_POST["student_id"])."
	";

	$res = db_query($q, "Updating Student Ethics Pledge");

	if (db_affected_rows($res)) {

		/*
		* Get Student Info
		*/
		$q = "
			select
				id
				,grade_id
				,firstname
			from \"system\".\"students\"
			where
				id = ".db_prep_sql((int) $_POST["student_id"])."
			limit
				1
		";

		$res = db_query($q, "Getting Student Info");

		if (db_num_rows($res)) {

			$arr = db_fetch_row($res);

			if (!empty($arr)) {

				$has_error = false;

				/*
				* Write Student Info to Session
				*/
				$_SESSION["student_id"] = (int) $arr["id"];
				$_SESSION["is_guest"] = false;
				$_SESSION["student_grade"] = (int) $arr["grade_id"];
				$_SESSION["student_firstname"] = $arr["firstname"];
				$_SESSION["user"]["student_id"] = (int) $arr["id"];
				$_SESSION["user"]["is_guest"] = false;
				$_SESSION["user"]["student_grade"] = (int) $arr["grade_id"];
				$_SESSION["user"]["student_firstname"] = $arr["firstname"];

			}

		}

	}

}

$json["success"] = ($has_error ? false : true);

if ($GLOBALS["debug_options"]["enabled"]) { $json["debug"] = ajax_debug(); }

echo json_encode($json);
