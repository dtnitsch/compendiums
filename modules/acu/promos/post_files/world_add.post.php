<?php
if(!empty($_POST) && !error_message()) {
	library('validation.php');

	$_POST["id"] = true;
	$json = validation_create_json_string(validation_load_file(__DIR__ ."/../validation.json"),"php");
	validate_from_json($json);
	error_message(get_all_validation_errors());


	if(!error_message()) {

		$table_info = array(
			'table_name' => 'worlds'
			,'table_schema' => 'public'
			,'primary_key' => 'id'
			# optional
			,'audit_table' => 'public_table_logs'
			,'audit_schema' => 'audits'
			,'returning_value' => 'id'
			,'primary_key_value' => ''
			# Key = DB column Name, Value = Post name
			,'table_columns' => array()
		);

		$table_info['table_columns'][] = array(
			
			'title' => db_prep_sql($_POST['title'])
			,'alias' => db_prep_sql($_POST['alias'])
			,'description' => db_prep_sql($_POST['description'])
			,'created' => 'now()'
			,'modified' => 'now()'
		);

		$new_id = post_functions_insert($table_info)[0];
		
		$has_error = (empty($new_id) || !is_numeric($new_id) ? true : false);
		if(!$has_error) { 
			$table_info['primary_key_value'] = $new_id;

			audit('table_insert',$table_info);

			$redirection_path = '/acu/worlds/?id='. $new_id;
			set_post_message("You have successfully created a new record");
			set_safe_redirect($redirection_path);

		} else {
			$has_error = true;
		}
		if($has_error) {
			error_message("An error has occurred while trying to create a new record");
		}

	}
}