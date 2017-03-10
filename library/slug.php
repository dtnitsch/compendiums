<?php
function convert_to_alias($val) {
	$val = strtolower(trim($val));

	$val = preg_replace('/[^0-9A-Za-z-_\s]+/','',$val);
	$val = preg_replace('/\s+/',' ',$val);
	$val = str_replace(' ','_',$val);
	return $val;
}

function convert_to_slug($val) {
	$val = strtolower(trim($val));

	$val = preg_replace('/[^0-9A-Za-z-_\s]+/','',$val);
	$val = preg_replace('/\s+/',' ',$val);
	$val = str_replace(' ','-',$val);
	return $val;
}