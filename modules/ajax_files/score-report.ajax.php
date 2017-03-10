<?php

_error_debug("Starting Ajax", "", __LINE__, __FILE__);

session_name("clevercrazes");
session_start();

library("pagination_ajax.php");

pagination_ajax_setup($_POST["table_id"], $_POST["display_count"]);

$col = (!empty($_POST["col"]) ? $_POST["col"] : "");
$ord = (!empty($_POST["ord"]) ? $_POST["ord"] : "");
$order = ($col != "" ? " order by ".$col." ".$ord : "");
$limit = " limit ".$_POST["display_count"];

if (strtolower($_POST["type"]) == "pagination") { $limit = ""; }

$where = array();
$filters = array();

if (!empty($_POST["filters"])) {

	$start_date = (!empty($_POST["filters"]["start_date"]) ? date("Y-m-d 00:00:00", strtotime($_POST["filters"]["start_date"])) : date("Y-m-01 00:00:00"));
	$end_date = (!empty($_POST["filters"]["end_date"]) ? date("Y-m-d 23:59:59", strtotime($_POST["filters"]["end_date"])) : date("Y-m-t 23:59:59"));

	$where = "
		and sas.created_date >= '".db_prep_sql($start_date)."'
		and sas.created_date <= '".db_prep_sql($end_date)."'
	";

}

if (empty($where)) { $where = ""; }

$q = "
	select
		sas.student_id
		,sas.student_firstname
		,sum(sas.total_score) as total_score
	from public.summary_activity_scores as sas
	where
		sas.user_id = ".db_prep_sql((int) $_SESSION["user"]["id"])."
		".$where."
	group by
		sas.student_id
		,sas.student_firstname
	".$order."
	".$limit."
";

$res = pagination_ajax_query($q, "Getting Pagination Path");
$query = get_pagination_ajax_query();

$output[$_POST["table_id"]] = array();

while ($row = db_fetch_row($res)) {

	$row["student_firstname"] = '<a href="/score-report/view/?id='.$row["student_id"].(empty($_POST["filters"]["start_date"]) ? "" : "&start_date=".$_POST["filters"]["start_date"]).(empty($_POST["filters"]["end_date"]) ? "" : "&end_date=".$_POST["filters"]["end_date"]).'">'.$row["student_firstname"].'</a>';
	$row["total_score"] = number_format($row["total_score"], 0);

	$output[$_POST["table_id"]][] = $row;

}

_error_debug("Ending Ajax", "", __LINE__, __FILE__);

$json = array(
	"output" => $output
	,"pagination" => pagination_return_results()
	//,"query" => rtrim(strtr(base64_encode(gzdeflate($query, 9)), "+/", "-_"), "=")
	,"debug" => ajax_debug()
);

echo json_encode($json);
