<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
#POST_QUEUE(substr(basename(__FILE__),0,-4));	# Needed if you are posting on this page
// if(!HAS_ACCESS('dynamic_web_pages')) { BACK_REDIRECT(); }

if(empty($_SESSION['user']['id'])) {
    safe_redirect("/player-login/");
    die();
}

$path_data = $GLOBALS["project_info"]["path_data"];

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
add_css("fancybox/jquery.fancybox.css");
add_js("fancybox/jquery.fancybox.pack.js", 4);

$GLOBALS["activity_info"]["title"] = (empty($info["title"]) ? "Clever Crazes" : $info["title"]);
$GLOBALS["activity_info"]["is_guest"] = (empty($_SESSION["is_guest"]) ? "false" : $_SESSION["is_guest"]);
$GLOBALS["activity_info"]["user_id"] = (empty($_SESSION["user"]["id"]) ? "false" : $_SESSION["user"]["id"]);
$GLOBALS["activity_info"]["student_id"] = (empty($_SESSION["student_id"]) ? "false" : $_SESSION["student_id"]);
$GLOBALS["activity_info"]["student_grade"] = (empty($_SESSION["student_grade"]) ? "false" : $_SESSION["student_grade"]);
$GLOBALS["activity_info"]["student_firstname"] = (empty($_SESSION["student_firstname"]) ? "false" : $_SESSION["student_firstname"]);
$GLOBALS['load_sidebar'] = 'myaccount';


##################################################
#   Content
##################################################

$dynamic_variables = json_decode($path_data["dynamic_variables"], true);
_error_debug("Dynamic Variables: ", $dynamic_variables);

?>

<?php echo DUMP_MESSAGES(); ?>

<style type="text/css">
	/* Fancybox border override */
	.fancybox-skin {
 		background-color: #FFC425 !important; /* or whatever */
	}

	/* Modal center text alignment override */
	div.greenbox ul li {
		text-align: left;
	}
</style>

<div id="ethics_modal" style="display: none; background: #FFC425;">

		<h1 style="">Take the Clever Crazes for Kids Ethics Pledge</h1>

		<div  class="greenbox margin0" style="width: 360px; font-size: 14px;">
				<h3>When I access CleverCrazes.com:</h3>
				<ul>
					<li>I know that being ethical and honest is totally cool.</li>
					<li>I promise that when I enter my information about how much physical activity I've done, I will be accurate.</li>
					<li>I promise that when I enter my information about how many good food choices I have made, I will be accurate.</li>
					<li>I promise that when I enter my information about how many changes my family and I have made to go green that I will be accurate.</li>
				</ul>
		</div>

		<div id="ethics_flash_container" style="position: absolute; top: 0px; left: 350px ; min-height: 480px;"></div>

		<div class="blackbox" style="width: 480px; margin: 0 auto; margin-top: 15px;">
				<a href="#" id="submit_ethics_pledge">Click this button and join our cool club</a>
				<!--p class="center"><a href="#" id="cancel_ethics_pledge">Not Now</a></p-->
		</div>



</div>

<div class="headline">

	<h1><?php echo (empty($info["title"]) ? "Welcome to CleverCrazes.com" : $info["title"]); ?></h1>

</div>

<div class="body">

	<div id="flash_container"></div>

</div>

<?php
##################################################
#   Javascript Functions
##################################################
ob_start();
?>
<script>

	var alternate_content = '',
		swf_path = <?php echo (empty($dynamic_variables["swf_path"]) ? false : '"'.$dynamic_variables["swf_path"]).'"'; ?>,
		world_alias = <?php echo (empty($dynamic_variables["world_alias"]) ? false : '"'.$dynamic_variables["world_alias"]).'"'; ?>,
		is_guest = <?php echo $GLOBALS["activity_info"]["is_guest"]; ?>,
		user_id = <?php echo $GLOBALS["activity_info"]["user_id"]; ?>,
		student_id = <?php echo $GLOBALS["activity_info"]["student_id"]; ?>,
		student_grade = <?php echo $GLOBALS["activity_info"]["student_grade"]; ?>,
		student_firstname = false,
		ethics_student_id = false;

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
				,"FlashVars", "world="+world_alias+"&is_guest="+is_guest+"&user_id="+user_id+"&grade="+student_grade+"&student_id="+student_id
			));

		} else {

			// Flash Player not installed or does not meet site requirements. Append alternate content below.
			alternateContent += '<h2>Clever Crazes for Kids requires Adobe Flash to view this content. <a href="http://get.adobe.com/flashplayer/">Click here</a> to download Adobe Flash</h2>';

			$("#flash_container").append(alternateContent);
			
		}

	}
	
	function set_session_student_id(student_id, student_grade, student_firstname) {

		$.ajax({
			"type": "POST"
			,"url": "/lib/set_session_student_id.php"
			,"data": {
				"set_session_student_id": true
				,"student_grade": student_grade
				,"student_id": student_id
				,"student_firstname": student_firstname
			}
			,"dataType": "json"
			,"success": function(data) {
				if (data.success) {
					window.location.href='/myaccount/'
				}
			}
		});

		return false;

	}

	function show_ethics_pledge(student_id, student_grade, student_firstname) {
		//xmlreplaceinnerhtml("/ethics/?student_id="+student_id);

		ethics_student_id = student_id;

		if (typeof AC_FL_RunContent != "function" || typeof DetectFlashVer != "function") {

			// Is missing ac_runactivecontent.js include
			alert("This page is blocking required code to function.");

		} else {

			// Check for compatible version of Flash Player
			if (DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision)) {

				// Embed the flash movie
				$("#ethics_flash_container").append(AC_FL_RunContent(
					"codebase", "http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0"
					,"width", "350"
					,"height", "400"
					,"src", "/swf/ethics"
					,"quality", "high"
					,"pluginspage", "http://www.macromedia.com/go/getflashplayer"
					,"align", "middle"
					,"play", "true"
					,"loop", "true"
					,"scale", "showall"
					,"wmode", "transparent"
					,"devicefont", "false"
					,"id", "flashcontainer"
					,"bgcolor", "#000"
					,"name", "container"
					,"menu", "true"
					,"allowScriptAccess", "sameDomain"
					,"allowFullScreen", "false"
					,"movie", "/swf/ethics"
					,"salign", ""
					,"FlashVars", ""
				));

			} else {

				// Flash Player not installed or does not meet site requirements. Append alternate content below.
				alternateContent += '<h2>Clever Crazes for Kids requires Adobe Flash to view this content. <a href="http://get.adobe.com/flashplayer/">Click here</a> to download Adobe Flash</h2>';

				$("#ethics_flash_container").append(alternateContent);

			}

		}

		$.fancybox.open({
			"type": "inline"
			,"href": "#ethics_modal"
			//,"padding": 15
			,"minWidth": 720
			//,"minHeight": 480
			,"scrolling": "no"
			,"afterClose": function() {
				$("#ethics_flash_container").empty();
			}
		});

	}

	/*
	* On accept ethics pledge run ethics.ajax.php update
	*/
	$("a#submit_ethics_pledge").on("click", false, function() {

		if (ethics_student_id) {
			submit_ethics_pledge();
		} else {
			alert("Student ID Not Set");
		}

		return false;

	});

	function submit_ethics_pledge() {

		$.ajax({
			"type": "POST"
			,"url": "/ajax.php"
			,"data": {
				"apid": "51e6630f24d9f229d721fca534feb9a2"
				,"update_ethics_pledge": true
				,"student_id": ethics_student_id
			}
			,"dataType": "json"
			,"success": function(data) {

				if (typeof ajax_debugger == "function" && typeof data.debug != "undefined") {
					ajax_debugger(data.debug, JSON.stringify(data.debug).length);
					data.debug = null;
				}

				/*
				* Student session set in ethics.ajax.php
				*/
				if (data.success) {
					window.location.href = "/myaccount/";
				}

			}
		});

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