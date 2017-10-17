<?php

function unique_tags() {
	$tags = [];
	$pieces = explode("\n",trim($_POST['inputs']));
	$output = [];
	for($i=0,$len=count($pieces); $i<$len; $i++) {
		$inner_pieces = explode(";",$pieces[$i]);
		if(!empty($inner_pieces[1])) {
			$tags = explode(',',trim($inner_pieces[1]));
			for($j=0,$lenj=count($tags); $j<$lenj; $j++) {
				$output[convert_to_slug($tags[$j])] = 1;
			}
		} else {
			continue;
		}
	}
	return $output;
}

function clean_assets($inputs) {
	$pieces = explode("\n",$inputs);
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
			foreach($tmp as $v) {
				if(trim($v) == "") {
					continue;
				}
				$slug = convert_to_slug($v);
				$filter_labels[$slug] = 1;
			}
		}
		$filter_labels = json_encode(array_keys($filter_labels));

		$alias = convert_to_alias($asset);
		$alias = db_prep_sql($alias);
		$alias_list[$alias] = [
			"asset" => $asset
			,"filter_labels" => $filter_labels
		];
	}
	return $alias_list;
}

function get_existing_assets($alias_list) {
	$q = "
		select id,alias
		from public.asset
		where
			alias in ('". implode("','",array_keys($alias_list)) ."')
	";
	$res = db_query($q,"Checking existing assets");
	$existing = [];
	while($row = db_fetch_row($res)) {
		$existing[$row['alias']] = $row['id'];
		$alias_list[$row['alias']]['id'] = $row['id'];
	}
	return [$alias_list,$existing];
}

function add_new_assets($alias_list,$existing) {
	$q = '';
	foreach($alias_list as $k => $v) {
		if(empty($existing[$k])) {
			$q .= "
				(
					'". db_prep_sql($v['asset']) ."'
					,'". db_prep_sql($k) ."'
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
	// die();
		$res = db_query($q,"Inserting/Selecting Asset");
		while($row = db_fetch_row($res)) {
			$alias_list[$row['alias']]['id'] = $row['id'];
		}
	}
	return $alias_list;
}


function insert_asset_map($list_id, $alias_list) {
	$map_ids = [];
	foreach($alias_list as $k => $v) {
		if(!empty($v['id'])) {
			$map_ids[] = "(
				". $list_id ."
				,". $v['id'] ."
				,'". db_prep_sql($v['filter_labels']) ."'::json
				,now()
			)";
		}
	}

	if(!empty($map_ids)) {
		$q = "
			insert into list_asset_map (
				list_id
				,asset_id
				,filters
				,created
			) values ". implode(',',$map_ids);
		$res = db_query($q,"Inserting list asset map");
		if (db_is_error($res)) {
			error_message("AN error occured trying to insert the new asset.");
		}
	}
}
