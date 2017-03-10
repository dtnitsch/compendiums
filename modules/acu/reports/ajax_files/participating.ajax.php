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

	$filters[] = " tp.alias = 'school' ";
	$filters[] = " sas.institution_id > 0 ";
	$filters[] = " sas.created_date >= '". $start_date ."' and sas.created_date <= '". $end_date ."' ";

	//$filter[] = " pi.active ";

	if(!empty($filters)) {
		$where = 'where '.implode(' and ',$filters);
	}

}

$q = "
	select
		pi.id
		,pi.title as institution
		,pi.city
		,pi.phone
		,pi.created
		,pi.population
		,pi.active_registration_count as registered_count
		,sr.\"2code\" as state
		,count(distinct sas.student_id) AS participating_count
	from public.summary_activity_scores as sas
	join public.institutions as pi on
	    pi.id = sas.institution_id
	left join public.institution_types as tp
		on pi.institution_type_id = tp.id
	left join supplements.regions as sr
		on pi.region_id = sr.id
    ". $where ."
	group by
		pi.id
		,pi.title
		,pi.city
		,pi.phone
		,pi.created
		,pi.population
		,sr.\"2code\"

	". $order ."
	". $limit ."

";


	// select 
	// 	pi.id
	// 	,pi.title as institution
	// 	,pi.city
	// 	,pi.phone
	// 	,pi.created
	// 	,pi.population
	// 	,sr.\"2code\" as state
	// 	,count(distinct ss.institution_id) as registered_count
	// 	,sum(original_score) as original_score
	// 	,sum(calculated_score) as total_score
	// from public.activity_scores as pas
	// join system.students as ss on
	// 	ss.id = pas.student_id
	// left join system.users as sa on
	// 	sa.id = ss.user_id
	// left join public.institutions as pi on
	// 	pi.id = ss.institution_id
	// left join supplements.regions as sr
	// 	on pi.region_id = sr.id
	// left join public.institution_types as tp 
	// 	on pi.institution_type_id = tp.id
	// 	". $where ."
	// group by
	// 	pi.id
	// 	,pi.title
	// 	,pi.city
	// 	,pi.phone
	// 	,pi.created
	// 	,pi.population
	// 	,sr.\"2code\"

	// ". $order ."
	// ". $limit ."

$res = pagination_ajax_query($q,"Getting Pagi Path");
$query = get_pagination_ajax_query();

$output[$_POST['table_id']] = array();
while($row = db_fetch_row($res)) {
	if(empty($row['title'])) {
		$row['title'] = '--';
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