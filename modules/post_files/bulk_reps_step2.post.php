<?php
if (!empty($_POST)) {

	$q = "
		select
			user_id
			,institution_id
			,grade_id
		from system.students
		where
			id = ".db_prep_sql((int) $_POST['siu_student_id'])."
	";

	$student_info = db_fetch($q, "Getting student info");

	$grade_id = (empty($student_info['grade_id']) ? 8 : $student_info['grade_id']);

	$q = "
		select
			step_it_up_id
			,points
		from \"activities\".\"step_it_up_points\"
		where
			active
			and grade_id = ".db_prep_sql((int) $grade_id)."
	";

	$res = db_query($q, "Getting point info");

	$points = array();

	while ($row = db_fetch_row($res)) {
		$points[$row["step_it_up_id"]] = $row["points"];
	}

	if (!empty($_POST["siu"])) {

		$q = "
			delete
			from public.activity_scores
			where
				reps > 0
				and student_id = '". db_prep_sql($_POST['siu_student_id']) ."'
				and institution_id = '". $student_info['institution_id'] ."'
				and created >= '". date('Y-m-d 00:00:00', $_POST['start_date']) ."' and created <= '". date('Y-m-d 23:59:59', $_POST['end_date']) ."'
		";

		db_query($q, "Delete old records");

		$insert_q = "";

		foreach($_POST['siu'] as $content_id => $r1) {

			foreach($r1 as $date => $r2) {

				foreach($r2 as $map_id => $value) {

					if (!empty($content_id) && !empty($date) && !empty($map_id) && !empty($value)) {
						$score = 0;

						if (!empty($points[$content_id])) {
							$score = $points[$content_id] * $value;
						}

						$insert_q .= "(
							'". $value ."'
							,'". $map_id ."'
							,'". db_prep_sql($_POST['siu_student_id']) ."'
							,'". $student_info['user_id'] ."'
							,'". $student_info['institution_id'] ."'
							,'". $score ."'
							,'". $score ."'
							,'3'
							,'1'
							,'". date('Y-m-d 00:00:00',$date) ."'
						),";


					} else if(!empty($content_id) && !empty($date) && empty($map_id) && !empty($value)) {
						// New map id needs to be generated
						// $q = "
						// 	insert into public.activity_map (
						// 		activity_id
						// 		,grade_id
						// 		,content_id
						// 	)
						// ";
					}
				}
			}
		}

		if(!empty($insert_q)) {
			$q = "
				insert into activity_scores (reps,activity_map_id,student_id,user_id,institution_id,original_score,calculated_score,score_status_id,multiplier,created) values
				". substr($insert_q,0,-1) ."
			";
			db_query($q,"Inserting activity_scores");
		}

		$GLOBALS['siu_success_message'] = "Scores have been successfully updated.";

	}

	// safe_redirect('/bulk_reps_step2/');
}