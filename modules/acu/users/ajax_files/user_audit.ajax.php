<?php
_error_debug("Starting Ajax","",__LINE__,__FILE__);

$orderby 	= "";
$rows		= (!empty($_POST["rows"]) ? $_POST["rows"] : 20);
$page		= (!empty($_POST["page"]) ? $_POST["page"] : 1); // get the requested page
$sidx		= (!empty($_POST["sidx"]) ? $_POST["sidx"] : "created"); // get index row - i.e. user click to sort
$sord		= (!empty($_POST["sord"]) ? $_POST["sord"] : "desc"); // get the direction
$where		= "";

$order = " order by ". $sidx ." ". $sord;

$vars = json_decode($info["dynamic_variables"],true)[0];

$audit_table = 'audit_table_logs';
$audit_field = 'audit_field_logs';
$user_table = 'users';
if(uses_schema()) {
	$audit_table = '"audits"."system_table_logs"';
	$audit_field = '"audits"."system_field_logs"';
	$user_table = '"system"."users"';
}

$q = "
	select count(*) as cnt
	from (
		select 
			". $audit_field .".id
			,". $audit_field .".table_log_id
			,". $audit_field .".column_name
			,". $audit_field .".old_value
			,". $audit_field .".new_value
			,concat(". $user_table .".firstname,' ',". $user_table .".lastname) as full_name
			,". $audit_field .".created
		from ". $audit_table ."
		join ". $audit_field ." on
			". $audit_field .".table_log_id = ". $audit_table .".id
			and ". $audit_table .".primary_key_id='". $_POST["id"] ."'
		left join ". $user_table ." on ". $user_table .".id = ". $audit_table .".user_id
		where
			". $audit_table .".table_name = '". $vars["db_table"] ."'
			". $where ."
	) as q
";


$res = db_fetch($q,"Fetching Row Count");
$rowcount = $res["cnt"];

$total_pages 	= ($rowcount > 0 ? ceil($rowcount / $rows) : 0);
$page 			= ($page > $total_pages ? $total_pages : $page);
$start 			= max(0, (($rows*$page) - $rows));
$limit 			= " limit ". $rows ." offset ". $start;

$q = "
	select 
		". $audit_field .".id
		,". $audit_field .".table_log_id
		,". $audit_field .".column_name
		,". $audit_field .".old_value
		,". $audit_field .".new_value
		,concat(". $user_table .".firstname,' ',". $user_table .".lastname) as full_name
		,". $audit_field .".created
	from ". $audit_table ."
	join ". $audit_field ." on
		". $audit_field .".table_log_id = ". $audit_table .".id
		and ". $audit_table .".primary_key_id='". $_POST["id"] ."'
	left join ". $user_table ." on ". $user_table .".id = ". $audit_table .".user_id
	where
		". $audit_table .".table_name = '". $vars["db_table"] ."'
		". $where ."
		". $order ."
		". $limit ."
";
$res = db_query($q,"Getting Records");


$response 				= new stdClass();
$response->page 		= $page;
$response->total 		= $total_pages;
$response->records 		= $rowcount;

$k=0;
while($row = db_fetch_row($res)) {
	$response->records = $rowcount;

	$output = array();

	$output[] = htmlspecialchars($row["column_name"]);
	$output[] = htmlspecialchars($row["full_name"]);
	$output[] = htmlspecialchars($row["old_value"]);
	$output[] = htmlspecialchars($row["new_value"]);
	$output[] = $row["created"];

	$response->rows[$k]["id"]		= $row["id"];
	$response->rows[$k++]["cell"]	= $output;
}



_error_debug("Ending Ajax","",__LINE__,__FILE__);
if($GLOBALS["debug_options"]["enabled"]) {
	$response->debug = ajax_debug();
}
echo json_encode($response);