<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

$path_data = $GLOBALS["project_info"]["path_data"];

library("registration_functions.php");

$GLOBALS["dynamic_variables"] = json_decode($path_data["dynamic_variables"], true);
_error_debug("Dynamic Variables: ", $GLOBALS["dynamic_variables"]);

$user_account = get_user_account_details($_SESSION["user"]["id"]);

if (empty($user_account)) {
	safe_redirect("/");
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

		<form name="cck_add_institution">

			<div class="formfieldintro">
				<h2>Add Your School</h2>
			</div>

			<!-- START FORM INPUTS -->
			<div class="row">

				<div class="fieldname">School Name</div>

				<div class="fieldvalue">
					<input name="institution_name" type="text" value="" tabindex="1" class="fieldvaluefld">
				</div>

			</div>

			<div style="clear: both; display: block; width: 100%; height: 15px;"></div>

			<div class="row">

				<div class="fieldname">Address</div>

				<div class="fieldvalue">
					<input name="institution_address" type="text" value="" tabindex="2" class="fieldvaluefld">
				</div>

			</div>

			<div class="row">

				<div class="fieldname">City</div>

				<div class="fieldvalue">
					<input name="institution_city" type="text" value="" tabindex="3" class="fieldvaluefld">
				</div>

			</div>

			<div class="row">

				<div class="fieldname">State</div>

				<div class="fieldvalue">

					<select name="institution_state" size="1" tabindex="4">
						<option value=""></option>
						<option value="non us">Non US</option>
						<option value="al">Alabama</option>
						<option value="ak">Alaska</option>
						<option value="as">American Samoa</option>
						<option value="az">Arizona</option>
						<option value="ar">Arkansas</option>
						<option value="ca">California</option>
						<option value="co">Colorado</option>
						<option value="ct">Connecticut</option>
						<option value="de">Delaware</option>
						<option value="dc">District Of Columbia</option>
						<option value="fl">Florida</option>
						<option value="ga">Georgia</option>
						<option value="gm">Guam</option>
						<option value="hi">Hawaii</option>
						<option value="id">Idaho</option>
						<option value="il">Illinois</option>
						<option value="in">Indiana</option>
						<option value="ia">Iowa</option>
						<option value="ks">Kansas</option>
						<option value="ky">Kentucky</option>
						<option value="la">Louisiana</option>
						<option value="me">Maine</option>
						<option value="md">Maryland</option>
						<option value="ma">Massachusetts</option>
						<option value="mi">Michigan</option>
						<option value="mn">Minnesota</option>
						<option value="ms">Mississippi</option>
						<option value="mo">Missouri</option>
						<option value="mt">Montana</option>
						<option value="ne">Nebraska</option>
						<option value="nv">Nevada</option>
						<option value="nh">New Hampshire</option>
						<option value="nj">New Jersey</option>
						<option value="nm">New Mexico</option>
						<option value="ny">New York</option>
						<option value="nc">North Carolina</option>
						<option value="nd">North Dakota</option>
						<option value="oh">Ohio</option>
						<option value="ok">Oklahoma</option>
						<option value="or">Oregon</option>
						<option value="pa">Pennsylvania</option>
						<option value="pr">Puerto Rico</option>
						<option value="ri">Rhode Island</option>
						<option value="sc">South Carolina</option>
						<option value="sd">South Dakota</option>
						<option value="tn">Tennessee</option>
						<option value="tx">Texas</option>
						<option value="ut">Utah</option>
						<option value="vt">Vermont</option>
						<option value="va">Virginia</option>
						<option value="wa">Washington</option>
						<option value="wv">West Virginia</option>
						<option value="wi">Wisconsin</option>
						<option value="wy">Wyoming</option>
					</select>

				</div>

			</div>


			<div class="row">

				<div class="fieldname">Postal Code</div>

				<div class="fieldvalue">
					<input name="institution_postal_code" type="text" value="" tabindex="5" class="fieldvaluefld">
				</div>

			</div>

			<div class="row">

				<div class="fieldname">Country</div>

				<div class="fieldvalue">
					<input name="institution_country" type="text" value="" tabindex="6" class="fieldvaluefld">
				</div>

			</div>

			<div style="clear: both; display: block; width: 100%; height: 15px;"></div>

			<div class="row">

				<div class="fieldname">Phone Number</div>

				<div class="fieldvalue">
					<input name="institution_phone" type="text" value="" tabindex="7" class="fieldvaluefld">
				</div>

			</div>

			<div class="row">

				<div class="fieldname">Population</div>

				<div class="fieldvalue">
					<input name="institution_population" type="text" value="" tabindex="8" class="fieldvaluefld">
				</div>

			</div>

			<div style="clear: both; display: block; width: 100%; height: 15px;"></div>

			<div class="row_form_buttons">

				<div class="row">

					<div class="fieldname">&nbsp;</div>

					<div class="fieldvalue">

						<input class="button round5" type="submit" value="Continue Registration">

					</div>

				</div>

			</div>

			<div class="formclose"></div>

			<input type="hidden" name="apid" value="070a27400e439c18a98794784f5fe61e">
			<input type="hidden" name="add_institution" value="true">

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

var form_element = "form[name=cck_add_institution]";

// START DOM READY
$(function() {

	$(form_element).validate({
		"rules": {
			"institution_name": {
				"required": true
			}
			,"institution_address": {
				"required": true
			}
			,"institution_city": {
				"required": true
			}
			,"institution_state": {
				"required": true
			}
			,"institution_postal_code": {
				"required": true
			}
			,"institution_country": {
				"required": true
			}
			,"institution_phone": {
				"required": true
			}
			,"institution_population": {
				"required": true
			}
		}

		,"errorPlacement": function(err, e) {
			$(e).closest("div.row").find("div.fieldname").prepend(err);
		}
	});

});
// END DOM READY

$("input[type=submit]").on("click", false, function() {

	if ($(form_element).valid()) {
		submit_add_school();
	}

	return false;

});

function submit_add_school() {

	var form = $(form_element).serialize();

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
				window.location.href = "/myaccount/";
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