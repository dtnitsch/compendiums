<?php

$routes = array(
	'get' => array(
		'static' => array(
		// 	'/questions/' => 'get_questions'
		// 	,'/skill_and_drill/' => 'get_skill_and_drill'
		// 	,'/crossword/' => 'get_crosswords'
		// 	,'/word_search/' => 'get_word_search'
		// 	,'/maze/' => 'get_maze'
		// 	,'/tic_tac_know/' => 'get_tic_tac_know'
		// 	,'/matchup/' => 'get_matchup'
		// 	,'/jigsaw/' => 'get_jigsaw'
		// 	,'/stepitup/' => 'get_stepitup'
		// 	,'/students/' => 'get_students'
			
		// 	,'/scores/save/' => 'put_scores'
		)
		,'dynamic' => array(
			'/lists/raw/:values' => 'get_raw_list_csv'
			,'/lists/:values' => 'get_list_csv'
		)
	)
	// ,'post' => array (
	// 	'static' => array(
	// 		'/users/' => 'add_user'
	// 	)
	// )
);

// krsort($routes['get']['static']);
// krsort($routes['post']['static']);
