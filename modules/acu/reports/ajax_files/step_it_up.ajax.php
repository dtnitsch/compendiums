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
		if($k == 'start_date' || $k == 'end_date') { continue; }
		$v = trim($v);
		if($v == '') { continue; }

		// int search for grade
		if($k == "ss.grade_id" || $k == "pi.region_id") {
			$filters[] = " ". $k ." in (". $v .") ";

		// Condensed username search
		} else if($k == "user_name") {
			$v = str_replace(' ','%',$v);
			$filters[] = " (sa.firstname ||' '|| sa.lastname) ilike '%". $v ."%' ";

		// Everyone else
		} else {
			$v = str_replace(' ','%',$v);
			$filters[] = " ". $k ." ilike '%". $v ."%' ";
		}
	}

	$start_date = (!empty($_POST['filters']['start_date']) ? date('Y-m-d 00:00:00',strtotime($_POST['filters']['start_date'])) : date('Y-m-d 00:00:00',strtotime('Previous Sunday')));
	$end_date = (!empty($_POST['filters']['end_date']) ? date('Y-m-d 23:59:59',strtotime($_POST['filters']['end_date'])) : date('Y-m-d 23:59:59',strtotime('Saturday this Week')));

	if(!empty($filters)) {
		$where = ' where '. implode(' and ',$filters);
	}
}

$q = "
	select
		q.student_id
		,ss.user_id
		,q.total_score
		,(ss.firstname ||' '|| ss.lastname) as student_name
		,g.title as grade
		,pit.title as institution_type
		,pi.title as institution
		,pi.site_name
		,(sa.firstname ||' '|| sa.lastname) as user_name
		,sa.email as user_email
		,pi.city
		,r.\"2code\" as state
		,q.activity_count
		,to_char(ss.created, 'MM/DD/YYYY') as created
	from (
		select
	        pas.student_id
	        ,pas.institution_id
			,count(distinct activity_map_id) as activity_count
			,sum(calculated_score) as total_score
			,to_char(max(pas.created), 'MM/DD/YYYY') as last_played
		from public.activity_map as am
		join public.activity_scores as pas on
			pas.activity_map_id = am.id
			and am.activity_id = '109'
		where
			pas.created >= '". $start_date ."' and pas.created <= '". $end_date ."'
		group by
	        pas.student_id
	        ,pas.institution_id
	) as q
		join system.students as ss on
			ss.id = q.student_id
		join system.users as sa on
			sa.id = ss.user_id
		join public.institutions as pi on
			pi.id = q.institution_id
		join public.institution_types as pit
			on pi.institution_type_id = pit.id
		left join public.grades as g on
			g.id = ss.grade_id
		left join supplements.regions as r on 
			r.id = pi.region_id

	". $where ."
	". $order ."
	". $limit ."
";

$csv_q = $q;

/*$csv_q = "
	select
		q.*
		,ss.firstname
		,(sa.firstname ||' '|| sa.lastname) as user_name
		,sa.email as user_email
		,pi.title as institution
		,pi.site_name
		,pi.city
		,r.\"2code\" as state
		,pi.address1
		,pi.postal_code
		,pit.title as institution_type
		,to_char(ss.created, 'MM/DD/YYYY') as created
		,(case when ss.gender_id = 1 then 'Boy' when ss.gender_id = '2' then 'Girl' else '--' end) as gender
		,g.title as grade
	from (
		select
	        pas.student_id
	        ,pas.institution_id
			,count(distinct activity_map_id) as activity_count
			,sum(calculated_score) as total_score
			,to_char(max(pas.created), 'MM/DD/YYYY') as last_played
		from public.activity_map as am
		join public.activity_scores as pas on
			pas.activity_map_id = am.id
			and am.activity_id = '109'
		where
			pas.created >= '". $start_date ."' and pas.created <= '". $end_date ."'
		group by
	        pas.student_id
	        ,pas.institution_id
	) as q
		join system.students as ss on
			ss.id = q.student_id
		join system.users as sa on
			sa.id = ss.user_id
		join public.institutions as pi on
			pi.id = q.institution_id
		join public.institution_types as pit
			on pi.institution_type_id = pit.id
		left join public.grades as g on
			g.id = ss.grade_id
		left join supplements.regions as r on 
			r.id = pi.region_id

	". $where ."
	". $order ."
	". $limit ."
";*/


$res = pagination_ajax_query($q,"Getting Pagi Path");
$query = get_pagination_ajax_query();

$output[$_POST['table_id']] = array();
while ($row = db_fetch_row($res)) {

	if(empty($row['firstname'])) {
		$row['firstname'] = '--';
	}

	if(empty($row['user_name'])) {
		$row['user_name'] = '--';
	}

	if(empty($row['user_email'])) {
		$row['user_email'] = '--';
	}

	if(empty($row['institution'])) {
		$row['institution'] = '--';
	}

	//$row["created"] = date("m/d/Y", strtotime($row["created"]));
	//$row["last_played"] = date("m/d/Y", strtotime($row["last_played"]));

	$output[$_POST['table_id']][] = $row;

}

_error_debug("Ending Ajax",'',__LINE__,__FILE__);
echo json_encode(array(
	"output" => $output
	,"pagination" => pagination_return_results()
	#,"query" => urlencode(base64_encode(gzcompress($query,9)))
	,"query" => rtrim(strtr(base64_encode(gzdeflate($csv_q, 9)), '+/', '-_'), '=')
	,"debug" => ajax_debug()
));