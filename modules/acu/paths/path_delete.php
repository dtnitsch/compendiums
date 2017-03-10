<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("path_delete")) { back_redirect(); }

POST_QUEUE($module_name,'modules/acu/paths/post_files/');

##################################################
#   Validation
##################################################
$id = GET_PAGE_ID();
if(empty($id)) {
	WARNING_MESSAGE("An error occured while trying to edit this record:  Missing Requred ID");
	SAFE_REDIRECT('/acu/paths/');
}

##################################################
#   DB Queries
##################################################
$info = db_fetch("select * from system.paths where id='". $id ."'",'Getting Path');

##################################################
#   Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/paths/");

##################################################
#   Content
##################################################
?>
	<h2 class='paths'>Delete Path: <?php echo $info["path"]; ?></h2>
  
  <div class='content_container'>

	<?= path_navigation($id,"delete") ?>

	<?php echo DUMP_MESSAGES(); ?>

	<form method="post" action="">
 
	<div class="delete_box_heading">
	    <p>Are you sure you want to do that?</p>
	</div>

	 <div class="delete_box">
		<input type="submit" value="Confirm Delete">
		<input type='hidden' name='id' value='<?php echo $id; ?>'>
		<input type="button" onclick="window.location.href='/acu/paths/'" value="Do Not Delete">
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