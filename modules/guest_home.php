<?php 
##################################################
#   Document Setup and Security
##################################################

if(!empty($_SESSION['user']['id'])) {
    safe_redirect("/myaccount/");
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
add_js("ac_runactivecontent.js");
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
			$("#flash_container").append(AC_FL_RunContent(
				"codebase", "http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0"
				,"width", "640"
				,"height", "480"
				,"src", "/swf/clevercrazes"
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
				,"movie", "/swf/clevercrazes"
				,"salign", ""
				,"FlashVars", "world=home"
			));

		} else {

			// Flash Player not installed or does not meet site requirements. Append alternate content below.
			alternateContent += '<h2>Clever Crazes for Kids requires Adobe Flash to view this content. <a href="http://get.adobe.com/flashplayer/">Click here</a> to download Adobe Flash</h2>';

			$("#flash_container").append(alternateContent);
			
		}

	}

	function set_session_student_grade(student_grade) {

		$.ajax({
			"type": "POST"
			,"url": "/lib/set_session_student_grade.php"
			,"data": {
				"set_session_student_grade": true
				,"student_grade": student_grade
			}
			,"dataType": "json"
			,"success": function(data) {
				if (data.success) {
					return true;
				}
			}
		});

		return false;

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
