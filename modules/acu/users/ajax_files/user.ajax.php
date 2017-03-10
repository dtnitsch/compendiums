<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);

LIBRARY("pagination_ajax.php");
pagination_ajax_setup($_POST["table_id"],$_POST["display_count"]);

$col = (!empty($_POST["col"]) ? $_POST["col"] : "");
$ord = (!empty($_POST["ord"]) ? $_POST["ord"] : "");
$order = ($col != "" ? " order by ". $col ." ". $ord : "");
$limit = " limit ". $_POST["display_count"];
if(strtolower($_POST["type"]) == "pagination") { $limit = ""; }

$columns = "su.id";
$group_by = 'su.id';

// $joins = "";
$where = '';
$student_join = '';
if(!empty($_POST['filters'])) {

	foreach($_POST['filters'] as $k => $v) {

		if ($v == "" || $k == "columns" || $k == "institution_ids") { continue; }

		if ($k == "institution") {
			$v = str_replace(' ','%',$v);
			$where .= " and pi.title ilike '%". $v ."%' ";	
		} else if ($k == "registration_type") {
			$v = str_replace(' ','%',$v);
			$where .= " and su.registration_type_id = ".$v." ";		
		} else {
			$v = str_replace(' ','%',$v);
			$where .= " and su.". $k ." ilike '%". $v ."%' ";
		}

	}

	if(!empty($_POST["filters"]["institution_ids"])) {

		$student_join = "
			join system.students as ss on
				ss.user_id = su.id
				and ss.institution_id in (". $_POST["filters"]["institution_ids"] .")
		";
	}

	if (!empty($_POST["filters"]["columns"])) {

		foreach($_POST["filters"]["columns"] as $k => $v) {

			if ($k == "registration_type") {
				// $joins .= " left join prt on prt.id = su.registration_type_id ";
				$columns .= ",prt.title as registration_type";
				$group_by .= ",prt.title";
			} else if ($k == "email") {
				$columns .= ",(case when su.email = '' or su.email is null then '--' else su.email end) as email";
				$group_by .= ",su.email";	
			} else if ($k == "heard_about") {
				$columns .= ",(case when su.marketing_source = '' or su.marketing_source is null then '--' else su.marketing_source end) as heard_about";
				$group_by .= ",pi.marketing_source";	
			} else if ($k == "institution") {
				$columns .= ",(case when pi.title = '' or pi.title is null then '--' else pi.title end) as institution";
				$group_by .= ",pi.title";	
			} else if ($k == "phone") {
				$columns .= ",(case when pi.phone = '' or pi.phone is null then '--' else pi.phone end) as phone";
				$group_by .= ",pi.phone";	
			} else if ($k == "created") {
				$columns .= ",to_char(su.created, 'MM/DD/YYYY') as created";
				$group_by .= ",su.created";
			} else if ($k == "modified") {
				$columns .= ",to_char(su.modified, 'MM/DD/YYYY') as modified";
				$group_by .= ",su.modified";		
			} else {
				$columns .= ",". $k;
				$group_by .= ",". $k;
			}

		}

	}

}

$q = "
	select
		". $columns ."

	from system.users as su
	left join (
		select
			s.user_id
			,min(institution_id) AS institution_id
		from system.students as s
		join public.institutions as pi on
			pi.id = s.institution_id
			and pi.active
			and (pi.title != '' or pi.title is not null)
		where
			s.user_id > 2
			and institution_id > 0
		group by
			s.user_id
	) as q on
		q.user_id = su.id
	". $student_join ."
	left join public.institutions as pi on
		pi.id = q.institution_id and pi.active
	left join public.institution_types as pit on
		pi.institution_type_id = pit.id
	left join public.registration_types as prt on
        prt.id = su.registration_type_id

	where
		su.active
		". $where ."
	group by
		". $group_by ."

		". $order ."
		". $limit ."
";
$res = pagination_ajax_query($q,"Getting Pagi User");
$query = get_pagination_ajax_query();

$output[$_POST["table_id"]] = array();

while ($row = db_fetch_row($res)) {

	if(empty($row['firstname'])) {
		$row['firstname'] = '--';
	}

	if(empty($row['lastname'])) {
		$row['lastname'] = '--';
	}

	$row["registration_type"] = (empty($row["registration_type"]) || $row["registration_type"] == 'null' ? '--' : $row["registration_type"]);
	$row["is_superadmin"] = (empty($row["is_superadmin"]) || $row["is_superadmin"] == 'f' ? 'False' : 'True');

	$output[$_POST["table_id"]][] = $row;

}

_error_debug("Ending Ajax",'',__LINE__,__FILE__);
echo json_encode(array(
	"output" => $output
	,"pagination" => pagination_return_results()
	,"query" => rtrim(strtr(base64_encode(gzdeflate($query, 9)), '+/', '-_'), '=')
	,"debug" => ajax_debug()
));
