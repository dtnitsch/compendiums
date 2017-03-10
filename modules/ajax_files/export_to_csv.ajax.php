<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);

#echo "Query: ";
$q = gzinflate(base64_decode(strtr($_POST['query'], '-_', '+/')));

$type = 'visible';
if(!empty($_POST['type'])) {
	$type = trim(strtolower($_POST['type']));
}
// Remove the offset if removing all information
if($type == 'all') {
	$q = preg_replace('/LIMIT \d+ OFFSET \d+/i','',$q);
}


$res = db_query($q,"Getting CSV information");

// If no columns were set, dynamically get and clean the query columns
if(empty($_POST['custom_columns'])) {
	$x = db_fetch_row($res);

	$columns_dirty = array_keys($x);
	$columns = array();
	foreach($columns_dirty as $row) {
		if($row == 'id') { $tmp = 'ID'; }
		$tmp = str_replace("_"," ",$row);
		$columns[$row] = ucwords($tmp);
	}	
}


// Reset data back to 0
db_data_seek($res);


LIBRARY('csv_generator.php');

$filename = "csv_export_" . date('Ymd') . ".csv";	
$handler = csv_header($filename);

$res = db_query($q,"Getting CSV information");

$flag = false; # check for first time
while($row = db_fetch_row($res)) {
	if(!$flag) {
		// display field/column names as first row
		fputcsv($handler, array_values($columns), ',', '"');
		$flag = true;
	}
	$output = array();

	foreach($columns as $key => $val) {
		$tmp = trim(isset($row[$key]) ? $row[$key] : '');
		$output[] = clean_csv_data($tmp);
	}

	fputcsv($handler, $output, ',', '"');
}

csv_footer();



// ob_start();

// echo "Export to CSV";

// $output = ob_get_clean();
// echo json_encode(array("output"=>$output,"debug"=>ajax_debug()));
