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
		sas.activity_id
		,sum(sas.total_score) as total_score
		,sum(sas.activity_count) as activity_count
		,a.title as activity_title
	from public.summary_activity_scores as sas
	join public.activities as a on
		a.id = sas.activity_id
	where
		sas.user_id = ".db_prep_sql((int) $_SESSION["user"]["id"])."
		and sas.student_id = ".db_prep_sql((int) $_POST["student_id"])."
		".$where."
	group by
		sas.activity_id
		,a.title
	".$order."
	".$limit."
";

$res = pagination_ajax_query($q, "Getting Pagination Path");
$query = get_pagination_ajax_query();

$output[$_POST["table_id"]] = array();

while ($row = db_fetch_row($res)) {

	//if ($row["activity_id"] == 0) { $row["activity_title"] = "Unknown"; }

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
