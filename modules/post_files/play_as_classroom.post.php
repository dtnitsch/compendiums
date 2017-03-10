<?php

if(!empty($_POST['act']) && $_POST['act'] == "playasaclassroom") {
	$grade = (!empty($_POST['grade']) ? $_POST['grade'] : 4);
	if(!in_array($grade,array('K','1','2','3','4','5','6','7','8'))) {
		$grade = 4;
	}

	$_SESSION['playasaclassroom'] = 1;
	$_SESSION['cckcidgrade'] = $grade;
	if ($grade == 'K' || $grade == 'k'){
		$grade = 1;
	}
	$_SESSION['student_grade'] = $grade;
	$_SESSION['user']['student_grade'] = $grade;
}