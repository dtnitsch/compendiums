<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
#POST_QUEUE(substr(basename(__FILE__),0,-4));	# Needed if you are posting on this page
// if(!HAS_ACCESS('dynamic_web_pages')) { BACK_REDIRECT(); }

$path_data = $GLOBALS['project_info']['path_data'];

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

##################################################
#   Content
##################################################

$GLOBALS["dynamic_variables"] = json_decode($path_data["dynamic_variables"], true);
_error_debug("Dynamic Variables: ", $GLOBALS["dynamic_variables"]);

?>

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
/*
ob_start();
?>

<?php
$js = trim(ob_get_clean());
if(!empty($js)) { ADD_JS_CODE($js); }
*/

##################################################
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################