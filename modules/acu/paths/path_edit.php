<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("path_edit")) { back_redirect(); }

post_queue($module_name,'modules/acu/paths/post_files/');

##################################################
#	Validation
##################################################
$id = get_page_id();
if(empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/acu/paths/');
}

##################################################
#	DB Queries
##################################################

##################################################
#	Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/paths/");

$info = array();
if(!empty($_POST)) {
	$info = $_POST;
} else {
	$info = db_fetch("select * from system.paths where id='". $id ."'",'Getting Path');
}

library('directory_structure.php');
$templates = directory_list( $GLOBALS['root_path'] ."templates/" );
$modules = directory_list( $GLOBALS['root_path'] ."modules/" );

##################################################
#	Content
##################################################
?>
	<h2 class='paths'>Edit Path: <?php echo $info["path"]; ?></h2>
  
  <div class='content_container'>

	<?= path_navigation($id,"edit") ?>

	<?php echo dump_messages(); ?>

	<form method="post" action="">
 
	<label class="form_label">Path <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="path" id="path" value="<?php if(!empty($info["path"])) { echo $info["path"]; } ?>">
	</div>

	<div class="form_data">
		<label for="module_name" class="form_label">Module Name <span>*</span></label><br>
		<select required id="module_name" name="module_name">
			<option value="">-Select Module Name-</option>
<?php
$output = select_files_and_folders( $modules, '/modules' );
if(!empty($info['module_name'])) {
	if(empty($info['folder'])) { $info['folder'] = 'modules/'; }
	$file = '/'.$info['folder'] . $info['module_name'] .'.php';	
	$output = str_replace('value="'. $file .'"', 'value="'. $file .'" selected',$output);
}
echo $output;
?>
		</select>
	</div>

	<div class="form_data">
		<label for="template" class="form_label">Template <span>*</span></label><br>
		<select required id="template" name="template">
			<option value="">-Select Template-</option>
<?php
$output = select_files_and_folders( $templates, '/templates' );
if(!empty($info['template'])) {
	$file = '/templates/' . $info['template'] .'.template.php';
	$output = str_replace('value="'. $file .'"', 'value="'. $file .'" selected',$output);
}
echo $output;
?>
		</select>
	</div>

	<label class="form_label">Title <span>*</span></label>
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

	<div class="form_data">
		<input type="checkbox" name="is_dynamic" id="is_dynamic" value="t"<?php echo (!empty($info["is_dynamic"]) && $info["is_dynamic"] == "t" ? " checked" : ""); ?>>
		<label for="is_dynamic">Is Dynamic?</label>
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
