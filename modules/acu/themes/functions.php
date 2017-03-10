<?php

function themes_navigation($id,$section) {
	$section = trim($section);
	$section = strtolower($section);

	add_css('tabs.css');

	$themes = [
		"edit" => [
			"label" => "Edit"
			,"url" => "/acu/themes/edit/?id=". $id
		]
		,"audit" => [
			"label" => "Audit"
			,"url" => "/acu/themes/audit/?id=". $id
		]
		,"delete" => [
			"label" => "Delete"
			,"url" => "/acu/themes/delete/?id=". $id
		]
	];

	$output = '
	<div id="navcontainer">
		<ul id="navlist">
	';
	foreach($themes as $section_name => $row) {
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