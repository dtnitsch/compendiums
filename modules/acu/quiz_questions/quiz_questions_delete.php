<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("quiz_questions_delete")) { back_redirect(); }

POST_QUEUE($module_name,'modules/acu/quiz_questions/post_files/');

##################################################
#   Validation
##################################################
$id = GET_PAGE_ID();

if (empty($id)) {
	// Warning message function does not exist. Bug found 08/10/2016 - TRM
	//WARNING_MESSAGE("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect("/acu/quiz_questions/");
}

##################################################
#   DB Queries
##################################################
$info = db_fetch("select * from \"activities\".\"quiz_questions\" where id = ".$id, "Getting Question Info");

##################################################
#   Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/quiz_questions/");

##################################################
#   Content
##################################################
?>
	<h2 class='quiz-questions'>Delete Question: <?php echo $info["question"]; ?></h2>
  
  <div class='content_container'>

	<?= quiz_questions_navigation($id, "delete") ?>

	<?php echo DUMP_MESSAGES(); ?>

	<form method="post" action="">
 
	<div class="delete_box_heading">
	    <p>Are you sure you want to do that?</p>
	</div>

	 <div class="delete_box">
		<input type="submit" value="Confirm Delete">
		<input type='hidden' name='id' value='<?php echo $id; ?>'>
		<input type="button" onclick="window.location.href='/acu/quiz-questions/'" value="Do Not Delete">
	</div>

	</form>
</div>
<?php
	site_wide_notes('ajax',$GLOBALS['project_info']['path_data']['id'],$id);
?>


<?php
##################################################
#	Javascript Functions
##################################################
$closure = function() {
	ob_start();
?>

<?php
	return trim(ob_get_clean());
};
$js = $closure();
if(!empty($js)) { ADD_JS_CODE($js); }

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################
?>