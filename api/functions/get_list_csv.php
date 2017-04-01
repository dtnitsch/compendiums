<?php

function get_list_csv($key) {
	
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
			asset.title
	";
	$res = db_query($q,"Getting Assets");

	$filename = $list['alias'] ."-". date('Y-m-d-H:i:s',strtotime($list['modified'])) .".csv";
	$cnt = 1;

	header('Content-Disposition: attachment; filename="'. $filename .'"');
	header('Content-Type: text/csv');

	$fp = fopen('php://output', 'w');

	// display field/column names as first row
	fputcsv($fp, ["Index",'Title','Slug','Created','Modified','Filters'], ',', '"');

	while($row = db_fetch_row($res)) {
		fputcsv($fp, [
			$cnt++
			,$row['title']
			,$row['alias']
			,$row['created']
			,$row['modified']
			,$row['filters']
		], ',', '"');
	}


	// $initial_columns = array_shift($data);
	// asort($data);

	// // display field/column names as first row
	// fputcsv($fp, array_values($initial_columns), ',', '"');

	// foreach($data as $row) {

	// 	$output = array();
	// 	foreach($columns as $key => $val) {
	// 		$tmp = trim(isset($row[$key]) ? $row[$key] : '');
	// 		if(is_datetime($tmp) && (strstr($key,'date') || strstr($key,'time'))) { 
	// 			$tmp = date('m/d/Y H:i:s',$tmp);
	// 		}
	// 		if(strstr($key,'average_score')){
	// 			$tmp = trim(isset($row[$key]) ? number_format($row[$key], 0) : '');
	// 		}
	// 		if(strstr($key,'participating_percent')){
	// 			$tmp = trim(isset($row[$key]) ? number_format($row[$key], 2) : '');
	// 		}
	// 		if(strstr($key,'registered_percent')){
	// 			$tmp = trim(isset($row[$key]) ? number_format($row[$key], 2) : '');
	// 		}
	// 		$output[] = clean_csv_data($tmp);
	// 	}

	// 	fputcsv($fp, $output, ',', '"');
	// }

	fclose($fp);
}