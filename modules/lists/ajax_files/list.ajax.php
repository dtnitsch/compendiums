<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);

LIBRARY('pagination_ajax.php');
PAGINATION_AJAX_SETUP($_POST['table_id'],$_POST['display_count']);

$col = (!empty($_POST['col']) ? $_POST['col'] : '');
$ord = (!empty($_POST['ord']) ? $_POST['ord'] : '');
$order = ($col != '' ? ' order by '. $col .' '. $ord : '');
$limit = ' limit '. $_POST['display_count'];
if(strtolower($_POST['type']) == 'pagination') { $limit = ''; }

$where = '';
if(!empty($_POST['filters'])) {
	foreach($_POST['filters'] as $k => $v) {
		$v = trim($v);
		if($v == '') { continue; }
		$where .= " and public.list.". $k ." ilike '%". $v ."%' ";
	}
}
if(!empty($_POST['u'])) {
	start_session();
	$where .= " and public.list.user_id='". $_SESSION['user']['id'] ."' ";
}

$q = "
	select
		list.id
		,list.title
		,list.alias
		,list.key
		,users.username
		,count(list_asset_map.id) as asset_count
		,to_char(list.modified, 'MM/DD/YYYY') as modified
	from public.list
	join public.list_asset_map on
		list_asset_map.list_id = list.id
		and list_asset_map.active
	join system.users on users.id=public.list.user_id
	where
		public.list.active 
		". $where ."
	group by
		list.id
		,list.title
		,list.alias
		,list.key
		,users.username
	". $order ."
	". $limit ."
";
$res = pagination_ajax_query($q,"Getting Pagi Path");
$query = get_pagination_ajax_query();

$output = array();
$output[$_POST['table_id']] = array();

while($row = db_fetch_row($res)) {

	if (empty($row["title"])) {
		$row["title"] = "--";
	}

	$output[$_POST["table_id"]][] = $row;

}

_error_debug("Ending Ajax",'',__LINE__,__FILE__);
echo json_encode(array(
	"output" => $output
	,"pagination" => pagination_return_results()
	#,"query" => urlencode(base64_encode(gzcompress($query,9)))
	// ,"query" => rtrim(strtr(base64_encode(gzdeflate($query, 9)), '+/', '-_'), '=')
	,"debug" => ajax_debug()
));