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
$having = array();
if(!empty($_POST['filters'])) {
	$filters = array();
	foreach($_POST['filters'] as $k => $v) {
		if($k == 'start_date' || $k == 'end_date' || $k == 'population' || $k == 'registered_count') { continue; }
		$v = trim($v);
		if($v == '') { continue; }

		if($k == 'pi.region_id') {
			$v = (double)$v;
			$filters[] .= " ". $k ." = '". $v ."' ";

		// Everyone else
		} else {
			$v = str_replace(' ','%',$v);
			$filters[] = " ". $k ." ilike '%". $v ."%' ";
		}
	}

	$start_date = (!empty($_POST['filters']['start_date']) ? date('Y-m-d 00:00:00',strtotime($_POST['filters']['start_date'])) : date('Y-m-d 00:00:00',strtotime('Previous Sunday')));
	$end_date = (!empty($_POST['filters']['end_date']) ? date('Y-m-d 23:59:59',strtotime($_POST['filters']['end_date'])) : date('Y-m-d 23:59:59',strtotime('Saturday this Week')));

	$filters[] = " sas.created_date >= '". $start_date ."' and sas.created_date <= '". $end_date ."' ";
	$filters[] = " sas.activity_id != 109 ";

	//$filter[] = " pi.active ";


	if(isset($_POST['filters']['population'])) {
		$having[] = " population >= '". $_POST['filters']['population'] ."'";
	}

	if(isset($_POST['filters']['registered_count'])) {
		$having[] = " pi.active_registration_count >= '". $_POST['filters']['registered_count'] ."'";
	}

	if(!empty($filters)) {
		$where = 'where '.implode(' and ',$filters);
	}
	if(!empty($having)) {
		$having = 'having '.implode(' and ',$having);
	}
}

$q = "
	select
		pi.id as institution_id
		,pi.title as institution
		,pi.city
		,sr.\"2code\" as state
		,pi.phone
		,pi.created
		,MAX(su.marketing_source) as heard_about
		,pi.population
		,pi.active_registration_count as registered_count
		,round(((pi.active_registration_count / (case when pi.population = 0 then 1 else pi.population end)::numeric) * 100), 2) as registered_percent
		,count(distinct sas.student_id) AS participating_count
		,((count(distinct sas.student_id) / (case when pi.active_registration_count = 0 then 1 else active_registration_count end)::numeric) * 100) as participating_percent
		,sum(sas.activity_count) as activities_played
		,sum(total_score) as total_score
		,(sum(total_score) / count(distinct sas.student_id)) as average_score
	from public.summary_activity_scores as sas
	join public.institutions as pi on
		pi.id = sas.institution_id
		and institution_id > 0
	join public.institution_types as tp on
        pi.institution_type_id = tp.id
        and tp.alias = 'school'
	join supplements.regions as sr
		on pi.region_id = sr.id
	left join system.users as su on
		su.id = sas.user_id
	". $where ."
	group by
		pi.id
		,pi.title
		,pi.city
		,pi.phone
		,pi.created
		,pi.population
		,sr.\"2code\"
		,registered_count
	". $having ."

	". $order ."
	". $limit ."
";

/*
	select
		pi.id
		,pi.title as institution
		,pi.city
		,pi.phone
		,pi.email
		,pi.created
		,pi.population
		,pi.active_registration_count as registered_count
		,((pi.active_registration_count / (case when pi.population = 0 then 1 else pi.population end)::numeric) * 100) as registered_percent
		,sr.\"2code\" as state
		,count(sas.institution_id) as participating_count
		,((count(sas.institution_id) / (case when pi.population = 0 then 1 else pi.population end)::numeric) * 100) as participating_percent
		,sum(sas.activity_count) as activities_played
		,sum(original_score) as original_score
		,sum(total_score) as total_score
		,(sum(total_score) / count(sas.institution_id)) as average_score
	from public.institutions as pi
	join public.institution_types as tp on
        pi.institution_type_id = tp.id
        and tp.alias = 'school'
	join supplements.regions as sr
		on pi.region_id = sr.id
	left join public.summary_activity_scores as sas on
		pi.id = sas.institution_id
        and sas.created_date >= '". $start_date ."' and sas.created_date <= '". $end_date ."'
        and sas.institution_id > 0
	". $where ."
	group by
		pi.id
		,pi.title
		,pi.city
		,pi.phone
		,pi.created
		,pi.population
		,sr.\"2code\"
	". $having ."

	". $order ."
	". $limit ."
*/


$res = pagination_ajax_query($q,"Getting Pagi Path");
$query = get_pagination_ajax_query();

$output[$_POST['table_id']] = array();
while($row = db_fetch_row($res)) {
	if(empty($row['title'])) {
		$row['title'] = '--';
	}
	if(empty($row['registered_percent']) || is_null($row['registered_percent'])) {
		$row['registered_percent'] = 0;
	}
	$row['registered_percent'] = number_format($row['registered_percent'],2);

	if(empty($row['participating_percent']) || is_null($row['participating_percent'])) {
		$row['participating_percent'] = 0;
	}
	$row['participating_percent'] = number_format($row['participating_percent'],2);

	if(empty($row['total_score']) || is_null($row['total_score'])) {
		$row['total_score'] = 0;
	}
	if(empty($row['average_score']) || is_null($row['average_score'])) {
		$row['average_score'] = 0;
	}

	$row['average_score'] = number_format($row['average_score'], 0);
	$row['total_score'] = number_format($row['total_score'], 0);

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