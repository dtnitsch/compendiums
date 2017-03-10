<?php
if(!empty($_POST) && !error_message()) {

	// library('validation.php');

	// Cleanup the answers
	$answers = array();
	foreach($_POST['answers'] as $k => $v) {
		if(is_array($v)) {
			foreach($v as $key => $row) {
				if(!empty($row)) {
					$row = trim($row);
					$answers['new_'. $key] = $row;
				}
			}
		} else if(!empty($v)) {
			$v = trim($v);
			$answers[$k] = $v;
		}
	}
	// check if there are any answers after cleanup
	if(empty($answers)) {
		error_message("At least one answer is required");
	}


echo "<pre>";
print_r($answers);
echo "</pre>";
die();

/*
	// Disabled for now
	// Use additional validation if needed

	validate($_POST['question'],['required','string_length_between:1,128'],"Question");

	// Getting all error messages from the validation functions
	// Loop through and add them to the error_message session array
	$errors = get_all_validation_errors();
	if(!empty($errors)) {
		foreach($errors as $error) {
			error_message($error);
		}		
	}
*/

	// check the error messages array - if no values are passed, return a count
	if(!error_message()) {

		// Insert new question
		$q = "
			insert into activities.quiz_questions (points,publish_date,question) values
				(
					100
					,now()
					,'". db_prep_sql($_POST['question']) ."'
				) returning id
		";
		// Use postgresql's "returning id" feature to return the newly inserted id
		$tmp = db_fetch($q,"Inserting new quiz_question");
		$id = $tmp['id'];

		// loop through each answer, and add it to a string
		$q = "";
		$series = 1;
		foreach($answers as $k => $row) {
			$is_correct = (!empty($_POST['is_correct'][$k]) ? "t" : "f");
			$q .= "(
				'". $id ."'
				,'". $series++ ."'
				,'". $is_correct ."'
				,1
				,'". db_prep_sql($row) ."'
			),";
		}
		// The the answer string is not empty, insert into the answers db
		if(!empty($q)) {
			$q = "
				insert into activities.quiz_question_answers (
					quiz_question_id
					,series
					,is_correct
					,language_id
					,answer
				) values ". substr($q,0,-1);
			$res = db_query($q,"Inserting new quiz question answer");
			if(db_is_error($res)) {
				error_message("An error occured while trying to insert the question question answers");
			}
		}

		if(!error_message()) {
			// audit('table_update',$table_info);

			$redirection_path = '/acu/quiz-questions/';
			set_post_message("The record has been successfully updated");
			set_safe_redirect($redirection_path);

		} else {
			error_message("An error has occurred while trying to update this record");
		}
	}
}