<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__));   # Debugger 
// Required for Posting
post_queue($module_name,"modules/post_files/");

if(!empty($_SESSION['user']['id'])) {
    safe_redirect("/choose-player/");
    die();
}

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

		$update_info = db_fetch($q,"Getting Forgot Password Information");

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

<?php

    if(empty($_GET['u'])) {

        if(empty($_POST)) {
            forgot_password_form();

        } else if(error_message()) {
            forgot_password_form();

        } else {
            echo "<p>An email has been sent.  Please check your spam box if you do not receive the Forgot Password email within the next 10 minutes.</p>";
        }

    } else {

        if(empty($update_info)) {
            forgot_password_form();
        } else {
            $x = time() - strtotime($update_info['created']);

            # Check if the date_created is over 1 day old (checking for less than 86,400 = older than a day)
            if($x > 86400) {
                error_message("It has been over 24 hours since the Forgot Password email was sent.  Please apply again!");
                forgot_password_form();
            } else {
                update_password($update_info);
            }
        }

    }

?>




<?php
##################################################
#   Javascript Functions
##################################################
// ob_start();
// $js = trim(ob_get_clean());
// if(!empty($js)) { ADD_JS_CODE($js); }

##################################################
#   Additional PHP Functions
##################################################
function forgot_password_form() {

    $login = '';
    if(!empty($_POST['email'])) { $login = ' value="'. $_POST['email'] .'"'; }
?>

<form method="post" action="" >
<div class='login_box'>

    <h1>Account Login</h1>

    <?php
        echo dump_messages();

        $login = '';
        if(!empty($_POST['login_input'])) { $login = ' value="'. $_POST['login_input'] .'"'; }
        else if(!empty($_COOKIE['r'])) { $login = ' value="'. $_COOKIE['r'] .'"'; }
    ?>
    <div class='mt'>
        <div class='form_label'>Login Name</div>
        <div class='form_data'>
            <input name="login_input" class="login input_full" type="text" placeholder="Username or Email"<?php echo $login; ?> autofocus required>
        </div>

        <div class='form_label'>Password <span>*</span></div>
        <div class='form_data'>
            <input name="login_password" class="pass input_full" type="password" placeholder="Password"<?php echo ($login != '' ? ' autofocus' : ''); ?> required>
        </div>
    </div>
    <div class='mt'>
        <button id='submit' class='login'>Login</button>
        <input type="hidden" id="referer" name="referer" value="<?php (!empty($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : ""); ?>">
        <input type="hidden" id="type" name="type" value="login">
    </div>

</div>
</form>

<form method="post" action="" >
<div class='login_box'>
    <h1>Forgot your password?</h1>
    <p>Simply enter your registered email and we will send it to you:</p>

    <div class='mt'>
        <div class='form_label'>Registered Email</div>
        <div class='form_data'>
            <input name="forgot_password" class="login input_full" type="text">
        </div>
    <div class='mt'>
        <button id='submit' class='register_email'>Submit</button>
        <input type="hidden" id="referer" name="referer" value="<?php (!empty($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : ""); ?>">
        <input type="hidden" id="type" name="type" value="forgot">
    </div>
    </div>
</form>

<?php
}

function update_password($info) {
?>
<form method="post" action="" >

    <h1>Re-Type Password</h1>   

    <?php echo dump_messages(); ?>

    <div class='mt'>
        <?php echo run_module('password'); ?>

        <input type="submit" id="submit" value="Submit">
    </div>

    <input type='hidden' name='password_change' value='1' />
    <input type='hidden' name='user_id' value='<?php echo $info['user_id']; ?>' />
</form>
<?php
}
##################################################
#   EOF
##################################################