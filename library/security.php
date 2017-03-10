<?php
########################################################################
#	Security Check
########################################################################
// function SET_SECURITY($path_id) {
// 	_error_debug("SET_SECURITY()",'',__LINE__,__FILE__);

// 	if(!empty($_SESSION['user']['is_superadmin'])) { return; }
// 	if(empty($_SESSION['user']['security_role_id'])) {
// 		$GLOBALS['nav_sections'] = '';
// 		$GLOBALS['page_access'] = '';
// 		return false;
// 	}

// 	if(empty($_SESSION['user']['security_role_id'])) {
// 		$q = "
// 			select path,nav_section
// 			from system.paths as p
// 			join system.security_role_group as srg on srg.path_id = p.path_id
// 			join system.security_role as sr on sr.security_role_id=srg.security_role_id and sr.alias='unauthenticated_user'
// 		";
// 	} else {
// 		$q = "
// 			select path,nav_section
// 			from system.paths as p
// 			join system.security_role_group as srg on srg.path_id = p.path_id
// 			where
// 				security_role_id in (". $_SESSION['user']['security_role_id']  .")
// 		";
// 	}
// 	$res = db_query($q,"Getting Security Access");

// 	$nav_sections = $page_access = array();
// 	while($row = db_fetch_row($res)) {
// 		$nav_sections[$row['nav_section']] = 1;
// 		$page_access[$row['path']] = 1;
// 	}
// 	$GLOBALS['nav_sections'] = $nav_sections;
// 	$GLOBALS['page_access'] = $page_access;
// }
// function CHECK_SECURITY_PATH($path) {
// 	if(!empty($_SESSION['user']['is_superadmin'])) { return true; }
// 	if(isset($GLOBALS['page_access'][$path])) { return true; }
// 	return false;
// }

########################################################################
#	Get list of permission based on roles and overrides
########################################################################
// function user_permissions($user_id) {
// 	_error_debug(__FUNCTION__."()");
// 	$q = "
// 		select
// 			user_permissions.id
// 			,user_permissions.alias
// 			,permission_override_map.permission_type_id
// 		from (
// 			select
// 				security.permission.id
// 				,security.permission.alias
// 			from security.permission
// 			join security.role_permission_map on
// 				security.role_permission_map.permission_id = security.permission.id
// 				and security.role_permission_map.role_id in (
// 					select role_id from security.role_user_map where user_id = '". $user_id ."'
// 				)
// 			where
// 				security.permission.active
// 			group by
// 				security.permission.id
// 		) as user_permissions
// 		left join security.permission_override_map on
// 			permission_override_map.permission_id = user_permissions.id
// 			and permission_override_map.user_id = '". $user_id ."'
// 	";
// 	$res = db_query($q,"Getting User Permissions");
// 	$output = array();
// 	while($row = db_fetch_row($res)) {
// 		$output[$row['alias']]['type'] = (!empty($row['permission_type_id']) ? $row['permission_type_id'] : 1);
// 	}
// 	// $_SESSION['user']['security'] = $output;
// }

########################################################################
#	Check Page Security
########################################################################
function module_security($aliases) {
	_error_debug(__FUNCTION__."()",$aliases);

	// No user ID means nothing to check against ... fail
	if(empty($_SESSION['user']['id'])) { return false; }
	$user_id = $_SESSION['user']['id'];

	$aliases = trim($aliases);
	if(empty($aliases)) { return true; }
	$pieces = explode(',',$aliases);



	// If superuser, continue
	if(!empty($_SESSION['user']['is_superadmin'])) { return superadmin_permissions($pieces); }

	// Check if we already have those permissions stored
	$aliases_to_check = "";
	foreach($pieces as $row) {
		if(!has_stored_permission($row)) {
			$aliases_to_check .= "'". db_prep_sql($row) ."',";
		}
	}

	// Nothing new to check?
	if(empty($aliases_to_check)) {
		return get_module_security($pieces);
	}

	// Add code to store security aliases that have already been looked up
	$q = "
		select
			permissions.alias
			,count(security.role_permission_map.id) as cnt
		from security.permissions
		left join security.role_permission_map on
			(
				security.role_permission_map.section_id = security.permissions.section_id
				or security.role_permission_map.group_id = security.permissions.group_id
				or security.role_permission_map.permission_id = security.permissions.id
			)
			and security.role_permission_map.role_id in (
				select role_id from security.role_user_map where user_id = '". $user_id ."'
			)
		where
			security.permissions.active
			and security.permissions.alias in (". substr($aliases_to_check,0,-1) .")
		group by
			security.permissions.id
	";
	$res = db_query($q,"Check Page Permissions");
	store_permissions($res);

	if(($output = get_module_security($pieces)) !== false) {
		return $output;
	}

	_error_debug("Security Fail",$aliases);
	return false;
}

function superadmin_permissions($permissions) {
	// Only one item
	if(empty($permissions[1])) {
		return true;
	}
	$output = array();
	foreach($permissions as $v) {
		$output[$v] = 1;
	}
	return $output;
}

function store_permissions($res) {

	if(empty($GLOBALS['security_permissions'])) { $GLOBALS['security_permissions'] = array(); }
	while($row = db_fetch_row($res)) {
		$GLOBALS['security_permissions'][$row['alias']] = $row['cnt'];
	}

}

function has_stored_permission($alias = "") {

	if(!empty($alias)) {
		if(!empty($GLOBALS['security_permissions'][$alias])) {
			return true;
		}
	}
	return false;
}

function get_stored_permission($alias = "") {
	// echo "<br>has_stored_permission: ". $alias;
	if(!empty($alias)) {
		if(isset($GLOBALS['security_permissions'][$alias])) {
			return $GLOBALS['security_permissions'][$alias];
		}
	}
	return false;
}

function get_module_security($aliases = "") {
	if(!empty($aliases) && !is_array($aliases)) {
		return (has_stored_permission($aliases) ? 1 : 0);

	} else if(!empty($aliases) && is_array($aliases) && empty($aliases[1])) {
		return (has_stored_permission($aliases[0]) ? 1 : 0);

	} else if(!empty($aliases) && is_array($aliases)) {
		$output = [];
		foreach($aliases as $row) {
			$output[$row] = (has_stored_permission($row) ? 1 : 0);
		}
		return $output;
	}
	return false;
}
