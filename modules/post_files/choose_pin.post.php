<?php

if(!empty($_POST['pin'])) {

	$pin = $_POST['pin'];
	$q = "
		select pin
		from system.users
		where id = '". db_prep_sql($_SESSION['user']['id']) ."'
	";
	$res = db_fetch($q,"Getting pin for comparison");

	if($res['pin'] == $_POST['pin']) {
		$_SESSION['user']['pin'] = $res['pin'];
		$path = (!empty($_SESSION['pin_redirect']) ? $_SESSION['pin_redirect'] : '/myaccount/');
		unset($_SESSION['pin_redirect']);
		safe_redirect($path);
	}

}