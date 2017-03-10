<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
POST_QUEUE($module_name,'modules/acu/pages/post_files/');

##################################################
#   Validation
##################################################
$id = GET_PAGE_ID();
if(empty($id)) {
	WARNING_MESSAGE("An error occured while trying to edit this record:  Missing Requred ID");
	SAFE_REDIRECT('/acu/pages/');
}

##################################################
#   DB Queries
##################################################
$info = db_query("select * from public.page where id='". $id ."'",'Getting Page','fetch');

##################################################
#   Pre-Content
##################################################

##################################################
#   Content
##################################################
?>
	<h2>Delete Page: <?php echo $info["page"]; ?></h2>

	<div id="navcontainer">
		<ul id="navlist">
			<li><a href="/acu/pages/edit/?id=<?php echo $id; ?>">Edit</a></li>
			<li><a href="/acu/pages/audit/?id=<?php echo $id; ?>">Audit</a></li>
			<li class="active">Delete</li>
		</ul>
	</div>

	<?php echo DUMP_MESSAGES(); ?>

	<form method="post" action="">
 
	<div class="delete_box_heading">
	    Are you sure you want to do that?
	</div>

	 <div class="delete_box">
		<input type="submit" value="Confirm Delete">
		<input type='hidden' name='id' value='<?php echo $id; ?>'>
		&nbsp; &nbsp;
	    <input type="button" onclick="window.location.href='/acu/pages/'" value="Do Not Delete">
	</div>

	</form>
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
if(!empty($js)) { ADD_JS_CODE($js); }

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################
?>