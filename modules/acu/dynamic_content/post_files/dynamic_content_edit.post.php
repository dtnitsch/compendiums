<?php
if(!empty($_POST) && !error_message()) {
	library('validation.php');


	validate($_POST['id'],'required','ID');
	validate($_POST['title'],['required','string_length_between:1,128'],"Dynamic Content");
	validate($_POST['alias'],['required'],"Alias");
	validate($_POST['dynamic_content_type_id'],['required'],"Dynamic Content Type");


	if(!error_message()) {

		$table_info = array(
			'table_name' => 'dynamic_content'
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
			,'dynamic_content_type_id' => db_prep_sql($_POST['dynamic_content_type_id'])
			,'content' => db_prep_sql(base64_encode($_POST['content']))
		);

		$res = '';
		// $original_values = post_has_changes($table_info);
		if(($original_values = post_has_changes($table_info)) !== false) {
			$table_info['original_values'] = $original_values;
			if(($res = post_functions_update($table_info)) === false) {
				error_message("An error has occured trying to update this record");
			}
		} else {
			error_message("Comparison table is incorrect");
		}

		if(!error_message()) {
			audit('table_update',$table_info);

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
			if(!db_num_rows($res)) {
				error_message("Dynamic Page URL's not valid");
			}

			if(!error_message()) {
				$q = "
					select
						system.paths.id as system_path_id
					from public.dynamic_content
					left join system.paths on
						paths.dynamic_content_id = dynamic_content.id
						and dynamic_content_type_id = 1
					where dynamic_content.id='". db_prep_sql($_POST['id']) ."'
				";
				$path_info = db_fetch($q,'Path ID');


				if(!empty($path_info['system_path_id'])) {
					$template = str_replace('/templates/','',$_POST['template']);
					$template = str_replace('.template.php','',$template);

					$table_info = array(
						'table_name' => 'paths'
						,'table_schema' => 'system'
						,'primary_key' => 'id'
						# optional
						,'audit_table' => 'system_table_logs'
						,'audit_schema' => 'audits'
						,'primary_key_value' => db_prep_sql($path_info['system_path_id'])
						# Key = DB column Name, Value = Post name
						,'table_columns' => array()
					);

					$table_info['table_columns'][] = array(
						'path' => db_prep_sql($_POST['path'])
						,'template' => db_prep_sql($template)
						,'title' => db_prep_sql($_POST['title'])
						,'alias' => db_prep_sql($_POST['alias'])
					);

					$original_values = post_has_changes($table_info);
					// No path change, do a normal update
					if(empty($original_values['path']) || $original_values['path'] == $_POST['path']) {

						if(!empty($original_values)) {
							$table_info['original_values'] = $original_values;
							// $res = post_functions_update($table_info);
							if(($res = post_functions_update($table_info)) === false) {
								error_message("An error has occured trying to update this record");
							}
						}



					} else {
						// Path has changed, Create new path and force redirect on old force a redirect

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

							'path' => db_prep_sql($_POST['path'])
							,'module_name' => ''
							,'dynamic_content_id' => $_POST['id']
							,'template' => db_prep_sql($template)
							,'title' => db_prep_sql($_POST['title'])
							,'alias' => db_prep_sql($_POST['alias'])
							,'is_dynamic' => 't'
							,'created' => 'now()'
							,'modified' => 'now()'
						);
						$new_id = post_functions_insert($table_info)[0];

						$has_error = (empty($new_id) || !is_numeric($new_id) ? true : false);
						if(!$has_error) {
							$table_info['primary_key_value'] = $new_id;

							audit('table_insert',$table_info);
							$res = '';
						}

						$table_info = array(
							'table_name' => 'paths'
							,'table_schema' => 'system'
							,'primary_key' => 'id'
							# optional
							,'audit_table' => 'system_table_logs'
							,'audit_schema' => 'audits'
							,'primary_key_value' => db_prep_sql($path_info['system_path_id'])
							# Key = DB column Name, Value = Post name
							,'table_columns' => array()
						);

						$table_info['table_columns'][] = array(
							'redirect' => 't'
							,'path_redirect' => db_prep_sql($_POST['path'])
							,'modified' => 'now()'
						);

						if(!empty($original_values)) {
							$table_info['original_values'] = $original_values;
							// $res = post_functions_update($table_info);
							if(($res = post_functions_update($table_info)) === false) {
								error_message("An error has occured trying to update this record");
							}

						}
					}

					if(!error_message()) {
						audit('table_update',$table_info);

						$redirection_path = '/acu/dynamic-content/';
						set_post_message("The record has been successfully updated");
						set_safe_redirect($redirection_path);
					}
				}
			}


		}
	}
}