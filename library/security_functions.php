<?php

function get_security_roles() {
	$table = "security_roles";
	if(uses_schema()) { $table = '"security"."roles"'; }
	
	$q = "select id,title from ". $table ."  where active order by title";
	$res = db_query($q,__FUNCTION__."()");
	if($res) {
		return $res;
	}
	return false;
}

function get_full_permissions_list($id) {
	if(empty($id)) {
		return false;
	}

	$security_permissions = "security_permissions";
	$security_sections = "security_sections";
	$security_groups = "security_groups";
	$security_permission_override_map = "security_permission_override_map";
	$security_permission_types = "security_permission_types";
	$security_role_permission_map = "security_role_permission_map";
	$security_roles = "security_roles";
	$security_role_user_map = "security_role_user_map";
	$group_concat = "group_concat(". $security_roles .".title separator ', ')";
	if(uses_schema()) {
		$security_permissions = '"security"."permissions"';
		$security_sections = '"security"."sections"';
		$security_groups = '"security"."groups"';
		$security_permission_override_map = '"security"."permission_override_map"';
		$security_permission_types = '"security"."permission_types"';
		$security_role_permission_map = '"security"."role_permission_map"';
		$security_roles = '"security"."roles"';
		$security_role_user_map = '"security"."role_user_map"';
		$group_concat = "string_agg(". $security_roles .".title, ', ')";
	}

	$q = "
		select
			". $security_permissions .".id
			,". $security_permissions .".title
			,". $security_permissions .".alias
			,". $security_permissions .".created
			,". $security_permissions .".modified
			,". $security_sections .".id as section_id
			,". $security_sections .".title as section
			,". $security_groups .".id as group_id
			,". $security_groups .".title as grp
			,user_permissions.id as role_permission_id
			,user_permissions.role_title
			,user_permissions.has_section
			,user_permissions.has_group
			,user_permissions.has_permission
			
			,user_overrides.override_section
			,user_overrides.override_group
			,user_overrides.override_permission
			
		from ". $security_permissions ."
		join ". $security_sections ." on ". $security_sections .".id = ". $security_permissions .".section_id and ". $security_sections .".active
		join ". $security_groups ." on ". $security_groups .".id = ". $security_permissions .".group_id and ". $security_groups .".active
		
		left join (
			select
				". $security_permissions .".id
				,". $security_permissions .".title
				,max(". $security_role_permission_map .".section_id) as has_section
				,max(". $security_role_permission_map .".group_id) as has_group
				,max(". $security_role_permission_map .".permission_id) as has_permission
				,". $group_concat ." as role_title

			from ". $security_permissions ."
			left join ". $security_role_permission_map ." on 
				(
					". $security_role_permission_map .".permission_id = ". $security_permissions .".id
					or ". $security_role_permission_map .".group_id = ". $security_permissions .".group_id
					or ". $security_role_permission_map .".section_id = ". $security_permissions .".section_id
				)
				and ". $security_role_permission_map .".role_id in (
					select role_id from ". $security_role_user_map ." where user_id='". $id ."'
				)
			join ". $security_roles ." on ". $security_roles .".id = ". $security_role_permission_map .".role_id
			where
				". $security_permissions .".active
			group by
				". $security_permissions .".id
				,". $security_permissions .".title

		) as user_permissions on user_permissions.id = ". $security_permissions .".id


		left join (
			select
				". $security_permissions .".id
				,". $security_permission_override_map .".section_id as override_section
				,". $security_permission_override_map .".group_id as override_group
				,". $security_permission_override_map .".permission_id as override_permission

			from ". $security_permissions ."
			left join ". $security_permission_override_map ." on
				(
					". $security_permission_override_map .".section_id = ". $security_permissions .".section_id
					or ". $security_permission_override_map .".group_id = ". $security_permissions .".group_id
					or ". $security_permission_override_map .".permission_id = ". $security_permissions .".id
				)
				and ". $security_permission_override_map .".user_id = '". $id ."'

			where
				". $security_permissions .".active
			group by
				". $security_permissions .".id
				,". $security_permission_override_map .".section_id
				,". $security_permission_override_map .".group_id
				,". $security_permission_override_map .".permission_id
		) as user_overrides on user_overrides.id = ". $security_permissions .".id
		

		where
			". $security_permissions .".active 
		order by
			section,
			grp,
			title
	";
	$res = db_query($q,__FUNCTION__."()");
	if($res) {
		return $res;
	}
	return false;
}