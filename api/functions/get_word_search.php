<?php 

if (strpos($GLOBALS["project_info"]["dns"], "api") !== false) {
	include_once($_SERVER["DOCUMENT_ROOT"]."functions/functions.php");
} else {
	include_once($_SERVER["DOCUMENT_ROOT"]."/../api/functions/functions.php");
}

function get_word_search($values) {

	$continue = true;

	// -------------------------------------------------------
	// -- TEMPORARY and needed for the app - 2016-02-28
	// -------------------------------------------------------

	if(empty($_REQUEST['student_id']) && !empty($_REQUEST['child'])) {
		$_REQUEST['student_id'] = $_REQUEST['child'];
	}
	if(empty($_REQUEST['student_id']) && empty($_REQUEST['child'])) {
		$_REQUEST['student_id'] = "0";
	}
	if(!empty($_REQUEST['student_id'])) {
		$_REQUEST['student_id'] = validate_student_id($_REQUEST['student_id']);
	}

	if(!is_numeric($_REQUEST['student_id'])) {
		$_REQUEST['student_id'] = "";
	}

	// -------------------------------------------------------
	// -- TEMPORARY and needed for the app - 2016-02-28
	// -------------------------------------------------------


	audit_function("audits.word_search");

	ob_start();

	$inputs = $_REQUEST;
	$inputs['world'] = strtolower($_REQUEST['genre']);
	if(substr($inputs['world'],-1) == "v") {
		$inputs['world'] = substr($inputs['world'],0,-1);
	}
	if($inputs['world'] == "a"){
		$inputs['world'] = "at";
	}

	// Correct Art world themes
	if ($inputs['world'] == 'ar'){
		$inputs['theme'] = 'va';
	}
	if ($inputs['world'] == 'da'){
		$inputs['theme'] = 'da';
		$inputs['world'] = 'ar';
	}
	if ($inputs['world'] == 'mu'){
		$inputs['theme'] = 'mu';
		$inputs['world'] = 'ar';
	}

	$inputs['game'] = "wordsearch";

	// validate the inputs (worlds, themes, genres, etc)
	if(($result = get_word_search_validate($inputs)) === false) {
		$continue = false;
	}

	// Get item counts for the next portion
	$item_count = (!empty($_REQUEST["items"]) ? (int)$_REQUEST["items"] : 10);

	// Build the SQL needed for this api call
	if($continue && ($questions = get_word_search_build_list($result,$item_count)) === false) {
		$continue = false;
	}

	// Run SQL and proess results
	if($continue && ($xml = get_word_search_build_xml($result,$questions,$item_count)) === false) {
		$continue = false;
	}

	echo $xml;

	$output = ob_get_clean();

	$output = str_replace("\n","",$output);
	$output = preg_replace("/\s\s+/"," ",$output);
	$output = str_replace("> <","><",$output);

	// header('Content-type: text/xml');

	echo "<CMSData>". $output ."</CMSData>";

}

function get_word_search_validate($inputs) {
	// list of requirements
	$required = array(
		"world"
		// ,"theme"
		// ,"genre"
		// ,"generation"
		,"grade"
		,"student_id"
		,"game"
	);
	$result = requirement_validation(array_flip($required),$inputs);

	// if we have errors, spit them out and end.
	if(!empty($result["errors"])) {
		$message = "All required data must be provided.";
		$details = "The fields world, theme, genre, and student id are required to proceed.";
		echo xml_errors($message,$details,$result["errors"]);
		return false;
	}

	return $result;

}

function get_word_search_build_list($options,$item_count = 500) {

	$where = array();
	$where["core"][] = " wsm.active = 't' ";
	if(!empty($options['generations'])) {
		$where["core"][] = " wsm.generation_id in ('". implode("','",$options["generations"]) ."') ";
	}
	// if(!empty($options['genres'])) {
	// 	$where[] = " genre_id in ('". implode("','",$options["genres"]) ."') ";
	// }

	// Assume grade 4 if not provided
	if(empty($options['grade'])) {
		$where["core"][] = " wsm.grade_id = '4' ";
	} else {
		$where["core"][] = " wsm.grade_id = '". (is_array($options['grade']) ? $options['grade'][0] : $options['grade']) ."' ";
	}

	if(!empty($options['world'])) {
		$tmp = array("ints" => array(),"aliases" => array());
		foreach($options['world'] as $row) {
			if($row == 0) { continue; }
			if(is_numeric($row)) { $tmp["ints"][] = $row; }
			else { $tmp["aliases"][] = $row; }
		}
		if(!empty($tmp['ints'])) {
			$where["worlds"][] = " w.id in ('". implode("','",$tmp["ints"]) ."') "; // world in ('3','22')	
		}
		if(!empty($tmp['aliases'])) {
			$where["worlds"][] = " w.alias in ('". implode("','",$tmp["aliases"]) ."') "; // world in ('i','ws')	
		}
	}
	if(!empty($options['theme'])) {
		$tmp = array("ints" => array(),"aliases" => array());
		foreach($options['theme'] as $row) {
			if($row == 0) { continue; }
			if(is_numeric($row)) { $tmp["ints"][] = $row; }
			else { $tmp["aliases"][] = $row; }
		}
		if(!empty($tmp['ints'])) {
			$where["themes"][] = " t.id in ('". implode("','",$tmp["ints"]) ."') "; // world in ('3','22')	
		}
		if(!empty($tmp['aliases'])) {
			$where["themes"][] = " t.alias in ('". implode("','",$tmp["aliases"]) ."') "; // world in ('i','ws')
		}
	}

	$activity_map_id = get_activity_map_id($options);

	if(empty($activity_map_id)) {
		return false;
	}

	// Subcat check was removed
	$q = "
		insert into public.activity_bundles (student_id,activity_map_id) values
			('". $options['student_id'] ."','". $activity_map_id ."')
		returning public.activity_bundles.id
	";
	$id = db_fetch($q,"Creating Activity Bundle");

	// $q = "
	// 	insert into activity_bundle_values (activity_bundle_id,pk_id)
 //    	select
	// 		'". $id['id'] ."'
	// 		,word_search_map.id
 //    	from activities.word_search_map
 //    	join public.worlds on
 //    		worlds.id = word_search_map.world_id
 //    		". (!empty($where["worlds"]) ? " and ( ". implode(' or ', $where["worlds"]) ." ) " : "") ."
 //    	join public.themes on
 //    		themes.id = word_search_map.theme_id
 //    		". (!empty($where["themes"]) ? " and ( ". implode(' or ', $where["themes"]) ." ) " : "") ."
 //    	where
 //    		". (!empty($where["core"]) ? implode(" and ", $where["core"]) : '')."
 //    	order by random()
 //    	limit ". $item_count ."
 //    ";
 //    db_query($q,"Creating random list");

    $questions = get_bundle_questions($id["id"],$where,$item_count);

    // $media = get_word_search_bundle_media($id["id"]);

    // foreach($media as $k => $v) {
    // 	if(!empty($questions[$k])) {
    // 		$questions[$k]["Media"] = $v["Media"];
    // 	}
    // }

	return $questions;
}

function get_bundle_questions($id,$where,$item_count) {
    $q = "
   		select 
			ws.id
			,ws.word
			,ws.description
			,ws.points
			,ws.number
			,ws.created
			,w.title as world
			,w.alias as world_alias
			,t.title as theme
			,t.alias as theme_alias
			,wsm.grade_id
			,wsm.generation_id
			,json_agg(wsmedia.filename order by series) AS media
		from activities.word_search as ws
		join activities.word_search_map as wsm on
			wsm.word_search_id = ws.id
		join public.worlds as w on
			w.id = wsm.world_id
			". (!empty($where["worlds"]) ? " and ( ". implode(' or ', $where["worlds"]) ." ) " : "") ."
		join public.themes as t on
			t.id = wsm.theme_id
			". (!empty($where["themes"]) ? " and ( ". implode(' or ', $where["themes"]) ." ) " : "") ."
		left join activities.word_search_media as wsmedia on
			wsmedia.word_search_id = ws.id
		where
			". (!empty($where["core"]) ? implode(" and ", $where["core"]) : '')."
		group by
    		ws.id
			,ws.word
			,ws.description
			,ws.points
			,ws.number
			,ws.created
			,w.title
			,w.alias
			,t.title
			,t.alias
			,wsm.grade_id
			,wsm.generation_id
		limit ". $item_count ."
     ";
     $res = db_query($q,"Getting questions");

     $output = array();
     while($row = db_fetch_row($res)) {

     	if(empty($output[$row["id"]])) {
     		$output[$row["id"]] = array(
     			"Id" => $row["id"]
     			,"Fact" => $row["word"]
     			,"MaximumPoints" => $row["points"]
     			,"DateInformation" => $row["created"]
     			,"World" => $row["world_alias"]
     			,"WorldName" => $row["world"]
     			,"Theme" => $row["theme_alias"]
     			,"ThemeName" => $row["theme"]
     			,"Grade" => $row["grade_id"]
     			,"Generation" => $row["generation_id"]
     			,"Number" => $row["number"]
     			,"Answer1" => $row["description"]
     			,"CorrectAnswer" => 0
     		);
     		$output[$row["id"]]['Media'] = array();
     		$pieces = json_decode($row['media'],true);
     		$tmp = array();
     		foreach($pieces as $k => $r) {
     			// HARD CODING "audios" !!
     			$key = 'Audio'.($k == 0 ? '' : ($k+1));
     			$output[$row["id"]]['Media']['audios'][$key] = $r;
     		}
     	}
     }


     return $output;
}


// function get_word_search_bundle_media($id) {

// 	$q = "
//    		select
// 			cm.id as media_id
// 			,cm.series as series
// 			,cm.word_search_id
// 			,cm.folder
// 			,cm.filename
// 			,mt.media_group
// 			,mt.title as media_title
// 		from activities.word_search_media as cm
// 		join supplements.media_types as mt on
// 			mt.id = cm.media_type_id
// 		where
// 			cm.word_search_id in (
// 				select word_search_id
// 				from public.activity_bundle_values
// 				join activities.word_search_map as cm on
// 				    cm.id = pk_id
// 				where activity_bundle_id = '". $id ."'
// 			)
//      ";
//      $res = db_query($q,"Media Questions");

//      $output = array();
//      while($row = db_fetch_row($res)) {
//      	$output[$row["word_search_id"]]["Media"][$row["media_group"]][$row["series"]] = array(
//      		"media_id" => $row["media_id"]
//      		,"folder" => $row["folder"]
//      		,"filename" => $row["filename"]
//      		,"series" => $row["series"]
//      	);
//      }

//      return $output;
// }

function get_word_search_build_xml($options,$questions,$item_count) {
	$full_output = '';

	$count = count($questions);
	if($count === 0) {
		$message = "Unable to provide any questions matching parameters passed.";
		$details = "We were unable to find questions matching your criteria.";
		echo xml_errors($message,$details);
		return false;
	}

	if($count < $item_count) {
		$message = "There were not enough items to return ". $item_count;
		$details = "Do to the parameters passed we were unable to provide you with ". $item_count ." items because there were less than ". $item_count ." items as a result of your query.";
		$full_output .= xml_warnings($message,$details);
	}

	$full_output .= xml_requests($options,array(
		"Items" => $item_count
	));

	$full_output .= "<Questions>";
	$cnt = 1;
	foreach($questions as $row) {

		$full_output .= word_search_question_xml($row);

	}
	$full_output .= "</Questions>";

	return $full_output;
}

function word_search_question_xml($row) {

	$tmp = strtotime($row['DateInformation']);
	$month = date('m',$tmp);
	$day = date('d',$tmp);
	$year = date('Y',$tmp);

	//$question = $SelectedQuestions[$i];
	$output = "
	<Question>
		<Id>". $row['Id'] ."</Id>
		<Series>". $cnt++ ."</Series>
		<MaximumPoints>". $row['points'] ."</MaximumPoints>
		<WorldName><![CDATA[". xml_prep($row['WorldName']) ."]]></WorldName>
		<World>". xml_prep($row['World']) ."</World>
		<ThemeName><![CDATA[". xml_prep($row['ThemeName']) ."]]></ThemeName>
		<Theme>". xml_prep($row['Theme']) ."</Theme>
		<DateInformation>". $month .",". $day .",". $year ."</DateInformation>
		<Grade>". $row['Grade'] ."</Grade>
		". (($row['Generation'] != '' && $row['Generation'] != null) ? "<Generation>". xml_prep($row['Generation']) ."</Generation>" : "<Generation/>") ."
		<Genre>ws</Genre>
		<Number>". $row['Number'] ."</Number>
		". ($row['Fact'] != '' ? "<Fact><![CDATA[". xml_prep($row['Fact']) ."]]></Fact>" : "<Fact/>") ."
		". ($row['FactMedia'] != '' ? "<FactMedia>". xml_prep($row['question_media']) ."</FactMedia>" : "<FactMedia/>") ."
		<Answer1><![CDATA[". xml_prep($row['Answer1']) ."]]></Answer1>
	";

	foreach($row['Media']['audios'] as $k => $r) {
		$output .= "\n". (!empty($r) ? "<". $k .">". xml_prep($r) ."</". $k .">" : "<". $k ."/>");
	}
	// $output .= get_word_search_media_xml($row);

	$output .= "
	</Question>";

	return $output;
}

// function get_word_search_media_xml($row) {

// 	$output = '';

// 	// Audio
// 	$audios = array();
// 	if(!empty($row['Media']['audio'])) {
// 		foreach($row['Media']['audio'] as $k => $v) {
// 			$key = "Audio". ($k);
// 			if($key == "Audio1") { $key = "Audio"; }
// 			if(!empty($v['filename'])) {
// 				$audios[$key] = xml_prep($v['folder'].$v['filename']);	
// 			}
// 		}
// 	}

// 	$keys = array(
// 		'audios' => array('Audio','Audio2')
// 	);

// 	foreach($keys as $arr => $r1) {
// 		foreach($r1 as $key) {
// 			$output .= "\n". (!empty($arr[$key]) ? "<". $key .">". xml_prep($arr[$key]) ."</". $key .">" : "<". $key ."/>");
// 		}
// 	}

// 	return $output;
// }