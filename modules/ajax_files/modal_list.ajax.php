<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);



$q = "
	select
	    key
	    ,title
	from public.list
	where title ilike '%". db_prep_sql(trim($_POST['val'])) ."%'
	limit 5
";
$res = db_query($q,"Getting Titles");

$output = array();

while($row = db_fetch_row($res)) {

	if (empty($row["title"])) {
		$row["title"] = "--";
	}

	$output[] = $row;

}


_error_debug("Ending Ajax",'',__LINE__,__FILE__);
echo json_encode(array(
	"output" => $output
	,"debug" => ajax_debug()
));