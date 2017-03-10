<?php
if(!empty($_POST) && !ERROR_MESSAGE()) {
	LIBRARY("validation.php");

	validate($_POST["id"],"required","ID");

	if(!ERROR_MESSAGE()) {

		library("security_permission_grid.php");

		$id = (int)$_POST["id"];
		$time = date('Y-m-d H:i:s',time());

		$q = "select count(id) as cnt from security.permission_override_map where user_id = '". $id ."'";
		$override_count = db_fetch($q,"Getting override Count");

		$perms = build_permission_grid();

		$sections = array();
		$groups = array();
		$permissions = array();
		foreach($perms as $row) {
			if(!empty($row[0])) {
				$sections[$row[0]] = 1;
			} else if(!empty($row[1])) {
				$groups[$row[1]] = 1;
			} else {
				$permissions[$row[2]] = 1;
			}
		}

		$use_override = true;
		if($override_count == 0) {
			$use_override = false;

			$checker = permission_override_checker($sections,$groups,$permissions);
			if(!$checker) {
				$use_override = true;
			}

		}

		// If there are any changes 
		if($use_override && !empty($perms)) {

			// DTN - Nov 10, 2015 -- YUCK!  Compare old to new before doing a cleanup?
			$q = "delete from security.permission_override_map where user_id = '". $id ."'";
			$res = db_query($q,"Deleting Security Permission");
			if(db_is_error($res)) {
				error_message("An error has occured while changing override permissions");
			}

			$q = '';
			foreach($perms as $row) {
				$q .= "('". $id ."','". $row[0] ."','". $row[1] ."','". $row[2] ."','". $time ."','". $time ."'),";
			}
			$q = "insert into security.permission_override_map (user_id,section_id,group_id,permission_id,created,modified) values ". substr($q,0,-1);
			$res = db_query($q,"Inserting new overrides");
			if(db_is_error($res)) {
				error_message("An error has occured while adding the override permissions");
			}

		}

		if(!error_message()) {
			$redirection_path = '/acu/security-roles/';
			set_post_message("The record has been successfully updated");
			// set_safe_redirect($redirection_path);
		} else {
			$has_errors = true;
		}
	}
}


function permission_override_checker($sections,$groups,$permissions) {

	$full_permissions_res = get_full_permissions_list($_POST['id']);

	$sections2 = array();
	$groups2 = array();
	$permissions2 = array();
	$full_permissions = array();
	$type_prefix = "has";
	while($row = mysql_fetch_assoc($full_permissions_res)) {
		$full_permissions[] = $row;
		if(!is_null($row['override_section'])) {
			$type_prefix = "override";
		}
	}

	foreach($full_permissions as $row) {
		if(!empty($row[$type_prefix .'_section'])) {
			$sections2[$row[$type_prefix .'_section']] = 1;
		}
		if(!empty($row[$type_prefix .'_group'])) {
			$groups2[$row[$type_prefix .'_group']] = 1;
		}
		if(!empty($row[$type_prefix .'_permission'])) {
			$permissions2[$row[$type_prefix .'_permission']] = 1;
		}
	}	

	return ($sections == $sections2 && $groups == $groups2 && $permissions == $permissions2);
}
