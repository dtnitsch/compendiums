<?php
if(!empty($_POST) && !error_message()) {

	library('validation.php');

	$json = validation_create_json_string(validation_load_file(__DIR__ ."/../validation.json"),"php");
	validate_from_json($json);
	error_message(get_all_validation_errors());


	if(!error_message()) {

		$table_info = array(
			'table_name' => 'quiz_questions'
			,'table_schema' => 'public'
			,'primary_key' => 'id'
			# optional
			,'audit_table' => 'public_table_logs'
			,'audit_schema' => 'audits'
			,'primary_key_value' => db_prep_sql($_POST['id'])
			# Key = DB column Name, Value = Post name
			,'table_columns' => array()
		);

		$table_info['table_columns'][] = array(
			'title' => db_prep_sql($_POST['title'])
			,'alias' => db_prep_sql($_POST['alias'])
			,'description' => db_prep_sql($_POST['description'])
		);

		$res = "";
		if(($original_values = post_has_changes($table_info)) !== false) {
			$table_info["original_values"] = $original_values;

			if(($res = post_functions_update($table_info)) === false) {
				error_message("An error has occured trying to update this record");
			}
		} else {
			error_message("Comparison table is incorrect");
		}

		if(!error_message()) {
			audit('table_update',$table_info);

			$redirection_path = '/acu/quiz-questions/';
			set_post_message("The record has been successfully updated");
			set_safe_redirect($redirection_path);

		} else {
			error_message("An error has occurred while trying to update this record");
		}
	}
}