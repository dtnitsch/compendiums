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
		$where .= " and public.dynamic_content.". $k ." ilike '%". $v ."%' ";
	}
}

$q = "
	select
		dynamic_content.id
		,dynamic_content.title
		,dynamic_content.alias
		,to_char(dynamic_content.modified, 'MM/DD/YYYY') as modified
		,dynamic_content_types.title as dynamic_content_type
	from public.dynamic_content
	join public.dynamic_content_types on dynamic_content_types.id = dynamic_content.dynamic_content_type_id
	where
		public.dynamic_content.active 
		". $where ."
		{$order}
		{$limit}
";
$res = pagination_ajax_query($q,"Getting Pagi Dynamic Content");
$query = get_pagination_ajax_query();

$output[$_POST['table_id']] = array();

while ($row = db_fetch_row($res)) {

	$output[$_POST["table_id"]][] = $row;

}

_error_debug("Ending Ajax",'',__LINE__,__FILE__);
echo json_encode(array(
	"output" => $output
	,"pagination" => pagination_return_results()
	,"query" => rtrim(strtr(base64_encode(gzdeflate($query, 9)), '+/', '-_'), '=')
	,"debug" => ajax_debug()
));