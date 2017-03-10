<?php

function skill_and_drill_navigation($id,$section) {
	$section = trim($section);
	$section = strtolower($section);

	add_css('tabs.css');

	// Permission Checks
	$security_check_list = ['skill_and_drill_edit','skill_and_drill_audit','skill_and_drill_delete'];
	$security_list = has_access(implode(",",$security_check_list)); 

	$paths = [
		"edit" => [
			"label" => "Edit"
			,"url" => "/acu/skill-and-drill/edit/?id=". $id
			,"permissions" => "skill_and_drill_edit"
		]
		,"audit" => [
			"label" => "Audit"
			,"url" => "/acu/skill-and-drill/audit/?id=". $id
			,"permissions" => "skill_and_drill_audit"
		]
		,"delete" => [
			"label" => "Delete"
			,"url" => "/acu/skill-and-drill/delete/?id=". $id
			,"permissions" => "skill_and_drill_delete"
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