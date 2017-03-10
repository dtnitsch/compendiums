<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
POST_QUEUE($module_name,'modules/acu/pages/post_files/');

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
	<h2>Create Page</h2>
	<?php echo DUMP_MESSAGES(); ?>

	<form method="post" action="">
 
		<label class="form_label">Page <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="page" id="page" value="<?php if(!empty($info["page"])) { echo $info["page"]; } ?>">
	</div>

	<label class="form_label">Alias <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="alias" id="alias" value="<?php if(!empty($info["alias"])) { echo $info["alias"]; } ?>">
	</div>

	<div class="inputs">
		<label for="content">Content <span>*</span></label><br>
		<textarea required name="content" id="content"><?php if(!empty($info["content"])) { echo $info["content"]; } ?></textarea>
	</div>



	<p>
		<input type="submit" value="Create Page">		
	</p>

	</form>

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