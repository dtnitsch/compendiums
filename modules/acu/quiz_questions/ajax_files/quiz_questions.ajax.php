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
if(!empty($_POST['filters'])) {
	foreach($_POST['filters'] as $k => $v) {
		$v = trim($v);
		if($v == '') { continue; }
		
		if($k == 'qqm.world_id' || $k == 'qqm.grade_id' || $k == 'qq.id' || $k == 'qqm.generation_id' || $k == 'qqm.theme_id')  {
			$v = (double)$v;
			$where .= " and ". $k ." = '". $v ."' ";

		} else {
			$v = str_replace( " ", "%", $v);
			$where .= " and ". $k ." ilike '%". $v ."%' ";

		}
	}
}

$q = "
	select
		qq.number AS question_number
		,qq.id
		,qq.question
		,string_agg(DISTINCT g.alias,', ') AS generation
		,string_agg(DISTINCT gr.alias,', ') AS grade
		,string_agg(DISTINCT w.alias,', ') AS world
		,string_agg(DISTINCT t.alias,', ') AS theme
		,to_char(qq.modified, 'MM/DD/YYYY') AS modified 
	FROM activities.quiz_questions AS qq
	LEFT JOIN activities.quiz_question_map AS qqm ON 
		qqm.quiz_question_id = qq.id
	LEFT JOIN public.generations AS g ON
		qqm.generation_id = g.id 
	LEFT JOIN public.worlds AS w ON
		qqm.world_id = w.id 
	LEFT JOIN public.themes AS t ON
		qqm.theme_id = t.id 
	LEFT JOIN public.grades AS gr ON
		qqm.grade_id = gr.id 
	where
		qq.active 
		". $where ."
	GROUP BY 
		question_number
		,qq.id
		,w.id
		,t.id
		
		". $order ."
		". $limit ."
";
$res = pagination_ajax_query($q,"Getting Pagi Path");
$query = get_pagination_ajax_query();

$output[$_POST['table_id']] = array();
$cnt = 1;

while ($row = db_fetch_row($res)) {

	$row["series"] = $cnt++;

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