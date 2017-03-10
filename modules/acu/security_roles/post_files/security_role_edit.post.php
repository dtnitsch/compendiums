<?php
if(!empty($_POST) && !error_message()) {
	library('validation.php');


	validate($_POST['id'],'required','ID');
	validate($_POST['title'],['required','string_length_between:1,128'],"Security Role");
	validate($_POST['alias'],['required'],"Alias");

	$errors = get_all_validation_errors();
	if(!empty($errors)) {
		foreach($errors as $error) {
			error_message($errors);
		}		
	}

	if(!error_message()) {

		$table_info = array(
			'table_name' => 'roles'
			,'table_schema' => 'security'
			,'primary_key' => 'id'
			# optional
			,'audit_table' => 'security_table_logs'
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

			$q = "
				delete from security.role_permission_map 
				where
					role_id = '". $_POST['id'] ."'
			";
			if(!db_query($q,"Removing role permissions")) {
				#$has_errors = true;
				error_message("Error removing role permissions");
			}

			$arr = build_permission_grid();
			$q = '';
			foreach($arr as $row) {
				$q .= "('". $_POST['id'] ."','". $row[0] ."','". $row[1] ."','". $row[2] ."'),";
			}
			$q = "insert into security.role_permission_map (role_id,section_id,group_id,permission_id) values ". substr($q,0,-1);
			if(!db_query($q,"Adding role permissions")) {
				error_message("Error adding role permissions");
			}
				
			// $res = db_query("select permission_id from security.role_permission_map where role_id = '". $_POST['id'] ."'");
			// $db_perms = array();
			// while($row = db_fetch_row($res)) {
			// 	$db_perms[] = $row['permission_id'];
			// }

			// $post_perms = array();
			// foreach($_POST['permission'] as $tmp1) {
			// 	foreach($tmp1 as $tmp2) {
			// 		foreach($tmp2 as $permission_id => $row) {
			// 			$post_perms[] = $permission_id;
			// 		}
			// 	}
			// }

			// $add = array_diff($post_perms,$db_perms);
			// $del = array_diff($db_perms,$post_perms);

			// if(!empty($add)) {
			// 	$q = '';
			// 	foreach($add as $permission_id) {
			// 		$q .= "('". $_POST['id'] ."','". $permission_id ."'),";
			// 	}
			// 	$q = "insert into security.role_permission_map (role_id,permission_id) values ". substr($q,0,-1);
			// 	if(!db_query($q,"Adding role permissions")) {
			// 		ERROR_MESSAGE("Error adding role permissions");
			// 	}
			// }

			// if(!empty($del)) {
			// 	$q = "
			// 		delete from security.role_permission_map 
			// 		where
			// 			role_id = '". $_POST['id'] ."'
			// 			and permission_id in ('". implode("','",$del) ."')
			// 	";
			// 	if(!db_query($q,"Removing role permissions")) {
			// 		#$has_errors = true;
			// 		ERROR_MESSAGE("Error removing role permissions");
			// 	}
			// }

			if(!error_message()) {
				$redirection_path = '/acu/security-roles/';
				set_post_message("The record has been successfully updated");
				// set_safe_redirect($redirection_path);
			} else {
				$has_errors = true;
			}

		} else {
			error_message("An error has occurred while trying to update this record");
		}
	}
}

function build_permission_grid() {

	if(!empty($_POST['section'])) {
		foreach($_POST['section'] as $section_id) {
			if(!empty($_POST['group'][$section_id])) {
				unset($_POST['group'][$section_id]);
			}
			if(!empty($_POST['permission'][$section_id])) {
				unset($_POST['permission'][$section_id]);
			}
		}
	}

	if(!empty($_POST['group'])) {
		foreach($_POST['group'] as $sid => $r1) {
			foreach($r1 as $gid => $r2) {
				if(!empty($_POST['permission'][$sid][$gid])) {
					unset($_POST['permission'][$sid][$gid]);
				}
			}
		}
	}

	$arr = array();
	$used = array();
	if(!empty($_POST['permission'])) {
		foreach($_POST['permission'] as $sid => $r1) {
			foreach($r1 as $gid => $r2) {
				foreach($r2 as $permission_id) {
					$section_id = (!empty($_POST['section'][$sid]) ? $sid : 0);
					$group_id = (!empty($_POST['group'][$sid][$gid]) ? $gid : 0);
					$used["section"][$section_id] = 1;
					$used["group"][$group_id] = 1;
					$arr[] = [$section_id , $group_id , $permission_id];
				}
			}
		}
	}

	if(!empty($_POST['group'])) {
		foreach($_POST['group'] as $sid => $r1) {
			foreach($r1 as $group_id) {
				$section_id = (!empty($_POST['section'][$sid]) ? $sid : 0);
				$used["section"][$section_id] = 1;
				if(empty($used["group"][$group_id])) {
					$arr[] = [$section_id , $group_id ,0];
				}
			}
		}
	}

	if(!empty($_POST['section'])) {
		foreach($_POST['section'] as $section_id) {
			if(empty($used["section"][$section_id])) {
				$arr[] = [$section_id,0,0];
			}
		}
	}



	unset($used);
	return $arr;
}