<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

$path_data = $GLOBALS["project_info"]["path_data"];

library("registration_functions.php");

$GLOBALS["dynamic_variables"] = json_decode($path_data["dynamic_variables"], true);
_error_debug("Dynamic Variables: ", $GLOBALS["dynamic_variables"]);

$user_account = false;

if (!empty($_GET["update"]) && $_GET["update"] == true) {

	if (empty($_SESSION["user"]["pin"])) {
		$_SESSION["pin_redirect"] = "/registration/?update=true";
		safe_redirect("/choose-pin/");
		die();
	}

	if (!empty($_SESSION["user"]["id"])) {
		$user_account = get_user_account_details($_SESSION["user"]["id"]);
	}

}

if (!empty($_GET["update"]) && $_GET["update"] == true) {
	$GLOBALS["registration_type"] = $user_account["registration_type_id"];
} else {
	$GLOBALS["registration_type"] = (empty($GLOBALS["dynamic_variables"]["registration_type"]) ? false : $GLOBALS["dynamic_variables"]["registration_type"]);
}

if (empty($GLOBALS["registration_type"])) {
	safe_redirect("/register/");
}

if (empty($_GET["update"]) && $_SESSION["user"]["id"]) {
	safe_redirect("/myaccount/");
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

##################################################
#   Content
##################################################



?>

<?php echo DUMP_MESSAGES(); ?>

<style>

label.error {
	color: #FF0000;
}

input.error, select.error {
	background: rgba(255, 0, 0, 0.25);
}

</style>

<div id="rightcolumnkids">

	<div class="headline">

		<h1><?php echo (empty($info["title"]) ? "Welcome to CleverCrazes.com" : $info["title"]); ?></h1>

	</div>

	<div class="body">

		<form name="cck_registration">

			<div class="formfieldintro">
				<h2>Enter Your Information</h2>
			</div>

			<!-- START FORM INPUTS -->
			<div class="row">

				<div class="fieldname">First Name</div>

				<div class="fieldvalue">
					<input name="firstname" type="text" value="<?php echo (empty($user_account["firstname"]) ? "" : $user_account["firstname"]); ?>" tabindex="1" class="fieldvaluefld">
				</div>

			</div>

			<div class="row">

				<div class="fieldname">Last Name</div>

				<div class="fieldvalue">
					<input name="lastname" type="text" value="<?php echo (empty($user_account["lastname"]) ? "" : $user_account["lastname"]); ?>" tabindex="2" class="fieldvaluefld">
				</div>

			</div>

			<div class="row">

				<div class="fieldname">Email</div>

				<div class="fieldvalue">
					<input name="email" type="text" value="<?php echo (empty($user_account["email"]) ? "" : $user_account["email"]); ?>" tabindex="3" class="fieldvaluefld">
				</div>

			</div>

			<div class="row">

				<div class="fieldname">Confirm Email</div>

				<div class="fieldvalue">
					<input name="email_confirm" type="text" value="<?php echo (empty($user_account["email"]) ? "" : $user_account["email"]); ?>" tabindex="4" class="fieldvaluefld">
				</div>

			</div>

			<div style="clear: both; display: block; width: 100%; height: 15px;"></div>

			<div class="row">

				<div class="fieldname">Username</div>

				<div class="fieldvalue">
					<input name="username" type="text" value="<?php echo (empty($user_account["username"]) ? "" : $user_account["username"]); ?>" tabindex="5" class="fieldvaluefld">
					<div class="formfieldnote">
						<p style="margin-top: 0px; margin-bottom: 0px; color: #008000; font-style: normal; font-weight: bold;">DO PROVIDE USERNAME TO STUDENTS <span style="color: #000000;">(Required for Login)</span></p>
					</div>
				</div>

			</div>

			<div class="row">

				<div class="fieldname">Password</div>

				<div class="fieldvalue">
					<input name="password" type="password" value="" tabindex="6" class="fieldvaluefld">
				</div>
			</div>

			<div class="row">

				<div class="fieldname">Confirm Password</div>

				<div class="fieldvalue">

					<input name="validate_password" type="password" value="" tabindex="7" class="fieldvaluefld">

					<div class="formfieldnote">
						<p style="margin-top: 0px; margin-bottom: 0px; color: #008000; font-style: normal; font-weight: bold;">DO PROVIDE PASSWORD TO STUDENTS</p>
						<ul style="margin: 0px; padding: 0px; list-style-image: none; color: #000000;">
							<span style="font-style: normal; font-weight: bold;">Password Requirements</span>
							<li>8 Characters or More</li>
							<li>At Least One Numeric Character</li>
							<li>At Least One Lowercase Character</li>
							<li>At Least One Uppercase Character</li>
						</ul>
					</div>

				</div>

			</div>

			<div class="row">

				<div class="fieldname">PIN</div>

				<div class="fieldvalue">

					<input name="pin" type="text" value="<?php echo (empty($user_account["pin"]) ? "" : $user_account["pin"]); ?>" tabindex="8" class="fieldvaluefld">

					<div class="formfieldnote">
						<p style="margin-top: 0px; margin-bottom: 0px; font-style: normal; font-weight: bold;">DO NOT PROVIDE PIN TO STUDENTS</p>
						<ul style="margin: 0px; padding: 0px; list-style-image: none; color: #000000;">
							<span style="font-style: normal; font-weight: bold;">Teachers / Adults Will Need PIN To</span>
							<li>Modify Account Information</li>
							<li>Add Kids</li>
							<li>View Scoreboards</li>
						</ul>
						<ul style="margin: 0px; padding: 0px; list-style-image: none; color: #000000;">
							<span style="font-style: normal; font-weight: bold;">PIN Requirements</span>
							<li>4 Alphanumeric Characters or More</li>
						</ul>
					</div>

				</div>

			</div>

			<div style="clear: both; display: block; width: 100%; height: 15px;"></div>

			<?php echo show_registration_additional_fields($GLOBALS["registration_type"], $user_account); ?>

			<div style="clear: both; display: block; width: 100%; height: 15px;"></div>

			<?php echo show_registration_marketing($GLOBALS["registration_type"], $user_account); ?>

			<div style="clear: both; display: block; width: 100%; height: 15px;"></div>

			<div class="row_form_buttons">

				<div class="row">

					<div class="fieldname">&nbsp;</div>

					<div class="fieldvalue">

						<input class="button round5" type="submit" id="form_submit_button" value="<?php echo (!empty($user_account) ? "Update Registration" : "Continue Registration"); ?>">

					</div>

				</div>

			</div>

			<div class="formclose"></div>

			<input type="hidden" name="apid" value="070a27400e439c18a98794784f5fe61e">

			<?php

				$display = "";

				if (!empty($user_account)) {
					$display .= '
						<input type="hidden" name="user_id" value="'.(empty($user_account["user_id"]) ? "" : $user_account["user_id"]).'">
						<input type="hidden" name="institution_id" value="'.(empty($user_account["institution_id"]) ? "" : $user_account["institution_id"]).'">
						<input type="hidden" name="institution_child_id" value="'.(empty($user_account["institution_child_id"]) ? "" : $user_account["institution_child_id"]).'">
					';
				}

				if (!empty($_GET["update"]) && $_GET["update"] == true) {
					if (!empty($_SESSION["user"]["id"])) {
						$display .= '<input type="hidden" name="update_user_registration" value="true">';	
					}
				} else {
					$display .= '<input type="hidden" name="submit_user_registration" value="true">';
				}

				echo $display;

			?>

			
			<input type="hidden" name="registration_type" value="<?php echo (empty($GLOBALS["registration_type"]) ? "false" : $GLOBALS["registration_type"]); ?>">

		</form>

	</div>

</div>

<?php
##################################################
#   Javascript Functions
##################################################
ob_start();
?>
<script>

var form_element = "form[name=cck_registration]",
	registration_type = <?php echo (empty($GLOBALS["registration_type"]) ? "false" : $GLOBALS["registration_type"]); ?>,
	update = <?php echo (!empty($_GET["update"]) && $_GET["update"] == true ? "true" : "false"); ?>;

// START DOM READY
$(function() {

// if has prop required add <span class="alert">*</span>

	// Additional method checks for password requirements.
	$.validator.addMethod("validPass", function(val, e) {
		return this.optional(e) || /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z].*$/.test(val);
	});

	// Additional method checks for PIN requirements.
	$.validator.addMethod("validPin", function(val, e) {
		return this.optional(e) || /^[a-z0-9]+$/.test(val);
	});

	$(form_element).validate({
		"rules": {
			"firstname": {
				"required": true
				,"maxlength": 96
			}
			,"lastname": {
				"required": true
				,"maxlength": 96
			}
			,"email": {
				"required": true
				,"email": true
				,"maxlength": 192
				,"remote": {
					"url": "/ajax.php"
					,"type": "post"
					,"data": {
						"apid": "070a27400e439c18a98794784f5fe61e"
						,"user_id": <?php echo (empty($user_account["user_id"]) ? 0 : $user_account["user_id"]); ?>
						,"check_existing_email": function() {
							return true;
						}
					}
					,"async": false
				}
			}
			,"email_confirm": {
				"required": true
				,"email": true
				,"maxlength": 192
				,"equalTo": "input[name=email]"
			}
			,"username": {
				"required": true
				,"maxlength": 192
				,"remote": {
					"url": "/ajax.php"
					,"type": "post"
					,"data": {
						"apid": "070a27400e439c18a98794784f5fe61e"
						,"user_id": <?php echo (empty($user_account["user_id"]) ? 0 : $user_account["user_id"]); ?>
						,"check_existing_username": function() {
							return true;
						}
					}
					,"async": false
				}
			}
			,"pin": {
				"required": true
				,"minlength": 4
				,"maxlength": 64
				//,"validPin": true
			}
			,"marketing": {
				"required": true
			}
		}
		,"messages": {
			"email": {
				"remote": "Email address is already in use."
			}
			,"validate_password": {
				"equalTo": "Passwords must match."
			}
			,"username": {
				"remote": "Username is already in use."
			}
			,"password": {
				"validPass": "Password does not meet security requirements."
			}
			,"pin": {
				"validPin": "PIN does not meet security requirements."
			}
		}
		,"errorPlacement": function(err, e) {
			$(e).closest("div.row").find("div.fieldname").prepend(err);
		}
	});

	if (update) {

		$("input[name=password]").rules("add", {
			"minlength": 8
			,"validPass": true
		});

		$("input[name=validate_password]").rules("add", {
			"equalTo": "input[name=password]"
		});

	} else {

		$("input[name=password]").rules("add", {
			"required": true
			,"minlength": 8
			,"validPass": true
		});

		$("input[name=validate_password]").rules("add", {
			"required": true
			,"equalTo": "input[name=password]"
		});

	}

	// ADDITIONAL VALIDATION RULES
	if (registration_type == 1 || registration_type == 6) {

		// TEACHERS
		$("input[name=class_name]").rules("add", {
			"required": true
			,"maxlength": 256
		});

		$("input[name=class_population]").rules("add", {
			"required": true
			,"digits": true
			,"maxlength": 8
		});

		$("input[name=add_kids]").rules("add", {
			"required": true
		});

	} else if (registration_type == 4) {

		// AFTER SCHOOL
		$("input[name=institution_name]").rules("add", {
			"required": true
			,"maxlength": 64
		});

		$("input[name=institution_site]").rules("add", {
			"required": true
			,"maxlength": 128
		});

		$("input[name=position]").rules("add", {
			"required": true
		});

		$("input[name=address]").rules("add", {
			"required": true
			,"maxlength": 128
		});

		$("input[name=city]").rules("add", {
			"required": true
			,"maxlength": 64
		});

		$("select[name=state]").rules("add", {
			"required": true
		});

		$("input[name=zip]").rules("add", {
			"required": true
			,"maxlength": 16
		});

		$("input[name=country]").rules("add", {
			"required": true
		});

		$("input[name=phone]").rules("add", {
			"required": true
			,"maxlength": 64
		});

		$("input[name=class_name]").rules("add", {
			"required": true
			,"maxlength": 256
		});

		$("input[name=class_population]").rules("add", {
			"required": true
			,"digits": true
			,"maxlength": 8
		});

		$("input[name=add_kids]").rules("add", {
			"required": true
		});

	} else if (registration_type == 2) {

		// PARENTS
/*
		$("input[name=address]").rules("add", {
			"required": true
			,"maxlength": 128
		});
*/
		$("input[name=city]").rules("add", {
			"required": true
			,"maxlength": 64
		});

		$("select[name=state]").rules("add", {
			"required": true
		});
/*
		$("input[name=zip]").rules("add", {
			"required": true
			,"maxlength": 16
		});
*/
		$("input[name=country]").rules("add", {
			"required": true
		});
/*
		$("input[name=phone]").rules("add", {
			"required": true
			,"maxlength": 64
		});
*/
	} else if (registration_type == 5) {

		// HOME-SCHOOL
		$("input[name=institution_name]").rules("add", {
			"required": true
			,"maxlength": 64
		});

		$("input[name=address]").rules("add", {
			"required": true
			,"maxlength": 128
		});

		$("input[name=city]").rules("add", {
			"required": true
			,"maxlength": 64
		});

		$("select[name=state]").rules("add", {
			"required": true
		});

		$("input[name=zip]").rules("add", {
			"required": true
			,"maxlength": 16
		});

		$("input[name=country]").rules("add", {
			"required": true
		});

		$("input[name=phone]").rules("add", {
			"required": true
			,"maxlength": 64
		});

		$("input[name=class_population]").rules("add", {
			"required": true
			,"digits": true
			,"maxlength": 8
		});

		$("input[name=add_kids]").rules("add", {
			"required": true
		});

	}

});
// END DOM READY

$("input[type=submit]").on("click", false, function() {

	if ($(form_element).valid()) {
		submit_user_registration();
		document.getElementById("form_submit_button").disabled = true;
	}

	return false;

});

function submit_user_registration() {

	var form = $(form_element).serialize();

	// ON SUCCESS CHOOSE INSTITUTION
	$.ajax({
		"type": "POST"
		,"url": "/ajax.php"
		,"data": form
		,"dataType": "json"
		,"success": function(data) {

			if (typeof ajax_debugger == "function" && typeof data.debug != "undefined") {
				ajax_debugger(data.debug, JSON.stringify(data.debug).length);
				data.debug = null;
			}

			if (data.success) {

				if (data.registration_type == 1 || data.registration_type == 6) {
					window.location.href = "/schools/";
				} else {
					if (data.add_kids == true) {
						window.location.href = "/page/Add_Kids/34/17/";
					} else {
						window.location.href = "/myaccount/";
					}
				}

			}

		}
	});

}

</script>
<?php
$js = trim(ob_get_clean());
if (!empty($js)) { ADD_JS_CODE($js); }

##################################################
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################