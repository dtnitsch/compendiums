<?php

if(!empty($_POST) && !error_message()) {

	if(strtolower($_POST['type']) == 'new') {
		add_new_student();
	} else if(strtolower($_POST['type']) == 'update') {
		update_students();
	}

}

function add_new_student() {

	$gender = 0;

	if (!empty($_POST["childgender0"])) {

		$gender = strtolower($_POST["childgender0"]);

		if ($gender == "boy") {
			$gender = 1;
		} else if($gender == "girl") {
			$gender = 2;
		}

	}

	$q = "
		insert into system.students (
			ethicspledge
			,gender_id
			,user_id
			,institution_id
			,grade_id
			,firstname
			,created
			,modified
		) values (
			'f'
			,".db_prep_sql((int) $gender)."
			,".db_prep_sql((int) $_POST["user_id"])."
			,".db_prep_sql((int) $_POST["childschoolid0"])."
			,".db_prep_sql((int) $_POST["childgrade0"])."
			,'".db_prep_sql($_POST["childfirstname0"])."'
			,now()
			,now()
		)
	";

	$res = db_query($q, "Insert new student");

	if (db_affected_rows($res)) {
		set_safe_redirect("/page/Add_Kids/34/17/");
	}

}

function update_students() {

	$students = array();
	foreach($_POST as $k => $v) {
		if(strpos($k,'childid') !== false) {
			$id = str_replace('childid','',$k);
			$student_id = $_POST['childid'.$id];
			$students[$student_id] = array(
				'id' => $_POST['childid'.$id]
				,'grade' => $_POST['childgrade'.$id]
				,'gender' => $_POST['childgender'.$id]
				,'school' => $_POST['childschoolid'.$id]
				,'firstname' => $_POST['childfirstname'.$id]
			);
		}
	}

	$q = "select id,gender_id,institution_id,grade_id,firstname from system.students where id in (". implode(',',array_keys($students)) .")";
	$res = db_query($q,"Getting students");

	$updates = array();
	while($row = db_fetch_row($res)) {

		$gender = 0;
		if(!empty($students[$row['id']]['gender'])) {
			$gender = strtolower($students[$row['id']]['gender']);
			if($gender == 'boy') {
				$gender = 1;
			} else if($gender == 'girl') {
				$gender = 2;
			}
		}

		$grade = 0;
		if(!empty($students[$row['id']]['grade'])) {
			$grade = strtolower($students[$row['id']]['grade']);
			if($grade == 'k') {
				$grade = 16;
			} else if($grade == 'other') {
				$grade = 18;
			}
		}

		if(
			$row['gender_id'] != $gender
			|| $row['grade_id'] != $grade
			|| $row['institution_id'] != $students[$row['id']]['school']
			|| $row['firstname'] != $students[$row['id']]['firstname']
		) {
			$updates[$row['id']] = $students[$row['id']];
			$updates[$row['id']]['grade_id'] = $grade;
			$updates[$row['id']]['gender_id'] = $gender;
		}
	}


	if(!empty($updates)) {
		$_SESSION['update_messages'] = array();
		foreach($updates as $row) {
			$q = "
				update system.students set
					gender_id = '". $row['gender_id'] ."'
					,institution_id = '". $row['school'] ."'
					,grade_id = '". $row['grade_id'] ."'
					,firstname = '". $row['firstname'] ."'
					,modified = now()
				where
					id = '". $row['id'] ."'
			";
			db_query($q,"updating student: ". $row['id']);
			$_SESSION['update_messages'][] = $row['firstname'] ." updated";
		}
	}

	set_safe_redirect('/page/Add_Kids/34/17/');
}