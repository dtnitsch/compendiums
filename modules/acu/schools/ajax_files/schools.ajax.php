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

		if ($k == 'pi.region_id')  {
			$v = (double)$v;
			$filters[] .= " ". $k ." = '". $v ."' ";
		} else if ($k == 'tp.id') {
			$v = (double)$v;
			$filters[] .= " ". $k ." = '". $v ."' ";	
		} else {
			//$v = str_replace( " ", "%", $v);
			$filters[] .= " ". $k ." ilike '%". str_replace(" ","%",$v) ."%' ";
		}


		//$v = str_replace(" ","%",$v)
		//$filters[] .= " ". $k ." ilike '%". $v ."%' ";

		//$v = 
		//$filters[] .= " ". $k ." ilike '%". str_replace(" ","%",$v) ."%' ";
	}

			

		



	/* if(!empty($_POST['filters']['start_date'])) {
		$filters[] = " pi.created >= '". date('Y-m-d 00:00:00',strtotime($_POST['filters']['start_date'])) ."' ";
	}

	if(!empty($_POST['filters']['end_date'])) {
		$filters[] = " pi.created <= '". date('Y-m-d 00:00:00',strtotime($_POST['filters']['end_date'])) ."' ";
	} */


	// $start_date = (!empty($_POST['filters']['start_date']) ? date('Y-m-d 00:00:00',strtotime($_POST['filters']['start_date'])) : date('Y-m-d 00:00:00',strtotime('Previous Sunday')));
	// $end_date = (!empty($_POST['filters']['end_date']) ? date('Y-m-d 23:59:59',strtotime($_POST['filters']['end_date'])) : date('Y-m-d 23:59:59',strtotime('Saturday this Week')));
	// $where[] = " pi.created >= '". $start_date ."' and pi.created <= '". $end_date ."' ";

	//$where[] = " pi.active  ";

	if(!empty($filters)) {
		$where = 'where '.implode(' and ',$filters);
	}

}

	// left join system.students as st
	// 	on pi.id = st.institution_id 

	// group by
	// 	pi.id
	// 	,pi.title
	// 	,pi.city
	// 	,pi.phone
	// 	,pi.created
	// 	,sr.\"2code\"
$q = "
	select
		pi.id
		,pi.title as institution
		,pi.city
		,sr.\"2code\" as state
		,pi.phone
		,pi.population
		,tp.title as institution_type
		,to_char(pi.created, 'MM/DD/YYYY') as created
	from public.institutions as pi
	join public.institution_types as tp 
		on pi.institution_type_id = tp.id
	left join supplements.regions as sr
		on pi.region_id = sr.id
		". $where ."
		". $order ."
		". $limit ."

";

$res = pagination_ajax_query($q,"Getting Pagi Path");
$query = get_pagination_ajax_query();

$output[$_POST['table_id']] = array();

while ($row = db_fetch_row($res)) {

	if (empty($row["title"])) {
		$row["title"] = "--";
	}

	if (empty($row['population']) || is_null($row['population'])) {
		$row['population'] = 0;
	} else {
		$row['population'] = number_format($row['population'], 0);
	}


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