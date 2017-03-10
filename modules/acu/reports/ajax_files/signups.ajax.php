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
		if($k == "user_name") {
			$v = str_replace(' ','%',$v);
			$filters[] = " (sa.firstname ||' '|| sa.lastname) ilike '%". $v ."%' ";

		} else if($k == "student_name") {
			$v = str_replace(' ','%',$v);
			$filters[] = " (ss.firstname ||' '|| ss.lastname) ilike '%". $v ."%' ";

		// Everyone else
		} else if($k == "ss.grade_id" || $k == 'pi.region_id' || $k == 'pi.institution_type_id') {
			$v = (double)$v;
			$filters[] = " ". $k ." in (". $v .") ";
		} else {
			$v = str_replace(' ','%',$v);
			$filters[] = " ". $k ." ilike '%". $v ."%' ";
		}
	}

	$start_date = (!empty($_POST['filters']['start_date']) ? date('Y-m-d 00:00:00',strtotime($_POST['filters']['start_date'])) : date('Y-m-d 00:00:00',strtotime('Previous Sunday')));
	$end_date = (!empty($_POST['filters']['end_date']) ? date('Y-m-d 23:59:59',strtotime($_POST['filters']['end_date'])) : date('Y-m-d 23:59:59',strtotime('Saturday this Week')));

	$filters[] = " sa.created >= '". $start_date ."' and sa.created <= '". $end_date ."' ";

	if(!empty($filters)) {
		$where = 'where '.implode(' and ',$filters);
	}
}

$q = "
	select
		sa.id as id
		,sa.firstname
		,sa.lastname
		,sa.email
		,pit.title as registration_type
		,pi.id as institution_id
		,pi.title as institution
		,pi.site_name as site_name
		,pi.city
		,pi.population
		,sr.\"2code\" as state
		,to_char(sa.created, 'MM/DD/YYYY') as created
		,count(ss.id) as registered
		,sa.marketing_source as heard_about
		,sa.active
	from system.students as ss
	join system.users as sa on
		sa.id = ss.user_id
	join public.institutions as pi on
		pi.id = ss.institution_id
	join public.institution_types as pit
		on pi.institution_type_id = pit.id
	left join supplements.regions as sr
		on pi.region_id = sr.id
	". $where ."
	group by
		sa.id
		,pi.id
		,pit.title
		,sr.id
		,heard_about
		,site_name
	". $order ."
	". $limit ."
";

$res = pagination_ajax_query($q,"Getting Pagi Path");
$query = get_pagination_ajax_query();

$output[$_POST['table_id']] = array();
while ($row = db_fetch_row($res)) {

	if (empty($row['firstname'])) {
		$row['firstname'] = '--';
	}

	if (empty($row['user_name'])) {
		$row['user_name'] = '--';
	}

	if (empty($row['email'])) {
		$row['email'] = '--';
	}

	if (empty($row['institution'])) {
		$row['institution'] = '--';
	}

	if (empty($row['population'])) {
		$row['population'] = '--';
	}

	if (empty($row['registered']) || is_null($row['registered'])) {
		$row['registered'] = 0;
	}

	if ($row['city'] == 'null' || is_null($row['city'])) {
		$row['city'] = '';
	}

	if ($row['state'] == 'null' || is_null($row['state'])) {
		$row['state'] = '';
	}

	//$row["created"] = date("m/d/Y", strtotime($row["created"]));

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