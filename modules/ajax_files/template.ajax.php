<?php
_error_debug("Starting Ajax");

ob_start();

$data = json_decode(base64_decode($_POST["z"]),true);
$note = base64_encode(strip_tags($_POST["note"]));
$now = date("Y-m-d H:i:s");
$q = "
	insert into xxx (user_id,path_id,identifier,content,created,modified) values
	('". $data['user_id'] ."','". $data['path_id'] ."','". $data['identifier'] ."','". $note ."','". $now ."','". $now ."')
";
db_query($q,"Inserting new site wide note");

echo json_encode(array(
	"note" => nl2br(strip_tags($_POST["note"]))
	,"datetime" => date("m/d/Y g:i a")
));

$output = ob_get_clean();
echo json_encode(array(
	"output"=>$output
	,"debug"=>ajax_debug()
));
