<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__));   # Debugger
// Required for Posting
// post_queue($module_name,"modules/post_files/");

$path_data = $GLOBALS["project_info"]["path_data"];

if(empty($_SESSION['user']['id'])) {
    safe_redirect("/player-login/");
    die();
} else if(empty($_SESSION['user']['pin'])) {
    // run_module("choose_student");
    $_SESSION['pin_redirect'] = $_SERVER['SCRIPT_URI'];
    safe_redirect("/choose-pin/");
    die();
}

##################################################
#   Validation
##################################################

##################################################
#   DB Queries
##################################################
$q = "
	select
		dynamic_content.id
		,dynamic_content.alias
		,dynamic_content.title
		,dynamic_content.content
	from public.dynamic_content
	where
		dynamic_content.id = '". $path_data['dynamic_content_id'] ."'
		and dynamic_content.active = 't'
";

$info = db_fetch($q, "Getting dynamic info");
##################################################
#   Pre-Content
##################################################
$GLOBALS['load_sidebar'] = 'myaccount';

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

<?php echo DUMP_MESSAGES(); ?>

<div class="headline">
	<h1><?php echo $info["title"]; ?></h1>
</div>
<div class="body">
	<?php echo base64_decode($info["content"]); ?>
</div>

<?php
##################################################
#   Javascript Functions
##################################################

##################################################
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################