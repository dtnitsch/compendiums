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
	$filters[] = "marketing_source is not null";
	$filters[] = "marketing_source != ''";
	$filters[] = "u.registration_type_id in (1,2)";
	foreach($_POST['filters'] as $k => $v) {
		if($k == 'start_date' || $k == 'end_date') { continue; }
		$v = trim($v);
		if($v == '') { continue; }
		$filters[] .= " ". $k ." ilike '%". $v ."%' ";
	}

	// $start_date = (!empty($_POST['filters']['start_date']) ? date('Y-m-d 00:00:00',strtotime($_POST['filters']['start_date'])) : date('Y-m-d 00:00:00',strtotime('Previous Sunday')));
	// $end_date = (!empty($_POST['filters']['end_date']) ? date('Y-m-d 23:59:59',strtotime($_POST['filters']['end_date'])) : date('Y-m-d 23:59:59',strtotime('Saturday this Week')));

	//$where[] = " pi.marketing_source = 'school' ";

	//$where[] = " pi.created >= '". $start_date ."' and pi.created <= '". $end_date ."' ";

	//$where[] = " pi.active ";

	if(!empty($filters)) {
		$where = 'where '.implode(' and ',$filters);
	}

}

$q = "
	select 
		u.marketing_source
		,count(u.marketing_source) as registered_count
		,u.firstname
		,u.lastname
		,u.created
		,u.email
		,u.registration_type_id
		,rt.title as type
	from system.users as u
	join public.registration_types as rt on
		rt.id = u.registration_type_id

	". $where ."

	group by
		u.marketing_source
		,u.firstname
		,u.lastname
		,u.created
		,u.email
		,u.registration_type_id
		,rt.title

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