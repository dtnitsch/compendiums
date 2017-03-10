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
$where_other = '';
$filters = array();
if(!empty($_POST['filters'])) {

	foreach($_POST['filters'] as $k => $v) {
		if($k == 'start_date' || $k == 'end_date' || $k == 'sas.grade_id') { continue; }
		$v = trim($v);
		if($v == '') { continue; }

		// int search for grade
		if($k == "ss.grade_id") {
			$filters['core'][] = " ". $k ." in (". $v .") ";

		// Condensed username search
		} else if($k == "user_name") {
			$v = str_replace(' ','%',$v);
			$filters['core'][] = " (sas.user_firstname ||' '|| sas.user_lastname) ilike '%". $v ."%' ";

		// Everyone else
		} else {
			$v = str_replace(' ','%',$v);
			$filters['core'][] = " ". $k ." ilike '%". $v ."%' ";
		}
	}

	if(!empty($_POST['filters']['sas.grade_id'])) {
		$filters['core'][] = "sas.grade_id = '". $_POST['filters']['ss.grade_id'] ."'";
	}

	$start_date = (!empty($_POST['filters']['start_date']) ? date('Y-m-d 00:00:00',strtotime($_POST['filters']['start_date'])) : date('Y-m-d 00:00:00',strtotime('Previous Sunday')));
	$end_date = (!empty($_POST['filters']['end_date']) ? date('Y-m-d 23:59:59',strtotime($_POST['filters']['end_date'])) : date('Y-m-d 23:59:59',strtotime('Saturday this Week')));

	$filters['core'][] = " sas.created_date >= '". $start_date ."' and sas.created_date <= '". $end_date ."' ";
	$filters['core'][] = " sas.activity_id != 109 ";

	if(!empty($filters['core'])) {
		$where = 'where '.implode(' and ',$filters['core']);
	}

	// if(!empty($filters['other'])) {
	// 	$where_other = 'where '.implode(' and ',$filters['other']);
	// }
}

$q = "
	select
		sas.student_id
		,sas.user_id
		,sum(sas.total_score) as total_score
		,(sas.student_firstname || ' ' || sas.student_lastname) as student_name
		,sas.grade
		,tp.title as institution_type
		,sas.institution
		,pi.site_name
		,(sas.user_firstname || ' ' || sas.user_lastname) as user_name
		,sas.user_email
		,pi.city
		,sr.\"2code\" as state
		,sum(sas.activity_count) as activity_count
		,to_char(pi.created, 'MM/DD/YYYY') as created
	from public.summary_activity_scores as sas
	join public.institutions as pi on
	    pi.id = sas.institution_id
	left join public.institution_types as tp
		on pi.institution_type_id = tp.id
	left join supplements.regions as sr
		on pi.region_id = sr.id
    ". $where ."
    ". $where_other ."
	group by
		sas.student_id
		,tp.title
		,pi.city
		,pi.created
		,pi.site_name
		,sas.student_firstname
		,sas.student_lastname
		,sas.user_firstname
		,sas.user_lastname
		,sas.grade
		,sas.user_email
		,sas.user_id
		,sas.institution
		,sr.\"2code\"
	". $order ."
	". $limit ."
";


$res = pagination_ajax_query($q,"Getting Pagi Path");
$query = get_pagination_ajax_query();

$output[$_POST['table_id']] = array();

while ($row = db_fetch_row($res)) {

	if (empty($row["firstname"])) {
		$row["firstname"] = "--";
	}

	if (empty($row["user_name"])) {
		$row["user_name"] = "--";
	}

	if (empty($row["user_email"])) {
		$row["user_email"] = "--";
	}

	if (empty($row["institution"])) {
		$row["institution"] = "--";
	}

	$row["total_score"] = number_format($row["total_score"], 0);

	//$row["created"] = date("m/d/Y", strtotime($row["created"]));

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
