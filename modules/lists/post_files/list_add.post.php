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

		$title = trim($_POST['title']);
		$alias = convert_to_slug($title);
		$key = create_key();
		$is_table = (strstr($_POST['inputs'],"|") !== false ? 't' : 'f');
		// $hash = hash('sha256',$title."||".trim($_POST['inputs']));

		$markdown = trim(strip_tags($_POST['markdown']));

		$q = "
			insert into public.list (
				user_id
				,key
				,parent_id
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
				,currval('list_id_seq')
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

			if(!error_message()) {
				$redirection_path = '/lists/';
				set_post_message("You have successfully created a new record");
				set_safe_redirect($redirection_path);
			}
		} 
	}
}
