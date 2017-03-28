<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

// if(!logged_in()) { safe_redirect("/login/"); }
// if(!has_access("list_add")) { back_redirect(); }

post_queue($module_name,'modules/post_files/');

##################################################
#	Validation
##################################################

##################################################
#	DB Queries
##################################################

##################################################
#	Pre-Content
##################################################
$info = (!empty($_POST) ? $_POST : array());

// library("validation.php");
// add_js("validation.js");

##################################################
#	Content
##################################################
?>
	<h2 class='lists'>Add List</h2>
  
  	<div id="messages">
		<?php echo dump_messages(); ?>
	</div>

	<form id="addform" method="post" action="" onsubmit="return true;">

		<label class="form_label" for="username">Username <span>*</span></label>
		<div class="form_data">
			<input type="text" name="username" id="username" value="<?php echo $info['username'] ?? ""; ?>">
		</div>

		<label class="form_label" for="password">Password <span>*</span></label>
		<div class="form_data">
			<input type="text" name="password" id="password" value="<?php echo $info['password'] ?? ""; ?>">
		</div>

		<input type="submit" value="Add List">

	</form>
	
<?php
##################################################
#	Javascript Functions
##################################################
ob_start();
?><?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional ƒHP Functions
##################################################

##################################################
#	EOF
##################################################
?>