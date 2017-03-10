<?php
if(!empty($_POST) && !ERROR_MESSAGE()) {
	LIBRARY('validation.php');

	
	validate($_POST['title'],['required','string_length_between:1,128'],"Security Role");
	validate($_POST['alias'],['required'],"Alias");

	if(!ERROR_MESSAGE()) {

		$table_info = array(
			'table_name' => 'role'
			,'table_schema' => 'security'
			,'primary_key' => 'id'
			# optional
			,'audit_table' => 'security_table_log'
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

			AUDIT('table_insert',$table_info);

			$q = '';
			foreach($_POST['permission'] as $section_id => $tmp1) {
				foreach($tmp1 as $group_id => $tmp2) {
					foreach($tmp2 as $permission_id => $row) {
						$q .= "('". $table_info['primary_key_value'] ."','". $permission_id ."'),";
					}
				}
			}
			$q = "insert into security.role_permission_map  (role_id,permission_id) values ". substr($q,0,-1);
			if(db_query($q,"Adding role permissions")) {
				SET_POST_MESSAGE("You have successfully created '". $_POST['title'] ."'.");
				$redirection_path = '/acu/security-roles/?id='. $new_id[0];
				SET_SAFE_REDIRECT($redirection_path);
			} else {
				$has_error = true;
			}

		} else {
			$has_error = true;
		}
		if($has_error) {
			ERROR_MESSAGE("An error has occurred while trying to create a new record");
		}

	}
}