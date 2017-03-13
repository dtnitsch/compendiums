<?php
if(!empty($_POST) && !error_message()) {
	library('uuid.php');
	library('validation.php');

	$percentages = calc_percentages();

	$json = validation_create_json_string(validation_load_file(__DIR__ ."/../validation.json"),"php");
	if(!empty($percentages)) {
		validation_custom("percentage","validate_percentages","Percentages do not add up to 100");
	}
	validate_from_json($json);
	error_message(get_all_validation_errors());

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
			,'". ($percentages == 100 ? "t" : "f") ."'
			,'". db_prep_sql(json_encode(array_keys(unique_tags()))) ."'
			,now()
			,now()
		";

		$q = "
			insert into public.list (
				user_id
				,key
				,title
				,alias
				,percentages
				,tags
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
				$inner_pieces = explode(";",$v);
				$asset = trim($inner_pieces[0]);
				# Percentages
				$perc = (!empty($inner_pieces[1]) ? (int)$inner_pieces[1] : 0);
				$tags = [];
				if(!empty($inner_pieces[2])) {
					$tmp = explode(",",$inner_pieces[2]);
					foreach($tmp as $k => $v) {
						$tags[$k] = strtolower(trim($v));
					}
				}
				$tags = json_encode($tmp);

				$alias = convert_to_alias($asset);
				$q = "
					insert into public.asset (
						title
						,alias
						,created
						,modified
					) values (
						'". db_prep_sql($asset) ."'
						,'". db_prep_sql($alias) ."'
						,now()
						,now()
					)
					on conflict (alias)
					do nothing;
					select * from public.asset where alias='". db_prep_sql($alias) ."';
				";
				$res = db_fetch($q,"Inserting/Selecting Asset: ". $title);

				$assets[$alias]['title'] = $asset;
				$assets[$alias]['id'] = $res['id'];
				$assets[$alias]['perc'] = $perc;
				$map_ids[] = "(
					". $list_id ."
					,". $res['id'] ."
					,". db_prep_sql($perc) ."
					,'". db_prep_sql($tags) ."'::json
					,now()
					,now()
				)";
			}

			$q = "
				insert into list_asset_map (
					list_id
					,asset_id
					,percentage
					,tags
					,created
					,modified
				) values ". implode(',',$map_ids);
			db_query($q,"Inserting list asset map");
		

			$redirection_path = '/lists/add/?id='. $new_id;
			set_post_message("You have successfully created a new record");
			set_safe_redirect($redirection_path);

			// error_message("An error has occurred while trying to create a new record");
		}
	}
}
function calc_percentages() {
	$pieces = explode("\n",trim($_POST['inputs']));
	$percentages = 0;
	for($i=0,$len=count($pieces); $i<$len; $i++) {
		$inner_pieces = explode(";",$pieces[$i]);
		if(!empty((int)$inner_pieces[1])) {
			$perc = (int)$inner_pieces[1];
			if($perc) {
				$percentages += $perc;
			}			
		} else {
			return 0;
		}
	}
	return $percentages;
}
function validate_percentages() {
	$input = calc_percentages();
	return ($input == 100 ? true : false);
}

function unique_tags() {
	$tags = [];
	$pieces = explode("\n",trim($_POST['inputs']));
	$percentages = 0;
	$output = [];
	for($i=0,$len=count($pieces); $i<$len; $i++) {
		$inner_pieces = explode(";",$pieces[$i]);
		if(!empty($inner_pieces[2])) {
			$tags = explode(',',trim($inner_pieces[2]));
			for($j=0,$lenj=count($tags); $j<$lenj; $j++) {
				$output[trim($tags[$j])] = 1;
			}
		} else {
			return true;
		}
	}
	return $output;
}