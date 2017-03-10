<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
if(!HAS_ACCESS('dynamic_content_type_delete')) { BACK_REDIRECT(); }
POST_QUEUE($module_name,'modules/acu/dynamic_content_type/post_files/');

##################################################
#   Validation
##################################################
$id = GET_PAGE_ID();
if(empty($id)) {
	WARNING_MESSAGE("An error occured while trying to edit this record:  Missing Requred ID");
	SAFE_REDIRECT('/acu/dynamic-content-type/');
}

##################################################
#   DB Queries
##################################################
$info = db_fetch("select * from public.dynamic_content_types where id='". $id ."'",'Getting Dynamic Content Type');

##################################################
#   Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/dynamic_content_type/");

##################################################
#   Content
##################################################
?>
	<h2 class='dynamic-content-types'>Delete Dynamic Content Type: <?php echo $info["title"]; ?></h2>
  
  <div class='content_container'>

	<?= dynamic_content_types_navigation($id,"delete") ?>

	<?php echo DUMP_MESSAGES(); ?>

	<form method="post" action="">
 
	<div class="delete_box_heading">
	    <p>Are you sure you want to do that?</p>
	</div>

	 <div class="delete_box">
		<input type="submit" value="Confirm Delete">
		<input type='hidden' name='id' value='<?php echo $id; ?>'>
		<input type="button" onclick="window.location.href='/acu/dynamic-content-type/'" value="Do Not Delete">
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