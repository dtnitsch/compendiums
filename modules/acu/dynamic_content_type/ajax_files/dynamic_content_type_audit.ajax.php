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
		$where .= " and public_field_log.". $k ." ilike '%". $v ."%' ";
	}
}

$vars = json_decode($info['dynamic_variables'],true);

$q = "
	select 
		public_field_log.id
		,public_field_log.table_log_id
		,public_field_log.column_name
		,public_field_log.old_value
		,public_field_log.new_value
		,(system.user.first || ' ' || system.user.last) as full_name
		,to_char(public_field_log.created,'Mon DD, YYYY HH:MI:SSam') as created
	from audits.public_table_log
	join audits.public_field_log on
		public_field_log.table_log_id = public_table_log.id
		and public_table_log.primary_key_id='". $_POST['id'] ."'
	left join system.user on system.user.id = public_table_log.user_id
	where
		public_table_log.schema_name = '". $vars['db_schema'] ."'
		and public_table_log.table_name = '". $vars['db_table'] ."'
		". $where ."
		". $order ."
		". $limit ."
";

$res = pagination_ajax_query($q,"Getting Pagi Dynamic Content Type");

$output[$_POST['table_id']] = array();
$i = 0;
while($row = db_fetch_row($res)) {
	$output[$_POST['table_id']][$i] = $row;
	if(empty($output[$_POST['table_id']][$i]['full_name']) || $output[$_POST['table_id']][$i]['full_name'] == 'null') {
		$output[$_POST['table_id']][$i]['full_name'] = '<em>N/A</em>';
	}
	$i++;
}

_error_debug("Ending Ajax",'',__LINE__,__FILE__);
echo json_encode(array(
	"output" => $output
	,"pagination" => pagination_return_results()
	,"debug" => ajax_debug()
));