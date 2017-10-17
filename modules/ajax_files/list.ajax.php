<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);

$output = [];
library("api.php");
$info = json_decode(call_api_function("get_list",$_POST['val']),1);

_error_debug("Ending Ajax",'',__LINE__,__FILE__);
echo json_encode(array(
	"output" => $info['lists'][$_POST['val']]
	,"debug" => ajax_debug()
));