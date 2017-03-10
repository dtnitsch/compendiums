<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__));   # Debugger 
// Required for Posting
post_queue($module_name,"modules/post_files/");

##################################################
#   Validation
##################################################

##################################################
#   DB Queries
##################################################

##################################################
#   Pre-Content
##################################################

##################################################
#   Content
##################################################
#siession_start();

?>
<form method="post" action="" >

<div class='login_box'>

    <h1>Administrative Login</h1>
  
    <?php
    	echo dump_messages();
    	
        $login = '';
        if(!empty($_POST['login_input'])) { $login = ' value="'. $_POST['login_input'] .'"'; }
        else if(!empty($_COOKIE['r'])) { $login = ' value="'. $_COOKIE['r'] .'"'; }
    ?>
    <div class='mt'>
        <div class='form_label'>Email <span>*</span></div>
        <div class='form_data'>
            <input name="login_input" class="login input_full" type="text" placeholder="Email"<?php echo $login; ?> autofocus required>   
        </div>

        <div class='form_label'>Password <span>*</span></div>
        <div class='form_data'>
            <input name="login_password" class="pass input_full" type="password" placeholder="Password"<?php echo ($login != '' ? ' autofocus' : ''); ?> required>
        </div>
    </div>
    <div class='mt'>
        <button id='submit' class='login'>Login</button>
        <input type="hidden" id="referer" name="referer" value="<?php (!empty($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : ""); ?>">
    </div>

    <div class='mt'>
        <a href="/acu/forgot_password/">Forgot your password?</a><br>
    </div>

</div>

</form>


<?php
##################################################
#   Javascript Functions
##################################################
// ob_start();
// ?><?php
// $js = trim(ob_get_clean());
// if(!empty($js)) { ADD_JS_CODE($js); }

##################################################
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################
