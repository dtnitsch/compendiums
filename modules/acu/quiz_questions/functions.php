<?php

function quiz_questions_navigation($id,$section) {
	$section = trim($section);
	$section = strtolower($section);

	add_css('tabs.css');

	// Permission Checks
	$security_check_list = ['quiz_questions_edit','quiz_questions_audit','quiz_questions_delete'];
	$security_list = has_access(implode(",",$security_check_list)); 

	$paths = [
		"edit" => [
			"label" => "Edit"
			,"url" => "/acu/quiz-questions/edit/?id=". $id
			,"permissions" => "quiz_questions_edit"
		]
		,"audit" => [
			"label" => "Audit"
			,"url" => "/acu/quiz-questions/audit/?id=". $id
			,"permissions" => "quiz_questions_audit"
		]
		,"delete" => [
			"label" => "Delete"
			,"url" => "/acu/quiz-questions/delete/?id=". $id
			,"permissions" => "quiz_questions_delete"
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
