<?php

function prize_navigation($id,$section) {
	$section = trim($section);
	$section = strtolower($section);

	add_css('tabs.css');

	// Permission Checks
	$security_check_list = ['prizes_edit','prizes_audit','prizes_delete'];
	$security_list = has_access(implode(",",$security_check_list)); 

	$prizes = [
		"edit" => [
			"label" => "Edit"
			,"url" => "/acu/prizes/edit/?id=". $id
			,"permissions" => "prizes_edit"
		]
		,"audit" => [
			"label" => "Audit"
			,"url" => "/acu/prizes/audit/?id=". $id
			,"permissions" => "prizes_edit"
		]
		,"delete" => [
			"label" => "Delete"
			,"url" => "/acu/prizes/delete/?id=". $id
			,"permissions" => "prizes_edit"
		]
	];

	$output = '
	<div id="navcontainer">
		<ul id="navlist">
	';
	foreach($prizes as $section_name => $row) {
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