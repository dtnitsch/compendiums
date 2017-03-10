<?php

function world_navigation($id,$section) {
	$section = trim($section);
	$section = strtolower($section);

	add_css('tabs.css');

	// Permission Checks
	$security_check_list = ['worlds_edit','worlds_audit','worlds_delete'];
	$security_list = has_access(implode(",",$security_check_list)); 

	$worlds = [
		"edit" => [
			"label" => "Edit"
			,"url" => "/acu/worlds/edit/?id=". $id
			,"permissions" => "worlds_edit"
		]
		,"audit" => [
			"label" => "Audit"
			,"url" => "/acu/worlds/audit/?id=". $id
			,"permissions" => "worlds_edit"
		]
		,"delete" => [
			"label" => "Delete"
			,"url" => "/acu/worlds/delete/?id=". $id
			,"permissions" => "worlds_edit"
		]
	];

	$output = '
	<div id="navcontainer">
		<ul id="navlist">
	';
	foreach($worlds as $section_name => $row) {
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