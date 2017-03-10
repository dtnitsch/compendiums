<?php 

if (strpos($GLOBALS["project_info"]["dns"], "api") !== false) {
	include_once($_SERVER["DOCUMENT_ROOT"]."functions/functions.php");
} else {
	include_once($_SERVER["DOCUMENT_ROOT"]."/../api/functions/functions.php");
}

function get_questions($values) {

	$continue = true;

	// -------------------------------------------------------
	// -- TEMPORARY and needed for the app - 2016-02-28
	// -------------------------------------------------------

	if(empty($_REQUEST['game'])) {
		$_REQUEST['game'] = "quiz_questions";
	}

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

	audit_function("audits.quiz_questions");

	ob_start();

	$inputs = $_REQUEST;

	// echo "<pre>";
	// print_r($inputs);
	// echo "</pre>";
	// die();

	// validate the inputs (worlds, themes, genres, etc)
	if(($result = get_questions_validate($inputs)) === false) {
		$continue = false;
	}

	// echo "<pre>";
	// print_r($result);
	// echo "</pre>";
	// die();

	// Get item counts for the next portion
	$item_count = (!empty($_REQUEST["items"]) ? (int)$_REQUEST["items"] : 10);

	// Build the SQL needed for this api call
	if($continue && ($questions = get_questions_build_list($result,$item_count)) === false) {
		$continue = false;
	}

	// Run SQL and proess results
	if($continue && ($xml = get_questions_build_xml($result,$questions,$item_count)) === false) {
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

function get_questions_validate($inputs) {
	// list of requirements
	$required = array(
		"world"
		// ,"theme"
		// ,"genre"
		// ,"generation"
		// ,"grade"
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

function get_questions_build_list($options,$item_count = 5) {

	$activity_map_id = get_activity_map_id($options);


	if(empty($activity_map_id)) {
		return false;
	}


	$worlds = (!empty($options['world']) ? implode(',',$options['world']) : 0);
	$themes = (!empty($options['theme']) ? implode(',',$options['theme']) : 0);
	$grades = (!empty($options['grade']) ? implode(',',$options['grade']) : 0);
	$generations = $options['generation'];

/*
	$where = array();
	$where["core"][] = " quiz_question_map.active = 't' ";
	if(!empty($options['generations'])) {
		$where["core"][] = " quiz_question_map.generation_id in ('". implode("','",$options["generations"]) ."') ";
	}
	// if(!empty($options['genres'])) {
	// 	$where[] = " genre_id in ('". implode("','",$options["genres"]) ."') ";
	// }

	// Assume grade 4 if not provided
	if(empty($options['grade'])) {
		$where["core"][] = " quiz_question_map.grade_id = '4' ";
	} else {
		$where["core"][] = " quiz_question_map.grade_id = '". (is_array($options['grade']) ? $options['grade'][0] : $options['grade']) ."' ";
	}

	if(!empty($options['world'])) {
		$tmp = array("ints" => array(),"aliases" => array());
		foreach($options['world'] as $row) {
			if(is_numeric($row)) { $tmp["ints"][] = $row; }
			// else { $tmp["aliases"][] = $row; }
		}
		if(!empty($tmp['ints'])) {
			$where["worlds"][] = " worlds.id in ('". implode("','",$tmp["ints"]) ."') "; // world in ('3','22')	
		}
		// if(!empty($tmp['aliases'])) {
		// 	$where["worlds"][] = " worlds.alias in ('". implode("','",$tmp["aliases"]) ."') "; // world in ('i','ws')	
		// }
	}
	if(!empty($options['theme'])) {
		$tmp = array("ints" => array(),"aliases" => array());
		foreach($options['theme'] as $row) {
			if(is_numeric($row)) { $tmp["ints"][] = $row; }
			// else { $tmp["aliases"][] = $row; }
		}
		if(!empty($tmp['ints'])) {
			$where["themes"][] = " themes.id in ('". implode("','",$tmp["ints"]) ."') "; // world in ('3','22')	
		}
		// if(!empty($tmp['aliases'])) {
		// 	$where["themes"][] = " themes.alias in ('". implode("','",$tmp["aliases"]) ."') "; // world in ('i','ws')	
		// }
	}
*/

	// Subcat check was removed
	// Activity ID = 1 - don't hardcode this
	$q = "
		insert into public.activity_bundles (student_id,activity_map_id) values
			('". $options['student_id'] ."','". $activity_map_id ."')
		returning public.activity_bundles.id
	";
	// echo "<pre>";
	// print_r($q);
	// echo "</pre>";
	// die();

	$id = db_fetch($q,"Creating Activity Bundle");

	// Checking for existing used id's
	$q = "
		select max(cnt) as max_count
		from (
			select
				pk_id
				,count(pk_id) as cnt
			from public.activity_bundle_values
	        join public.activity_bundles on
	            activity_bundles.student_id = '". $options['student_id'] ."'
	            and activity_bundles.activity_map_id = '". $activity_map_id ."'
	            and activity_bundle_values.activity_bundle_id = activity_bundles.id
			group by
				pk_id
		) as q
	";
	// echo "<pre>";
	// print_r($q);
	// echo "</pre>";
	// die();

	$res = db_query($q,"Checking bundle counts for repeats");
	$max_count = 0;
	if(db_num_rows($res)) {
		$row = db_fetch_row($res);
		$max_count = $row['max_count'];
		// echo "Max: $max_count";
	}

	$current_item_count = $item_count;
	$total_items = 0;
	$ignore_ids = true;
	while($total_items < $item_count) {

		$q = "
			insert into activity_bundle_values (activity_bundle_id,pk_id)
	    	select
				'". $id['id'] ."'
				,quiz_question_map.id
	    	from activities.quiz_question_map
	    	join public.worlds on
	    		worlds.id = quiz_question_map.world_id
	    		". (!empty($worlds) ? " and worlds.id in ( ". $worlds ." ) " : "") ."
	    	join public.themes on
	    		themes.id = quiz_question_map.theme_id
	    		". (!empty($themes) ? " and themes.id in ( ". $themes ." ) " : "") ."
	    	join public.grades on
	    		grades.id = quiz_question_map.grade_id
	    		". (!empty($grades) ? " and grades.id in ( ". $grades ." ) " : "") ."
	    	where
	    		quiz_question_map.active = 't'
		";
		if(!empty($max_count) && $ignore_ids) {
			$q .= "
				and quiz_question_map.id not in (
					SELECT
						pk_id
					FROM public.activity_bundle_values
			        JOIN public.activity_bundles ON
			            activity_bundles.student_id = '". $options['student_id'] ."'
			            AND activity_bundles.activity_map_id = '". $activity_map_id ."'
			            and activity_bundle_values.activity_bundle_id = activity_bundles.id
					GROUP BY
						pk_id
			       HAVING count(pk_id) = '". $max_count ."'
			  	)
	       ";
		}
		$q .= "
	    	order by random()
	    	limit ". $current_item_count ."
	    ";

	    // echo "<pre>";
	    // print_r($q);
	    // echo "</pre>";
	    // die();


	    $res = db_query($q,"Creating random list");
	    if(db_affected_rows($res)) {
	    	$affected_rows = db_affected_rows($res);
	    	$total_items += $affected_rows;
	    	$current_item_count -= $affected_rows;

	    	// No rows?
	    	// 	Try runnnig the query without removing id's
	    	// 	... This might be needed for subsets of questions lower than 10 (item_count)
	    } else if($ignore_ids) {
	    	$ignore_ids = false;

	    	// If ignore_ids is off, AND we have no items, break out and move on
	    } else {
	    	break;
	    }
	}
    // echo "<pre>";
    // print_r(db_num_rows($res));
    // echo "<br>";
    // print_r($q);
    // print_r($options);
    // echo "</pre>";
    // die();

    // $id['id'] = 22;

    $questions = get_bundle_questions($id["id"]);

    // echo "<pre>";
    // print_r($questions);
    // echo "</pre>";
    // die();

    $fact_ids = array();
    foreach($questions as $k => $v) {
    	if(!empty($v['FactId'])) {
    		$fact_ids[$v['FactId']] = 1;
    	}
    }

    // Some games require facts and their media.  This covers that issue
    if(!empty($options['addfacts'])) {
	    $facts = get_question_fact_media(array_keys($fact_ids));

	    foreach($questions as $k => $v) {
	    	foreach($facts as $fact_id => $fact) {
	    		if(!empty($v['FactId']) && $v['FactId'] == $fact_id) {
	    			$questions[$k]["FactMedia"] = $fact;
	    		}

	    	}
	    }
    }

    $media = get_question_bundle_media($id["id"]);

    foreach($media as $k => $v) {
    	if(!empty($questions[$k])) {
    		$questions[$k]["Media"] = $v["Media"];
    	}
    }

    // echo "<pre>";
    // print_r($questions);
    // echo "</pre>";
    // die();

	return $questions;
}

function get_bundle_questions($id) {
    $q = "
   		select
			qq.id
			,qq.question
			,qq.points
			,qq.number
			,qq.created
			,f.fact
			,w.title as world
			,w.alias as world_alias
			,t.title as theme
			,t.alias as theme_alias
			,qqm.grade_id
			,qqm.generation_id
			,qqa.id AS answer_id
			,qqa.answer
			,qqa.is_correct
			,qqa.series
			,abv.id as activity_bundle_value_id
			,abv.activity_bundle_id as activity_bundle_id
			,f.id as fact_id
			,f.fact as fact_title
		from activities.quiz_questions as qq
		join activities.quiz_question_map as qqm on
			qqm.quiz_question_id = qq.id
--			and qqm.id in (select pk_id from activity_bundle_values where activity_bundle_id = '". $id ."')
		join public.activity_bundle_values as abv on
			abv.activity_bundle_id = '". $id ."'
			and abv.pk_id = qqm.id
		join public.worlds as w on
			w.id = qqm.world_id
		join public.themes as t on
			t.id = qqm.theme_id
		left join activities.quiz_question_answers as qqa on
			qqa.quiz_question_id = qq.id
		left join activities.quiz_question_fact_map as qqfm on
			qqfm.quiz_question_id = qq.id
		left join public.facts as f on
			f.id = qqfm.fact_id
		order by
			qq.number
			,qqa.series
     ";

     // echo "<pre>";
     // print_r($q);
     // echo "</pre>";
     // die();

     $res = db_query($q,"Getting questions");

     $output = array();
     while($row = db_fetch_row($res)) {
     	if(empty($output[$row["id"]])) {
     		$output[$row["id"]] = array(
     			"Id" => $row["id"]
     			,"Fact" => $row["question"]
     			,"MaximumPoints" => $row["points"]
     			,"DateInformation" => $row["created"]
     			,"World" => $row["world_alias"]
     			,"WorldName" => $row["world"]
     			,"Theme" => $row["theme_alias"]
     			,"ThemeName" => $row["theme"]
     			,"Grade" => $row["grade_id"]
     			,"Generation" => $row["generation_id"]
     			,"Number" => $row["number"]
     			,"FactId" => $row["fact_id"]
     			,"FactTitle" => $row["fact_title"]
     			,"ActivityBundleId" => $row["activity_bundle_id"]
     			,"ActivityBundleValueId" => $row["activity_bundle_value_id"]
     			,"CorrectAnswer" => 0
     			,"Answers" => array()
     		);
     	}

     	$output[$row["id"]]["Answers"][$row["series"]] = array(
     		"answer_id" => $row["answer_id"]
     		,"Answer" => $row["answer"]
     		,"is_correct" => ($row["is_correct"] == "t" ? "true" : "false")
     		,"series" => $row["series"]
     	);

     	if($row["is_correct"] == "t") {
			$output[$row["id"]]["CorrectAnswer"] = $row["series"];
     	}
     }

     return $output;
}


function get_question_bundle_media($id) {

	$q = "
   		select
			qqm.id as media_id
			,qqm.series as series
			,qqm.quiz_question_id
			,qqm.folder
			,qqm.filename
			,mt.media_group
			,mt.title as media_title
		from activities.quiz_question_media as qqm
		join supplements.media_types as mt on
			mt.id = qqm.media_type_id
		where
			qqm.quiz_question_id in (
				select quiz_question_id
				from public.activity_bundle_values
				join activities.quiz_question_map as qqm on
				    qqm.id = pk_id
				where activity_bundle_id = '". $id ."'
			)
     ";
     $res = db_query($q,"Media Questions");

     $output = array();
     while($row = db_fetch_row($res)) {
     	$output[$row["quiz_question_id"]]["Media"][$row["media_group"]][$row["series"]] = array(
     		"media_id" => $row["media_id"]
     		,"folder" => $row["folder"]
     		,"filename" => $row["filename"]
     		,"series" => $row["series"]
     	);
     }

     return $output;
}

function get_questions_build_xml($options,$questions,$item_count) {
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

		if(!empty($row['FactId']) && !empty($options['addfacts'])) {
			$full_output .= quiz_question_fact_xml($row);
		}

		$full_output .= quiz_question_question_xml($row);

	}
	$full_output .= "</Questions>";

	return $full_output;
}

function quiz_question_question_xml($row) {

	// Images
	$images = array();
	if(!empty($row['Media']['image'])) {
		foreach($row['Media']['image'] as $k => $v) {
			$key = "PhotoContent". ($k);
			if($key == "PhotoContent1") { $key = "PhotoContent"; }
			if(!empty($v['filename'])) {
				$images[$key] = xml_prep($v['filename']);	
			}
		}
	}

	// Audio
	$audios = array();
	if(!empty($row['Media']['audio'])) {
		foreach($row['Media']['audio'] as $k => $v) {
			$key = "Audio". ($k);
			if($key == "Audio1") { $key = "Audio"; }
			if(!empty($v['filename'])) {
				$audios[$key] = xml_prep($v['filename']);
			}
		}
	}

	// Video
	$videos = array();
	if(!empty($row['Media']['video'])) {
		foreach($row['Media']['video'] as $k => $v) {
			$key = "VideoContent". ($k);
			if($key == "VideoContent1") { $key = "VideoContent"; }
			if(!empty($v['filename'])) {
				$videos[$key] = xml_prep($v['filename']);
			}
		}
	}


	// Sound
	$sounds = array();
	if(!empty($row['Media']['sound'])) {
		foreach($row['Media']['sound'] as $k => $v) {
			$key = "SoundContent". ($k);
			if($key == "SoundContent1") { $key = "SoundContent"; }
			if(!empty($v['filename'])) {
				$sounds[$key] = xml_prep($v['filename']);
			}
		}
	}

	$tmp = strtotime($row['DateInformation']);
	$month = date('m',$tmp);
	$day = date('d',$tmp);
	$year = date('Y',$tmp);

	//$question = $SelectedQuestions[$i];
	$output = "
	<Question>
		<Id>". $row['Id'] ."</Id>
		<Series>". $cnt++ ."</Series>
		<ActivityBundleId>". $row['ActivityBundleId'] ."</ActivityBundleId>
		<ActivityBundleValueId>". $row['ActivityBundleValueId'] ."</ActivityBundleValueId>
		<MaximumPoints>". $row['points'] ."</MaximumPoints>
		<WorldName><![CDATA[". xml_prep($row['WorldName']) ."]]></WorldName>
		<World>". xml_prep($row['World']) ."</World>
		<ThemeName><![CDATA[". xml_prep($row['ThemeName']) ."]]></ThemeName>
		<Theme>". xml_prep($row['Theme']) ."</Theme>
		<DateInformation>". $month .",". $day .",". $year ."</DateInformation>
		<Grade>". $row['Grade'] ."</Grade>
		". (($row['Generation'] != '' && $row['Generation'] != null) ? "<Generation>". xml_prep($row['Generation']) ."</Generation>" : "<Generation/>") ."
		<Genre>q</Genre>
		<Number>". $row['Number'] ."</Number>
		". ($row['Fact'] != '' ? "<Fact><![CDATA[". xml_prep($row['Fact']) ."]]></Fact>" : "<Fact/>") ."
		". ($row['FactMedia'] != '' ? "<FactMedia>". xml_prep($row['question_media']) ."</FactMedia>" : "<FactMedia/>") ."
	";

	$i = 0;
	while($i++ < 5) {
		$key = "Answer". $i;
		$output .= "
		". (!empty($row['Answers'][$i]) ? "<". $key ."><![CDATA[". xml_prep($row['Answers'][$i]['Answer']) ."]]></". $key .">" : "<". $key ."/>");	
	}

	$output .= "
		". ($row['CorrectAnswer'] != '' ? "<CorrectAnswer>". xml_prep($row['CorrectAnswer']) ."</CorrectAnswer>" : "<CorrectAnswer/>") ."
		". ($row['subcat'] != '' ? "<Subcat>". xml_prep($row['subcat']) ."</Subcat>" : "<Subcat/>") ."
	";

	foreach($images as $k => $v) {
		$output .= "
		". ($v != '' ? "<". $k .">". xml_prep($v) ."</". $k .">" : "<". $k ."/>");	
	}


	$i = 0;
	while($i++ < 4) {
		$key = "Audio". ($i == 1 ? '' : $i);
		$output .= "
		". (!empty($audios[$key]) ? "<". $key .">". xml_prep($audios[$key]) ."</". $key .">" : "<". $key ."/>");	
	}

	$i = 0;
	while($i++ < 3) {
		$key = "VideoContent". ($i == 1 ? '' : $i);
		$output .= "
		". (!empty($videos[$key]) ? "<". $key .">". xml_prep($videos[$key]) ."</". $key .">" : "<". $key ."/>");	
	}

	$i = 0;
	while($i++ < 3) {
		$key = "SoundContent". ($i == 1 ? '' : $i);
		$output .= "
		". (!empty($sounds[$key]) ? "<". $key .">". xml_prep($sounds[$key]) ."</". $key .">" : "<". $key ."/>");	
	}

	$output .= "
	</Question>";

	return $output;
}


function quiz_question_fact_xml($row) {

	// Images
	$images = array();
	if(!empty($row['FactMedia']['image'])) {
		foreach($row['FactMedia']['image'] as $v) {
			$key = "PhotoContent". ($k+1);
			if($key == "PhotoContent1") { $key = "PhotoContent"; }
			if(!empty($v['filename'])) {
				$images[$key] = xml_prep($v['filename']);	
			}
		}
	}
	// Audio
	$audios = array();
	if(!empty($row['FactMedia']['audio'])) {
		foreach($row['FactMedia']['audio'] as $v) {
			$key = "Audio". ($k+1);
			if($key == "Audio1") { $key = "Audio"; }
			if(!empty($v['filename'])) {
				$audios[$key] = xml_prep($v['filename']);	
			}
		}
	}

	// Video
	$videos = array();
	if(!empty($row['FactMedia']['video'])) {
		foreach($row['FactMedia']['video'] as $v) {
			$key = "VideoContent". ($k+1);
			if($key == "VideoContent1") { $key = "VideoContent"; }
			if(!empty($v['filename'])) {
				$videos[$key] = xml_prep($v['filename']);	
			}
		}
	}


	// Sound
	$sounds = array();
	if(!empty($row['FactMedia']['sound'])) {
		foreach($row['FactMedia']['sound'] as $v) {
			$key = "SoundContent". ($k+1);
			if($key == "SoundContent1") { $key = "SoundContent"; }
			if(!empty($v['filename'])) {
				$sounds[$key] = xml_prep($v['filename']);	
			}
		}
	}

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
		<Genre>f</Genre>
		<Number>". $row['Number'] ."</Number>
		". ($row['Fact'] != '' ? "<Fact><![CDATA[". xml_prep($row['FactTitle']) ."]]></Fact>" : "<Fact/>") ."
	";

	foreach($images as $k => $v) {
		$output .= "
		". ($v != '' ? "<". $k .">". xml_prep($v) ."</". $k .">" : "<". $k ."/>");	
	}

	$i = 0;
	while($i++ < 4) {
		$key = "Audio". ($i == 1 ? '' : $i);
		$output .= "
		". (!empty($audios[$key]) ? "<". $key .">". xml_prep($audios[$key]) ."</". $key .">" : "<". $key ."/>");	
	}

	$i = 0;
	while($i++ < 3) {
		$key = "VideoContent". ($i == 1 ? '' : $i);
		$output .= "
		". (!empty($videos[$key]) ? "<". $key .">". xml_prep($videos[$key]) ."</". $key .">" : "<". $key ."/>");	
	}

	$i = 0;
	while($i++ < 3) {
		$key = "SoundContent". ($i == 1 ? '' : $i);
		$output .= "
		". (!empty($sounds[$key]) ? "<". $key .">". xml_prep($sounds[$key]) ."</". $key .">" : "<". $key ."/>");	
	}

	$output .= "
	</Question>";

	return $output;
}

function get_question_fact_media($facts) {
	if(empty($facts)) {
		return array();
	}

	$q = "
   		select 
			fm.id as media_id
			,fm.series as series
			,fm.fact_id
			,fm.folder
			,fm.filename
			,mt.media_group
			,mt.title as media_title
		from public.facts_media as fm
		join supplements.media_types as mt on
			mt.id = fm.media_type_id
		where
			fm.fact_id in (". implode(',',$facts) .")
     ";
     $res = db_query($q,"Media Questions");

     $output = array();
     while($row = db_fetch_row($res)) {
     	$output[$row["fact_id"]][$row["media_group"]][$row["series"]] = array(
     		"media_id" => $row["media_id"]
     		,"folder" => $row["folder"]
     		,"filename" => $row["filename"]
     		,"series" => $row["series"]
     	);
     }

     return $output;
}
