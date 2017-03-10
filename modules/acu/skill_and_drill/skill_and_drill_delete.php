<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("skill_and_drill_delete")) { back_redirect(); }

post_queue($module_name,'modules/acu/skill_and_drill/post_files/');

##################################################
#   Validation
##################################################
$id = get_page_id();
if(empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/acu/skill_and_drill/');
}

##################################################
#   DB Queries
##################################################
$info = db_fetch("select * from public.skill_and_drill where id='". $id ."'",'Getting Themes');

##################################################
#   Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/skill_and_drill/");

##################################################
#   Content
##################################################
?>
	<h2 class='skill-and-drill'>Delete Theme: <?php echo $info["title"]; ?></h2>

  <div class='content_container'>

	<?= skill_and_drill_navigation($id,"delete") ?>

	<?php echo dump_messages(); ?>

	<form method="post" action="">
 
	<div class="delete_box_heading">
	    <p>Are you sure you want to do that?</p>
	</div>

	 <div class="delete_box">
		<input type="submit" value="Confirm Delete">
		<input type='hidden' name='id' value='<?php echo $id; ?>'>
		<input type="button" onclick="window.location.href='/acu/skill-and-drill/'" value="Do Not Delete">
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
$closure = function() {
	ob_start();
?>

<?php
	return trim(ob_get_clean());
};
$js = $closure();
if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################
?>