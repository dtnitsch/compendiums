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
		} else {
			$v = str_replace(' ','%',$v);
			$filters[] .= " ". $k ." ilike '%". $v ."%' ";
		}
	}

	$start_date = (!empty($_POST['filters']['start_date']) ? date('Y-m-d 00:00:00',strtotime($_POST['filters']['start_date'])) : date('Y-m-d 00:00:00',strtotime('Previous Sunday')));
	$end_date = (!empty($_POST['filters']['end_date']) ? date('Y-m-d 23:59:59',strtotime($_POST['filters']['end_date'])) : date('Y-m-d 23:59:59',strtotime('Saturday this Week')));

	$filters[] = " sas.created_date >= '". $start_date ."' and sas.created_date <= '". $end_date ."' ";
	$filters[] = " sas.activity_id != 109 ";

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
		,sas.user_id
		,(sas.user_firstname || ' ' || sas.user_lastname) as user_name
		,sas.user_email
		,pi.phone
		,pi.city
		,sr.\"2code\" as state
		,pi.created
		,su.marketing_source as heard_about
		,pi.population
		,pi.active_registration_count as registered_count
		,count(distinct sas.student_id) AS participating_count
		,sum(sas.activity_count) as activities_played
		,sum(total_score) as total_score
		,(sum(total_score) / count(distinct sas.student_id)) as average_score
	from public.summary_activity_scores as sas
	join public.institutions as pi on
		pi.id = sas.institution_id
		and institution_id > 0
	join public.institution_types as tp on
        pi.institution_type_id = tp.id
        and tp.alias = 'homeschool'
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
		,pi.marketing_source
		,sr.\"2code\"
		,sas.user_firstname
		,sas.user_lastname
		,sas.user_email
		,sas.user_id
		,heard_about
	". $having ."

	". $order ."
	". $limit ."
";

/*
	select
		q.id
		,q.institution
		,q.city
		,q.phone
		,q.email
		,q.created
		,q.population
		,q.state
	    ,max(q.participating) as participating_count
	    ,((max(q.participating) / q.population::numeric) * 100) as participating_percent
		,max(q.activities_played) as activities_played
		,max(q.original_score) as original_score
		,max(q.total_score) as total_score
	    ,max(q.average_score) as average_score
		,count(ss.id) as registered_count
		,((count(ss.id) / q.population::numeric) * 100) registered_percent
	from (
		select
			q.id
			,q.institution
			,q.city
			,q.phone
			,q.email
			,q.created
			,q.population
			,q.state
		    ,count(q.student_id) as participating
			,sum(q.activities_played) as activities_played
			,sum(q.original_score) as original_score
			,sum(q.total_score) as total_score
		    ,(sum(q.total_score) / sum(q.activities_played)) as average_score
		from (
			select
				pi.id
				,pi.title as institution
				,pi.city
				,pi.phone
				,pi.email
				,pi.created
				,pi.population
				,sr.\"2code\" as state
				,ss.id as student_id
				,count(pas.id) as activities_played
				,sum(original_score) as original_score
				,sum(calculated_score) as total_score
			from public.institutions as pi
			join public.institution_types as tp on
		        pi.institution_type_id = tp.id
		        and tp.alias = 'homeschool'
			join supplements.regions as sr
				on pi.region_id = sr.id
		--	left join system.users as sa on
		--		sa.id = ss.user_id
			left join public.activity_scores as pas on
				pi.id = pas.institution_id
		        and pas.created >= '". $start_date ."' and pas.created <= '". $end_date ."'
		        and pas.institution_id > 0
			left join system.students as ss on
				ss.id = pas.student_id
				and ss.id > 0
			". $where ."
			group by
				pi.id
				,pi.title
				,pi.city
				,pi.phone
				,pi.created
				,pi.population
				,sr.\"2code\"
				,ss.id
		) as q
		group by
			q.id
			,q.institution
			,q.city
			,q.phone
			,q.email
			,q.created
			,q.population
			,q.state
	) as q
	join system.students as ss on
		ss.institution_id = q.id
	group by
		q.id
		,q.institution
		,q.city
		,q.phone
		,q.email
		,q.created
		,q.population
		,q.state
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
	$row['participating_percent'] = number_format($row['participating_percent'],2).'%';
	$row['registered_percent'] = number_format($row['registered_percent'], 2).'%';
	$row['total_score'] = number_format($row['total_score'], 0);
	$row['average_score'] = number_format($row['average_score'], 0);
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