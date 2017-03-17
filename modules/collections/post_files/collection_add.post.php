<?php
if(!empty($_POST) && !error_message()) {
	library('uuid.php');
	// library('validation.php');

	// $_POST["id"] = true;
	// $json = validation_create_json_string(validation_load_file(__DIR__ ."/../validation.json"),"php");
	// validate_from_json($json);
	// error_message(get_all_validation_errors());

	if(!error_message()) {


		library("slug.php");
		$title = trim($_POST['title']);
		$alias = convert_to_alias($title);
		$key = create_key();

		$q = "
			'2'
			,'". $key ."'
			,'". db_prep_sql($title) ."'
			,'". db_prep_sql($alias) ."'
			,now()
			,now()
		";

		$q = "
			insert into public.collection (
				user_id
				,key
				,title
				,alias
				,created
				,modified
			) values (
			". $q ."
			) returning id
		";
		$res = db_fetch($q, "Inserting Collection Name"); 
		if(empty($res['id'])) {
			error_message("Collection insert failed");
		}
		
		if(!error_message()) {
			$collection_id = $res['id'];

			$q = "";
			foreach($_POST['list_keys'] as $k => $v) {
				$v = trim($v);
				if(!empty($v)) {
					$q .= "'". db_prep_sql($v) ."',";
				}
			}
			$q = "select id,key from public.list where key in (". substr($q,0,-1) .")";
			$res = db_query($q,"Getting list_ids");
			
			// $key_id_map = array_flip($_POST['list_keys']);
			$q = "";
			$key_index_map = [];
			while($row = db_fetch_row($res)) {
				$key_index_map[$row['key']] = $row['id'];
			}

			$key_id_map = array_flip($_POST['list_keys']);
			foreach($_POST['list_keys'] as $index => $key) {
				$q .= "(
					'". db_prep_sql($collection_id) ."'
					,'". db_prep_sql($key_index_map[$key]) ."'
					,'". db_prep_sql(trim($_POST['list_labels'][$index])) ."'
					,'". (int)$_POST['randomize'][$index] ."'
					,'". (int)$_POST['display_limit'][$index] ."'
					,now()
					,now()
				),";
			}
			if(!empty($q)) {
				$q = "
					insert into collection_list_map (
						collection_id
						,list_id
						,label
						,randomize
						,display_limit
						,created
						,modified
					) VALUES 
				". substr($q,0,-1);
				db_query($q,"Inserting collection_list_maps");
			}
		

			// $redirection_path = '/collections/add/?id='. $new_id;
			// set_post_message("You have successfully created a new record");
			// set_safe_redirect($redirection_path);

			// error_message("An error has occurred while trying to create a new record");
		}
	}
}