<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("list_edit")) { back_redirect(); }

post_queue($module_name,'modules/acu/lists/post_files/');

##################################################
#	Validation
##################################################
$id = get_page_id();
if(empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/acu/lists/');
}

##################################################
#	DB Queries
##################################################

##################################################
#	Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/lists/");

library("validation.php");
add_js("validation.js");

$info = array();
if(!empty($_POST)) {
	$info = $_POST;
} else {
	$info = db_fetch("select * from public.lists where id='". $id ."'",'Getting List');
}

##################################################
#	Content
##################################################
?>
	<h2 class='lists'>Edit List: <?php echo $info["title"]; ?></h2>
  
  <div class='content_container'>

	<?= list_navigation($id,"edit") ?>

	<div id="messages">
		<?php echo dump_messages(); ?>
	</div>

	<form method="post" action="" onsubmit="return v.validate();">

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
ob_start();
?>

<script type="text/javascript">
	var j = <?php echo validation_create_json_string(validation_load_file(__DIR__."/validation.json"),"js"); ?>;
	// name of variable should be sent in the validation function
	var v = new validation("v"); 
	v.load_json(j);
	// v.optional('password1',false);
	// v.optional('password2',false);

</script>

<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################
