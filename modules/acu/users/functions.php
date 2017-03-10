<?php

function user_navigation($id,$section) {
	$section = trim($section);
	$section = strtolower($section);

	add_css('tabs.css');

	// Permission Checks
	$security_check_list = ['admin_users_edit','admin_users_permissions','admin_users_audit','admin_users_delete'];
	$security_list = has_access(implode(",",$security_check_list)); 

	$paths = [
		"edit" => [
			"label" => "Edit"
			,"url" => "/acu/users/edit/?id=". $id
			,"permissions" => "admin_users_edit"
		]
		,"permissions" => [
			"label" => "Permissions"
			,"url" => "/acu/users/permissions/?id=". $id
			,"permissions" => "admin_users_permissions"
		]
		,"audit" => [
			"label" => "Audit"
			,"url" => "/acu/users/audit/?id=". $id
			,"permissions" => "admin_users_audit"
		]
		,"delete" => [
			"label" => "Delete"
			,"url" => "/acu/users/delete/?id=". $id
			,"permissions" => "admin_users_delete"
		]
	];

	$output = '
	<div id="navcontainer">
		<ul id="navlist">
	';
	foreach($paths as $section_name => $row) {
		if(empty($security_list[$row['permissions']])) { continue; }
		if($section_name == $section) {
			$output .= '<li id="active"><a href="#" id="current">'. $row["label"] .'</a></li>';
		} else {
			$output .= '<li><a href="'. $row["url"] .'" title="'. $row["label"] .'">'. $row["label"] .'</a></li>';
		}
	}
	$output .= '
		</ul>
	</div>';

	return $output;
}