<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);

$orderby 	= "";
$rows		= (!empty($_POST["rows"]) ? $_POST["rows"] : 20);
$page		= (!empty($_POST["page"]) ? $_POST["page"] : 1); // get the requested page
$sidx		= (!empty($_POST["sidx"]) ? $_POST["sidx"] : "lastname,firstname"); // get index row - i.e. user click to sort
$sord		= (!empty($_POST["sord"]) ? $_POST["sord"] : "asc"); // get the direction
$where		= "";

$order = " order by ". $sidx .' '. $sord;

$table = "users";
if(uses_schema()) { $table = '"system"."users"'; }

$where = "";
if(!empty($_POST["filters"])) {
	foreach($_POST["filters"] as $k => $v) {
		$v = trim($v);
		if($v == "") { continue; }
		$where .= " and ". $table .".". $k ." ilike '%". $v ."%' ";
	}
}

$q = "
	select count(id) as cnt
	from (
		select
			id
		from ". $table ."
		where
			". $table .".active 
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
		". $table .".id
		,". $table .".email
		,". $table .".firstname
		,". $table .".lastname
		,". $table .".created
	from ". $table ."
	where
		". $table .".active 
		". $where ."
	". $order ."
	". $limit ."
";
$res = db_query($q,"Getting Pagi User");


$response 				= new stdClass();
$response->page 		= $page;
$response->total 		= $total_pages;
$response->records 		= $rowcount;

$k=0;
while($row = db_fetch_row($res)) {
	$response->records = $rowcount;

	$output = array();

	$output[] = htmlspecialchars($row["firstname"]);
	$output[] = htmlspecialchars($row["lastname"]);
	$output[] = htmlspecialchars($row["email"]);
	$output[] = $row["created"];

	$response->rows[$k]["id"]		= $row["id"];
	$response->rows[$k++]["cell"]	= $output;
}

_error_debug("Ending Ajax","",__LINE__,__FILE__);
if($GLOBALS["debug_options"]["enabled"]) {
	$response->debug = ajax_debug();
}
echo json_encode($response);