<?php

if (!empty($_POST) && !error_message()) {

	// library('validation.php');

	// Cleanup the answers
	$answers = array();

	foreach ($_POST["answers"] as $k => $v) {

		if ($k == "new") {

			foreach ($v as $key => $row) {

				if (!empty($row)) {

					$row = trim($row["answer"]);
					$answers["new_".$key] = $row;

				}

			}

		} else {

			$v = trim($v["answer"]);
			$answers[$k] = $v;

		}

	}

	// check if there are any answers after cleanup
	if (empty($answers)) {
		error_message("At least one answer is required.");
	}

	$time = date("Y-m-d H:i:s");

	// check the error messages array - if no values are passed, return a count
	if (!error_message()) {

		////////////////////////
		// START ADD QUESTION //
		////////////////////////
		$q = "
			insert into \"activities\".\"quiz_questions\" (
				display
				,question
				,number
				,points
				,description
				,created
				,modified
			)
			values (
				'f'
				,'".db_prep_sql(trim($_POST["question"]))."'
				,".db_prep_sql((int) $_POST["number"])."
				,".db_prep_sql((int) $_POST["points"])."
				,'".db_prep_sql(trim($_POST["description"]))."'
				,'".$time."'
				,'".$time."'
			) returning id
		";

		// Use postgresql's "returning id" feature to return the newly inserted id
		$tmp = db_fetch($q, "Inserting New Quiz Question");
		$question_id = $tmp["id"];

		if (empty($question_id)) { error_message("An error occured while adding a new question."); }

		///////////////////////
		// START ADD ANSWERS //
		///////////////////////
		$series = 1;

		if (!empty($answers)) {

			$is_error = false;

			$q = array();

			foreach ($answers as $k => $v) {

				$q = "
					insert into activities.quiz_question_answers (
						quiz_question_id
						,series
						,is_correct
						,language_id
						,answer
					) values (
						".db_prep_sql((int) $question_id)."
						,".$series++."
						,'".($_POST["is_correct"] == $k ? 't' : 'f')."'
						,1
						,'".db_prep_sql(trim($v))."'
					) returning id
				";

				$tmp = db_fetch($q, "Inserting new quiz question answer");
				$answer_id = $tmp["id"];

				$old_k = (int) str_replace("new_", "", $k);

				$answer_voiceover_upload_id = $_POST["media"]["answer_voiceover"]["upload_id"][$old_k];
				unset($_POST["media"]["answer_voiceover"]["upload_id"][$old_k]);
				$_POST["media"]["answer_voiceover"]["upload_id"][$answer_id] = $answer_voiceover_upload_id;

				$answer_media_upload_id = $_POST["media"]["answer_media"]["upload_id"][$old_k];
				unset($_POST["media"]["answer_media"]["upload_id"][$old_k]);
				$_POST["media"]["answer_media"]["upload_id"][$answer_id] = $answer_media_upload_id;

				if (empty($answer_id)) { error_message("An error occured while adding a new answer."); }

			}

		}

		////////////////////
		// START ADD FACT //
		////////////////////
		if (!error_message()) {

			// FINISH INSERT FACT QUERY //
			$q = "
				insert into \"public\".\"facts\" (
					number
					,publish_date
					,hash
					,fact
					,created
					,modified
				)
				values (
					".db_prep_sql((int) $_POST["number"])."
					,'".$time."'
					,'".db_prep_sql(md5(trim($_POST["fact"])))."'
					,'".db_prep_sql(trim($_POST["fact"]))."'
					,'".$time."'
					,'".$time."'
				) returning id
			";

			$tmp = db_fetch($q, "Inserting New Quiz Fact");
			$fact_id = $tmp["id"];

			$fact_voiceover_upload_id = $_POST["media"]["fact_voiceover"]["upload_id"][1];
			unset($_POST["media"]["fact_voiceover"]["upload_id"][1]);
			$_POST["media"]["fact_voiceover"]["upload_id"][$fact_id] = $fact_voiceover_upload_id;

			$fact_media_upload_id = $_POST["media"]["fact_media"]["upload_id"][1];
			unset($_POST["media"]["fact_media"]["upload_id"][1]);
			$_POST["media"]["fact_media"]["upload_id"][$fact_id] = $fact_media_upload_id;

			if (empty($fact_id)) { error_message("An error occured while adding a new fact."); }

			if (!error_message()) {

				$q = "
					insert into \"activities\".\"quiz_question_fact_map\" (
						quiz_question_id
						,fact_id
						,created
						,modified
					)
					values (
						".$question_id."
						,".$fact_id."
						,'".$time."'
						,'".$time."'
					) returning id
				";

			}

			$tmp = db_fetch($q, "Inserting New Fact Map");

			if (empty($tmp["id"])) { error_message("An error occured while adding a new fact map."); }

		}

		////////////////////
		// START ADD GRID //
		////////////////////
		if (!error_message()) {
			// FINISH ADD GRID //

			unset($_POST["grid"]["templateid"]);

			foreach ($_POST["grid"] as $grid) {

				$q = "
					insert into \"activities\".\"quiz_question_map\" (
						quiz_question_id
						,world_id
						,grade_id
						,theme_id
						,generation_id
						,publish_date
						,created
						,modified
					)
					values (
						".$question_id."
						,".db_prep_sql((int) $grid["world_id"])."
						,".db_prep_sql((int) $grid["grade_id"])."
						,".db_prep_sql((int) $grid["theme_id"])."
						,".db_prep_sql((int) $grid["generation_id"])."
						,'".$time."'
						,'".$time."'
						,'".$time."'
					) returning id
				";

				$tmp = db_fetch($q, "Inserting New Quiz Grid");

				if (empty($tmp["id"])) { error_message("An error occured while adding a new quiz grid."); }

			}

		}

		///////////////////
		// MEDIA UPDATES //
		///////////////////
		if (!update_quiz_question_media($question_id, $_POST["media"])) { error_message("An error occured while updating media."); }

		if (!error_message()) {
			// audit('table_update',$table_info);

			$redirection_path = "/acu/quiz-questions/edit/?id=".$question_id;
			set_post_message("The record has been successfully updated");
			set_safe_redirect($redirection_path);

		} else {
			error_message("An error has occurred while trying to update this record");
		}

	}

}