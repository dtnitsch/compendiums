<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
post_queue($module_name);

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

//$password1 = (!empty($_POST['password1']) ? $_POST['password1'] : '');
//$password2 = (!empty($_POST['password2']) ? $_POST['password2'] : '');

//$password_acu1 = (!empty($_POST['password_acu1']) ? $_POST['password_acu1'] : '');
//$password_acu2 = (!empty($_POST['password_acu2']) ? $_POST['password_acu2'] : '');

##################################################
#	Content
##################################################

$display = "";

if (!empty($GLOBALS["show_password_acu"]) && $_SERVER["SCRIPT_URL"] == "/acu/forgot_password/") {

	$display = '
		<label class="form_label" for="password_acu1">Admin Password <span>*</span></label>
		<div class="form_data">
			<input required type="password" name="password_acu1" id="password_acu1" tabindex="52">
		</div>

		<label class="form_label" for="password_acu2">Admin Password Confirmation <span>*</span></label>
		<div class="form_data">
			<input required type="password" name="password_acu2" id="password_acu2" tabindex="53">
		</div>

		<input type="hidden" name="acu_password_reset" value="true">
	';

} else if (!empty($GLOBALS["user_edit_acu"])) {
	$display = '
		<label class="form_label" for="password1">Password <span>*</span></label>
		<div class="form_data">
			<input type="password" name="password1" id="password1" tabindex="50">
		</div>

		<label class="form_label" for="password2">Password Confirmation <span>*</span></label>
		<div class="form_data">
			<input type="password" name="password2" id="password2" tabindex="51">
		</div>

		<input type="hidden" name="front_password_reset" value="true">
	';
	if (!empty($_SESSION['user']['is_superadmin'])){
		if ($_SESSION['user']['is_superadmin'] && $_SESSION['user'][id] == 53825){
			$display .= '
				<div id="acu_password">
					<a class="add" href="javascript:show_acu_password()">Add ACU Password</a>
				</div>
			';
		}
	}
} else {

	$display = '
		<label class="form_label" for="password1">Password <span>*</span></label>
		<div class="form_data">
			<input required type="password" name="password1" id="password1" tabindex="50">
		</div>

		<label class="form_label" for="password2">Password Confirmation <span>*</span></label>
		<div class="form_data">
			<input required type="password" name="password2" id="password2" tabindex="51">
		</div>

		<input type="hidden" name="front_password_reset" value="true">
	';

}

echo $display;

?>

<?php
##################################################
#	Javascript Functions
##################################################
// ob_start();
// $js = trim(ob_get_clean());
// if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################