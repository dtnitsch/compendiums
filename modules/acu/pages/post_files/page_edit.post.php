<?php
if(!empty($_POST) && !ERROR_MESSAGE()) {
	LIBRARY('validation.php');

	
	validate($_POST['id'],'required','ID');
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
			,'primary_key_value' => db_prep_sql($_POST['id'])
			# Key = DB column Name, Value = Post name
			,'table_columns' => array()
		);

		$table_info['table_columns'][] = array(
			
			'page' => db_prep_sql($_POST['page'])
			,'alias' => db_prep_sql($_POST['alias'])
			,'content' => db_prep_sql($_POST['content'])
		);

		$res = '';
		$original_values = POST_HAS_CHANGES($table_info);
		if(!empty($original_values)) {
			$table_info['original_values'] = $original_values;
			$res = POST_FUNCTIONS_UPDATE($table_info);
		}

		if($res != 'error') {
			AUDIT('table_update',$table_info);

			$redirection_path = '/acu/pages/';
			SET_POST_MESSAGE("The record has been successfully updated");
			SET_SAFE_REDIRECT($redirection_path);

		} else {
			ERROR_MESSAGE("An error has occurred while trying to update this record");
		}
	}
}