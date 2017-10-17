<?php

$routes = array(
	'GET' => array(
		'static' => array(
			// '/lists/' => 'get_lists'
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
			'/list/:values' => 'get_list'
			'/collection/:values' => 'get_collection'
		)
	)
	// ,'POST' => array(
	// 	,'dynamic' => array(
	// 	'/list/:values' => 'get_list'
	// 	)
	// )
// ,'post' => array (
	// 	'static' => array(
	// 		'/users/' => 'add_user'
	// 	)
	// )
);

// krsort($routes['get']['static']);
// krsort($routes['post']['static']);
