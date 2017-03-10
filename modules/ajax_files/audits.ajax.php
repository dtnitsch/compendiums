<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);

LIBRARY('pagination_ajax.php');
pagination_ajax_setup($_POST['table_id'],$_POST['display_count']);

$vars = json_decode($info['dynamic_variables'],true);

$schema = $vars['db_schema'] ."_";

$col = (!empty($_POST['col']) ? $_POST['col'] : '');
$ord = (!empty($_POST['ord']) ? $_POST['ord'] : '');
$order = ' order by '. ($col != '' ? $col .' '. $ord : $schema ."field_logs.created desc");
$limit = ' limit '. $_POST['display_count'];
if(strtolower($_POST['type']) == 'pagination') { $limit = ''; }

$where = '';
if(!empty($_POST['filters'])) {
	foreach($_POST['filters'] as $k => $v) {
		$v = trim($v);
		if($v == '') { continue; }
		$where .= " and ". $schema ."field_logs.". $k ." ilike '%". $v ."%' ";
	}
}



$q = "
	select 
		". $schema ."field_logs.id
		,". $schema ."field_logs.table_log_id
		,". $schema ."field_logs.column_name
		,". $schema ."field_logs.old_value
		,". $schema ."field_logs.new_value
		,(system.users.firstname || ' ' || system.users.lastname) as full_name
		,to_char(". $schema ."field_logs.created,'Mon DD, YYYY HH:MI:SSam') as created
	from audits.". $schema ."table_logs
	join audits.". $schema ."field_logs on
		". $schema ."field_logs.table_log_id = ". $schema ."table_logs.id
		and ". $schema ."table_logs.primary_key_id='". $_POST['id'] ."'
	left join system.users on system.users.id = ". $schema ."table_logs.user_id
	where
		". $schema ."table_logs.table_name = '". $vars['db_table'] ."'
		". $where ."
		". $order ."
		". $limit ."
";

$res = pagination_ajax_query($q,"Getting Pagi Security Group");

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