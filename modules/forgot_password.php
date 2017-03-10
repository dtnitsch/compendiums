<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__));   # Debugger 
// Required for Posting
post_queue($module_name, "modules/post_files/");

##################################################
#   Validation
##################################################

##################################################
#   DB Queries
##################################################

##################################################
#   Pre-Content
##################################################
if (!empty($_GET["u"])) {
	# Coming to the page from an Email with the "u" field in the URL
	$unique_value = trim($_GET["u"]);

	if (strlen($unique_value) == 40) {
		$q = "
			select 
				fp.user_id
				,fp.is_admin
				,fp.created
			from system.forgot_password as fp
			where
				fp.unique_value = '".db_prep_sql($unique_value)."'
		";

		$update_info = db_fetch($q, "Getting Forgot Password Information");

		// Redirects non admin users to front end player login
		if ($update_info["is_admin"] == "f") {
			safe_redirect("/player-login/?u=".$unique_value);
		}

		if (empty($update_info["user_id"])) {
			error_message("This forgot password attempt is invalid. Please apply again");
		}

	} else {
		error_message("An error has occured. Please resubmit the Forgot Password Form");
	}

}

##################################################
#   Content
##################################################
?>

<div class='login_box'>

	<form method="post" action="" >

	<?php

	   	if (empty($_GET["u"])) {

			if (empty($_POST)) {
				forgot_password_form();
			} else if (error_message()) {
				forgot_password_form();
			} else {
				echo "<p>An email has been sent. Please check your spam box if you do not receive the Forgot Password email within the next 10 minutes.</p>";
			}

		} else {

			if (empty($update_info)) {
				forgot_password_form();
			} else {

				$x = time() - strtotime($update_info["created"]);

				# Check if the date_created is over 1 day old (checking for less than 86,400 = older than a day)
				if ($x > 86400) {
					error_message("It has been over 24 hours since the Forgot Password email was sent.  Please apply again!");
					forgot_password_form();
				} else {

					if ($update_info["is_admin"] == "t") { $GLOBALS["show_password_acu"] = true; }

					update_password($update_info);

				}
			}

		}

	?>

	</form>
</div>


<?php
##################################################
#   Javascript Functions
##################################################

##################################################
#   Additional PHP Functions
##################################################
function forgot_password_form() {

	$login = '';
	if(!empty($_POST['email'])) { $login = ' value="'. $_POST['email'] .'"'; }
?>
    <h1>Forgot Password</h1>	

	<?php echo dump_messages(); ?>

	<p>Please fill out the form below and we will send you an email with instructions on how to reset your password.</p>

    <div class='mt'>
        <div class='form_label'>Email Address<span>*</span></div>
        <div class='form_data'>
            <input name="email" class="login input_full" type="text" placeholder="example@address.com"<?php echo $login; ?> autofocus required>   
        </div>

        <input type="submit" id="submit" value="Submit">
    </div>

<?php
}

function update_password($info) {
?>
    <h1>Re-Type Password</h1>	

	<?php echo dump_messages(); ?>

    <div class='mt'>
		<?php echo run_module("password"); ?>

        <input type="submit" id="submit" value="Submit">
	</div>

    <input type='hidden' name='password_change' value='1' />
    <input type='hidden' name='user_id' value='<?php echo $info['user_id']; ?>' />
<?php
}

##################################################
#   EOF
##################################################