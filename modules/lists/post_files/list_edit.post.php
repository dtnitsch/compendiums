<?php
if(!empty($_POST) && !error_message()) {
	library('uuid.php');
	library("slug.php");
	library("assets.php");

	validate([
		'title'=>'List Name'
		,'inputs' =>'Inputs'
	]);

	if(!error_message()) {

		$id = get_url_param("key");
				
		########################################################################################## 
		# DO NOW...
		#	1. Build exact inputs as edit page and compare title + inputs for diffs (updates vs new)
		#	2. Allow updates ONLY to descriptions (markdowns)
		#	3. Update this whole things to create VERSION (insert functions)
		##########################################################################################

		$info = db_fetch("select * from public.list where key='". $id ."'",'Getting List');
		$info['filter_labels'] = json_decode($info['filter_labels'],true);
	
		$q = "
			select
				public.asset.*
				,list_asset_map.filters
			from public.asset
			join public.list_asset_map on 
				list_asset_map.asset_id = asset.id
				and list_asset_map.list_id = '". $info['id'] ."'
			order by
				asset.id
		";
		$assets = db_query($q,"Getting assets");
	
		$asset_body = "";
		while($row = db_fetch_row($assets)) {
			$tmp = json_decode($row['filters'],true);
			$filters = [];
			foreach($tmp as $v) {
				$filters[] = $info['filter_labels'][$v];
			}
			$asset_body .= $row['title'] .";". implode(',',$filters) ."\n";
		}

		if(empty($_POST['markdown'])) {
			$_POST['markdown'] = "";
		}
		$markdown = trim(strip_tags($_POST['markdown']));
		if($markdown != $info['description']) {
			$q = "
				update public.list set
					description = '". db_prep_sql($markdown) ."'				
					,modified = now()
				where
					id = '". $info['id'] ."'
			";
			$res = db_query($q, "Updating List"); 
		}

		if(!error_message()) {
			$continue = false;

			$title = trim($_POST['title']);
			$inputs = preg_replace('/(\\r|\\n)/', "", $_POST['inputs']);
			$asset_body = preg_replace('/(\\r|\\n)/', "", $asset_body);
	
			if($title != $info['title']) {
				$continue = true;
			}
			if($inputs != $asset_body) {
				$continue = true;
			}		

			if($continue) {
					
				$alias = convert_to_slug($title);
				$key = create_key();
				$is_table = (strstr($_POST['inputs'],"|") !== false ? 't' : 'f');
		
				$parent_id = ($info['parent_id'] == 0 ? $info['id'] : $info['parent_id']);
				$q = "
					select (count(id) + 1) as v
					from public.list
					where
						parent_id='". $parent_id ."'
				";
				$version = db_fetch($q, "Inserting List Name");

				$q = "
					update public.list set
						active='f'
					where
						parent_id='". $parent_id ."'
						and active='t'
				";
				db_query($q, "Deactivating all similar parent_id's");

				$q = "
					insert into public.list (
						user_id
						,key
						,parent_id
						,version
						,title
						,alias
						,tables
						,tags
						,filter_labels
						,filter_orders
						,description
						,created
						,modified
					) values (
						'". db_prep_sql(!empty($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 1) ."'
						,'". $key ."'
						,'". $parent_id ."'
						,'". $version['v'] ."'
						,'". db_prep_sql($title) ."'
						,'". db_prep_sql($alias) ."'
						,'". $is_table ."'
						,'{}'
						,'". db_prep_sql(json_encode($_POST['filter_labels'])) ."'
						,'". db_prep_sql(json_encode($_POST['filter_order'])) ."'
						,'". db_prep_sql($markdown) ."'
						,now()
						,now()
					) returning id
				";
				$res = db_fetch($q, "Inserting List Name");
				if(empty($res['id'])) {
					error_message("List insert failed");
				}
		
				if(!error_message()) {
					$list_id = $res['id'];
		
					// Split assets and filters
					$alias_list = clean_assets($_POST['inputs']);
					// Get a list of existing of IDs and assets
					list($alias_list,$existing) = get_existing_assets($alias_list);
					// Add any new assets if any exist
					$alias_list = add_new_assets($alias_list,$existing);
					// Insert into asset map
					insert_asset_map($list_id, $alias_list);
		
				} 
			} # End continue

			if(!error_message()) {
				$redirection_path = '/lists/';
				set_post_message("You have successfully created a new record");
				set_safe_redirect($redirection_path);
			}


		}
	}
}
