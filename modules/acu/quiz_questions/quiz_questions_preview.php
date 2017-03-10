<?php
/*************************************************
INPUT VARIABLES (POST or GET) EXPLANATION
************************************************** 
REQUIRED VARIABLES:
--------------------------------------------------
'id'
	- id column in activities.quiz_questions

OPTIONAL VARIABLES: (if not supplied, the values from the first row of this question's 'availability grid' will be used. The idea behind these is to preview the configuration of a specific row in the availability grid, but these are left optional in case we want to allow for a 'general preview')
--------------------------------------------------
'world_id'
	- id column in public.worlds to determine which world's "skin" to show the question in
'grade_id'
	- id column in public.grades to determine which grade's configuration to use when presenting the question (currently only determines whether or not to play voiceovers. If under 3rd grade, voiceovers are played, otherwise they are not)
'theme_id'
	- id column in public.themes to determine which theme's 'skin' to use when presenting the question (currently only makes a difference in the world of Art)
'generation_id'
	- id column in public.generations. In theory, this should never make a difference, as questions don't appear in more than one generation, but you never know when you will need it...
'availability_row'
	- if setting this variable, do not set any of the other optional variables. This will set all of the above optional variables to the values from the supplied row number in the availability grid. (note that this starts with 1 instead of 0, so if you want to use the 1st row's values, this should be set to '1')
*/

##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("quiz_questions_edit")) { back_redirect(); }

##################################################
#	Validation
##################################################

##################################################
#	DB Queries
##################################################
if(empty($_REQUEST)) {
	$input = array();
} else {
	$input = $_REQUEST;
}
if(empty($input['id'])){
	$input['id'] = '1';
}
if(empty($input['availability_row'])){
	$input['availability_row'] = '1';
}

$q = "
	select 
		qq.id as id
		,qq.question
		,qq.number as number
		,qq.points
		,qq.description
		,f.id as fact_id
	from activities.quiz_questions as qq
	left join activities.quiz_question_fact_map as qqfm on
		qqfm.quiz_question_id = qq.id
	left join public.facts as f on
		f.id = qqfm.fact_id
	where 
		qq.id = '". $input['id'] ."'
	group by
		qq.id
		,qq.question
		,f.id
";

$info = db_fetch($q,"Getting Quiz Questions");


$q = "
	select
		qqm.active as active
		,qqm.id as id
		,qqm.world_id as world_id
		,qqm.grade_id as grade_id
		,qqm.theme_id as theme_id
		,qqm.generation_id as generation_id
		,pw.alias as world_alias
		,pt.alias as theme_alias
	from activities.quiz_question_map as qqm
	left join public.worlds as pw on
		pw.id = qqm.world_id
	left join public.themes as pt on
		pt.id = qqm.theme_id
	where
		qqm.quiz_question_id = '". $input['id'] ."'
		and qqm.active
	order by
		world_id
		,grade_id
		,theme_id
		,generation_id
";
$res = db_query($q,'Getting Quiz Questions Grid');

$grid = array();
while($row = db_fetch_row($res)) {
	$grid[] = $row;
}


##################################################
#	Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/quiz_questions/");

// set up variables to be sent to the flash
$flash_vars = array();
$flash_vars['number'] = $info['number'];
$flash_vars['id'] = $info['id'];

// WORLD
if(!empty($input['world_id'])){
	$q = "
		select
			alias
		from public.worlds
		where
			id = '". $input['world_id'] ."'
			and active
	";
	$res = db_query($q,'Getting Worlds');
	while($row = db_fetch_row($res)) {
		$flash_vars['world'] = $row['alias'];
	}
} else {
	$flash_vars['world'] = $grid[((int)$input['availability_row'])-1]['world_alias'];
}
// GRADE
if(!empty($input['grade_id'])){
	$flash_vars['grade'] = $input['grade_id'];
} else {
	$flash_vars['grade'] = $grid[((int)$input['availability_row'])-1]['grade_id'];
}
// fix all grades not 1-8; kindergarten gets grade of 1, all others default to 4 per CCK's instructions
if($flash_vars['grade']=='16'){
	$flash_vars['grade'] = '1';
}
if($flash_vars['grade']!='1' && $flash_vars['grade']!='2' && $flash_vars['grade']!='3' && $flash_vars['grade']!='4' && $flash_vars['grade']!='5' && $flash_vars['grade']!='6' && $flash_vars['grade']!='7' && $flash_vars['grade']!='8'){
	$flash_vars['grade'] = '4';
}
// THEME
if(!empty($input['theme_id'])){
	$q = "
		select
			alias
		from public.themes
		where
			id = '". $input['theme_id'] ."'
			and active
	";
	$res = db_query($q,'Getting Themes');
	while($row = db_fetch_row($res)) {
		$flash_vars['theme'] = $row['alias'];
	}
} else {
	$flash_vars['theme'] = $grid[((int)$input['availability_row'])-1]['theme_alias'];
}
// GENERATION
if(!empty($input['generation_id'])){
	$flash_vars['generation'] = $input['generation_id'];
} else {
	$flash_vars['generation'] = $grid[((int)$input['availability_row'])-1]['generation_id'];
}
// QUESTION_ID
if(!empty($input['id'])){
	$flash_vars['question_id'] = $input['id'];
} else {
	$flash_vars['question_id'] = $grid[((int)$input['availability_row'])-1]['id'];
}
// QUESTION_NUMBER
if(!empty($info['number'])){
	$flash_vars['question_number'] = $info['number'];
} else {
	$flash_vars['question_number'] = '';
}

##################################################
#	Content
##################################################
?>
<div id="flashcontainer" style="min-height: 480px;">
<script src="/js/ac_runactivecontent.js" type="text/javascript"></script>
<script src="/js/cck2.js" type="text/javascript"></script>
<script type="text/javascript">
	var alternate_content = '';

	if (typeof AC_FL_RunContent != "function" || typeof DetectFlashVer != "function") {

		// Is missing ac_runactivecontent.js include
		alert("This page is blocking required code to function.");

	} else {

		// Check for compatible version of Flash Player
		if (DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision)) {

			// Embed the flash movie
			document.write(AC_FL_RunContent(
				"codebase", "http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0"
				,"width", "640"
				,"height", "480"
				,"src", "/swf/quiz_multiformat"
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
				,"movie", "/swf/quiz_multiformat"
				,"salign", ""
				,"FlashVars", "world=<?php echo $flash_vars['world']; ?>&question_id=<?php echo $flash_vars['question_id']; ?>&question_number=<?php echo $flash_vars['question_number']; ?>&is_guest=true&user_id=0&grade=<?php echo $flash_vars['grade']; ?>&student_id=0&playasclassroom=false&generation=<?php echo $flash_vars['generation']; ?>&theme=<?php echo $flash_vars['theme']; ?>"
			));

		} else {

			// Flash Player not installed or does not meet site requirements. Append alternate content below.
			alternateContent += '<h2>Clever Crazes for Kids requires Adobe Flash to view this content. <a href="http://get.adobe.com/flashplayer/">Click here</a> to download Adobe Flash</h2>';

			document.write(alternateContent);
		}

	}

</script>
</div>

<?php

	//run_module('password');

	site_wide_notes('ajax',$GLOBALS['project_info']['path_data']['id'],$id);

##################################################
#	Javascript Functions
##################################################
ob_start();
?>


<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional PHP Functions
##################################################


##################################################
#	EOF
##################################################
