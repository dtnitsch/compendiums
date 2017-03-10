<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("schools_delete")) { back_redirect(); }

post_queue($module_name,'modules/acu/schools/post_files/');

##################################################
#   Validation
##################################################
$id = get_page_id();
if(empty($id)) {
	error_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/acu/schools/');
}

##################################################
#   DB Queries
##################################################
$info = db_fetch("select * from public.institutions where id='". $id ."'",'Getting Schools');

##################################################
#   Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/schools/");

##################################################
#   Content
##################################################
?>
	<h2 class='schools'>Delete School: <?php echo $info["title"]; ?></h2>
  
  <div class='content_container'>

	<?= school_navigation($id,"delete") ?>

	<?php echo dump_messages(); ?>

	<form method="post" action="">
 
	<div class="delete_box_heading">
	    <p>Are you sure you want to do that?</p>
	</div>

	 <div class="delete_box">
		<input type="submit" value="Confirm Delete">
		<input type='hidden' name='id' value='<?php echo $id; ?>'>
		<input type="button" onclick="window.location.href='/acu/schools/'" value="Do Not Delete">
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
ob_start();
?>
<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################
?>