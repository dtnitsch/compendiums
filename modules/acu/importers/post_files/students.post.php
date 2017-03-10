<?php

ini_set('auto_detect_line_endings',TRUE);

if(!empty($_POST) && !error_message()) {

	if(empty($_FILES["file"]["tmp_name"])) {
		error_message("File could not be successfully uploaded");
	}

	if(!error_message() && ($fp = fopen($_FILES["file"]["tmp_name"],'r')) === false) {
		error_message("Unable to open file, contact your Site Administrator.");
	}

	// Validate the file first
	if (!error_message()) {

		$line = 0;
		$import_errrors = array();

		while (($data = fgetcsv($fp, 4096, ",")) !== FALSE) {

			$line++;

			$errors = array();

			$data[0] = trim(strtolower($data[0]));
			$data[1] = trim($data[1]);
			$data[2] = trim(strtolower($data[2]));
			$data[3] = trim(strtolower($data[3]));
			$data[4] = (int) $data[4];

			if (empty($data[0])) {
				$errors[] = "An email address is required";
			}

			if (empty($data[1])) {
				$errors[] = "An students first name is required";
			}

			if (empty($data[2])) {
				$errors[] = "An gender is required";
			}

			if ($data[2] != 'boy' && $data[2] != 'girl' && $data[2] != 'none') {
				if($data[2] != '') {
					$errors[] = "'". $data[2] ."' is not a valid gender";
				}
			}

			if (empty($data[3])) {
				$errors[] = "A grade is required";
			}

			if ($data[3] != 'k' && $data[3] != 'prek' && $data[3] != 'other' && ($data[3] < 1 || $data[3] > 8)) {
				if ($data[3] != 0)  {
					$errors[] = "'". $data[3] ."' is not a valid grade";
				}
			}


			if (empty($data[4])) {
				$errors[] = "An school id is required";
			}

			if (!empty($errors)) {
				$import_errors[$line] = $errors;
			}

		}

		if (!empty($import_errors)) {
			// $GLOBALS['import_errors'] = $import_errors;
			$str = "";
			foreach($import_errors as $line => $r1) {
				$str .= "<p>Line $line:<br>";
				foreach($r1 as $row) {
					$str .= "$row<br>";
				}
				$str .= "</p>";
			}
			error_message("Errors were found in the import file: <br><br>". $str);
			unset($str);
			unset($import_errors);
		}
	}

	// Move files to import students
	if(!error_message()) {

		db_query("truncate data_imports.students");

		$conn = $GLOBALS['db_options']['connection_string']['default'];
		$filename = $_FILES["file"]["tmp_name"];

		db_query("copy data_imports.students (user_email,firstname,gender,grade,institution_id) from stdin DELIMITER ',' QUOTE '\"' CSV","Loading: ". $filename);

		$handle = @fopen($filename, "r");
		if ($handle) {
			$error = false;
		    while (($data = fgets($handle, 4096)) !== false) {
				if(!pg_put_line($conn, $data)) {
					$error = true;
					break;
				}
		    }
		}

		if(empty($error)) {
			pg_end_copy($conn);
		} else {
			error_message("An error occurred trying to copy records to the database");
		}

	}


	// Create new students from the imports table
	if(!error_message()) {
		// --
		$q = "
			insert into system.students (
				ethicspledge
				,gender_id
				,user_id
				,institution_id
				,grade_id
				,firstname
				,batch_created
			)
			select
				'f'
				,(case when lower(s.gender) = 'boy' then 1 when lower(s.gender) = 'girl' then 2 else 0 end) as gender_id
			    ,max(u.id) as user_id
			    ,s.institution_id
				,g.id
			    ,s.firstname
			    ,now()
			from data_imports.students as s
			join system.users as u on
				lower(u.email) = lower(s.user_email)
			left join public.grades as g on
				g.alias = lower(s.grade)
			group by
				s.institution_id
			    ,s.firstname
				,g.id
				,s.gender
		";
		$res = db_query($q,'Inserting New Students');
		if(db_is_error($res)) {
			error_message("Error creating new student records");
		}
	}

	// Create new students from the imports table
	if(!error_message()) {
		set_post_message("The import was successful!");
		set_safe_redirect("/acu/importers/students/");
	}
}