<?php
if(!empty($_POST) && !error_message()) {
	library('validation.php');

	
	validate($_POST['path'],['required','string_length_between:1,128'],"Path");
	validate($_POST['module_name'],['required','string_length_between:1,128'],"Module Name");
	validate($_POST['template'],['required'],"Template");
	validate($_POST['title'],['required','string_length_between:1,128'],"Title");
	validate($_POST['alias'],['required','string_length_between:1,128'],"Alias");

	$errors = get_all_validation_errors();
	if(!empty($errors)) {
		foreach($errors as $error) {
			error_message($error);
		}		
	}

	if(!error_message()) {

		$table_info = array(
			'table_name' => 'paths'
			,'table_schema' => 'system'
			,'primary_key' => 'id'
			# optional
			,'audit_table' => 'system_table_logs'
			,'audit_schema' => 'audits'
			,'returning_value' => 'id'
			,'primary_key_value' => ''
			# Key = DB column Name, Value = Post name
			,'table_columns' => array()
		);

		if($_POST['path'] != "/") { $_POST['path'] = "/".$_POST['path']; }
		if(substr($_POST['path'],-1) != "/") { $_POST['path'] = $_POST['path']."/"; }

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
			,'is_dynamic' => (!empty($_POST['is_dynamic']) && $_POST['is_dynamic'] == 't' ? 't' : 'f')
			,'alias' => db_prep_sql($_POST['description'])
			,'created' => 'now()'
			,'modified' => 'now()'
		);

		$new_id = post_functions_insert($table_info)[0];
		
		$has_error = (empty($new_id) || !is_numeric($new_id) ? true : false);
		if(!$has_error) { 
			$table_info['primary_key_value'] = $new_id;

			audit('table_insert',$table_info);

			$redirection_path = '/acu/paths/?id='. $new_id;
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