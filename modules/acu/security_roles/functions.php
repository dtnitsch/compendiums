<?php

function security_role_navigation($id,$section) {
	$section = trim($section);
	$section = strtolower($section);

	add_css('tabs.css');

	// Permission Checks
	$security_check_list = ['security_role_edit','security_role_audit','security_role_delete'];
	$security_list = has_access(implode(",",$security_check_list)); 

	$paths = [
		"edit" => [
			"label" => "Edit"
			,"url" => "/acu/security-roles/edit/?id=". $id
			,"permissions" => "security_role_edit"
		]
		,"audit" => [
			"label" => "Audit"
			,"url" => "/acu/security-roles/audit/?id=". $id
			,"permissions" => "security_role_audit"
		]
		,"delete" => [
			"label" => "Delete"
			,"url" => "/acu/security-roles/delete/?id=". $id
			,"permissions" => "security_role_delete"
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