<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
POST_QUEUE($module_name,'modules/acu/security_permissions/post_files/');

##################################################
#	Validation
##################################################

##################################################
#	DB Queries
##################################################
$q = "select id,title from security.section where active";
$section_res = db_query($q,"Get Sections");

$q = "select id,title from security.group where active";
$group_res = db_query($q,"Get Groups");

##################################################
#	Pre-Content
##################################################
$info = (!empty($_POST) ? $_POST : array());

##################################################
#	Content
##################################################
?>
	<h2>Create Security Permission</h2>
	<?php echo DUMP_MESSAGES(); ?>

	<form method="post" action="">
 
		<div class="inputs">
		<label for="section_id">Section <span>*</span></label><br>
		<select required id="section_id" name="section_id">
			<option value="">-Select Section-</option>
<?php
$output = '';
while($row = db_fetch_row($section_res)) {
	$output .= '<option value="'. $row['id'] .'">'. $row['title'] .'</option>';
}
if(!empty($info['section_id'])) {
	$output = str_replace('value="'. $info['section_id'] .'"', 'value="'. $info['section_id'] .'" selected',$output);
}
echo $output;
?>
		</select>
	</div>

	<div class="inputs">
		<label for="group_id">Group <span>*</span></label><br>
		<select required id="group_id" name="group_id">
			<option value="">-Select Group-</option>
<?php
$output = '';
while($row = db_fetch_row($group_res)) {
	$output .= '<option value="'. $row['id'] .'">'. $row['title'] .'</option>';
}
if(!empty($info['group_id'])) {
	$output = str_replace('value="'. $info['group_id'] .'"', 'value="'. $info['group_id'] .'" selected',$output);
}
echo $output;
?>
		</select>
	</div>

	<label class="form_label">Security Permission <span>*</span></label>
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
		<input type="submit" value="Create Security Permission">		
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