<?php
if(!empty($_POST) && !error_message()) {

	// library('validation.php');

	// Cleanup the answers
	$old_answers_update = array();
	$old_answers_delete = array();
	$new_answers_add = array();
	foreach($_POST['answers'] as $k => $v) {
		if($k == 'new') {
			foreach($v as $key => $row) {
				if(!empty($row)) {
					$row = trim($row['answer']);
					$new_answers_add['new_'. $key] = $row;
				}
			}
		} else {
			$v = trim($v['answer']);
			// delete this
			if(empty($v)) {
				$old_answers_delete[] = $k;
			} else {
				$old_answers_update[$k] = $v;
			}
		}
	}
	// check if there are any answers after cleanup
	if(empty($new_answers_add) && empty($old_answers_update)) {
		error_message("At least one answer is required");
	}

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

	$time = date('Y-m-d H:i:s');

	// check the error messages array - if no values are passed, return a count
	if(!error_message()) {

		$table_info = array(
			'table_name' => 'quiz_questions'
			,'table_schema' => 'activities'
			,'primary_key' => 'id'
			# optional
			,'audit_table' => 'activity_table_logs'
			,'audit_schema' => 'audits'
			,'primary_key_value' => db_prep_sql($_POST['id'])
			# Key = DB column Name, Value = Post name
			,'table_columns' => array()
		);

		$table_info['table_columns'][] = array(
			'question' => db_prep_sql(trim($_POST['question']))
			,'number' => db_prep_sql(trim($_POST['number']))
			,'points' => db_prep_sql(trim($_POST['points']))
			,'description' => db_prep_sql(trim($_POST['description']))
		);

		$res = "";
		if(($original_values = post_has_changes($table_info)) !== false) {
			$table_info["original_values"] = $original_values;

			if(!empty($original_values)) {
				if(($res = post_functions_update($table_info)) === false) {
					error_message("An error has occured trying to update this record");
				}
			}
		} else {
			error_message("Comparison table is incorrect");
		}
	}

	// If we had no errors updating the question, move on to answers
	// This whole section needs to be added to the audit tables!
	if(!error_message()) {

		// Delete
		if(!empty($old_answers_delete)) {
			$ids = implode(',',$old_answers_delete);
			$q = "
				update activities.quiz_question_answers set
					active = 'f'
					,is_correct = 'f'
					,modified = '". $time ."'
				where id in (". $ids .")
			";
			$res = db_query($q,"Deleting quiz question answers");

			if(db_is_error($res)) {
				error_message("An error occured deleting an answer");
			}
		}

		// Updates - CHANGE THIS - should only update that which has specifically changed
		if(!empty($old_answers_update)) {
			$is_error = false;
			foreach($old_answers_update as $k => $v) {
				$q = "
					update activities.quiz_question_answers set
						answer = '". $v ."'
						,modified = '". $time ."'
						,is_correct = '". ($_POST['is_correct'] == $k ? 't' : 'f') ."'
					where id = '". $k ."'";
				$res = db_query($q,"Updating quiz question answers: ". $k);
				if(db_is_error($res)) {
					$is_error = true;
				}
			}
			if($is_error) {
				error_message("An error occured updating an answer");
			}
		}

		if(!empty($new_answers_add)) {
			$is_error = false;
			$quiz_question_id = (int)$_POST['id'];
			$q = '';
			foreach($new_answers_add as $k => $v) {
				$q .= "(
						'". db_prep_sql($quiz_question_id) ."'
						,(select max(series) + 1 from activities.quiz_question_answers where quiz_question_id = '". $quiz_question_id ."')
						,'". ($_POST['is_correct'] == $k ? 't' : 'f') ."'
						,'1'
						,'". db_prep_sql($v) ."'
					),";
			}

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
				error_message("An error occured adding a new answer");
			}
		}
	} // end answers portion

	// If we have no errors with the question, and answers, move on to facts
	if(!error_message() && !empty($_POST['fact_id'])) {

		$table_info = array(
			'table_name' => 'facts'
			,'table_schema' => 'public'
			,'primary_key' => 'id'
			# optional
			,'audit_table' => 'public_table_logs'
			,'audit_schema' => 'audits'
			,'primary_key_value' => db_prep_sql($_POST['fact_id'])
			# Key = DB column Name, Value = Post name
			,'table_columns' => array()
		);

		$table_info['table_columns'][] = array(
			'fact' => db_prep_sql(trim($_POST['fact']))
			,'number' => db_prep_sql(trim($_POST['number']))
		);

		$res = "";
		if(($original_values = post_has_changes($table_info)) !== false) {
			$table_info["original_values"] = $original_values;

			if(!empty($original_values)) {
				if(($res = post_functions_update($table_info)) === false) {
					error_message("An error has occured trying to update this record");
				}				
			}
		} else {
			error_message("Comparison table is incorrect");
		}

	}

	// If we have no errors with the question, answers, and facts, move on to the grid
	if(!error_message()) {
		// First, get the CURRENT (aka: old) id's.  This is needed in case we erased
		// 	a row and it doesn't exist in the array anymore
		$q = "
			select
				active
				,id
				,world_id
				,grade_id
				,theme_id
				,generation_id
			from activities.quiz_question_map
			where
				quiz_question_id = '". (int)$_POST['id'] ."'
				and active
		";
		$res = db_query($q,'Getting Quiz Questions Grid');

		unset($_POST['grid']['templateid']);
		$grid = array();
		foreach($_POST['grid'] as $k => $v) {
			$id = ($v['id'] == 'new' ? 'new_'. $k : $v['id']);
			$grid[$id] = $v;
		}

		$old_map_delete = array();
		$old_map_update = array();
		while($row = db_fetch_row($res)) {
			// Check first if we are missing the world array secton (erased column)
			if(empty($grid[$row['id']])) {
				$old_map_delete[] = $row['id'];
			
			// It exists... but they're erased / set all values to blank (this is a version of delete)
			} else {
				if(!empty($grid[$row['id']])) {
					if(
						empty($grid[$row['id']]['world_id'])
						&& empty($grid[$row['id']]['theme_id'])
						&& empty($grid[$row['id']]['grade_id'])
						&& empty($grid[$row['id']]['generation_id'])
					) {
						$old_map_delete[] = $row['id'];	
					

					// Set the current values here to compare against
					} else {
						if(
							$row['world_id'] != $grid[$row['id']]['world_id']
							|| $row['theme_id'] != $grid[$row['id']]['theme_id']
							|| $row['grade_id'] != $grid[$row['id']]['grade_id']
							|| $row['generation_id'] != $grid[$row['id']]['generation_id']
						) {
							$old_map_update[$row['id']] = $grid[$row['id']];	
						}
					}
				}
			}
		}


		// Delete
		if(!empty($old_map_delete)) {
			$ids = implode(',',$old_map_delete);
			$q = "
				update activities.quiz_question_map set
					active = 'f'
					,modified = '". $time ."'
				where id in (". $ids .")
			";
			$res = db_query($q,"Deleting quiz question maps");

			if(db_is_error($res)) {
				error_message("An error occured deleting a mapping");
			}
		}

		$is_error = false;
		foreach($old_map_update as $k => $v) {
			$q = "
				update activities.quiz_question_map set
					world_id = '". (int)$v['world_id'] ."'
					,theme_id = '". (int)$v['theme_id'] ."'
					,grade_id = '". (int)$v['grade_id'] ."'
					,generation_id = '". (int)$v['generation_id'] ."'
					,modified = '". $time ."'
				where id = '". $k ."'";
			$res = db_query($q,"Updating quiz question map: ". $k);
			if(db_is_error($res)) {
				$is_error = true;
			}
		}
		if($is_error) {
			error_message("An error occured updating a mapping");
		}

		// New Inserts
		if(!empty($grid)) {
			$is_error = false;
			$quiz_question_id = (int)$_POST['id'];
			$q = '';
			foreach($grid as $k => $v) {
				if($v['id'] != 'new') { continue; }
				
				// Skip options where things are all blank
				if(empty($v['world_id']) && empty($v['theme_id']) && empty($v['grade_id']) && empty($v['generation_id'])) {
					continue;
				}

				$q .= "(
						'". db_prep_sql($quiz_question_id) ."'
						,'". (int)(!empty($v['world_id']) ? $v['world_id'] : 0) ."'
						,'". (int)(!empty($v['theme_id']) ? $v['theme_id'] : 0) ."'
						,'". (int)(!empty($v['grade_id']) ? $v['grade_id'] : 0) ."'
						,'". (int)(!empty($v['generation_id']) ? $v['generation_id'] : 0) ."'
					),";
			}

			if(!empty($q)) {
				$q = "
					insert into activities.quiz_question_map (
						quiz_question_id
						,world_id
						,theme_id
						,grade_id
						,generation_id
					) values ". substr($q,0,-1);
				$res = db_query($q,"Inserting new quiz question map");
				if(db_is_error($res)) {
					error_message("An error occured adding a new map");
				}				
			}
		}
	}
error_message('x');
	if(!error_message()) {
		// audit('table_update',$table_info);

		$redirection_path = '/acu/quiz-questions/';
		set_post_message("The record has been successfully updated");
		set_safe_redirect($redirection_path);

	} else {
		error_message("An error has occurred while trying to update this record");
	}
}