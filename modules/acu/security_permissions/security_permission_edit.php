<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("security_permission_edit")) { back_redirect(); }

post_queue($module_name,'modules/acu/security_permissions/post_files/');

##################################################
#	Validation
##################################################
$id = get_page_id();
if(empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/acu/security-permissions/');
}

##################################################
#	DB Queries
##################################################
$q = "select id,title from security.sections where active";
$section_res = db_query($q,"Get Sections");

$q = "select id,title from security.groups where active";
$group_res = db_query($q,"Get Groups");

##################################################
#	Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/security_permissions/");

$info = array();
if(!empty($_POST)) {
	$info = $_POST;
} else {
	$info = db_fetch("select * from security.permissions where id='". $id ."'",'Getting Security Permission');
}

##################################################
#	Content
##################################################
?>
	<h2>Edit Security Permission: <?php echo $info["title"]; ?></h2>

	<?= security_permissions_navigation($id,"edit") ?>

	<?php echo dump_messages(); ?>

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
ob_start();
?>

<?php
$js = trim(ob_get_clean());
if(!empty($js)) { ADD_JS_CODE($js); }

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################
?>