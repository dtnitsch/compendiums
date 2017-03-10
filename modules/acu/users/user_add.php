<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("admin_users_add")) { back_redirect(); }

post_queue(substr(basename(__FILE__),0,-4),"modules/acu/users/post_files/");


##################################################
#	Validation
##################################################

##################################################
#	DB Queries
##################################################
library("security_functions.php");
library("users.php");

$role_list = get_security_roles();

##################################################
#	Pre-Content
##################################################
$info = (!empty($_POST) ? $_POST : array());

library("validation.php");
add_js("validation.js");


##################################################
#	Content
##################################################
?>
	<h2 class='users'>Create User</h2>

  <div class='content_container'>

	<div id="messages">
		<?= dump_messages() ?>
	</div>

	<form method="post" action="" onsubmit="return v.validate();">

	<div class="float_right" style="width: 50%;">
		<?php echo run_module("password"); ?>

		<label class="form_label">Security Roles <span>*</span></label>
<?php
	$output = '';
	while($row = db_fetch_row($role_list)) {
		$checked = (!empty($user_roles[$row['id']]) ? ' checked' : '');
		$output .= '<br><label for="role_'. $row['id'] .'"><input type="checkbox" name="roles[]" id="role_'. $row['id'] .'" value="'. $row['id'] .'"'. $checked .'> '. $row['title'] .'</label>';
	}
	echo $output;
?>	</div>
 
	<label class="form_label">First Name <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="firstname" id="firstname" value="<?php if(!empty($info["firstname"])) { echo $info["firstname"]; } ?>">
	</div>

	<label class="form_label">Last Name <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="lastname" id="lastname" value="<?php if(!empty($info["lastname"])) { echo $info["lastname"]; } ?>">
	</div>

	<label class="form_label">Email Address <span>*</span></label>
	<div class="form_data">
		<input required type="email" name="email" id="email" placeholder="example@address.com" value="<?php if(!empty($info["email"])) { echo $info["email"]; } ?>">
	</div>

	<div class="inputs">
		<input type="checkbox" name="is_superadmin" id="is_superadmin" value="t"<?php echo (!empty($info["is_superadmin"]) && $info["is_superadmin"] == "t" ? " checked" : ""); ?>>
		<label for="is_superadmin">Is Superadmin?</label>
	</div>

	<p>
		<input type="submit" value="Create User">		
	</p>

	</form>
  
  </div>

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
?>