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
$having = array();
$filters = array();
if(!empty($_POST['filters'])) {

	$start_date = (!empty($_POST['filters']['start_date']) ? date('Y-m-d 00:00:00',strtotime($_POST['filters']['start_date'])) : date('Y-m-d 00:00:00',strtotime('Previous Sunday')));
	$end_date = (!empty($_POST['filters']['end_date']) ? date('Y-m-d 23:59:59',strtotime($_POST['filters']['end_date'])) : date('Y-m-d 23:59:59',strtotime('Saturday this Week')));

	$filters[] = " sas.created_date >= '". $start_date ."' and sas.created_date <= '". $end_date ."' ";
	$filters[] = " sas.is_classroom = 't' ";

	if(!empty($_POST['filters']['population'])) {
		$having[] = " population >= '". $_POST['filters']['population'] ."'";
	}

	if(!empty($_POST['filters']['registered_count'])) {
		$having[] = " pi.active_registration_count >= '". $_POST['filters']['registered_count'] ."'";
	}

	if(!empty($filters)) {
		$where = 'where '.implode(' and ',$filters);
	}
	if(!empty($having)) {
		$having = 'having '.implode(' and ',$having);
	}
}
if(empty($where)) { $where = ""; }
if(empty($having)) { $having = ""; }

$q = "
	select
		tp.title as institution_type
		,pi.id as institution_id
		,pi.title as institution
		,pi.site_name
		,pi.city
		,sr.\"2code\" as state
		,sas.user_id
		,(sas.user_firstname || ' ' || sas.user_lastname) as user_name
		,sas.user_email
		,pi.phone
		,su.marketing_source as heard_about
		,pi.population
		,pi.active_registration_count as registered_count
		,round(((count(distinct sas.student_id) / (case when pi.active_registration_count = 0 then 1 else pi.active_registration_count end)::numeric) * 100), 2) as registered_percent
		,count(distinct sas.student_id) AS participating_count
		,round(((count(distinct sas.student_id) / (case when active_registration_count = 0 then 1 else active_registration_count end)::numeric) * 100), 2) as participating_percent
		,sum(sas.activity_count) as activities_played
		,sum(total_score) as total_score
		,(sum(total_score) / count(distinct sas.student_id)) as average_score
		,pi.created
	from public.summary_activity_scores as sas
	join public.institutions as pi on
		pi.id = sas.institution_id
		and institution_id > 0
	join supplements.regions as sr
		on pi.region_id = sr.id
	left join public.institution_types as tp
		on pi.institution_type_id = tp.id
	left join system.users as su on
		su.id = sas.user_id
	". $where ."
	group by
		tp.title
		,pi.id
		,pi.title
		,pi.city
		,pi.site_name
		,pi.phone
		,pi.created
		,pi.population
		,sas.user_firstname
		,sas.user_lastname
		,sas.user_id
		,sas.user_email
		,sr.\"2code\"
		,heard_about
	". $having ."

	". $order ."
	". $limit ."
";


$res = pagination_ajax_query($q,"Getting Pagi Path");
$query = get_pagination_ajax_query();

$output[$_POST['table_id']] = array();
while($row = db_fetch_row($res)) {
	if(empty($row['title'])) {
		$row['title'] = '--';
	}
	$row['participating_percent'] = number_format($row['participating_percent'],2).'%';
	// $row['registered_percent'] = number_format($row['registered_percent'],2).'%';
	$row['total_score'] = number_format($row['total_score'],0);
	$row['average_score'] = number_format($row['average_score'],0);
	$row['activities_played'] = number_format($row['activities_played'],0);

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