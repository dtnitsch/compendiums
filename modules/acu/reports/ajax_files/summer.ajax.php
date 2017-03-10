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

	if(!empty($filters['core'])) {
		$where = 'where '.implode(' and ',$filters['core']);
	}

	// if(!empty($filters['other'])) {
	// 	$where_other = 'where '.implode(' and ',$filters['other']);
	// }
}

$q = "
	SELECT
		ts.student_id as student_id
		,ts.user_id as user_id
		,ws.total_score as worlds_score
		,ss.total_score as siu_score
		,ts.total_score as total_score
		,ts.firstname as firstname
		,ts.lastname as lastname
		,ts.grade as grade
		,ts.institution_type as institution_type
		,ts.institution as institution
		,ts.site_name as site_name
		,ts.user_name as user_name
		,ts.user_email as user_email
		,ts.city as city
		,ts.state as state
		,ts.activity_count as activity_count
		,ts.created as created
		,ts.registered_count as registered_count
		,ts.institution_id as institution_id
		,ts.institution_phone as institution_phone
		,sysu.phone1 as user_phone
	FROM
		(SELECT
			sas.student_id
			,tp.title AS institution_type
			,to_char(pi.created, 'MM/DD/YYYY') AS created
			,pi.active_registration_count AS registered_count
			,pi.phone AS institution_phone
			,sas.student_firstname AS firstname
			,sas.student_lastname AS lastname
			,sas.grade as grade
			,(sas.user_firstname || ' ' || sas.user_lastname) AS user_name
			,sas.user_email as user_email
			,sas.user_id as user_id
			,sas.institution as institution
			,sas.institution_id as institution_id
			,sum(sas.total_score) AS total_score
			,sum(sas.activity_count) AS activity_count
			,sr.\"2code\" AS state
			,count(DISTINCT sas.student_id) AS participating_count
			,pi.site_name as site_name
			,pi.city as city
		FROM public.summary_activity_scores AS sas
	    JOIN public.institutions AS pi ON
		    pi.id = institution_id
		LEFT JOIN public.institution_types AS tp
			ON pi.institution_type_id = tp.id
		LEFT JOIN supplements.regions AS sr
			ON pi.region_id = sr.id
	    ". $where ."
    	". $where_other ."
		GROUP BY
			sas.student_id
			,tp.title
			,pi.created
			,pi.active_registration_count
			,pi.phone
			,sas.student_firstname
			,sas.student_lastname
			,sas.user_firstname
			,sas.user_lastname
			,sas.grade
			,sas.user_email
			,sas.user_id
			,sas.institution_id
			,sas.institution
			,sr.\"2code\"
			,pi.site_name
			,pi.city) as ts
	LEFT JOIN
		(SELECT
			sas.student_id
			,tp.title AS institution_type
			,to_char(pi.created, 'MM/DD/YYYY') AS created
			,pi.active_registration_count AS registered_count
			,sas.student_firstname AS firstname
			,sas.student_lastname AS lastname
			,sas.grade as grade
			,(sas.user_firstname || ' ' || sas.user_lastname) AS user_name
			,sas.user_email as user_email
			,sas.user_id as user_id
			,sas.institution as institution
			,sas.institution_id as institution_id
			,sum(sas.total_score) AS total_score
			,sum(sas.activity_count) AS activity_count
			,count(DISTINCT sas.student_id) AS participating_count
		FROM public.summary_activity_scores AS sas
	    JOIN public.institutions AS pi ON
		    pi.id = institution_id
		LEFT JOIN public.institution_types AS tp
			ON pi.institution_type_id = tp.id
		LEFT JOIN supplements.regions AS sr
			ON pi.region_id = sr.id
	    ". $where ."
    	". $where_other ."
	    	and sas.activity_id = '109'
		GROUP BY
			sas.student_id
			,tp.title
			,pi.created
			,pi.active_registration_count
			,sas.student_firstname
			,sas.student_lastname
			,sas.user_firstname
			,sas.user_lastname
			,sas.grade
			,sas.user_email
			,sas.user_id
			,sas.institution_id
			,sas.institution) as ss on
		 ss.student_id = ts.student_id
	LEFT JOIN
		(SELECT
			sas.student_id
			,tp.title AS institution_type
			,to_char(pi.created, 'MM/DD/YYYY') AS created
			,pi.active_registration_count AS registered_count
			,sas.student_firstname AS firstname
			,sas.student_lastname AS lastname
			,sas.grade as grade
			,(sas.user_firstname || ' ' || sas.user_lastname) AS user_name
			,sas.user_email as user_email
			,sas.user_id as user_id
			,sas.institution as institution
			,sas.institution_id as institution_id
			,sum(sas.total_score) AS total_score
			,sum(sas.activity_count) AS activity_count
			,count(DISTINCT sas.student_id) AS participating_count
		FROM public.summary_activity_scores AS sas
	    JOIN public.institutions AS pi ON
		    pi.id = institution_id
		LEFT JOIN public.institution_types AS tp
			ON pi.institution_type_id = tp.id
		LEFT JOIN supplements.regions AS sr
			ON pi.region_id = sr.id
	    ". $where ."
    	". $where_other ."
	    	and sas.activity_id != '109'
		GROUP BY
			sas.student_id
			,tp.title
			,pi.created
			,pi.active_registration_count
			,sas.student_firstname
			,sas.student_lastname
			,sas.user_firstname
			,sas.user_lastname
			,sas.grade
			,sas.user_email
			,sas.user_id
			,sas.institution_id
			,sas.institution) as ws on
		 ws.student_id = ts.student_id
	LEFT JOIN
		system.users as sysu on
		sysu.id = ts.user_id
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

	if (empty($row["user_phone"])) {
		$row["user_phone"] = "--";
	}

	if (empty($row["institution"])) {
		$row["institution"] = "--";
	}

	if (empty($row["institution_phone"])) {
		$row["institution_phone"] = "--";
	}

	if (empty($row["worlds_score"])) {
		$row["worlds_score"] = "--";
	} else {
		$row["worlds_score"] = number_format($row["worlds_score"], 0);
	}

	if (empty($row["siu_score"])) {
		$row["siu_score"] = "--";
	} else {
		$row["siu_score"] = number_format($row["siu_score"], 0);
	}

	if (empty($row["total_score"])) {
		$row["total_score"] = "--";
	} else {
		$row["total_score"] = number_format($row["total_score"], 0);
	}


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
