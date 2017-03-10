<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("security_role_delete")) { back_redirect(); }

post_queue($module_name,'modules/acu/security_roles/post_files/');

##################################################
#   Validation
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/security_roles/");

$id = get_page_id();
if(empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/acu/security-roles/');
}

##################################################
#   DB Queries
##################################################
$info = db_fetch("select * from security.roles where id='". $id ."'",'Getting Security Role');

##################################################
#   Pre-Content
##################################################

##################################################
#   Content
##################################################
?>
	<h2 class='security-roles'>Delete Security Role: <?php echo $info["title"]; ?></h2>
  
  <div class='content_container'>

	<?= security_role_navigation($id,"delete") ?>

	<?= dump_messages() ?>

	<form method="post" action="">
 
	<div class="delete_box_heading">
	    <p>Are you sure you want to do that?</p>
	</div>

	 <div class="delete_box">
		<input type="submit" value="Confirm Delete">
		<input type='hidden' name='id' value='<?php echo $id; ?>'>
	  <input type="button" onclick="window.location.href='/acu/security-roles/'" value="Do Not Delete">
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