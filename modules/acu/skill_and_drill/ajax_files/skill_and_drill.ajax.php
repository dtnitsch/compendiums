<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);

LIBRARY('pagination_ajax.php');
PAGINATION_AJAX_SETUP($_POST['table_id'],$_POST['display_count']);

$col = (!empty($_POST['col']) ? $_POST['col'] : 'question');
$ord = (!empty($_POST['ord']) ? $_POST['ord'] : 'asc');
$order = ($col != '' ? ' order by '. $col .' '. $ord : '');
$limit = ' limit '. $_POST['display_count'];
if(strtolower($_POST['type']) == 'pagination') { $limit = ''; }

$where = '';
$where_sub = '';
if(!empty($_POST['filters'])) {
	foreach($_POST['filters'] as $k => $v) {
		$v = trim($v);
		if($v == '') { continue; }
		if(strpos($k,'modified') !== false) {
			$where .= " and ". $k ."::text like '%". $v ."%' ";

		} else if(strpos($k,'answer') !== false) {
			$where_sub .= " answers[1] ilike '%". $v ."%' or answers[2] ilike '%". $v ."%' or answers[3] ilike '%". $v ."%' ";

		} else {
			//$where .= " and ". $k ." ilike '%". $v ."%' ";
			if($k == 'sdm.world_id' || $k == 'sdm.generation_id' || $k == 'sdm.theme_id')  {
				$v = (double)$v;
				$where .= " and ". $k ." = '". $v ."' ";
			} else {
				$where .= " and ". $k ." ilike '%". $v ."%' ";
			}

		}
		/*
		if($k == 'sdm.world_id' || $k == 'sdm.generation_id' || $k == 'sdm.theme_id')  {
			$v = (double)$v;
			$where .= " and ". $k ." = '". $v ."' ";
		} else {
			$where .= " and ". $k ." ilike '%". $v ."%' ";
		}
		*/
	}
}

// $q = "
// 	select
// 		sd.id
// 		,sd.question
// 		,sd.modified
// 		,array_agg(sda.answer) AS answers
// 	from activities.skill_and_drill as sd
// 	join activities.skill_and_drill_answers as sda on
// 		sda.skill_and_drill_id = sd.id
// 	where
// 		sd.active 
// 		". $where ."
// 	group by
// 		sd.id
// 		,sd.question
// 		,sd.modified
// 		". $order ."
// 		". $limit ."
// ";
$q = "
SELECT
    q.*
    ,answers[1] AS answer1
    ,answers[2] AS answer2
    ,answers[3] AS answer3
FROM (
    SELECT
		sd.id
		,sd.question
		,to_char(sd.modified, 'MM/DD/YYYY') as modified
		,array_agg(sda.answer) AS answers
	FROM activities.skill_and_drill AS sd
	JOIN activities.skill_and_drill_answers AS sda ON
		sda.skill_and_drill_id = sd.id
	join activities.skill_and_drill_map as sdm on 
		sdm.skill_and_drill_id = sd.id
 	where
 		sd.active 
 		". $where ."
	GROUP BY
		sd.id
		,sd.question
		,sd.modified
) AS q
	". (!empty($where_sub) ? " where ". $where_sub : '') ."
	". $order ."
	". $limit ."
";

$res = pagination_ajax_query($q,"Getting Pagi Path");
$query = get_pagination_ajax_query();

$output[$_POST['table_id']] = array();

while ($row = db_fetch_row($res)) {

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