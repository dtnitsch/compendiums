<?php 
##################################################
#   Document Setup and Security
##################################################
// if(!HAS_ACCESS("template")) { BACK_REDIRECT(); }
// POST_QUEUE($module_name,"modules/post_files/");

##################################################
#   Validation
##################################################
// $id = GET_PAGE_ID();
// if(empty($id)) {
// 	WARNING_MESSAGE("An error occured while trying to edit this record:  Missing Requred ID");
// 	SAFE_REDIRECT("/acu/paths/");
// }

##################################################
#   DB Queries
##################################################
// $info = db_query("select * from users","Getting all users");

##################################################
#   Pre-Content
##################################################
// $info = array();
// if(!empty($_POST)) {
// 	$info = $_POST;
// } else {
// 	$info = db_fetch("select * from path where id = '". $id ."'",'Getting Path');
// }
// 
// ADD_CSS("new_stuff.css"); // You can add weight by adding an integer second param, ex: 100
// ADD_JS("new_stuff.js"); // You can add weight by adding an integer second param, ex: 100

##################################################
#   Content
##################################################
?>

<p>This is a template page</p>
<?php echo DUMP_MESSAGES(); ?>

<?php #echo do_somthing_unique_here(); ?>

<?php

##################################################
#   Javascript Functions
##################################################
/*
ob_start();
?>
<?php
$js = trim(ob_get_clean());
if(!empty($js)) { ADD_JS_CODE($js); }
*/

##################################################
#   Additional PHP Functions
##################################################
// function do_somthing_unique_here() {
// 	return "<p>Something unique to this page</p>";
// }

##################################################
#   EOF
##################################################
