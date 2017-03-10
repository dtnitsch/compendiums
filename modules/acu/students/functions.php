<?php

function student_navigation($id, $section) {
	$section = trim($section);
	$section = strtolower($section);

	add_css("tabs.css");

	// Permission Checks
	$security_check_list = ["admin_students_edit", "admin_students_permissions", "admin_students_audit", "admin_students_delete"];
	$security_list = has_access(implode(",", $security_check_list)); 

	$paths = [
		"edit" => [
			"label" => "Edit"
			,"url" => "/acu/students/edit/?id=". $id
			,"permissions" => "admin_students_edit"
		]
		,"delete" => [
			"label" => "Delete"
			,"url" => "/acu/students/delete/?id=". $id
			,"permissions" => "admin_students_delete"
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