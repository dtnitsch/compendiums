<?php

function process(){

	_error_debug("Starting Ajax",'',__LINE__,__FILE__);

	if (isset($_POST['action'])) {
		if ($_POST['action'] == 'upload'){
			return proc_upload();
		}
	}

	$json = array();
	if($GLOBALS['debug_options']['enabled']) {
		$json['debug'] = ajax_debug();
	}
	
	return json_encode($json);

}

function get_target_path($ext){

	switch(strtolower($ext)){

		case "flv":
			return "videocontent";
			break;

		case "wav":
		case "wave":
		case "mp3":
			return "soundcontent";
			break;

		case "png":
		case "bmp":
		case "jpg":
		case "jpeg":
		case "jpg":
		case "gif":
		case "tif":
		case "tiff":
			return "photocontent";
			break;	

		default:
			return "error";
	}
}

function get_media_group($ext){

	switch(strtolower($ext)){

		case "flv":
			return "video";
			break;

		case "wav":
		case "wave":
		case "mp3":
			return "audio";
			break;

		case "png":
		case "bmp":
		case "jpg":
		case "jpeg":
		case "jpg":
		case "gif":
		case "tif":
		case "tiff":
			return "image";
			break;	

		default:
			return "error";
	}
}

function proc_upload(){

	_error_debug(__METHOD__,'',__LINE__,__FILE__);

	define ('SITE_ROOT', realpath(dirname(__FILE__)));

	#######################################
	# if config_id is not set and not empty
	#######################################

	if(!isset($_POST['config_id']) && !empty($_POST['config_id'])) {

		$json = array();
		if($GLOBALS['debug_options']['enabled']) {
			$json['debug'] = ajax_debug();
		}

		$json['error'] = true;

		return json_encode($json);

	}


	$arr = array();
	$arr['ext'] = pathinfo($_FILES['Filedata']['name'], PATHINFO_EXTENSION);
	$arr["quiz_question_id"] = (empty($_POST["quiz_question_id"]) ? 0 : db_prep_sql((int) $_POST["quiz_question_id"]));
	$arr["media_type_id"] = get_media_type_id_by_ext($arr['ext']);
	$arr['media_group'] = get_media_group($arr['ext']);
	$arr["filename"] = strtolower($_FILES["Filedata"]["name"]);
	$arr["position"] = (empty($_POST["position"]) ? 0 : db_prep_sql((int) $_POST["position"]));
	$arr['element_type'] = $_POST['type'];

	if (isset($_POST["voiceover"])) {
		$arr["folder"] = "audio";
	} else {
		$arr["folder"] = get_target_path($arr['ext']);
	}

	$arr['target_path'] = $_SERVER["DOCUMENT_ROOT"].$arr["folder"]."/";

	move_uploaded_file($_FILES['Filedata']["tmp_name"], $arr['target_path'].$arr["filename"]);

	if($arr['element_type'] == 'fact_media' || $arr['element_type'] == 'fact_voiceover') {
		$arr['schema'] = 'public';
		$arr['table'] = 'facts_media';
	} else {
		$arr['schema'] = 'activities';
		$arr['table'] = 'quiz_question_media';
	}

	// echo "<pre>";
	// print_r($arr);
	// echo "</pre>";
	// die();

	return add_quiz_question_media($arr);

}


function add_quiz_question_media($arr = false) {

	$json = array();
	$has_error = true;

	// active (can be t/f)
	// series (= sortorder starts at 1)
	// laguage_id (for multi language audio)
	// quiz_question_id (do not have question_id until save)
	// media_type_id (can be 162, 267, or 404)
	// folder (can be /audio/, /photocontent/, /soundcontent/, or /videocontent/)
	// filename (e.g. filename.mp3)

	$q = "
		insert into \"".$arr['schema']."\".\"".$arr['table']."\" (
			active
			,series
			,language_id
			,media_type_id
			,element_type
			,folder
			,filename
			,created
			,modified
		)
		values (
			't'
			,1
			,1
			,".(empty($arr["media_type_id"]) ? 0 : db_prep_sql((int) $arr["media_type_id"]))."
			,'".db_prep_sql($arr['element_type'])."'
			,'/".db_prep_sql($arr["folder"])."/'
			,'".db_prep_sql($arr['filename'])."'
			,'now()'
			,'now()'
		)
	";

	$res = db_query($q, __FUNCTION__."()");

	if (db_affected_rows($res)) {
		$has_error = false;
		$json["upload_id"] = db_insert_id($res);
	}

	$json["debug"] = ajax_debug();
	$json["success"] = ($has_error ? false : true);
	$json['position'] = $arr["position"];
	$json["output"] = "/".$arr["folder"]."/".$arr["filename"];

	return json_encode($json);

}

//////////////////////////////////////////////////////
// OUTPUT 
//////////////////////////////////////////////////////

echo process();

?>