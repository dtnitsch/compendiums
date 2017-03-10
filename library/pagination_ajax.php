<?php 

function pagination_ajax_setup($pagi_name,$ipp = 10) {
	if(empty($pagi_name)) { return false; }
	if(empty($ipp)) { $ipp = 10; }
	
	$cp = 1;
	if(!empty($_POST['cp'])) { $cp = $_POST['cp']; }
	
	$GLOBALS['pagination']['current'] = $pagi_name;
	$GLOBALS['pagination'][$pagi_name]['ipp'] = $ipp;
	$GLOBALS['pagination'][$pagi_name]['cp'] = $cp;
}

function pagination_ajax_query($q,$display='',$db='default') {
	$p = $GLOBALS['pagination'][$_POST['table_id']];
	$q2 = $q ." LIMIT ". $p['ipp'] ." OFFSET ". (($p['cp'] - 1) * $p['ipp']);
	$GLOBALS['pagination']['query'] = $q2;
	pagination_ajax_get_count($q,'Getting Pagi Paths - Count');
	return db_query($q2,$display ." - Pagination Limited",$db);
}

function get_pagination_ajax_query() {
	return $GLOBALS['pagination']['query'];
}

function pagination_ajax_get_count($query,$display='') {
	$q2 = str_replace(stristr($query, 'order by'),'',$query);
	$q2 = "select count(*) as cnt from (". $q2 .") as q";

	$pagi_name = $GLOBALS['pagination']['current'];
	$res = db_fetch($q2,$display .' - Pagination Count');
	$GLOBALS['pagination'][$pagi_name]['total_count'] = $res['cnt'];

	return $res['cnt'];
}

function pagination_return_results() {
	$pagi_name = $GLOBALS['pagination']['current'];
	return array(
		'table_id' => $pagi_name
		, "ipp" => $GLOBALS['pagination'][$pagi_name]['ipp']
		,'total_records' => $GLOBALS['pagination'][$pagi_name]['total_count']
	);
}