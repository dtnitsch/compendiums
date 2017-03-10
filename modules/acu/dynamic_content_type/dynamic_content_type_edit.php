<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
if(!HAS_ACCESS('dynamic_content_type_edit')) { BACK_REDIRECT(); }
POST_QUEUE($module_name,'modules/acu/dynamic_content_type/post_files/');

##################################################
#	Validation
##################################################
$id = GET_PAGE_ID();
if(empty($id)) {
	WARNING_MESSAGE("An error occured while trying to edit this record:  Missing Requred ID");
	SAFE_REDIRECT('/acu/dynamic-content-type/');
}

##################################################
#	DB Queries
##################################################

##################################################
#	Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/dynamic_content_type/");

$info = array();
if(!empty($_POST)) {
	$info = $_POST;
} else {
	$info = db_fetch("select * from public.dynamic_content_types where id='". $id ."'",'Getting Dynamic Content Type');
}

##################################################
#	Content
##################################################
?>
	<h2 class='dynamic-content-types'>Edit Dynamic Content Type: <?php echo $info["title"]; ?></h2>
  
  <div class='content_container'>

	<?= dynamic_content_types_navigation($id,"edit") ?>

	<?php echo DUMP_MESSAGES(); ?>

	<form method="post" action="">
 
		<label class="form_label">Dynamic Content Type <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="title" id="title" value="<?php if(!empty($info["title"])) { echo $info["title"]; } ?>">
	</div>

	<label class="form_label">Alias <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="alias" id="alias" value="<?php if(!empty($info["alias"])) { echo $info["alias"]; } ?>">
	</div>

	<div class="form_data">
		<label for="description" class="form_label">Description</label><br>
		<textarea name="description" id="description"><?php if(!empty($info["description"])) { echo $info["description"]; } ?></textarea>
	</div>



	<p>
		<input type="submit" value="Update Information">		
		<input type='hidden' name='id' value='<?php echo $id; ?>'>
	</p>

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