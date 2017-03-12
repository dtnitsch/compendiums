<?php
function create_key() {
	$u = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$l = "abcdefghijklmnopqrstuvwxyz";
	$output = '';
	while(strlen($output) < 10) {
		$r = mt_rand(1,3);
		if($r == 1) { $output .= $u[mt_rand(0,25)]; }
		else if($r == 2) { $output .= $l[mt_rand(0,25)]; }
		else { $output .= mt_rand(0,9); }
	}
	return $output;
}