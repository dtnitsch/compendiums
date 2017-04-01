<?php

function get_raw_list_csv($key) {
	
	$key = db_prep_sql(trim($key));
	$list = db_fetch("select * from public.list where key='". $key ."'");

	$q = "
		select
		    asset.title
		    ,asset.alias
		    ,asset.created
		    ,asset.modified
		    ,public.list_asset_map.filters
		from public.list_asset_map
		join public.asset on
		    asset.id = list_asset_map.asset_id
		where
		    public.list_asset_map.list_id = '". $list['id'] ."'
		order by
			asset.id
	";
	$res = db_query($q,"Getting Assets");

	$filename = $list['alias'] ."-raw-". date('Y-m-d-H:i:s',strtotime($list['modified'])) .".csv";

	header('Content-Disposition: attachment; filename="'. $filename .'"');
	header('Content-Type: text/csv');

	$fp = fopen('php://output', 'w');

	// display field/column names as first row
	// fputcsv($fp, ["Index",'Title','Slug','Created','Modified','Filters'], ',', '"');

	while($row = db_fetch_row($res)) {
		$filters = implode(',',json_decode($row['filters'],true));
		echo $row['title'] .";;". $filters ."\n";
	}

	fclose($fp);
}