<?php
if(!empty($_POST) && !error_message()) {
	library('validation.php');

	
	validate($_POST['id'],'required','ID');
	validate($_POST['path'],['required','string_length_between:1,128'],"Path");
	validate($_POST['module_name'],['required','string_length_between:1,128'],"Module Name");
	validate($_POST['template'],['required'],"Template");
	validate($_POST['title'],['required','string_length_between:1,128'],"Title");
	validate($_POST['alias'],['required','string_length_between:1,128'],"Alias");

	if(!error_message()) {

		$table_info = array(
			'table_name' => 'paths'
			,'table_schema' => 'system'
			,'primary_key' => 'id'
			# optional
			,'audit_table' => 'system_table_logs'
			,'audit_schema' => 'audits'
			,'primary_key_value' => db_prep_sql($_POST['id'])
			# Key = DB column Name, Value = Post name
			,'table_columns' => array()
		);

		$pieces = explode("/",$_POST['module_name']);
		$module_name = array_pop($pieces);
		$_POST['module_name'] = str_replace('.php','',$module_name);

		$pieces = explode("/",$_POST['template']);
		$template = array_pop($pieces);
		$_POST['template'] = str_replace('.template.php','',$template);

		$_POST['is_dynamic'] = (!empty($_POST['is_dynamic']) && $_POST['is_dynamic'] == 't' ? 't' : 'f');

		$table_info['table_columns'][] = array(
			'path' => db_prep_sql($_POST['path'])
			,'module_name' => db_prep_sql($_POST['module_name'])
			,'template' => db_prep_sql($_POST['template'])
			,'title' => db_prep_sql($_POST['title'])
			,'alias' => db_prep_sql($_POST['alias'])
			,'description' => db_prep_sql($_POST['description'])
			,'is_dynamic' => $_POST['is_dynamic']
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

			$redirection_path = '/acu/paths/';
			set_post_message("The record has been successfully updated");
			set_safe_redirect($redirection_path);

		} else {
			error_message("An error has occurred while trying to update this record");
		}
	}
}