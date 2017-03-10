<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);

LIBRARY('pagination_ajax.php');
PAGINATION_AJAX_SETUP($_POST['table_id'],$_POST['display_count']);

$col = (!empty($_POST['col']) ? $_POST['col'] : 'word');
$ord = (!empty($_POST['ord']) ? $_POST['ord'] : 'asc');
$order = ($col != '' ? ' order by '. $col .' '. $ord : '');
$limit = ' limit '. $_POST['display_count'];
if(strtolower($_POST['type']) == 'pagination') { $limit = ''; }

$where = '';
if(!empty($_POST['filters'])) {
	foreach($_POST['filters'] as $k => $v) {
		$v = trim($v);
		if($v == '') { continue; }

		if($k == 'wsm.world_id' || $k == 'wsm.generation_id' || $k == 'wsm.theme_id')  {
			$v = (double)$v;
			$where .= " and ". $k ." = '". $v ."' ";
		} else {
			$where .= " and ". $k ." ilike '%". $v ."%' ";
		}
		
	}
}

$q = "
	select
		ws.id
		,ws.word
		,to_char(ws.modified, 'MM/DD/YYYY') as modified
	from activities.word_search as ws
	join activities.word_search_map as wsm on 
		wsm.word_search_id = ws.id
	where
		ws.active 
		". $where ."
		". $order ."
		". $limit ."
";
$res = pagination_ajax_query($q,"Getting Pagi Path");
$query = get_pagination_ajax_query();

$output[$_POST['table_id']] = array();

while ($row = db_fetch_row($res)) {

	$output[$_POST["table_id"]][] = $row;

}

_error_debug("Ending Ajax",'',__LINE__,__FILE__);
echo json_encode(array(
	"output" => $output
	,"pagination" => pagination_return_results()
	#,"query" => urlencode(base64_encode(gzcompress($query,9)))
	,"query" => rtrim(strtr(base64_encode(gzdeflate($query, 9)), '+/', '-_'), '=')
	,"debug" => ajax_debug()
));