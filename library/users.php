<?php

function get_user_security_roles($id) {
	$table = "security_role_user_map";
	if(uses_schema()) { $table = '"security"."role_user_map"'; }
	
	$q = "select role_id from ". $table ." where user_id='". $id ."'";
	$res = db_query($q,"Getting user roles");
	$user_roles = array();
	while($row = db_fetch_row($res)) {
		$user_roles[$row['role_id']] = 1;
	}
	return $user_roles;
}

function get_user_by_id($id, $type = "admin") {
	$type = strtolower($type);
	$type = trim($type);

	$users = "users";
	$registration_types = "registration_types";

	if (uses_schema()) {
		$users = '"system"."users"';
		$registration_types = '"public"."registration_types"';
	}

	if($type == "admin") {
		$q = "
			select
				u.*
				,rt.title as registration_type_title
			from ".$users." as u
			left join ".$registration_types." as rt on
				rt.id = u.registration_type_id
			where
				u.id = '".$id."'
		";
		return db_fetch($q,'Getting User');	
	}
}


function get_institution_by_user_id($id) {
	$q = "
		select
			institutions.*
			,regions.title as region
			,countries.title as country
		from public.institutions
		left join supplements.regions on
			regions.id = institutions.region_id
		left join supplements.countries on
			countries.id = institutions.country_id
		where
			user_id='". $id ."'
	";
	return db_fetch($q,'Getting User Institution');	
}


