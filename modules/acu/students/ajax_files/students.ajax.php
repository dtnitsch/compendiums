<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);

library('pagination_ajax.php');
pagination_ajax_setup($_POST['table_id'],$_POST['display_count']);

$col = (!empty($_POST['col']) ? $_POST['col'] : '');
$ord = (!empty($_POST['ord']) ? $_POST['ord'] : '');
$order = ($col != '' ? ' order by '. $col .' '. $ord : '');
$limit = ' limit '. $_POST['display_count'];
if(strtolower($_POST['type']) == 'pagination') { $limit = ''; }

$where = '';
if(!empty($_POST['filters'])) {
	$filters = array();
	foreach($_POST['filters'] as $k => $v) {
		if(is_array($v)) { continue; }
		$v = trim($v);
		if($v == '') { continue; }

		if($k == "school") {
			$filters[] .= " pi.title ilike '%". $v ."%' ";

		} else if($k == "institution_ids") {
			$filters[] = " ss.institution_id in (". $v .") ";

		} else if($k == "ss.grade_id") {
			$v = (double)$v;
			$filters[] = " ". $k ." in (". $v .") ";

		} else if(strpos($k,"_id") !== false) {
			$filters[] .= " ss.". $k ." = '". $v ."' ";

		} else if($k == "created_start") {
			$filters[] .= " ss.created >= '". $v ."' ";

		} else if($k == "created_end") {
			$filters[] .= " ss.created < '". $v ."' ";


		} else {
			$filters[] .= " ss.". $k ." ilike '%". $v ."%' ";
		}
		//$filters[] .= " and ss.". $k ." ilike '%". $v ."%' ";
	}

	if (!empty($filters)) {
		$where = 'and '.implode(' and ',$filters);
	}
}

$q = "
	select
		ss.id
		,ss.firstname
		,ss.lastname
		,(case when ss.gender_id = 1 then 'Boy' when ss.gender_id = '2' then 'Girl' else '--' end) as gender
		,pg.title as grade
		,sa.id as user_id
		,(sa.firstname || ' ' || sa.lastname) as user_name
		,(case when sum(sas.total_score) is null then 0 else sum(sas.total_score) end) as total_score
		,to_char(ss.created, 'MM/DD/YYYY') as created
		,to_char(ss.modified, 'MM/DD/YYYY') as modified
		,pi.title AS school
		,pi.id AS institution_id
	from system.students as ss
	join system.users as sa on
		sa.id = ss.user_id
	join public.institutions as pi on
		pi.id = ss.institution_id
	left join public.grades as pg
		on pg.id = ss.grade_id
	left join public.summary_activity_scores as sas on
		sas.student_id = ss.id
	where
		ss.active
		".$where."
	group by
		ss.id
		,pg.title
		,sa.id
		,pi.title
		,pi.id
	".$order."
	".$limit."
";



$res = pagination_ajax_query($q,"Getting Pagi Path");
$query = get_pagination_ajax_query();

$output[$_POST["table_id"]] = array();

while ($row = db_fetch_row($res)) {

	if (empty($row["title"])) {
		$row["title"] = "--";
	}

	$row["total_score"] = number_format($row["total_score"], 0);

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