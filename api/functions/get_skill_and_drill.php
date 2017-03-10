<?php 

if (strpos($GLOBALS["project_info"]["dns"], "api") !== false) {
	include_once($_SERVER["DOCUMENT_ROOT"]."functions/functions.php");
} else {
	include_once($_SERVER["DOCUMENT_ROOT"]."/../api/functions/functions.php");
}

function get_skill_and_drill($values) {

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

	audit_function("audits.skill_and_drill");

	$inputs = $_REQUEST;
	$inputs['world'] = strtolower($_REQUEST['genre']);
	if(substr($inputs['world'],-1) == "v") {
		$inputs['world'] = substr($inputs['world'],0,-1);
	}
	if($inputs['world'] == "a"){
		$inputs['world'] = "at";
	}
	
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

	$inputs['game'] = "skill_and_drill";

	ob_start();

	// validate the inputs (worlds, themes, genres, etc)
	if(($result = get_skill_and_drill_validate($inputs)) === false) {
		$continue = false;
	}

	// Get item counts for the next portion
	$item_count = (!empty($_REQUEST["items"]) ? (int)$_REQUEST["items"] : 10);

	// Build the SQL needed for this api call
	if($continue && ($questions = get_skill_and_drill_build_list($result,$item_count)) === false) {
		$continue = false;
	}

	// Run SQL and proess results
	if($continue && ($xml = get_skill_and_drill_build_xml($result,$questions,$item_count)) === false) {
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

function get_skill_and_drill_validate($inputs) {
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

function get_skill_and_drill_build_list($options,$item_count = 5) {

	$where = array();
	$where["core"][] = " skill_and_drill_map.active = 't' ";
	if(!empty($options['generations'])) {
		$where["core"][] = " skill_and_drill_map.generation_id in ('". implode("','",$options["generations"]) ."') ";
	}
	// if(!empty($options['genres'])) {
	// 	$where[] = " genre_id in ('". implode("','",$options["genres"]) ."') ";
	// }

	// Assume grade 4 if not provided
	if(empty($options['grade'])) {
		$where["core"][] = " skill_and_drill_map.grade_id = '4' ";
	} else {
		$where["core"][] = " skill_and_drill_map.grade_id = '". (is_array($options['grade']) ? $options['grade'][0] : $options['grade']) ."' ";
	}

	if(!empty($options['world'])) {
		$tmp = array("ints" => array(),"aliases" => array());
		foreach($options['world'] as $row) {
			if($row == 0) { continue; }
			if(is_numeric($row)) { $tmp["ints"][] = $row; }
			else { $tmp["aliases"][] = $row; }
		}
		if(!empty($tmp['ints'])) {
			$where["worlds"][] = " worlds.id in ('". implode("','",$tmp["ints"]) ."') "; // world in ('3','22')	
		}
		if(!empty($tmp['aliases'])) {
			$where["worlds"][] = " worlds.alias in ('". implode("','",$tmp["aliases"]) ."') "; // world in ('i','ws')	
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
			$where["themes"][] = " themes.id in ('". implode("','",$tmp["ints"]) ."') "; // world in ('3','22')	
		}
		if(!empty($tmp['aliases'])) {
			$where["themes"][] = " themes.alias in ('". implode("','",$tmp["aliases"]) ."') "; // world in ('i','ws')	
		}
	}

	$activity_map_id = get_activity_map_id($options);

	if(empty($activity_map_id)) {
		return false;
	}

	// Subcat check was removed
	// Activity ID = 1 - don't hardcode this
	$q = "
		insert into public.activity_bundles (student_id,activity_map_id) values
			('". $options['student_id'] ."','". $activity_map_id ."')
		returning public.activity_bundles.id
	";
	$id = db_fetch($q,"Creating Activity Bundle");

	$q = "
		insert into activity_bundle_values (activity_bundle_id,pk_id)
    	select
			'". $id['id'] ."'
			,skill_and_drill_map.id
    	from activities.skill_and_drill_map
    	join public.worlds on
    		worlds.id = skill_and_drill_map.world_id
    		". (!empty($where["worlds"]) ? " and ( ". implode(' or ', $where["worlds"]) ." ) " : "") ."
--    	join public.themes on
--    		themes.id = skill_and_drill_map.theme_id
--    		". (!empty($where["themes"]) ? " and ( ". implode(' or ', $where["themes"]) ." ) " : "") ."
    	where
    		". (!empty($where["core"]) ? implode(" and ", $where["core"]) : '')."
    	order by random()
    	limit ". $item_count ."
    ";
    db_query($q,"Creating random list");

    // $id['id'] = 22;

    $questions = get_bundle_questions($id["id"]);

    $media = get_skill_and_drill_bundle_media($id["id"]);

    foreach($media as $k => $v) {
    	if(!empty($questions[$k])) {
    		$questions[$k]["Media"] = $v["Media"];
    	}
    }

    return $questions;
}

function get_bundle_questions($id) {
    $q = "
   		select 
			sd.id
			,sd.question
			,sd.points
			,sd.number
			,sd.created
			,w.title as world
			,w.alias as world_alias
			,t.title as theme
			,t.alias as theme_alias
			,sdm.grade_id
			,sdm.generation_id
			,sda.id as answer_id
			,sda.answer
			,sda.is_correct
			,sda.series
			,abv.id as activity_bundle_value_id
			,abv.activity_bundle_id as activity_bundle_id
		from activities.skill_and_drill as sd
		join activities.skill_and_drill_map as sdm on
			sdm.skill_and_drill_id = sd.id
			-- and sdm.id in (select pk_id from activity_bundle_values where activity_bundle_id = '". $id ."')
		join public.activity_bundle_values as abv on
			abv.activity_bundle_id = '". $id ."'
			and abv.pk_id = sdm.id
		join public.worlds as w on
			w.id = sdm.world_id
		join public.themes as t on
			t.id = sdm.theme_id
		left join activities.skill_and_drill_answers as sda on
			sda.skill_and_drill_id = sd.id
     ";
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


function get_skill_and_drill_bundle_media($id) {
    
	$q = "
   		select 
			sdm.id as media_id
			,sdm.series as series
			,sdm.skill_and_drill_id
			,sdm.folder
			,sdm.filename
			,mt.media_group
			,mt.title as media_title
		from activities.skill_and_drill_media as sdm
		join supplements.media_types as mt on
			mt.id = sdm.media_type_id
		where
			sdm.skill_and_drill_id in (
				select skill_and_drill_id
				from public.activity_bundle_values
				join activities.skill_and_drill_map as sdm on
				    sdm.id = pk_id
				where activity_bundle_id = '". $id ."'
			)
     ";
     $res = db_query($q,"Media Questions");

     $output = array();
     while($row = db_fetch_row($res)) {
     	$output[$row["skill_and_drill_id"]]["Media"][$row["media_group"]][$row["series"]] = array(
     		"media_id" => $row["media_id"]
     		,"folder" => $row["folder"]
     		,"filename" => $row["filename"]
     		,"series" => $row["series"]
     	);
     }

     return $output;
}

function get_skill_and_drill_build_xml($options,$questions,$item_count) {
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
		,"Genre" => "v"
	));

	$full_output .= "<Questions>";
	$cnt = 1;
	foreach($questions as $row) {
		
		$full_output .= skill_and_drill_question_xml($row);

	}
	$full_output .= "</Questions>";

	return $full_output;
}

function skill_and_drill_question_xml($row) {

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
		<Genre>sd</Genre>
		<Number>". $row['Number'] ."</Number>
		". ($row['Fact'] != '' ? "<Fact><JustText>". xml_prep($row['Fact']) ."</JustText></Fact>" : "<Fact/>") ."
		". ($row['FactMedia'] != '' ? "<FactMedia>". xml_prep($row['question_media']) ."</FactMedia>" : "<FactMedia/>") ."
	";


	$i = 0;
	while($i++ < 5) {
		$key = "Answer". $i;
		$output .= "
		". (!empty($row['Answers'][$i]) ? "<". $key ."><JustText>". xml_prep($row['Answers'][$i]['Answer']) ."</JustText></". $key .">" : "<". $key ."/>");	
	}

	$output .= "
		". ($row['CorrectAnswer'] != '' ? "<CorrectAnswer>". xml_prep($row['CorrectAnswer']) ."</CorrectAnswer>" : "<CorrectAnswer/>") ."
		". ($row['subcat'] != '' ? "<Subcat>". xml_prep($row['subcat']) ."</Subcat>" : "<Subcat/>") ."
	";

	foreach($images as $k => $v) {
		$output .= "
		". ($v != '' ? "<". $k .">". xml_prep($v) ."</". $k .">" : "<". $k ."/>");	
	}

	$output .= get_skill_and_drill_media_xml($row);

	$output .= "
	</Question>";

	return $output;
}

function get_skill_and_drill_media_xml($row) {

	$output = '';
	$arr = array();

	// Images
	$arr['images'] = array();
	if(!empty($row['Media']['image'])) {
		foreach($row['Media']['image'] as $k => $v) {
			$key = "PhotoContent". $k;
			if($key == "PhotoContent1") { $key = "PhotoContent"; }
			if(!empty($v['filename'])) {
				$arr['images'][$key] = xml_prep($v['filename']);	
			}
		}
	}
	// Audio
	$arr['audios'] = array();
	if(!empty($row['Media']['audio'])) {
		foreach($row['Media']['audio'] as $k => $v) {
			$key = "Audio". $k;
			if($key == "Audio1") { $key = "Audio"; }
			if(!empty($v['filename'])) {
				$arr['audios'][$key] = xml_prep($v['filename']);	
			}
		}
	}

	// Video
	$arr['videos'] = array();
	if(!empty($row['Media']['video'])) {
		foreach($row['Media']['video'] as $k =>$v) {
			$key = "VideoContent". $k;
			if($key == "VideoContent1") { $key = "VideoContent"; }
			if(!empty($v['filename'])) {
				$arr['videos'][$key] = xml_prep($v['filename']);	
			}
		}
	}


	// Sound
	$arr['sounds'] = array();
	if(!empty($row['Media']['sound'])) {
		foreach($row['Media']['sound'] as $k =>$v) {
			$key = "SoundContent". $k;
			if($key == "SoundContent1") { $key = "SoundContent"; }
			if(!empty($v['filename'])) {
				$arr['sounds'][$key] = xml_prep($v['filename']);	
			}
		}
	}


	$keys = array(
		'photos' => array('PhotoContent')
		,'audios' => array('Audio','Audio2','Audio3','Audio4')
		,'videos' => array('VideoContent','VideoContent2','VideoContent3')
		,'sounds' => array('SoundContent','SoundContent2','SoundContent3')
	);

	foreach($keys as $section => $r1) {
		foreach($r1 as $key) {
			$output .= "\n". (!empty($arr[$section][$key]) ? "<". $key .">". $arr[$section][$key] ."</". $key .">" : "<". $key ."/>");	
		}
	}


	// $i = 0;
	// while($i++ < 4) {
	// 	$key = "Audio". ($i == 1 ? '' : $i);
	// 	$output .= "
	// 	". (!empty($audios[$key]) ? "<". $key .">". xml_prep($audios[$key]) ."</". $key .">" : "<". $key ."/>");
	// }

	// $i = 0;
	// while($i++ < 3) {
	// 	$key = "VideoContent". ($i == 1 ? '' : $i);
	// 	$output .= "
	// 	". (!empty($videos[$key]) ? "<". $key .">". xml_prep($videos[$key]) ."</". $key .">" : "<". $key ."/>");
	// }

	// $i = 0;
	// while($i++ < 3) {
	// 	$key = "SoundContent". ($i == 1 ? '' : $i);
	// 	$output .= "
	// 	". (!empty($sounds[$key]) ? "<". $key .">". xml_prep($sounds[$key]) ."</". $key .">" : "<". $key ."/>");	
	// }

	return $output;
}