<?php

function school_navigation($id,$section) {
	$section = trim($section);
	$section = strtolower($section);

	add_css('tabs.css');

	// Permission Checks
	$security_check_list = ['admin_schools_edit','admin_schools_permissions','admin_schools_audit','admin_schools_delete'];
	$security_list = has_access(implode(",",$security_check_list)); 

	$paths = [
		"edit" => [
			"label" => "Edit"
			,"url" => "/acu/schools/edit/?id=". $id
			,"permissions" => "admin_schools_edit"
		]
		,"audit" => [
			"label" => "Audit"
			,"url" => "/acu/schools/audit/?id=". $id
			,"permissions" => "admin_schools_audit"
		]
		,"delete" => [
			"label" => "Delete"
			,"url" => "/acu/schools/delete/?id=". $id
			,"permissions" => "admin_schools_delete"
		]
	];

	$output = '
	<div id="navcontainer">
		<ul id="navlist">
	';
	foreach($paths as $section_name => $row) {
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