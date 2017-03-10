<?php

function list_navigation($id,$section) {
	$section = trim($section);
	$section = strtolower($section);

	add_css('tabs.css');

	// Permission Checks
	$security_check_list = ['lists_edit','lists_audit','lists_delete'];
	$security_list = has_access(implode(",",$security_check_list)); 

	$lists = [
		"edit" => [
			"label" => "Edit"
			,"url" => "/acu/lists/edit/?id=". $id
			,"permissions" => "lists_edit"
		]
		,"audit" => [
			"label" => "Audit"
			,"url" => "/acu/lists/audit/?id=". $id
			,"permissions" => "lists_edit"
		]
		,"delete" => [
			"label" => "Delete"
			,"url" => "/acu/lists/delete/?id=". $id
			,"permissions" => "lists_edit"
		]
	];

	$output = '
	<div id="navcontainer">
		<ul id="navlist">
	';
	foreach($lists as $section_name => $row) {
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