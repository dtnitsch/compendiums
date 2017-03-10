<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);

library('pagination_ajax.php');
pagination_ajax_setup($_POST['table_id'],$_POST['display_count']);

$col = (!empty($_POST['col']) ? $_POST['col'] : '');
$ord = (!empty($_POST['ord']) ? $_POST['ord'] : '');
$order = ($col != '' ? ' order by '. $col .' '. $ord : '');
$limit = ' limit '. $_POST['display_count'];
if(strtolower($_POST['type']) == 'pagination') { $limit = ''; }

$where = '';
if(!empty($_POST['filters'])) {
	$filters = array();
	foreach($_POST['filters'] as $k => $v) {
		if($k == 'start_date' || $k == 'end_date') { continue; }
		$v = trim($v);
		if($v == '') { continue; }
		$filters[] .= " pi.". $k ." ilike '%". $v ."%' ";
	}

	$start_date = (!empty($_POST['filters']['start_date']) ? date('Y-m-d 00:00:00',strtotime($_POST['filters']['start_date'])) : date('Y-m-d 00:00:00',strtotime('Previous Sunday')));
	$end_date = (!empty($_POST['filters']['end_date']) ? date('Y-m-d 23:59:59',strtotime($_POST['filters']['end_date'])) : date('Y-m-d 23:59:59',strtotime('Saturday this Week')));

	//$where[] = " pi.marketing_source = 'school' ";

	//$where[] = " pi.created >= '". $start_date ."' and pi.created <= '". $end_date ."' ";

	//$where[] = " pi.active ";

	if(!empty($filters)) {
		$where = 'where '.implode(' and ',$filters);
	}

}

$q = "
	select 
		pi.marketing_source
		,count(pi.marketing_source) as registered_count
	from public.institutions as pi

	". $where ."

	group by
		pi.marketing_source

	". $order ."
	". $limit ."
";


$res = pagination_ajax_query($q,"Getting Pagi Path");
$query = get_pagination_ajax_query();

$output[$_POST['table_id']] = array();
while($row = db_fetch_row($res)) {
	if(empty($row['title'])) {
		$row['title'] = '--';
	}
	$output[$_POST['table_id']][] = $row;
}

_error_debug("Ending Ajax",'',__LINE__,__FILE__);
echo json_encode(array(
	"output" => $output
	,"pagination" => pagination_return_results()
	#,"query" => urlencode(base64_encode(gzcompress($query,9)))
	,"query" => rtrim(strtr(base64_encode(gzdeflate($query, 9)), '+/', '-_'), '=')
	,"debug" => ajax_debug()
));