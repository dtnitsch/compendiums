<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);

LIBRARY('pagination_ajax.php');
pagination_ajax_setup($_POST['table_id'],$_POST['display_count']);

$col = (!empty($_POST['col']) ? $_POST['col'] : '');
$ord = (!empty($_POST['ord']) ? $_POST['ord'] : '');
$order = ($col != '' ? ' order by '. $col .' '. $ord : '');
$limit = ' limit '. $_POST['display_count'];
$pagination = false;
if(strtolower($_POST['type']) == 'pagination') {
	$pagination = true;
	$limit = '';
}

$where = '';
if(!empty($_POST['filters'])) {
	foreach($_POST['filters'] as $k => $v) {
		$v = trim($v);
		if($v == '') { continue; }
		$where .= " and audits.page_hits.". $k ."::text ilike '%". $v ."%' ";
	}
}

$q = "
	select
		id
		,session_id
		,path
		,params
		,created
	from audits.page_hits
	where
		1=1
		". $where ."
		". $order ."
		". $limit ."
";
$res = ($pagination ? pagination_ajax_query($q,"Getting Pagi Page Hits") : db_query($q,"Getting Pagi Hits") );

$output[$_POST['table_id']] = array();
while($row = db_fetch_row($res)) {
	$output[$_POST['table_id']][] = $row;
}

_error_debug("Ending Ajax",'',__LINE__,__FILE__);
$results = array(
	"output" => $output
	,"debug" => ajax_debug()
);
if($pagination) {
	$results["pagination"] = pagination_return_results();
}
echo json_encode($results);