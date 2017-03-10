<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);

ob_start();

$student_id = (int)trim($_POST['student_id']);
if(!empty($student_id)) {
	$q = "
		update system.students set
			institution_id = '". db_prep_sql($_POST['school_id']) ."'
		where id = '". db_prep_sql($student_id) ."'
	";
	db_query($q,"Updating student institution");

}

$q = "select title from public.institutions where id = '". db_prep_sql($_POST['school_id']) ."'";
$info = db_fetch($q,"");

echo json_encode(array(
	'success' => true
	,'title' => $info['title']
	,'school_id' => $_POST['school_id']
));


$output = ob_get_clean();
echo json_encode(array("output"=>$output,"debug"=>ajax_debug()));
