<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("prizes_delete")) { back_redirect(); }

POST_QUEUE($module_name,'modules/acu/prizes/post_files/');

##################################################
#   Validation
##################################################
$id = GET_PAGE_ID();
if(empty($id)) {
	WARNING_MESSAGE("An error occured while trying to edit this record:  Missing Requred ID");
	SAFE_REDIRECT('/acu/prizes/');
}

##################################################
#   DB Queries
##################################################
$info = db_fetch("select * from public.prizes where id='". $id ."'",'Getting Prize');

##################################################
#   Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/prizes/");

##################################################
#   Content
##################################################
?>
	<h2>Delete Prize: <?php echo $info["title"]; ?></h2>

	<?= prize_navigation($id,"delete") ?>

	<?php echo DUMP_MESSAGES(); ?>

	<form method="post" action="">
 
	<div class="delete_box_heading">
	    Are you sure you want to do that?
	</div>

	 <div class="delete_box">
		<input type="submit" value="Confirm Delete">
		<input type='hidden' name='id' value='<?php echo $id; ?>'>
		&nbsp; &nbsp;
	    <input type="button" onclick="window.location.href='/acu/prizes/'" value="Do Not Delete">
	</div>

	</form>

<?php
	site_wide_notes('ajax',$GLOBALS['project_info']['path_data']['id'],$id);
?>


<?php
##################################################
#	Javascript Functions
##################################################
/*
ob_start();
?>
<script type="text/javascript"></script>
<?php
$js = trim(ob_get_clean());
if(!empty($js)) { ADD_JS_CODE($js); }
*/

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################
?>