<?php
#$session->get_permissions("ADMIN", "MODULES", "MESSAGES", false, true);

// echo "!!<pre>";
// print_r($_POST);
// $data = json_decode($_POST['result_data_simple'],true);
// print_r($data);
// echo "</pre>";
// die();

if(!empty($_POST['query_csv']) && empty($_POST['query'])) {
	$_POST['query'] = $_POST['query_csv'];
}


if(!empty($_POST['type'])) {
	if($_POST['type'] == 'csv_visible' && !empty($_POST['result_data_simple'])) {
		output_csv_from_simple_array($_POST['result_data_simple'],$_POST['column_names'],$_POST['filename']);
	} else if($_POST['type'] == 'csv_all' && !empty($_POST['query_csv'])) {
		output_csv_from_query();
	}
} else if(!empty($_POST['query_csv'])) {
	output_csv_from_query();
} else if(!empty($_POST['result_data'])) {
	output_csv_from_array($_POST['result_data'],$_POST['column_names'],$_POST['column_models'],$_POST['filename']);
} else if(!empty($_POST['result_data_simple'])) {
	output_csv_from_simple_array($_POST['result_data_simple'],$_POST['column_names'],$_POST['filename']);
}

dump_messages();

function output_csv_from_simple_array($data,$column_names='',$filename='') {
	$filename = (!empty($filename) ? $filename : 'csv_export_'.date("Y-m-d_H-i",time()).'.csv');
	if(empty($data)) { die("No results"); }

	$columns = json_decode($column_names,true);

	$data = json_decode($data,true);

	header('Content-Disposition: attachment; filename="'. $filename .'"');
	header('Content-Type: text/csv');

	$fp = fopen('php://output', 'w');

	// display field/column names as first row
	fputcsv($fp, array_values($columns), ',', '"');

	foreach($data as $row) {
		if(empty($row)) { continue; }

		$output = array();
		foreach($row as $val) {
			// $tmp = trim(isset($row[$key]) ? $row[$key] : '');
			// if(is_datetime($tmp) && (strstr($key,'date') || strstr($key,'time'))) { 
			// 	$tmp = date('m/d/Y H:i:s',$tmp);
			// }
			$val = trim(str_replace('&nbsp;','',$val));
			$output[] = clean_csv_data($val);
		}

		fputcsv($fp, $output, ',', '"');
	}

	fclose($fp);
}

function output_csv_from_array($data,$column_names='',$column_models='',$filename='') {

	$filename = (!empty($filename) ? $filename : 'csv_export_'.date("Y-m-d_H-i",time()).'.csv');
	if(empty($data)) { die("No results"); }

	$columns = get_clean_columns($data,$column_names,$column_models);

	$data = json_decode(gzinflate(base64_decode(strtr($data, '-_', '+/'))),true);

	header('Content-Disposition: attachment; filename="'. $filename .'"');
	header('Content-Type: text/csv');

	$fp = fopen('php://output', 'w');

	// display field/column names as first row
	//fputcsv($fp, array_values($columns), ',', '"');

	$initial_columns = array_shift($data);
	asort($data);

	// display field/column names as first row
	fputcsv($fp, array_values($initial_columns), ',', '"');

	foreach($data as $row) {

		$output = array();
		foreach($columns as $key => $val) {
			$tmp = trim(isset($row[$key]) ? $row[$key] : '');
			if(is_datetime($tmp) && (strstr($key,'date') || strstr($key,'time'))) { 
				$tmp = date('m/d/Y H:i:s',$tmp);
			}
			if(strstr($key,'average_score')){
				$tmp = trim(isset($row[$key]) ? number_format($row[$key], 0) : '');
			}
			if(strstr($key,'participating_percent')){
				$tmp = trim(isset($row[$key]) ? number_format($row[$key], 2) : '');
			}
			if(strstr($key,'registered_percent')){
				$tmp = trim(isset($row[$key]) ? number_format($row[$key], 2) : '');
			}
			$output[] = clean_csv_data($tmp);
		}

		fputcsv($fp, $output, ',', '"');
	}

	fclose($fp);
}

function output_csv_from_query() {
	global $database, $db_store;

	$filename = (!empty($_POST['filename']) ? $_POST['filename'] : 'csv_export_'.date("Y-m-d_H-i",time()).'.csv');

	$q = get_clean_query();

	$q = preg_replace('/limit \d+/i','',$q);
	$q = preg_replace('/offset \d+/i','',$q);

	$res = db_query($q,"Running CSV query");
	// Kill the script if there are no results
	if(db_num_rows($res) == 0) { die("No results"); }

	$columns = get_clean_columns($res,$_POST['column_names'],$_POST['column_models'],'database');

	header('Content-Disposition: attachment; filename="'. $filename .'"');
	header('Content-Type: text/csv');

	$fp = fopen('php://output', 'w');

	// display field/column names as first row
	fputcsv($fp, array_values($columns), ',', '"');

	while($row = db_fetch_row($res)) {

		$output = array();
		foreach($columns as $key => $val) {
			$tmp = trim(isset($row[$key]) ? $row[$key] : '');
			if(is_datetime($tmp) && (strstr($key,'date') || strstr($key,'time'))) {
				$tmp = date('m/d/Y H:i:s',$tmp);
			}
			if(strstr($key,'average_score')){
				$tmp = trim(isset($row[$key]) ? number_format($row[$key], 0) : '');
			}
			if(strstr($key,'participating_percent')){
				$tmp = trim(isset($row[$key]) ? number_format($row[$key], 2) : '');
			}
			if(strstr($key,'registered_percent')){
				$tmp = trim(isset($row[$key]) ? number_format($row[$key], 2) : '');
			}
			$output[] = clean_csv_data($tmp);
		}

		fputcsv($fp, $output, ',', '"');
	}

	fclose($fp);
	die();
}


function get_clean_query() {
	# Reinflate a compressed query
	$q = gzinflate(base64_decode(strtr($_POST['query_csv'], '-_', '+/')));
	
	$type = strtolower(!empty($_POST['type']) ? $_POST['type'] : 'csv_all');

	// Remove the offset if removing all information
	if(substr($type,-3) === 'all') {
		$q = preg_replace('/LIMIT\s+\d+\,?\s+?\d+/','',$q);
	}
	return $q;
}

function get_clean_columns($data,$column_names,$column_models='',$type='') {

	$columns = array();
	
	// If no columns were set, dynamically get and clean the query columns
	if($type == 'database' && empty($column_names) && empty($column_models)) {
		$first_row = db_fetch_row($data);

		$columns_dirty = array_keys($first_row);
		foreach($columns_dirty as $row) {
			if($row == 'id') { $tmp = 'ID'; }
			$tmp = str_replace("_"," ",$row);
			$columns[$row] = ucwords($tmp);
		}	
		// Reset data back to 0
		db_data_seek($data,0);
	} else if($type == 'array' && empty($column_names) && empty($column_models)) {
		$first_row = $data[0];

		$columns_dirty = array_keys($first_row);
		foreach($columns_dirty as $row) {
			if($row == 'id') { $tmp = 'ID'; }
			$tmp = str_replace("_"," ",$row);
			$columns[$row] = ucwords($tmp);
		}

	} else if(!empty($column_names) && !empty($column_models)) {
		$column_names = json_decode($column_names,true);
		$column_models = json_decode($column_models,true);
		if(count($column_names) == count($column_models)) {
			$i = 0;
			foreach($column_names as $row) {
				$columns[$column_models[$i]['index']] = $row;
				$i++;
			}
		}
	}

	return $columns;
}

function is_datetime($timestamp) {
	return ((string)(int)$timestamp === $timestamp) 
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
}

function clean_csv_data($val) {
	$val = strip_tags($val);
	if($val == 't') $val = 'TRUE';
	if($val == 'f') $val = 'FALSE';
	// Checking for weird date formats
	// Add an apos in front of the date to force the format
	if(preg_match("/^\+?\d{8,}$/", $val) || preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $val)) {
		$val = "'$val";
	}
	// if(strstr($val, '"')) {
	//   $val = '"' . str_replace('"', '""', $val) . '"';
	// }
	return $val;
}
