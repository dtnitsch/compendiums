<?php

function compendium_navigation($id,$section) {
	$section = trim($section);
	$section = strtolower($section);

	add_css('tabs.css');

	// Permission Checks
	$security_check_compendium = ['compendiums_edit','compendiums_audit','compendiums_delete'];
	$security_compendium = has_access(implode(",",$security_check_compendium)); 

	$compendiums = [
		"edit" => [
			"label" => "Edit"
			,"url" => "/acu/compendiums/edit/?id=". $id
			,"permissions" => "compendiums_edit"
		]
		,"audit" => [
			"label" => "Audit"
			,"url" => "/acu/compendiums/audit/?id=". $id
			,"permissions" => "compendiums_edit"
		]
		,"delete" => [
			"label" => "Delete"
			,"url" => "/acu/compendiums/delete/?id=". $id
			,"permissions" => "compendiums_edit"
		]
	];

	$output = '
	<div id="navcontainer">
		<ul id="navcompendium">
	';
	foreach($compendiums as $section_name => $row) {
		if(empty($security_compendium[$row['permissions']])) { continue; }
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