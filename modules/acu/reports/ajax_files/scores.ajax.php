<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);

library('pagination_ajax.php');
pagination_ajax_setup($_POST['table_id'],$_POST['display_count']);

$col = (!empty($_POST['col']) ? $_POST['col'] : '');
$ord = (!empty($_POST['ord']) ? $_POST['ord'] : '');
$order = ($col != '' ? ' order by '. $col .' '. $ord : '');
$limit = ' limit '. $_POST['display_count'];
if(strtolower($_POST['type']) == 'pagination') { $limit = ''; }

$where = array();
if(!empty($_POST['filters'])) {
	foreach($_POST['filters'] as $k => $v) {
		if($k == 'start_date' || $k == 'end_date') { continue; }
		$v = trim($v);
		if($v == '') { continue; }
		
		// int search for grade
		if($k == "sas.grade_id") {
			$where[] = " ". $k ." in (". $v .") ";

		// Condensed username search
		} else if($k == "user_name") {
			$v = str_replace(' ','%',$v);
			$where[] = " (saa.firstname ||' '|| saa.lastname) ilike '%". $v ."%' ";

		// Everyone else
		} else {
			$v = str_replace(' ','%',$v);
			$where[] = " ". $k ." ilike '%". $v ."%' ";
		}
	}
	
	$start_date = (!empty($_POST['filters']['start_date']) ? date('Y-m-d 00:00:00',strtotime($_POST['filters']['start_date'])) : date('Y-m-d 00:00:00',strtotime('Previous Sunday')));
	$end_date = (!empty($_POST['filters']['end_date']) ? date('Y-m-d 23:59:59',strtotime($_POST['filters']['end_date'])) : date('Y-m-d 23:59:59',strtotime('Saturday this Week')));

	$where[] = " sas.created_date >= '". $start_date ."' and sas.created_date <= '". $end_date ."' ";

	if(!empty($where)) {
		$where = implode(' and ',$where);
	}
}

/*
// ORIGINAL QUERY - Pre Sumamry Table
$q = "
	select
		ss.firstname
		,g.title as grade_id
		,(sa.firstname ||' '|| sa.lastname) as user_name
		,sa.email as user_email
		,pi.title as institution
		,count(activity_map_id) as activity_count
		,sum(original_score) as original_score
		,sum(calculated_score) as total_score
	from public.activity_scores as pas
	join system.students as ss on
		ss.id = pas.student_id
	join system.users as sa on
		sa.id = ss.user_id
	left join public.institutions as pi on
		pi.id = pas.institution_id
	join public.grades as g on
		g.id = ss.grade_id
	where
		". $where ."

	group by
		ss.firstname
		,ss.grade_id
		,g.title
		,sa.firstname
		,sa.lastname
		,sa.email
		,pi.title

	". $order ."
	". $limit ."
";
*/
$q = "
	select
		sas.student_firstname
		,sas.grade_id
		,sas.grade
		,(sas.user_firstname ||' '|| sas.user_lastname) as user_name
		,sas.user_email
		,sas.institution
		,sas.institution_id
		,sas.user_id
		,sum(activity_count) as activity_count
		,sum(original_score) as original_score
		,sum(total_score) as total_score
	from public.summary_activity_scores as sas
	where
		 ". $where ."
	group by
		sas.student_firstname
		,sas.grade_id
		,sas.grade
		,sas.user_email
		,sas.institution
		,sas.user_firstname
		,sas.user_lastname
		,sas.student_id
		,sas.institution_id
		,sas.user_id

	". $order ."
	". $limit ."
";
$res = pagination_ajax_query($q,"Getting Pagi Path");
$query = get_pagination_ajax_query();

$output[$_POST['table_id']] = array();
while($row = db_fetch_row($res)) {
	if(empty($row['student_firstname'])) {
		$row['student_firstname'] = '--';
	}
	if(empty($row['user_name'])) {
		$row['user_name'] = '--';
	}
	if(empty($row['user_email'])) {
		$row['user_email'] = '--';
	}
	if(empty($row['institution']) || $row['institution_id'] == 0) {
		$row['institution'] = '--';
	}

	if (empty($row['original_score']) || is_null($row['original_score'])) {
		$row['original_score'] = 0;
	} else {
		$row['original_score'] = number_format($row['original_score'], 0);
	}

	if (empty($row['total_score']) || is_null($row['total_score'])) {
		$row['total_score'] = 0;
	} else {
		$row['total_score'] = number_format($row['total_score'], 0);
	}
	$output[$_POST['table_id']][] = $row;
}

_error_debug("Ending Ajax",'',__LINE__,__FILE__);
echo json_encode(array(
	"output" => $output
	,"pagination" => pagination_return_results()
	#,"query" => urlencode(base64_encode(gzcompress($query,9)))
	,"query" => rtrim(strtr(base64_encode(gzdeflate($query, 9)), '+/', '-_'), '=')
	,"debug" => ajax_debug()
));