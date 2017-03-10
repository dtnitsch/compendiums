<?php
if(!empty($_POST) && !error_message()) {
	library('uuid.php');

	// $_POST["id"] = true;
	// $json = validation_create_json_string(validation_load_file(__DIR__ ."/../validation.json"),"php");
	// validate_from_json($json);
	// error_message(get_all_validation_errors());

	if(!error_message()) {



		library("slug.php");
		$title = trim($_POST['title']);
		$alias = convert_to_alias($title);
		$key = get_url_param("key");

		$pub = db_fetch("select id from public.list where key = '". db_prep_sql($key) ."'","Getting id");

		$q = "
			update public.list set
				title = '". db_prep_sql($title) ."'
				,alias = '". db_prep_sql($alias) ."'
				,modified = now()
			where
				key = '". db_prep_sql($key) ."'
		";
		$res = db_query($q, "Updating List"); 
		
		if(!error_message()) {
			$list_id = $pub['id'];

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
				$map_ids[] = $res['id'];
			}

			$q = "
				select public.list_asset_map.asset_id as id
				from public.list_asset_map
				where list_asset_map.list_id = '". $list_id ."'
				order by asset_id
			";
			$res = db_query($q,"Getting list assets");
			$asset_ids = [];
			while($row = db_fetch_row($res)) {
				$asset_ids[] = $row['id'];
			}

			library('add_remove_diff.php');
			list($remove,$add) = add_remove_diff($map_ids,$asset_ids);


			if(!empty($remove)) {
				$q = "
					delete from public.list_asset_map
					where
						list_asset_map.list_id = '". $list_id ."'
						and asset_id in (". implode(',',$remove) .")
				";
				db_query($q,"Removing Assets from list");				
			}

			if(!empty($add)) {
				$map_ids = [];
				foreach($add as $v) {
					$map_ids[] = "(". $list_id .",". $v .",now(),now())";
				}
				$q = "insert into list_asset_map (list_id,asset_id,created,modified) values ". implode(',',$map_ids);
				db_query($q,"Inserting Assets for list");
			}

			// $q = "insert into list_asset_map (list_id,asset_id,created,modified) values ". implode(',',$map_ids);
			// db_query($q,"Inserting list asset map");
		

			$redirection_path = '/lists/edit/?key='. $key;
			set_post_message("You have successfully updated a record");
			set_safe_redirect($redirection_path);

			// error_message("An error has occurred while trying to create a new record");
		}
	}
}
