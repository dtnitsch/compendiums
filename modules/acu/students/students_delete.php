<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if (!logged_in()) { safe_redirect("/login/"); }
if (!has_access("admin_students_edit")) { back_redirect(); }

post_queue($module_name, "modules/acu/students/post_files/");

##################################################
#	Validation
##################################################
$id = get_page_id();

if (empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect("/acu/students/");
}

##################################################
#	DB Queries
##################################################
library("security_functions.php");
library("students.php");

##################################################
#	Pre-Content
##################################################
library("functions.php", $GLOBALS["root_path"]."modules/acu/students/");

library("validation.php");
add_js("validation.js");

$info = array();

if (!empty($_POST)) {
	$info = $_POST;
} else {
	$info = get_student_by_id($id);
	$info["institution"] = get_student_institution($info["institution_id"]);
	_error_debug("Student Info", $info);
}

##################################################
#	Content
##################################################
?>
	<h2 class="students">Delete Student: <?php echo $info["firstname"]." ".$info["lastname"]; ?></h2>
  
  <div class='content_container'>

	<?= student_navigation($id, "delete") ?>

	<?php echo dump_messages(); ?>

	<form method="post" action="">
 
	<div class="delete_box_heading">
	    <p>Are you sure you want to do that?</p>
	</div>

	 <div class="delete_box">
		<input type="submit" value="Confirm Delete">
		<input type='hidden' name='id' value='<?php echo $id; ?>'>
		<input type="button" onclick="window.location.href='/acu/students/'" value="Do Not Delete">
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