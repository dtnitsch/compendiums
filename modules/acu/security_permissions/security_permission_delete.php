<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("security_permission_delete")) { back_redirect(); }

post_queue($module_name,'modules/acu/security_permissions/post_files/');

##################################################
#   Validation
##################################################
$id = get_page_id();
if(empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/acu/security-permissions/');
}

##################################################
#   DB Queries
##################################################
$info = db_fetch("select * from security.permissions where id='". $id ."'",'Getting Security Permission');

##################################################
#   Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/security_permissions/");

##################################################
#   Content
##################################################
?>
	<h2>Delete Security Permission: <?php echo $info["title"]; ?></h2>

	<?= security_permissions_navigation($id,"delete") ?>

	<?php echo dump_messages(); ?>

	<form method="post" action="">
 
	<div class="delete_box_heading">
	    Are you sure you want to do that?
	</div>

	 <div class="delete_box">
		<input type="submit" value="Confirm Delete">
		<input type='hidden' name='id' value='<?php echo $id; ?>'>
		&nbsp; &nbsp;
	    <input type="button" onclick="window.location.href='/acu/security-permissions/'" value="Do Not Delete">
	</div>

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
if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################
?>