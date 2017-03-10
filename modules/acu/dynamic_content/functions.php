<?php

function dynamic_content_navigation($id,$section) {
	$section = trim($section);
	$section = strtolower($section);

	add_css('tabs.css');

	$paths = [
		"edit" => [
			"label" => "Edit"
			,"url" => "/acu/dynamic-content/edit/?id=". $id
		]
		,"audit" => [
			"label" => "Audit"
			,"url" => "/acu/dynamic-content/audit/?id=". $id
		]
		,"delete" => [
			"label" => "Delete"
			,"url" => "/acu/dynamic-content/delete/?id=". $id
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