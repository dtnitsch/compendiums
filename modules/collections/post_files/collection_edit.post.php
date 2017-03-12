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

		$pub = db_fetch("select id from public.collection where key = '". db_prep_sql($key) ."'","Getting id");

		$q = "
			update public.collection set
				title = '". db_prep_sql($title) ."'
				,alias = '". db_prep_sql($alias) ."'
				,modified = now()
			where
				key = '". db_prep_sql($key) ."'
		";
		$res = db_query($q, "Updating Collection"); 
		
		if(!error_message()) {
			$collection_id = $pub['id'];

			
			$keys = "'". implode("','",$_POST['list_keys']) ."'";
			$q = "select id,key from public.list where key in (". $keys .")";
			$res = db_query($q, "Getting new list of ID's");
			$new_list_ids = [];
			while($row = db_fetch_row($res)) {
				$new_list_ids[$row['key']] = $row['id'];
			}


			$q = "
				select
					collection_list_map.*
					,list.key
				from public.collection_list_map
				join public.list on
					list.id = collection_list_map.list_id
				where collection_list_map.collection_id = '". $collection_id ."'
				order by
					list_id
			";
			$res = db_query($q,"Getting collection list map list ids");
			$existing_list_ids = [];
			$existing_list = [];
			while($row = db_fetch_row($res)) {
				$existing_list_ids[$row['key']] = $row['list_id'];
				$existing_list[$row['key']] = $row;
				$existing_list[$row['key']]['randomize'] = ($row['randomize'] == 't' ? 1 : 0);
			}

			library('add_remove_diff.php');
			list($remove,$add) = add_remove_diff($new_list_ids,$existing_list_ids);


			# Check for updates
			$keys_ids_map = array_flip($_POST['list_keys']);
			foreach($existing_list_ids as $k => $v) {
				# not new and not in the list of removes
				if(empty($remove[$k])) {
					// existing_list
					$index = $keys_ids_map[$k];
					$updates = [];
					if($_POST['list_labels'][$index] != $existing_list[$k]['label']) {
						$updates[] = " label = '". db_prep_sql($_POST['list_labels'][$index]) ."' ";
					}
					if($_POST['randomize'][$index] != $existing_list[$k]['randomize']) {
						$tmp = ($_POST['randomize'][$index] == 1 ? "t" : "f");
						$updates[] = " randomize = '". db_prep_sql($tmp) ."' ";
					}
					if($_POST['display_limit'][$index] != $existing_list[$k]['display_limit']) {
						$updates[] = " display_limit = '". db_prep_sql($_POST['display_limit'][$index]) ."' ";
					}

					if(count($updates)) {
						# Changes need to be made
						$q = "
							update public.collection_list_map set
								". implode(', ',$updates) ."
								,modified = now()
							where
								id = '". $existing_list[$k]['id'] ."'
						";
						db_query($q,"Updating collection list");
					}
				}
			}

			if(!empty($remove)) {
				$q = "
					delete from public.collection_list_map
					where
						collection_list_map.collection_id = '". $collection_id ."'
						and list_id in (". implode(',',$remove) .")
				";
				db_query($q,"Removing Assets from collection");				
			}

			if(!empty($add)) {
				$map_ids = [];
				foreach($add as $k => $v) {
					$index = $keys_ids_map[$k];
					$tmp = ($_POST['randomize'][$index] == 1 ? "t" : "f");
					$map_ids[] = "(
						'". $collection_id ."'
						,'". $v ."'
						,'". db_prep_sql(trim($_POST['list_labels'][$index])) ."'
						,'". db_prep_sql($tmp) ."'
						,'". db_prep_sql(trim($_POST['display_limit'][$index])) ."'
						,now()
						,now())";
				}
				$q = "insert into collection_list_map (
						collection_id
						,list_id
						,label
						,randomize
						,display_limit
						,created
						,modified
					) values ". implode(',',$map_ids);
				db_query($q,"Inserting Assets for collection");
			}

			// $q = "insert into collection_list_map (collection_id,asset_id,created,modified) values ". implode(',',$map_ids);
			// db_query($q,"Inserting collection asset map");
		

			$redirection_path = '/collections/edit/?key='. $key;
			set_post_message("You have successfully updated a record");
			set_safe_redirect($redirection_path);

			// error_message("An error has occurred while trying to create a new record");
		}
	}
}
