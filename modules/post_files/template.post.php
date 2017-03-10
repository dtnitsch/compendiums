<?php
if(!empty($_POST) && !ERROR_MESSAGE()) {
	LIBRARY("validation.php");
	
	validate($_POST["id"],"required","ID");
	validate($_POST["first"],["required","string_length_between:1,128"],"First Name");
	validate($_POST["last"],["required","string_length_between:1,128"],"Last Name");
	validate($_POST["email"],["required","email"],"Email Address");

	if(!ERROR_MESSAGE()) {

		$time = time();
		$q = "insert into xxx (modified) values ('". $time ."')";
		$res = db_query($q,"Inserting");

		if(!db_is_error($res)) {
		
				$redirection_path = "/";
				SET_POST_MESSAGE("The record has been successfully updated");
				SET_SAFE_REDIRECT($redirection_path);

		} else {
			ERROR_MESSAGE("An error has occurred while trying to update this record");
		}
	}
}