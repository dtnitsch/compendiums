<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
#POST_QUEUE(substr(basename(__FILE__),0,-4));	# Needed if you are posting on this page
// if(!HAS_ACCESS('dynamic_web_pages')) { BACK_REDIRECT(); }

$path_data = $GLOBALS["project_info"]["path_data"];

if (empty($_SESSION["playasaclassroom"])){
	$_SESSION["playasaclassroom"] = "0";
}

##################################################
#   Validation
##################################################

##################################################
#   DB Queries
##################################################
if (!empty($path_data["dynamic_content_id"])){
	$q = "
		select
			dynamic_content.id
			,dynamic_content.alias
			,dynamic_content.title
			,dynamic_content.content
		from public.dynamic_content
		where
			dynamic_content.id = '". $path_data["dynamic_content_id"] ."'
			and dynamic_content.active = 't'
	";

	$info = db_fetch($q, "Getting Dynamic Info");
} else {
	$info = array();
	$info["title"] = $path_data["title"];
}
##################################################
#   Pre-Content
##################################################

$GLOBALS["activity_info"]["title"] = (empty($info["title"]) ? "Clever Crazes" : $info["title"]);
$GLOBALS["activity_info"]["is_guest"] = (empty($_SESSION["is_guest"]) ? "false" : $_SESSION["is_guest"]);
$GLOBALS["activity_info"]["user_id"] = (empty($_SESSION["user"]["id"]) ? "false" : $_SESSION["user"]["id"]);
$GLOBALS["activity_info"]["student_id"] = (empty($_SESSION["student_id"]) ? "false" : $_SESSION["student_id"]);
$GLOBALS["activity_info"]["student_grade"] = (empty($_SESSION["student_grade"]) ? "false" : $_SESSION["student_grade"]);
$GLOBALS["activity_info"]["student_firstname"] = (empty($_SESSION["student_firstname"]) ? "Player" : $_SESSION["student_firstname"]);
$GLOBALS["activity_info"]["as_classroom"] = (empty($_SESSION["playasaclassroom"]) ? "0" : $_SESSION["playasaclassroom"]);

##################################################
#   Content
##################################################

$GLOBALS["dynamic_variables"] = json_decode($path_data["dynamic_variables"], true);
_error_debug("Dynamic Variables: ", $GLOBALS["dynamic_variables"]);

// Conditionals for redirecting to guest home
$should_redirect = false;
if(empty($_SESSION['student_grade'])) {
	$should_redirect = true;
}
if (!empty($GLOBALS["dynamic_variables"]["world_alias"])){
	if ($GLOBALS["dynamic_variables"]["world_alias"] == "stepitup"){
		$should_redirect = false;
	}
}
if($should_redirect == true) {
	safe_redirect("/page/welcome_to_clevercrazescom/72/1/");
	die();
}


?>

<?php echo DUMP_MESSAGES(); ?>

<div class="headline">

	<h1><?php echo (empty($info["title"]) ? "Welcome to CleverCrazes.com" : $info["title"]); ?></h1>

</div>

<div class="body">

	<div id="flash_container" style="min-height: 480px;"></div>

</div>

<?php
##################################################
#   Javascript Functions
##################################################
ob_start();
?>
<script>

	var alternate_content = '',
		swf_path = <?php echo (empty($GLOBALS["dynamic_variables"]["swf_path"]) ? "false" : '"'.$GLOBALS["dynamic_variables"]["swf_path"]).'"'; ?>,
		world_alias = <?php echo (empty($GLOBALS["dynamic_variables"]["world_alias"]) ? "false" : '"'.$GLOBALS["dynamic_variables"]["world_alias"]).'"'; ?>,
		is_guest = <?php echo $GLOBALS["activity_info"]["is_guest"]; ?>,
		user_id = <?php echo $GLOBALS["activity_info"]["user_id"]; ?>,
		student_id = <?php echo $GLOBALS["activity_info"]["student_id"]; ?>,
		student_grade = <?php echo $GLOBALS["activity_info"]["student_grade"]; ?>,
		as_classroom = <?php echo $GLOBALS["activity_info"]["as_classroom"]; ?>;
		

	if (typeof AC_FL_RunContent != "function" || typeof DetectFlashVer != "function") {

		// Is missing ac_runactivecontent.js include
		alert("This page is blocking required code to function.");

	} else {

		// Check for compatible version of Flash Player
		if (DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision)) {

			// Embed the flash movie
			$("#flash_container").append(AC_FL_RunContent(
				"codebase", "http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0"
				,"width", "640"
				,"height", "480"
				,"src", swf_path
				,"quality", "high"
				,"pluginspage", "http://www.macromedia.com/go/getflashplayer"
				,"align", "middle"
				,"play", "true"
				,"loop", "true"
				,"scale", "showall"
				,"wmode", "opaque"
				,"devicefont", "false"
				,"id", "flashcontainer"
				,"bgcolor", "#000"
				,"name", "container"
				,"menu", "true"
				,"allowScriptAccess", "sameDomain"
				,"allowFullScreen", "false"
				,"movie", swf_path
				,"salign", ""
				,"FlashVars", "world="+world_alias+"&is_guest="+is_guest+"&user_id="+user_id+"&grade="+student_grade+"&student_id="+student_id+"&playasclassroom="+as_classroom
			));

		} else {

			// Flash Player not installed or does not meet site requirements. Append alternate content below.
			alternateContent += '<h2>Clever Crazes for Kids requires Adobe Flash to view this content. <a href="http://get.adobe.com/flashplayer/">Click here</a> to download Adobe Flash</h2>';

			$("#flash_container").append(alternateContent);
			
		}

	}
</script>
<?php
$js = trim(ob_get_clean());
if (!empty($js)) { ADD_JS_CODE($js); }

##################################################
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################