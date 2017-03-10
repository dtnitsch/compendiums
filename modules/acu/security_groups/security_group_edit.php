<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
POST_QUEUE($module_name,'modules/acu/security_groups/post_files/');

##################################################
#	Validation
##################################################
$id = GET_PAGE_ID();
if(empty($id)) {
	WARNING_MESSAGE("An error occured while trying to edit this record:  Missing Requred ID");
	SAFE_REDIRECT('/acu/security-groups/');
}

##################################################
#	DB Queries
##################################################

##################################################
#	Pre-Content
##################################################
$info = array();
if(!empty($_POST)) {
	$info = $_POST;
} else {
	$info = db_query("select * from security.group where id='". $id ."'",'Getting Security Group','fetch');
}

##################################################
#	Content
##################################################
?>
	<h2>Edit Security Group: <?php echo $info["title"]; ?></h2>

	<div id="navcontainer">
		<ul id="navlist">
			<li class="active">Edit</li>
			<li><a href="/acu/security-groups/audit/?id=<?php echo $id; ?>">Audit</a></li>
			<li><a href="/acu/security-groups/delete/?id=<?php echo $id; ?>">Delete</a></li>
		</ul>
	</div>

	<?php echo DUMP_MESSAGES(); ?>

	<form method="post" action="">
 
		<label class="form_label">Security Group <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="title" id="title" value="<?php if(!empty($info["title"])) { echo $info["title"]; } ?>">
	</div>

	<label class="form_label">Alias <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="alias" id="alias" value="<?php if(!empty($info["alias"])) { echo $info["alias"]; } ?>">
	</div>

	<div class="inputs">
		<label for="description">Description</label><br>
		<textarea name="description" id="description"><?php if(!empty($info["description"])) { echo $info["description"]; } ?></textarea>
	</div>



	<p>
		<input type="submit" value="Update Information">		
		<input type='hidden' name='id' value='<?php echo $id; ?>'>
	</p>

	</form>
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