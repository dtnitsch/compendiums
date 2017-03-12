<?php

function collection_navigation($id,$section) {
	$section = trim($section);
	$section = strtolower($section);

	add_css('tabs.css');

	// Permission Checks
	$security_check_collection = ['collections_edit','collections_audit','collections_delete'];
	$security_collection = has_access(implode(",",$security_check_collection)); 

	$collections = [
		"edit" => [
			"label" => "Edit"
			,"url" => "/acu/collections/edit/?id=". $id
			,"permissions" => "collections_edit"
		]
		,"audit" => [
			"label" => "Audit"
			,"url" => "/acu/collections/audit/?id=". $id
			,"permissions" => "collections_edit"
		]
		,"delete" => [
			"label" => "Delete"
			,"url" => "/acu/collections/delete/?id=". $id
			,"permissions" => "collections_edit"
		]
	];

	$output = '
	<div id="navcontainer">
		<ul id="navcollection">
	';
	foreach($collections as $section_name => $row) {
		if(empty($security_collection[$row['permissions']])) { continue; }
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