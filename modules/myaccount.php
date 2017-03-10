<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__));   # Debugger
// Required for Posting
// post_queue($module_name,"modules/post_files/");

if(empty($_SESSION['user']['id'])) {
    safe_redirect("/player-login/");
    die();
} else if(empty($_SESSION['user']['student_id'])) {
    // run_module("choose_student");
    safe_redirect("/choose-player/");
    die();
}

##################################################
#   Validation
##################################################

##################################################
#   DB Queries
##################################################

##################################################
#   Pre-Content
##################################################
$GLOBALS['load_sidebar'] = 'myaccount';

##################################################
#   Content
##################################################

?>

	<div id="flash_container"></div>

<?php
##################################################
#   Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">

	var alternate_content = '';

	if (typeof AC_FL_RunContent != "function" || typeof DetectFlashVer != "function") {

		// Is missing ac_runactivecontent.js include
		alert("This page is blocking required code to function.");

	} else {

		// Check for compatible version of Flash Player
		if (DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision)) {

			// Embed the flash movie
			$id("flash_container").innerHTML = AC_FL_RunContent(
				"codebase", "http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0"
				,"width", "640"
				,"height", "480"
				,"src", "/swf/home_w"
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
				,"movie", "/swf/home_w"
				,"salign", ""
				,"FlashVars", "world=home"
			);

		} else {

			// Flash Player not installed or does not meet site requirements. Append alternate content below.
			alternate_content += '<h2>Clever Crazes for Kids requires Adobe Flash to view this content. <a href="http://get.adobe.com/flashplayer/">Click here</a> to download Adobe Flash</h2>';

			$("#flash_container").append(alternate_content);

		}

	}

</script>

<?php

$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }
##################################################
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################
