<?php

if(!empty($_SERVER['SCRIPT_URL']) && $_SERVER['SCRIPT_URL'] == "/xml/concentration.php") {
	$_REQUEST['game'] = "matchup";
} else if(!empty($_SERVER['SCRIPT_URL']) && $_SERVER['SCRIPT_URL'] == "/xml/jigsaw.php") {
	$_REQUEST['game'] = "jigsaw";
} else if(!empty($_SERVER['SCRIPT_URL']) && $_SERVER['SCRIPT_URL'] == "/xml/stepupmobilesubmit.php") {
	$_REQUEST['game'] = "stepitup";
} else if(!empty($_SERVER['SCRIPT_URL']) && $_SERVER['SCRIPT_URL'] == "/xml/login.php") {
	$_REQUEST['game'] = "";
	$_REQUEST['world'] = "login";
}

$api = 'questions';
if(!empty($_REQUEST['game'])) {
	if($_REQUEST['game'] == 'quiz_questions') {
		$api = 'questions';
	} else if($_REQUEST['game'] == 'skill_and_drill') {
		$api = 'skill_and_drill';
	} else if($_REQUEST['game'] == 'crossword') {
		$api = 'crossword';
	} else if($_REQUEST['game'] == 'word_search') {
		$api = 'word_search';
	} else if($_REQUEST['game'] == 'maze') {
		$api = 'maze';
	} else if($_REQUEST['game'] == 'tic_tac_know' || $_REQUEST['game'] == 'tictac') {
		$api = 'tic_tac_know';
	} else if($_REQUEST['game'] == 'matchup') {
		$api = 'matchup';
	} else if($_REQUEST['game'] == 'jigsaw') {
		$api = 'jigsaw';
	} else if($_REQUEST['game'] == 'stepitup') {
		$api = 'stepitup';
	}
} else {
	$world = strtolower(trim(!empty($_REQUEST['world']) ? $_REQUEST['world'] : ''));
	if($world == "sd") {
		$_REQUEST['world'] = substr($_REQUEST['genre'],0,-1);
		$_REQUEST['game'] = "skill_and_drill";
		$api = 'skill_and_drill';

	} else if($world == "cross") {
		$_REQUEST['world'] = substr($_REQUEST['genre'],0,-1);
		$_REQUEST['game'] = "crossword";
		$api = 'crossword';

	} else if($world == "ws") {
		$_REQUEST['world'] = substr($_REQUEST['genre'],0,-1);
		$_REQUEST['game'] = "word_search";
		$api = 'word_search';
	} else if($world == "login") {
		$api = 'students';
	}

}
header('Location: /api/'. $api .'/?'. $_SERVER['QUERY_STRING']);
die();