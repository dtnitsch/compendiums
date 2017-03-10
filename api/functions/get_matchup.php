<?php

if (strpos($GLOBALS["project_info"]["dns"], "api") !== false) {
	include_once($_SERVER["DOCUMENT_ROOT"]."functions/functions.php");
} else {
	include_once($_SERVER["DOCUMENT_ROOT"]."/../api/functions/functions.php");
}

function get_matchup($values) {

	$continue = true;

	// -------------------------------------------------------
	// -- TEMPORARY and needed for the app - 2016-02-28
	// -------------------------------------------------------

	if(empty($_REQUEST['game'])) {
		$_REQUEST['game'] = "match";
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

	audit_function("audits.matchup");

	ob_start();

	$inputs = $_REQUEST;

	// validate the inputs (worlds, themes, genres, etc)
	if(($result = get_matchup_validate($inputs)) === false) {
		$continue = false;
	}

	// Get item counts for the next portion
	$item_count = (!empty($_REQUEST["items"]) ? (int)$_REQUEST["items"] : 6);

	// Build the SQL needed for this api call
	if($continue && ($questions = get_matchup_build_list($result,$item_count)) === false) {
		$continue = false;
	}

	// Run SQL and proess results
	if($continue && ($xml = get_matchup_build_xml($result,$questions,$item_count)) === false) {
		$continue = false;
	}

	echo $xml;

	$output = ob_get_clean();

	$output = str_replace("\n","",$output);
	$output = preg_replace("/\s\s+/"," ",$output);
	$output = str_replace("> <","><",$output);

	// header('Content-type: text/xml');

	echo $output;

}

function get_matchup_validate($inputs) {
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

function get_matchup_build_list($options,$item_count = 6) {

	$activity_map_id = get_activity_map_id($options);

	if(empty($activity_map_id)) {
		return false;
	}

	$worlds = (!empty($options['world']) ? implode(',',$options['world']) : 0);
	$themes = (!empty($options['theme']) ? implode(',',$options['theme']) : 0);
	$grades = (!empty($options['grade']) ? implode(',',$options['grade']) : 0);
	$generations = $options['generation'];

	$where = array();
	$where["core"][] = " mmap.active = 't' ";
	if(!empty($options['generations'])) {
		$where["core"][] = " mmap.generation_id in ('". implode("','",$options["generations"]) ."') ";
	}

	// Assume grade 4 if not provided
	if(empty($options['grade'])) {
		$where["core"][] = " mmap.grade_id = '4' ";
	} else {
		$where["core"][] = " mmap.grade_id = '". (is_array($options['grade']) ? $options['grade'][0] : $options['grade']) ."' ";
	}

	if(!empty($options['world'])) {
		$tmp = array("ints" => array(),"aliases" => array());
		foreach($options['world'] as $row) {
			if($row == 0) { continue; }
			if(is_numeric($row)) { $tmp["ints"][] = $row; }
			// else { $tmp["aliases"][] = $row; }
		}
		if(!empty($tmp['ints'])) {
			$where["worlds"][] = " worlds.id in ('". implode("','",$tmp["ints"]) ."') "; // world in ('3','22')	
		}
	}

	if(!empty($options['theme'])) {
		$tmp = array("ints" => array(),"aliases" => array());
		foreach($options['theme'] as $row) {
			if($row == 0) { continue; }
			if(is_numeric($row)) { $tmp["ints"][] = $row; }
			// else { $tmp["aliases"][] = $row; }
		}
		if(!empty($tmp['ints'])) {
			$where["themes"][] = " themes.id in ('". implode("','",$tmp["ints"]) ."') "; // world in ('3','22')	
		}
	}


	// // Subcat check was removed
	// // Activity ID = 1 - don't hardcode this
	// $q = "
	// 	insert into public.activity_bundles (student_id,activity_map_id) values
	// 		('". $options['student_id'] ."','". $activity_map_id ."')
	// 	returning public.activity_bundles.id
	// ";
	// // echo "<pre>";
	// // print_r($q);
	// // echo "</pre>";
	// $id = db_fetch($q,"Creating Activity Bundle");

	// // Checking for existing used id's
	// $q = "
	// 	select max(cnt) as max_count
	// 	from (
	// 		select
	// 			pk_id
	// 			,count(pk_id) as cnt
	// 		from public.activity_bundle_values
	//         join public.activity_bundles on
	//             activity_bundles.student_id = '". $options['student_id'] ."'
	//             and activity_bundles.activity_map_id = '". $activity_map_id ."'
	//             and activity_bundle_values.activity_bundle_id = activity_bundles.id
	// 		group by
	// 			pk_id
	// 	) as q
	// ";
	// // echo "<pre>";
	// // print_r($q);
	// // echo "</pre>";
	// $res = db_query($q,"Checking bundle counts for repeats");
	// $max_count = 0;
	// if(db_num_rows($res)) {
	// 	$row = db_fetch_row($res);
	// 	$max_count = $row['max_count'];
	// 	// echo "Max: $max_count";
	// }

	// $current_item_count = $item_count;
	// $total_items = 0;
	// $ignore_ids = true;
	// while($total_items < $item_count) {
	// 	$q = "
	// 		insert into activity_bundle_values (activity_bundle_id,pk_id)
	//     	select
	// 			'". $id['id'] ."'
	// 			,matchup_map.id
	//     	from activities.matchup_map
	//     	join public.worlds on
	//     		worlds.id = matchup_map.world_id
	//     		". (!empty($worlds) ? " and worlds.id in ( ". $worlds ." ) " : "") ."
	//     	join public.themes on
	//     		themes.id = matchup_map.theme_id
	//     		". (!empty($themes) ? " and themes.id in ( ". $themes ." ) " : "") ."
	//     	join public.grades on
	//     		grades.id = matchup_map.grade_id
	//     		". (!empty($grades) ? " and grades.id in ( ". $grades ." ) " : "") ."
	//     	where
	//     		matchup_map.active = 't'
	// 	";
	// 	if(!empty($max_count) && $ignore_ids) {
	// 		$q .= "
	// 			and matchup_map.id not in (
	// 				SELECT
	// 					pk_id
	// 				FROM public.activity_bundle_values
	// 		        JOIN public.activity_bundles ON
	// 		            activity_bundles.student_id = '". $options['student_id'] ."'
	// 		            AND activity_bundles.activity_map_id = '". $activity_map_id ."'
	// 		            and activity_bundle_values.activity_bundle_id = activity_bundles.id
	// 				GROUP BY
	// 					pk_id
	// 		       HAVING count(pk_id) = '". $max_count ."'
	// 		  	)
	//        ";
	// 	}
	// 	$q .= "
	//     	order by random()
	//     	limit ". $current_item_count ."
	//     ";
	//     $res = db_query($q,"Creating random list");
	//     if(db_affected_rows($res)) {
	//     	$affected_rows = db_affected_rows($res);
	//     	$total_items += $affected_rows;
	//     	$current_item_count -= $affected_rows;

	//     	// No rows?
	//     	// 	Try runnnig the query without removing id's
	//     	// 	... This might be needed for subsets of questions lower than 10 (item_count)
	//     } else if($ignore_ids) {
	//     	$ignore_ids = false;

	//     	// If ignore_ids is off, AND we have no items, break out and move on
	//     } else {
	//     	break;
	//     }
	// }
    // echo "<pre>";
    // print_r(db_num_rows($res));
    // echo "<br>";
    // print_r($q);
    // print_r($options);
    // echo "</pre>";
    // die();

    // $id['id'] = 22;

    $questions = get_matchup_values($id["id"],$where,$item_count);

	return $questions;
}

function get_matchup_values($id,$where,$item_count) {
    $q = "
		select
		    m.id
		    ,m.points
		    ,m.title
		    ,json_agg(mm.filename order by series) as media
		from activities.matchup as m
		join activities.matchup_media as mm on
		    mm.matchup_id = m.id
		join activities.matchup_map as mmap on
		    mmap.matchup_id = m.id
    	join public.worlds on
    		worlds.id = mmap.world_id
    		". (!empty($where["worlds"]) ? " and ( ". implode(' or ', $where["worlds"]) ." ) " : "") ."
		";
		if(!empty($where["themes"])) {
			$q .= "
	    	join public.themes on
    			themes.id = mmap.theme_id
    			and ( ". implode(' or ', $where["themes"]) ." )
			";
		}
		$q .= "
    	where
    		". (!empty($where["core"]) ? implode(" and ", $where["core"]) : '')."
		group by
		    m.id
		    ,m.points
		    ,m.title
		order by random()
		limit ". $item_count ."
	";
	$res = db_query($q,"Getting questions");

	$output = array();
	while($row = db_fetch_row($res)) {
		if(empty($output[$row["id"]])) {
			$media = json_decode($row['media'],true);

			$photos = array();
			$audio = array();
			foreach($media as $m) {
				$ext = strtolower(substr($m,-3));
				if($ext == "jpg" || $ext == "jpeg" || $ext == "gif" || $ext == "png" || $ext == "bmp") {
					$photos[] = $m;
				} else if($ext == "mp3") {
					$audio[] = $m;
				}
			}

			$output[$row["id"]] = array(
				"Id" => $row["id"]
				,"typeOne" => "image"
				,"typeTwo" => "image"
				,"one" => (!empty($photos[0]) ? $photos[0] : '')
				,"two" => (!empty($photos[1]) ? $photos[1] : '')
				,"audio" => (!empty($audio[0]) ? $audio[0] : '')
				,"audio2" => (!empty($audio[1]) ? $audio[1] : '')
				,"text" => $row['title']
				,"points" => $row['points']
			);
		}
	}

	return $output;
}


function get_question_bundle_media($id) {

	$q = "
   		select
			qqm.id as media_id
			,qqm.series as series
			,qqm.matchup_id
			,qqm.folder
			,qqm.filename
			,mt.media_group
			,mt.title as media_title
		from activities.matchup_media as qqm
		join supplements.media_types as mt on
			mt.id = qqm.media_type_id
		where
			qqm.matchup_id in (
				select matchup_id
				from public.activity_bundle_values
				join activities.matchup_map as qqm on
				    qqm.id = pk_id
				where activity_bundle_id = '". $id ."'
			)
     ";
     $res = db_query($q,"Media Questions");

     $output = array();
     while($row = db_fetch_row($res)) {
     	$output[$row["matchup_id"]]["Media"][$row["media_group"]][$row["series"]] = array(
     		"media_id" => $row["media_id"]
     		,"folder" => $row["folder"]
     		,"filename" => $row["filename"]
     		,"series" => $row["series"]
     	);
     }

     return $output;
}

function get_matchup_build_xml($options,$questions,$item_count) {
	$full_output = '';

	$full_output .= "<puzzleParams>";

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

	$cnt = 1;
	foreach($questions as $row) {

		$full_output .= matchup_question_xml($row);

	}
	$full_output .= "</puzzleParams>";

	return $full_output;
}

function matchup_question_xml($row) {

	// if we have audio1 but not audio2, make sure we echo the audio1 for both
	$audio_file_1 = $row['audio'];
	$audio_file_2 = $row['audio2'];
	
	if (!empty($audio_file_1)){
		if (empty($audio_file_2)){
			$audio_file_2 = $audio_file_1;
		}
	}
	
	$output = '
	<cards>
		<pair
			typeOne="'. $row['typeOne'] .'"
			typeTwo="'. $row['typeTwo'] .'"
			one="'. $row['one'] .'"
			two="'. $row['two'] .'"
			audio="'. $audio_file_1 .'"
			audio2="'. $audio_file_2 .'"
			points="'. $row['points'] .'"
		>'. $row['text'] .'</pair>
	';

	$output .= "
	</cards>";

	return $output;
}


function matchup_fact_xml($row) {

	// Images
	$images = array();
	if(!empty($row['FactMedia']['image'])) {
		foreach($row['FactMedia']['image'] as $k => $v) {
			$key = "PhotoContent". ($k);
			if($key == "PhotoContent1") { $key = "PhotoContent"; }
			if(!empty($v['filename'])) {
				$images[$key] = xml_prep($v['folder'].$v['filename']);	
			}
		}
	}
	// Audio
	$audios = array();
	if(!empty($row['FactMedia']['audio'])) {
		foreach($row['FactMedia']['audio'] as $k => $v) {
			$key = "Audio". ($k);
			if($key == "Audio1") { $key = "Audio"; }
			if(!empty($v['filename'])) {
				$audios[$key] = xml_prep($v['folder'].$v['filename']);	
			}
		}
	}

	// Video
	$videos = array();
	if(!empty($row['FactMedia']['video'])) {
		foreach($row['FactMedia']['video'] as $k => $v) {
			$key = "VideoContent". ($k);
			if($key == "VideoContent1") { $key = "VideoContent"; }
			if(!empty($v['filename'])) {
				$videos[$key] = xml_prep($v['folder'].$v['filename']);	
			}
		}
	}


	// Sound
	$sounds = array();
	if(!empty($row['FactMedia']['sound'])) {
		foreach($row['FactMedia']['sound'] as $k => $v) {
			$key = "SoundContent". ($k);
			if($key == "SoundContent1") { $key = "SoundContent"; }
			if(!empty($v['filename'])) {
				$sounds[$key] = xml_prep($v['folder'].$v['filename']);	
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
