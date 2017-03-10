<?php
if(!empty($_POST) && !ERROR_MESSAGE()) {
	LIBRARY('validation.php');

	
	validate($_POST['id'],'required','ID');
	validate($_POST['title'],['required','string_length_between:1,128'],"Dynamic Content Type");
	validate($_POST['alias'],['required'],"Alias");

	if(!ERROR_MESSAGE()) {

		$table_info = array(
			'table_name' => 'dynamic_content_types'
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

		$res = '';
		$original_values = POST_HAS_CHANGES($table_info);
		if(!empty($original_values)) {
			$table_info['original_values'] = $original_values;
			$res = POST_FUNCTIONS_UPDATE($table_info);
		}

		if($res != 'error') {
			AUDIT('table_update',$table_info);

			$redirection_path = '/acu/dynamic-content-type/';
			SET_POST_MESSAGE("The record has been successfully updated");
			SET_SAFE_REDIRECT($redirection_path);

		} else {
			ERROR_MESSAGE("An error has occurred while trying to update this record");
		}
	}
}