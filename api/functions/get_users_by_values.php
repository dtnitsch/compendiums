<?php 

function get_users_by_values($values) {
	
	echo "<p>". __FUNCTION__;
	$values = explode('/',$values);

	$ids = array();
	$aliases = array();
	$emails = array();

	foreach($values as $value) {
		$tmp = trim($value);
		if(empty($tmp)) { continue; }
		if(is_numeric($tmp)) {
			$ids[] = $tmp;
		} else if(filter_var($tmp, FILTER_VALIDATE_EMAIL)){
			$emails[] = $tmp;
		} else {
			$aliases[] = $tmp;
		}
	}

	echo "<pre>";
	print_r($ids);
	print_r($emails);
	print_r($aliases);
	echo "</pre>";
	die();

}