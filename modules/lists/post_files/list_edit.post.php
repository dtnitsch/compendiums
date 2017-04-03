<?php
if(!empty($_POST) && !error_message()) {
	library('uuid.php');
	library("slug.php");

	validate([
		'title'=>'List Name'
		,'inputs' =>'Inputs'
	]);

	if(!error_message()) {

		$title = trim($_POST['title']);
		$alias = convert_to_alias($title);
		$key = get_url_param("key");
		$is_table = (strstr($_POST['inputs'],"|") !== false ? 't' : 'f');

		$pub = db_fetch("select id from public.list where key = '". db_prep_sql($key) ."'","Getting id");

		$labels = [];
		$orders = [];
		foreach($_POST['filter_labels'] as $k => $v) {
			if(trim($v) == "") {
				unset($_POST['filter_labels'][$k]);
				unset($_POST['filter_orders'][$k]);
			}
		}
		asort($_POST['filter_orders']);

		$q = "
			update public.list set
				title = '". db_prep_sql($title) ."'
				,alias = '". db_prep_sql($alias) ."'
				,tables = '". $is_table ."'
				,tags = '". db_prep_sql(json_encode(array_keys(unique_tags()))) ."'
				,filter_labels = '". db_prep_sql(json_encode($_POST['filter_labels'])) ."'
				,filter_orders = '". db_prep_sql(json_encode($_POST['filter_orders'])) ."'
				,modified = now()
			where
				id = '". db_prep_sql($pub['id']) ."'
		";

		$res = db_query($q, "Updating List"); 

		if(!error_message()) {
			$list_id = $pub['id'];

			$pieces = explode("\n",$_POST['inputs']);
			$assets = array();
			$percentages = "";
			$map_ids = [];
			$alias_list = [];
			foreach($pieces as $v) {
				$v = trim($v);
				if($v == "") {
					continue;
				}
				
				$inner_pieces = explode(";",$v);
				$asset = trim($inner_pieces[0]);
				# Filters
				$filter_labels = [];
				if(!empty($inner_pieces[1])) {
					$tmp = explode(",",$inner_pieces[1]);
					foreach($tmp as $k => $v) {
						if(trim($k) == "" || trim($v) == "") {
							continue;
						}
						$filter_labels[$k] = convert_to_alias($v);
					}
				}
				$filter_labels = json_encode($filter_labels);

				$alias = convert_to_alias($asset);
				$alias = db_prep_sql($alias);
				$alias_list[$alias] = [
					"asset" => $asset
					,"perc" => $perc
					,"filter_labels" => $filter_labels
				];
			}
			$q = "select id,alias from public.asset where alias in ('". implode("','",array_keys($alias_list)) ."')";
			$res = db_query($q,"Checking existing assets");
			$existing = [];
			while($row = db_fetch_row($res)) {
				$existing[$row['alias']] = $row['id'];
				$alias_list[$row['alias']]['id'] = $row['id'];
			}

			$q = '';
			foreach($alias_list as $k => $v) {
				if(empty($v['id'])) {
					$q .= "
						(
							'". db_prep_sql($v['asset']) ."'
							,'". db_prep_sql(convert_to_alias($v['asset'])) ."'
							,now()
							,now()
					),";
				}
			}

			if(!empty($q)) {
				$q = "
					insert into public.asset (
						title
						,alias
						,created
						,modified
					) values ". substr($q,0,-1) ."
					RETURNING id,title,alias
				";
				$res = db_query($q,"Inserting/Selecting Asset: ". $title);
				while($row = db_fetch_row($res)) {
					$alias_list[$row['alias']]['id'] = $row['id'];
				}
			}
			$id_to_alias = [];
			foreach($alias_list as $k => $v) {
				$id_to_alias[$v['id']] = [$k];
				$map_ids[$v['id']] = "(
					". $list_id ."
					,". $v['id'] ."
					,". db_prep_sql($v['perc']) ."
					,'". db_prep_sql($v['filter_labels']) ."'::json
					,now()
					,now()
				)";				
			}

			// $q = "
			// 	insert into list_asset_map (
			// 		list_id
			// 		,asset_id
			// 		,percentage
			// 		,filters
			// 		,created
			// 		,modified
			// 	) values ". implode(',',$map_ids);
			// $res = db_query($q,"Inserting list asset map");
			// if (db_is_error($res)) {
			// 	error_message("AN error occured trying to insert the new asset.");
			// }

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
			list($remove,$add) = add_remove_diff(array_keys($id_to_alias),$asset_ids);

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
				// $map_ids = [];
				foreach($add as $v) {
					$filter = db_prep_sql($alias_list[$id_to_alias[$v][0]]['filter_labels']);
					$list_asset_map[] = "(". $list_id .",". $v .",'". $filter ."',now(),now())";
				}
				$q = "insert into list_asset_map (list_id,asset_id,filters,created,modified) values ". implode(',',$list_asset_map);
				db_query($q,"Inserting Assets for list");
			}

			// $q = "insert into list_asset_map (list_id,asset_id,created,modified) values ". implode(',',$map_ids);
			// db_query($q,"Inserting list asset map");


			$markdown = trim(strip_tags($_POST['markdown']));
			$q = "
				update public.list_markdown set
					markdown = '". db_prep_sql($markdown) ."'
					,modified = now()
				where
					list_id = '". db_prep_sql($pub['id']) ."'
			";
			$res = db_query($q, "Updating markdown"); 

			if(!error_message()) {
				$redirection_path = '/lists/';
				set_post_message("You have successfully updated a record");
				set_safe_redirect($redirection_path);
			}

			// error_message("An error has occurred while trying to create a new record");
		}
	}
}


function unique_tags() {
	$tags = [];
	$pieces = explode("\n",trim($_POST['inputs']));
	$percentages = 0;
	$output = [];
	for($i=0,$len=count($pieces); $i<$len; $i++) {
		$inner_pieces = explode(";",$pieces[$i]);
		if(!empty($inner_pieces[1])) {
			$tags = explode(',',trim($inner_pieces[1]));
			for($j=0,$lenj=count($tags); $j<$lenj; $j++) {
				$output[convert_to_alias($tags[$j])] = 1;
			}
		} else {
			continue;
		}
	}
	return $output;
}
