<?php
if(!empty($_POST) && !ERROR_MESSAGE()) {
	LIBRARY('validation.php');

	
	validate($_POST['page'],['required','string_length_between:1,128'],"Page");
	validate($_POST['alias'],['required','string_length_between:1,128'],"Alias");
	validate($_POST['content'],['required'],"Content");

	if(!ERROR_MESSAGE()) {

		$table_info = array(
			'table_name' => 'page'
			,'table_schema' => 'public'
			,'primary_key' => 'id'
			# optional
			,'audit_table' => 'public_table_log'
			,'audit_schema' => 'audits'
			,'returning_value' => 'id'
			,'primary_key_value' => ''
			# Key = DB column Name, Value = Post name
			,'table_columns' => array()
		);

		$table_info['table_columns'][] = array(
			
			'page' => db_prep_sql($_POST['page'])
			,'alias' => db_prep_sql($_POST['alias'])
			,'content' => db_prep_sql($_POST['content'])
			,'created' => 'now()'
			,'modified' => 'now()'
		);

		$new_id = POST_FUNCTIONS_INSERT($table_info)[0];
		
		$has_error = (empty($new_id) || !is_numeric($new_id) ? true : false);
		if(!$has_error) { 
			$table_info['primary_key_value'] = $new_id;

			AUDIT('table_insert',$table_info);

			$redirection_path = '/acu/pages/?id='. $new_id;
			SET_POST_MESSAGE("You have successfully created a new record");
			SET_SAFE_REDIRECT($redirection_path);

		} else {
			$has_error = true;
		}
		if($has_error) {
			ERROR_MESSAGE("An error has occurred while trying to create a new record");
		}

	}
}