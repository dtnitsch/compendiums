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
			insert into public.list (
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
		$res = db_fetch($q, "Inserting List Name"); 
		if(empty($res['id'])) {
			error_message("List insert failed");
		}
		
		if(!error_message()) {
			$list_id = $res['id'];

			$pieces = explode("\n",$_POST['inputs']);
			$assets = array();
			$percentages = "";
			$map_ids = [];
			foreach($pieces as $v) {
				$v = trim($v);
				$inner_pieces = explode(",",$v);
				# Percentages
				if($percentages == "") {
					$len = count($inner_pieces);
					if($len > 1 && is_numeric($inner_pieces[$len - 1])) {
						$percentages = 1;
					}
				}
				$perc = ($percentages ? array_pop($inner_pieces) : 0);
				$v = implode(',',$inner_pieces);

				$alias = convert_to_alias($v);
				$q = "
					insert into public.asset (
						title
						,alias
						,created
						,modified
					) values (
						'". db_prep_sql($v) ."'
						,'". db_prep_sql($alias) ."'
						,now()
						,now()
					)
					on conflict (alias)
					do nothing;
					select * from public.asset where alias='". db_prep_sql($alias) ."';
				";
				$res = db_fetch($q,"Inserting/Selecting Asset: ". $title);

				$assets[$alias]['title'] = $v;
				$assets[$alias]['id'] = $res['id'];
				$assets[$alias]['perc'] = $perc;
				$map_ids[] = "(". $list_id .",". $res['id'] .",now(),now())";
			}

			$q = "insert into list_asset_map (list_id,asset_id,created,modified) values ". implode(',',$map_ids);
			db_query($q,"Inserting list asset map");
		

			$redirection_path = '/lists/add/?id='. $new_id;
			set_post_message("You have successfully created a new record");
			set_safe_redirect($redirection_path);

			// error_message("An error has occurred while trying to create a new record");
		}
	}
}