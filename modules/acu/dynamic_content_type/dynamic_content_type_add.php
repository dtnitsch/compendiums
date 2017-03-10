<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
if(!HAS_ACCESS('dynamic_content_type_add')) { BACK_REDIRECT(); }
POST_QUEUE($module_name,'modules/acu/dynamic_content_type/post_files/');

##################################################
#	Validation
##################################################

##################################################
#	DB Queries
##################################################

##################################################
#	Pre-Content
##################################################
$info = (!empty($_POST) ? $_POST : array());

##################################################
#	Content
##################################################
?>
	<h2 class='dynamic-content-types'>Create Dynamic Content Type</h2>
	<?php echo DUMP_MESSAGES(); ?>
  
<div class='content_container'>
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
		<input type="submit" value="Create Dynamic Content Type">		
	</p>

	</form>
</div>
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