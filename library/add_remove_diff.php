<?php
function speed_diff($core, $against) {
	$against = array_flip($against);
	foreach ($core as $key => $value) {
		if(isset($against[$value])) { unset($core[$key]); }
	}
	return $core;
}

function add_remove_diff($a1,$a2) {
	return array(speed_diff($a2,$a1),speed_diff($a1,$a2));
}

/*
Example ::

$a1 = array('a'=>1,2,5,7,10,20,25);
$a2 = array('a'=>1,2,5,20,25,30);

list($add,$remove) = add_remove_diff($a1,$a2);
Output: 
	$add: 30
	$remove: 7,10
*/

?>