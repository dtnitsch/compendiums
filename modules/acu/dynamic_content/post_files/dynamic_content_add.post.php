<?php
if(!empty($_POST) && !ERROR_MESSAGE()) {
	LIBRARY('validation.php');

	
	validate($_POST['title'],['required','string_length_between:1,128'],"Dynamic Content");
	validate($_POST['alias'],['required'],"Alias");
	validate($_POST['dynamic_content_type_id'],['required'],"Dynamic Content Type");

	if(!ERROR_MESSAGE()) {

		$table_info = array(
			'table_name' => 'dynamic_content'
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
			,'dynamic_content_type_id' => db_prep_sql($_POST['dynamic_content_type_id'])
			,'content' => db_prep_sql(base64_encode($_POST['content']))
			,'created' => 'now()'
			,'modified' => 'now()'
		);

		$new_id = POST_FUNCTIONS_INSERT($table_info)[0];
		
		$has_error = (empty($new_id) || !is_numeric($new_id) ? true : false);
		if(!$has_error) { 
			$table_info['primary_key_value'] = $new_id;

			AUDIT('table_insert',$table_info);

			# If we need to add stuff to the paths table, do so here
			$dynamic_url_pages = array('web_pages','newsletters','blog');
			$q = "
				select id
				from public.dynamic_content_types
				where
					alias in ('". implode("','",$dynamic_url_pages) ."')
					and id='". db_prep_sql($_POST['dynamic_content_type_id']) ."'
			";
			$res = db_query($q,"Checking for Dynamic URL Page");
			if(db_num_rows($res)) {

				$template = str_replace('/templates/','',$_POST['template']);
				$template = str_replace('.template.php','',$template);

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

				$table_info['table_columns'][] = array(
					
					'path' => db_prep_sql($_POST['url'])
					,'module_name' => ''
					,'dynamic_content_id' => $new_id
					,'template' => db_prep_sql($template)
					,'title' => db_prep_sql($_POST['title'])
					,'alias' => db_prep_sql($_POST['alias'])
					,'is_dynamic' => 't'
					,'created' => 'now()'
					,'modified' => 'now()'
				);
				$new_id = POST_FUNCTIONS_INSERT($table_info)[0];
				
				$has_error = (empty($new_id) || !is_numeric($new_id) ? true : false);
				if(!$has_error) { 
					$table_info['primary_key_value'] = $new_id;

					AUDIT('table_insert',$table_info);
				}
			}

			$redirection_path = '/acu/dynamic-content/?id='. $new_id;
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