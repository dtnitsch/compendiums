<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);

LIBRARY('pagination_ajax.php');
pagination_ajax_setup($_POST['table_id'],$_POST['display_count']);

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
		$where .= " and security.roles.". $k ."::text ilike '%". $v ."%' ";
	}
}

$q = "
	select
		id
		,title
		,alias
		,to_char(created, 'MM/DD/YYYY') as created
		,to_char(modified, 'MM/DD/YYYY') as modified
	from security.roles
	where
		security.roles.active 
		". $where ."
		". $order ."
		". $limit ."
";
$res = pagination_ajax_query($q,"Getting Pagi Security Role");

$output[$_POST['table_id']] = array();
while ($row = db_fetch_row($res)) {

	$output[$_POST['table_id']][] = $row;

}

_error_debug("Ending Ajax",'',__LINE__,__FILE__);
echo json_encode(array(
	"output" => $output
	,"pagination" => pagination_return_results()
	,"debug" => ajax_debug()
));