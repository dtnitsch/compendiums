<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);

$GLOBALS['project_info']['path_data']['path'] = '/list/'.$_POST['val'].'/';
$GLOBALS['show_js_now'] = true;

$output = [];
$q = "
	select
		id
	    ,key
	    ,title
	from public.list
	where key ilike '%". db_prep_sql(trim($_POST['val'])) ."%'
";
$output["info"] = db_fetch($q,"Getting info");

$output["html"] = run_module('list_simple');
$x = strip_tags(show_js_code());

$output["js"] = $x;
$x = str_replace("\n","",$x);
preg_match('/(assets\[.*\}\;)/im',$x,$matches);
$matches[0] = preg_replace("/\s\s+/"," ",$matches[0]);
$output["assets"] = $matches[0];

_error_debug("Ending Ajax",'',__LINE__,__FILE__);
echo json_encode(array(
	"output" => $output
	,"debug" => ajax_debug()
));