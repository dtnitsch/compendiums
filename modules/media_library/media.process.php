<?php

include_once($_SERVER['DOCUMENT_ROOT']."/lib/config.php");

include_once($_SERVER['DOCUMENT_ROOT']."/lib/media/media.class.php");

include_once($_SERVER['DOCUMENT_ROOT']."/lib/media/resize.images.class.php");


// ini_set("display_errors","2");
// ERROR_REPORTING(E_ALL);

//print_r($_REQUEST);

function process(){

	_error_debug("Starting Ajax",'',__LINE__,__FILE__);

	if (isset($_POST['listMedia']))
		return proc_list_media();

	else if (isset($_POST['uploadMedia']))
		return proc_upload_media();

	else if (isset($_POST['editUploads']))
		return proc_edit_uploads();

	else if (isset($_POST['cropImages']))
		return proc_crop_images();

	else if (isset($_POST['getDetails']))
		return proc_get_details();

	else if (isset($_POST['getMediaConfig']))
		return proc_get_media_config();

	else if (isset($_POST['delete']))
		return proc_delete();

	else if (isset($_POST['delbulk']))
		return proc_delete_bulk();

	$json = array();
	if($GLOBALS['debug_options']['enabled']) {
		$json['debug'] = ajax_debug();
	}
	
	return json_encode($json);

}

function proc_get_media_config() {
	
	$media = get_media_config($_POST['config_id']);
	
	if($media) {
		
		$json = array(
			'success' 		=> true,
			'config_id'		=> $media['id'],
			'directory'		=> $media['directory'],
			'container'		=> $media['container'],
			'crop' 			=> ($media['crop'] ? true : false),
			'ratio' 		=> ($media['ratio'] ? true : false),
			'multi' 		=> ($media['multi'] ? true : false),
			'textarea' 		=> ($media['textarea'] ? true : false),
			'selector_w' 	=> $media['selector_w'],
			'selector_h' 	=> $media['selector_h'],
			'target_w' 		=> $media['target_w'],
			'target_h' 		=> $media['target_h']
		);

	} else {

		$json = array(
			'error' => true
		);

	}

	echo json_encode($json);

}

function proc_get_details() {
	
	$thumbnail 	= 0;
	$large 		= 0;
	$medium 	= 0;
	$fullsize 	= 0;

	$uploads	= get_uploads($_POST['upl_id']);

	// print_r($uploads);
	// return false;

	if(is_array($uploads)) {

		if($uploads['type'] == "img") {
			
			$file_url = "../../upl/".$uploads['directory']."/".$uploads['filename']."_150x150.".$uploads['ext'];
			
			if(file_exists($file_url)) {
				$size = getimagesize($file_url);
				$thumbnail = $size[0]." x ".$size[1];
			}			

			$file_url = "../../upl/".$uploads['directory']."/".$uploads['filename']."_300.".$uploads['ext'];

			if(file_exists($file_url)) {
				$size = getimagesize($file_url);
				$medium = $size[0]." x ".$size[1];
			}

			$file_url = "../../upl/".$uploads['directory']."/".$uploads['filename']."_570.".$uploads['ext'];

			if(file_exists($file_url)) {
				$size = getimagesize($file_url);
				$large = $size[0]." x ".$size[1];
			}				

			$file_url = "../../upl/".$uploads['directory']."/".$uploads['filename'].".".$uploads['ext'];

			if(file_exists($file_url)) {
				$size = getimagesize($file_url);
				$fullsize = $size[0]." x ".$size[1];
			}

		}

		$json = array(
			'success' 			=> true,
			'upl_id' 			=> $uploads['id'],
			'config_id' 		=> $uploads['config_id'],
			'title' 			=> $uploads['title'],
			'filename' 			=> $uploads['filename'],				
			'type' 				=> $uploads['type'],
			'ext' 				=> $uploads['ext'],
			'directory'			=> $uploads['directory'],
			'container'			=> $uploads['container'],
			'textarea'			=> $uploads['textarea'],
			'targetpath'		=> $GLOBALS['MAINURL'].'upl/'.$uploads['directory'].'/',
			'crop' 				=> ($uploads['crop'] ? true : false),
			'ratio' 			=> ($uploads['ratio'] ? true : false),
			'multi' 			=> ($uploads['multi'] ? true : false),
			'selector_w' 		=> $uploads['selector_w'],
			'selector_h' 		=> $uploads['selector_h'],
			'target_w' 			=> $uploads['target_w'],
			'target_h' 			=> $uploads['target_h'],
			'date'				=> date("F d, Y", time()),
			'thumbnail' 		=> $thumbnail,
			'medium' 			=> $medium,
			'large' 			=> $large,
			'fullsize' 			=> $fullsize
		);

	} else {
	
		$json = array(
			'error' => true,
			'message' => "There was an error processing your request."
		);

	}

	echo json_encode($json);

}



function proc_upload_media(){
	global $session, $crop;

	_error_debug(__METHOD__,'',__LINE__,__FILE__);

	// print_r($_POST);
	// return false;

	$media = get_media_config($_POST['config_id']);
	
	// print_r($media);
	// return false;

	$prephix 		= time();
	$filename 		= $_FILES['Filedata']['name'];
	$ext 			= pathinfo($filename, PATHINFO_EXTENSION);
	$ext 			= $crop->safe_file_extension(strtolower($ext));
	$filename 		= $prephix."_".$crop->new_file_name($filename);
	$new_filename 	= $filename.".".$ext;
	$type 			= $crop->safe_file_type(strtolower($ext));
	$directory 		= strtolower($media['directory']);

	if($media['id'] == 3 && $type == "img") {

		$status 				= 0;
		$crop 					= new Crop_Images;
		$crop->quality 			= 90;
		$crop->source_file 		= $new_filename;
		$crop->target_path 		= "../../upl/".$directory."/";

		move_uploaded_file($_FILES['Filedata']['tmp_name'], $crop->target_path.$crop->source_file);

		$process = $crop->crop('resize');

	} 

	else if($media['id'] == 4 && $type == "img") {

		$status 				= 1;
		$crop 					= new Crop_Images;
		$crop->quality 			= 90;
		$crop->source_file 		= $new_filename;
		$crop->target_path 		= "../../upl/".$directory."/";

		move_uploaded_file($_FILES['Filedata']['tmp_name'], $crop->target_path.$crop->source_file);

		$crop->crop('resize');

		$new_source_file 		= $crop->source_file;

		$crop 					= new Crop_Images;
		$crop->quality 			= 90;
		$crop->new_width 		= 920;
		$crop->new_height 		= 490;
		$crop->source_file 		= $new_source_file;
		$crop->target_path 		= "../../upl/".$directory."/";

		$crop->crop('fixed');

		$crop 					= new Crop_Images;
		$crop->quality 			= 90;
		$crop->new_width 		= 300;
		$crop->new_height 		= 300;
		$crop->source_file 		= $new_source_file;
		$crop->target_path 		= "../../upl/".$directory."/";

		$process = $crop->crop('fixed');


	} 


	////////////////////////////////////////////
	// IMAGES // CONTENT // TEXTAREA
	////////////////////////////////////////////

	else if ($directory == "content" && $type == "img") {
		
		$status 				= 1;
		$crop 					= new Crop_Images;
		$crop->quality 			= 90;
		$crop->source_file 		= $new_filename;
		$crop->target_path 		= "../../upl/".$directory."/";

		move_uploaded_file($_FILES['Filedata']['tmp_name'], $crop->target_path.$crop->source_file);

		$crop->crop('resize');

		$new_source_file 		= $crop->source_file;

		$crop 					= new Crop_Images;
		$crop->quality 			= 90;
		$crop->new_width 		= 150;
		$crop->new_height 		= 150;
		$crop->source_file 		= $new_source_file;
		$crop->target_path 		= "../../upl/".$directory."/";

		$crop->crop('fixed');

		$crop 					= new Crop_Images;
		$crop->quality 			= 90;
		$crop->new_size 		= 300;
		$crop->source_file 		= $new_source_file;
		$crop->target_path 		= "../../upl/".$directory."/";

		$crop->crop('resize');

		$crop 					= new Crop_Images;
		$crop->quality 			= 90;
		$crop->new_size 		= 570;
		$crop->source_file 		= $new_source_file;
		$crop->target_path 		= "../../upl/".$directory."/";

		$process 				= $crop->crop('resize');

	} 


	// IMAGES (MISC)
	else if ($type == 'img') {

		$status 				= 1;
		
		$crop 					= new Crop_Images;
		$crop->quality 			= 90;
		$crop->source_file 		= $new_filename;
		$crop->target_path 		= "../../upl/".$directory."/";	

		move_uploaded_file($_FILES['Filedata']['tmp_name'], $crop->target_path.$crop->source_file);

		$process = $crop->crop('resize');

		$new_source_file = $crop->source_file;

	} 


	// DOCUMENTS
	else if ($type == 'doc') {

		$status 				= 1;
		$directory 				= "documents";
		
		$crop 					= new Crop_Images;
		$crop->source_file 		= $new_filename;
		$crop->target_path 		= "../../upl/".$directory."/";	

		move_uploaded_file($_FILES['Filedata']['tmp_name'], $crop->target_path.$crop->source_file);

		$process = array();
		$process['success'] 		= true;
		$process['new_ext'] 		= $ext;
		$process['new_title'] 		= $crop->safe_title($crop->source_file);
		$process['new_source_file'] = $crop->new_file_name($crop->source_file);
		$process['new_width']		= 0;
		$process['new_height']		= 0;

	}


	# If image - Create thumbnail for all types

	if ($type == 'img') {

		$crop 					= new Crop_Images;
		$crop->quality 			= 90;
		$crop->new_width 		= 150;
		$crop->new_height 		= 150;
		$crop->source_file 		= $new_source_file;
		$crop->new_file_name 	= $filename.'_thumbnail';

		$crop->target_path 		= "../../upl/".$directory."/";

		$crop->crop('fixed');

	}




	$ret_title = '';

	if($_POST['update_upl_id'] == 'true'){

		$upl_id = $_POST['update_upl_id'];

		$q = "
			SELECT 
				title
			FROM 
				".db_tables('uploads')." 
			WHERE 
				id = '".$upl_id."' 
			LIMIT 
				1
		";

		//echo $q;

		$result 	= db_query($q);
		$original 	= db_fetch_row($result);


		//print_r($original);
		//return false;

		$q = "
			UPDATE 
				".db_tables('uploads')." 
			SET 
				user_id = '".db_prep_sql($session->user['id'])."' 
				,config_id = '".db_prep_sql($_POST['config_id'])."' 
				,title = '".db_prep_sql($original['title'])."' 
				,filename = '".db_prep_sql($process['new_source_file'])."' 
				,ext = '".db_prep_sql($process['new_ext'])."' 
				,type = '".db_prep_sql($type)."' 
				,date = '".db_prep_sql(time())."' 
			WHERE 
				id = '".$upl_id."'
		";

		db_query($q);

		$ret_title = $original['title'];


	} else {

		$ret_title = $process['new_title'];

		$upl_id = add_uploads($session->user['id'], $_POST['config_id'], $process['new_title'], $process['new_source_file'], $process['new_ext'], $type, $status);

	}


	if($upl_id) {
		
		$json = array(
			'success' 			=> true,
			'config_id'			=> $media['id'],
			'upl_id'			=> $upl_id,
			'type'				=> $type,
			'date'				=> date("F d, Y", time()),
			'directory'			=> $directory,
			'container'			=> $media['container'],
			'textarea'			=> $media['textarea'],
			'targetpath'		=> $GLOBALS['MAINURL'].'upl/'.$directory.'/',
			'crop' 				=> ($media['crop'] ? true : false),
			'ratio' 			=> ($media['ratio'] ? true : false),
			'multi' 			=> ($media['multi'] ? true : false),
			'selector_w' 		=> $media['selector_w'],
			'selector_h' 		=> $media['selector_h'],
			'target_w' 			=> $media['target_w'],
			'target_h' 			=> $media['target_h'],
			'filename' 			=> $process['new_source_file'],
			'ext' 				=> $process['new_ext'],
			'title'		 		=> $ret_title,
			'source_w'	 		=> $process['new_width'],
			'source_h'	 		=> $process['new_height']
		);
		
		//print_r($json);

	} else {

		$json = array(
			'error' => true
		);

	}

	if($GLOBALS['debug_options']['enabled']) {
		$json['debug'] = ajax_debug();
	}

	echo json_encode($json);

}

function proc_crop_images(){
	global $session, $crop;

	_error_debug(__METHOD__,'',__LINE__,__FILE__);

	$imageSource = $_SERVER['DOCUMENT_ROOT'].'/upl/'. $_POST['directory'].'/'.$_POST['imageFilename'].'.'.$_POST['imageExt'];


	// echo "<pre>";
	// print_r($_POST);
	// echo "</pre>";
	// die();

	// PREPARE IMAGE FOR CROPPING

	// $binary_data = file_get_contents($imageSource);
	// $im = imagecreatefromstring($binary_data);
	// $sourceW = imagesx($im);
	// $sourceH = imagesy($im);

	// print_r($binary_data);
	// die();

	$pieces = explode(' ',shell_exec('identify '.$imageSource));
	$mime = strtolower($pieces[1]);

	list($sourceW,$sourceH) = explode('x',$pieces[2]);


	//list($sourceW, $sourceH) = getimagesize($imageSource); //2000, 1500

	$imageW		= $_POST["imageW"]; // 800
	$imageH		= $_POST["imageH"]; // 600
	$imageX		= $_POST["imageX"]; // 120
	$imageY		= $_POST["imageY"]; // (-35)

	//$image_scale = $imageW / $sourceW; // 800/2000 = 0.4
	
	$targetW	= $_POST["targetW"]; // 1140
	$targetH	= $_POST["targetH"]; // 650
	
	$selectorW	= $_POST["selectorW"]; // 684
	$selectorH	= $_POST["selectorH"]; // 390
	$selectorX 	= $_POST["selectorX"]; // 143
	$selectorY 	= $_POST["selectorY"]; // 67.5

	//$ext 		= end(explode(".",$imageSource));

	$ext = $_POST['imageExt'];

	/////////////////////////////////

	//$info = GetImageSize($imageSource);
	//$mime = $info['mime'];




	//$type = substr(strrchr($mime, '/'), 1);

	switch ($mime) {
		case 'jpeg':
			$image_create_func 	= 'ImageCreateFromJPEG';
			$image_save_func 	= 'ImageJPEG';
			$new_ext 			= 'jpg';
			break;
		
		case 'png':
			$image_create_func 	= 'ImageCreateFromPNG';
			$image_save_func 	= 'ImagePNG';
			$new_ext 			= 'png';
			break;
		
		case 'bmp':
			$image_create_func 	= 'ImageCreateFromBMP';
			$image_save_func 	= 'ImageBMP';
			$new_ext 			= 'bmp';
			break;
		
		case 'gif':
			$image_create_func 	= 'ImageCreateFromGIF';
			$image_save_func 	= 'ImageGIF';
			$new_ext 			= 'gif';
			break;
		
		case 'vnd.wap.wbmp':
			$image_create_func 	= 'ImageCreateFromWBMP';
			$image_save_func 	= 'ImageWBMP';
			$new_ext 			= 'bmp';
			break;
		
		case 'xbm':
			$image_create_func 	= 'ImageCreateFromXBM';
			$image_save_func 	= 'ImageXBM';
			$new_ext 			= 'xbm';
			break;
		
		default:
			$image_create_func 	= 'ImageCreateFromJPEG';
			$image_save_func 	= 'ImageJPEG';
			$new_ext 			= 'jpg';
	}
	

	/////////////////////////////////

	//$function 	= returnCorrectFunction($ext);
	$image 		= $image_create_func($imageSource);


	if(isset($_POST["imageRatio"])) {
		
		//$imageX = $imageX + ($imageW/2);
		//$imageY = $imageY + ($imageH/2);

		$selector_scale = $selectorW / $targetW; // 684/1140 = 0.6
		
		$extrapolated_imageW = $imageW / $selector_scale; // 800/0.6 = 1333.33
		$extrapolated_imageH = $imageH / $selector_scale; // 600/0.6 = 1000
		$extrapolated_imageX = $imageX / $selector_scale; // 120/0.6 = 200
		$extrapolated_imageY = $imageY / $selector_scale; // (-35)/0.6 = (-58.33)
		
		$extrapolated_selectorX = $selectorX / $selector_scale; // 143/0.6 = 238.33
		$extrapolated_selectorY = $selectorY / $selector_scale; // 67.5/0.6 = 112.5
		
		//$extrapolated_image_scale = $extrapolated_imageW / $sourceW; // 1333.33/2000 = 0.67
		
		$selectorX_relative_to_imageX = $selectorX - $imageX; // 143-120 = 23
		$selectorY_relative_to_imageY = $selectorY - $imageY; // 67.5-(-35) = 102.5
		
		$extrapolated_selectorX_relative_to_extrapolated_imageX = $extrapolated_selectorX - $extrapolated_imageX; // 238.33-200 = 38.33
		$extrapolated_selectorY_relative_to_extrapolated_imageY = $extrapolated_selectorY - $extrapolated_imageY; // 112.5-(-58.88) = 171.38

		$final_image = imagecreatetruecolor($targetW, $targetH);
		setTransparency($image,$final_image,$ext);

		imagecopyresampled(
			$final_image, 
			$image, 
			-$extrapolated_selectorX_relative_to_extrapolated_imageX, 
			-$extrapolated_selectorY_relative_to_extrapolated_imageY, 
			0, 
			0, 
			$extrapolated_imageW, 
			$extrapolated_imageH, 
			$sourceW, 
			$sourceH
		);
		
		imagedestroy($image);
	
	} else {
		
		// CREATE NEW IMAGE IN MEMORY BASED ON SCALE

		$image_p = imagecreatetruecolor($imageW, $imageH);
		setTransparency($image,$image_p,$ext);

		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $imageW, $imageH, $sourceW, $sourceH);
		imagedestroy($image);

		// IMAGE ROTATION CALCULATIONS
		if($_POST["imageRotate"]){

			$rotateWidth 	= imagesx($image_p);
			$rotateHeight 	= imagesy($image_p);

			$angle 			= 360 - $_POST["imageRotate"];
			$image_p 		= imagerotate($image_p,$angle,0);
			
			$imageW 		= imagesx($image_p);
			$imageH 		= imagesy($image_p);
		
			$diffW 			= abs($imageW - $rotateWidth) / 2;
			$diffH 			= abs($imageH - $rotateHeight) / 2;
				
			$imageX = ($imageW > $rotateWidth ? $imageX - $diffW : $imageX + $diffW);
			$imageY = ($imageH > $rotateHeight ? $imageY - $diffH : $imageY + $diffH);
			
		}

		// ESTABLISH NEW POSITION OF SOURCE IMAGE

		$dst_x = $src_x = $dst_y = $src_y = 0;

		if($imageX > 0)
			$dst_x = abs($imageX);
		else
			$src_x = abs($imageX);

		if($imageY > 0)
			$dst_y = abs($imageY);
		else
			$src_y = abs($imageY);

		// PREPARE TO CROP IMAGE

		$viewport = imagecreatetruecolor($viewPortW, $viewPortH);
		setTransparency($image_p,$viewport,$ext);

		imagecopy($viewport, $image_p, $dst_x, $dst_y, $src_x, $src_y, $imageW, $imageH);
		imagedestroy($image_p);

		$final_image = imagecreatetruecolor($selectorWidth, $selectorHeight);
		setTransparency($viewport,$final_image,$ext);

		imagecopy($final_image, $viewport, 0, 0, $selectorX, $selectorY, $viewPortW,  $viewPortH);			
		
	}

	// REMOVE TIMESTAMP FROM ORIGINAL FILE

	$directory 		= strtolower($_POST['directory']);
	$pos 			= strpos($_POST['imageFilename'], '_');
	$prephix 		= substr($_POST['imageFilename'], 0, ($pos+1));
	$imageFilename 	= str_replace($prephix, "", $_POST['imageFilename']);

	// ADD TIMESTAMP TO NEW FILE

	$time 			= time();
	$new_file_name 	= $time."_".$imageFilename;
	$file_name 		= $time."_".$imageFilename.".".$_POST['imageExt'];
	$new_filename 	= preg_replace('/_[^_.]*\./', '.', $file_name);	
	$new_filename 	= $crop->new_file_name($new_filename);
	//$file_name 		= $new_filename."_".$sourceW."x".$sourceH.".".$_POST['imageExt'];
	$file_name 		= $new_filename.".".$_POST['imageExt'];
	$file 			= "../../upl/".$directory."/".$file_name;

	// CREATE FILE

	parseImage($ext,$final_image,$file);
	@imagedestroy($viewport);

	// CREATE THUMBNAIL

	$crop 					= new Crop_Images;
	$crop->quality 			= 90;
	$crop->new_width 		= 150;
	$crop->new_height 		= 150;		
	$crop->source_file 		= $file_name;
	$crop->new_file_name 	= $new_filename."_thumbnail";
	$crop->target_path 		= "../../upl/".$directory."/";

	$crop->crop('fixed');

	if($_POST['config_id'] == 3) {

		$crop 					= new Crop_Images;
		$crop->quality 			= 90;
		$crop->new_width 		= 1346;
		$crop->new_height 		= 484;
		$crop->source_file 		= $file_name;
		$crop->new_file_name 	= $new_filename."_1346x484";
		$crop->target_path 		= "../../upl/".$directory."/";
		$crop->crop('fixed');

		$crop 					= new Crop_Images;
		$crop->quality 			= 90;
		$crop->new_size 		= 300;
		$crop->source_file 		= $file_name;
		$crop->new_file_name 	= $new_filename."_400x300";
		$crop->target_path 		= "../../upl/".$directory."/";
		$crop->crop('resize');


	} 


	// SAVE NEW FILE

	$upl_id = add_uploads($session->user['id'], $_POST['config_id'], $_POST['imageTitle'], $new_filename, $_POST['imageExt'], $_POST['imageType'], 0);

	if($upl_id) {
		
		$json = array(
			'success' 		=> true,
			'config_id'		=> $_POST['config_id'],
			'upl_id'		=> $upl_id,
			'filename'		=> $file_name,
			'container'		=> $_POST['container'],
			'targetpath'	=> $_POST['targetpath']
		);

	} else {

		$json = array(
			'error' => true
		);

	}


	if($GLOBALS['debug_options']['enabled']) {
		$json['debug'] = ajax_debug();
	}

	echo json_encode($json);
			
}


function proc_edit_uploads(){

	$isInsert = ($_POST['isInsert'] == 'true' ? 1 : 0);

	if(isset($_POST['isMulti'])){


	} else {

		update_uploads($_POST['upl_id'], "title", $_POST['title']);

	}


	$json = array(
		'success' => true,
		'isInsert' => $isInsert
	);

	echo json_encode($json);

}

function proc_list_media(){

	_error_debug(__METHOD__,'',__LINE__,__FILE__);

	$orderby 	= "";
	$hash		= "";
	$keywordSql = "";
	$page		= $_POST['page'];
	$limit		= $_POST['rows'];
	$sidx		= $_POST['sidx'];
	$sord		= $_POST['sord'];

	if(!$sidx)
		$sidx =1;

	$sidx = explode(",", $sidx);

	for($i=0;$i<count($sidx);$i++){
		($i>0)?$hash = ", ": "";
		$orderby .= $hash.$sidx[$i]." ".$sord;
    }

	// sql to filter results by keyword
	if ($_POST['keyword'])
		$keywordSql = " 
			AND 
				u.title LIKE \"%".db_prep_sql($_POST['keyword'])."%\" 
			";

	// sql to filter results by category
	$categorySql = "";
	if ($_POST['config_id'])
		$categorySql = " 
			AND 
				uc.id = ".db_prep_sql($_POST['config_id'])." 
			";

	$sql = "
		SELECT 
			u.id
		FROM 
			".db_tables('uploads')." AS u
		INNER JOIN
			".db_tables('uploads_config')." AS uc 
		ON 
			uc.id = u.config_id
		WHERE 
			u.status = 1 
		AND
			uc.status = 1

		".$keywordSql."
		".$categorySql."
	";

	//echo $sql;

	$result 	= db_query($sql);
	$rowcount 	= db_num_rows($result);
	
	$total_pages 	= ($rowcount > 0 ? ceil($rowcount / $limit) : 0);
	$page 			= ($page > $total_pages ? $total_pages : $page);
	
	$start 			= max(0, (($limit*$page) - $limit));

	$q = "
		SELECT 
			u.id AS id,
			u.type AS type,
			u.title AS title,
			u.filename AS filename,
			u.ext AS ext,
			u.date AS date,
			u.status AS status,
			uc.directory AS directory,
			uc.name AS name 
		FROM 
			".db_tables('uploads')." AS u
		INNER JOIN
			".db_tables('uploads_config')." AS uc 
		ON 
			uc.id = u.config_id
		WHERE 
			u.status = 1 
		AND
			uc.status = 1

			".$keywordSql."
			".$categorySql."
		ORDER BY 
			$orderby 
		LIMIT 
			$start, 
			$limit
	";

	$res = db_query($q,"Getting Media");

	$response 			= new stdClass();
	$response->page 	= $page;
	$response->total 	= $total_pages;
	$response->records 	= $rowcount;
	
	$k = 0;

	while($row = db_fetch_row($res)) {
		
		$output 	= array();

		if($row['type'] == "img")
			$output[] = "<img src=\"/upl/".strtolower($row['directory'])."/".$row['filename']."_thumbnail.".$row['ext']."\" width=\"125\" />";
		else
			$output[] = "<img src=\"/lib/media/images/text.png\" width=\"30\" />";

		$output[] 	= $row['title'];

		$output[] 	= ucfirst($row['name']);

        $output[] 	= "&nbsp;<a href=\"#\" rel=\"".$row['id']."\" class=\"showDetails edit\" title=\"Show\">Show</a> <a href=\"#\" rel=\"".$row['id']."\" class=\"delete\" title=\"Delete\">Delete</a>";

		$response->rows[$k]['id']	= $row['id'];
		$response->rows[$k]['cell']	= $output;
		
		$k++;

	}

	if($GLOBALS['debug_options']['enabled']) {
		$response->debug = ajax_debug();
	}

	echo json_encode($response);

}

function add_uploads($user_id, $config_id, $title, $filename, $ext, $type, $status){

	$q = "
		INSERT 
		INTO 
			".db_tables('uploads')." 
		(
		 	user_id, 
			config_id,
			title, 
			filename, 
			ext, 
			type,
			date, 
			status
		) 
		VALUES 
		(
		 	'".$user_id."', 
			'".db_prep_sql($config_id)."', 
			'".db_prep_sql($title)."', 
			'".db_prep_sql($filename)."', 
			'".db_prep_sql($ext)."', 
			'".db_prep_sql($type)."', 
			'".time()."', 
			'".db_prep_sql($status)."'
		)
	";

	//echo $q;

	$res = db_query($q);

	return db_insert_id($res);

}

function update_uploads($id, $field, $value){

	$q = "
		UPDATE 
			".db_tables('uploads')." 
		SET 
			".$field." = '".db_prep_sql($value)."' 
		WHERE 
			id = '".$id."'
	";
	
	return db_query($q);

}

function get_media_config($id){
	
	$q = "
		SELECT 
			id,
			name,
			directory,
			container,
			textarea,
			crop,
			ratio,
			multi,
			selector_h,
			selector_w,
			target_h,
			target_w
		FROM 
			".db_tables('uploads_config')." 
		WHERE 
			id = '".$id."' 
		LIMIT 
			1
	";
		
	//echo $q;

	$result = db_query($q);

	if(!$result || (db_num_rows($result) < 1))
		return NULL;

	return db_fetch_row($result);

}

function get_uploads($id){
	global $database;

	$q = "
		SELECT 
			u.id AS id
			,u.title AS title
			,u.filename AS filename
			,u.type AS type
			,u.ext AS ext
			,u.date AS date
			,c.id AS config_id
			,c.name AS name
			,c.directory AS directory
			,c.container AS container
			,c.textarea AS textarea
			,c.crop AS crop
			,c.ratio AS ratio
			,c.multi AS multi
			,c.selector_w AS selector_w
			,c.selector_h AS selector_h
			,c.target_w AS target_w
			,c.target_h AS target_h
		FROM
			".db_tables('uploads')." AS u
		INNER JOIN
			".db_tables('uploads_config')." AS c
		ON
			c.id = u.config_id 
		WHERE
			u.id = '".$id."' 
		LIMIT 
			1
	";

	//echo $q;

	$result = db_query($q);

	if(!$result || (db_num_rows($result) < 1))
		return NULL;

	return db_fetch_row($result);

}

function setTransparency($imgSrc,$imgDest,$ext){

	if($ext == "png" || $ext == "gif"){
		$trnprt_indx = imagecolortransparent($imgSrc);
		// If we have a specific transparent color
		if ($trnprt_indx >= 0) {
			// Get the original image's transparent color's RGB values
			$trnprt_color    = imagecolorsforindex($imgSrc, $trnprt_indx);
			// Allocate the same color in the new image resource
			$trnprt_indx    = imagecolorallocate($imgDest, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
			// Completely fill the background of the new image with allocated color.
			imagefill($imgDest, 0, 0, $trnprt_indx);
			// Set the background color for new image to transparent
			imagecolortransparent($imgDest, $trnprt_indx);
		}
		// Always make a transparent background color for PNGs that don't have one allocated already
		elseif ($ext == "png") {
			// Turn off transparency blending (temporarily)
			imagealphablending($imgDest, true);
			// Create a new transparent color for image
			$color = imagecolorallocatealpha($imgDest, 0, 0, 0, 127);
			// Completely fill the background of the new image with allocated color.
			imagefill($imgDest, 0, 0, $color);
			// Restore transparency blending
			imagesavealpha($imgDest, true);
		}

	}
}

function parseImage($ext,$img,$file = null){
	switch($ext){
		case "png":
			imagepng($img,($file != null ? $file : ''));
			break;
		case "jpeg":
			imagejpeg($img,($file ? $file : ''),90);
			break;
		case "jpg":
			imagejpeg($img,($file ? $file : ''),90);
			break;
		case "gif":
			imagegif($img,($file ? $file : ''));
			break;
	}
}

function returnCorrectFunction($ext){
	$function = "";
	switch($ext){
		case "png":
			$function = "imagecreatefrompng";
			break;
		case "jpeg":
			$function = "imagecreatefromjpeg";
			break;
		case "jpg":
			$function = "imagecreatefromjpeg";
			break;
		case "gif":
			$function = "imagecreatefromgif";
			break;
	}
	return $function;
}

function proc_delete() {

	proc_DeleteContent($_POST['id']);

	$json = array(
		'success' => true
	);

	echo json_encode($json);

}

function proc_delete_bulk() {

	$array = explode(',', $_POST['id']);

	foreach ($array as $id)
		proc_DeleteContent($id);

	$json = array(
		'success' => true
	);

	echo json_encode($json);

}

function proc_DeleteContent($id) {

	$files = get_uploads($id);

	@unlink($GLOBALS['MAINURL']."upl/".$files['directory']."/".$files['filename']."_150x150.".$files['ext']);
	@unlink($GLOBALS['MAINURL']."upl/".$files['directory']."/".$files['filename']."_300.".$files['ext']);
	@unlink($GLOBALS['MAINURL']."upl/".$files['directory']."/".$files['filename']."_570.".$files['ext']);
	@unlink($GLOBALS['MAINURL']."upl/".$files['directory']."/".$files['filename'].".".$files['ext']);				

	delete_uploads($id);

	delete_uploads_list('upl_id', $id);

	return true;

}

//////////////////////////////////////////////////////
// OUTPUT 
//////////////////////////////////////////////////////

echo process();

?>