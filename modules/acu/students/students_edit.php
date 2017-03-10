<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if (!logged_in()) { safe_redirect("/login/"); }
if (!has_access("admin_students_edit")) { back_redirect(); }

post_queue($module_name, "modules/acu/students/post_files/");

##################################################
#	Validation
##################################################
$id = get_page_id();

if (empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect("/acu/students/");
}

##################################################
#	DB Queries
##################################################
library("security_functions.php");
library("students.php");

##################################################
#	Pre-Content
##################################################
library("functions.php", $GLOBALS["root_path"]."modules/acu/students/");

library("validation.php");
add_js("validation.js");

$info = array();

if (!empty($_POST)) {
	$info = $_POST;
} else {
	$info = get_student_by_id($id);
	$info["institution"] = get_student_institution($info["institution_id"]);
	_error_debug("Student Info", $info);
}

##################################################
#	Content
##################################################
?>
	<h2 class='students'>Edit Student: <?php echo $info["firstname"]." ".$info["lastname"]; ?></h2>

	<div class='content_container'>

		<?= student_navigation($id, "edit") ?>

		<div id="messages">
			<?= dump_messages() ?>
		</div>

		<form method="post" action="" onsubmit="return v.validate();"> <!-- return false; return v.validate() -->

			<label class="form_label" for="firstname">First Name <span>*</span></label>
			<div class="form_data">
				<input required type="text" name="firstname" id="firstname" placeholder="firstname" value="<?php if(!empty($info["firstname"])) { echo $info["firstname"]; } ?>">
			</div>

			<label class="form_label" for="lastname">Last Name</label>
			<div class="form_data">
				<input type="text" name="lastname" id="lastname" value="<?php if(!empty($info["lastname"])) { echo $info["lastname"]; } ?>">
			</div>

			<label class="form_label" for="gender_id">Gender</label>
			<div class="form_data">
				<select name="gender_id" id="gender_id" class="">
					<option value="0" <? echo ($info["gender_id"] == 0 ? "selected" : ""); ?>>Gender (Optional)</option>
					<option value="1" <? echo ($info["gender_id"] == 1 ? "selected" : ""); ?>>Boy</option>
					<option value="2" <? echo ($info["gender_id"] == 2 ? "selected" : ""); ?>>Girl</option>
				</select>
			</div>

			<label class="form_label" for="grade_id">Grade <span>*</span></label>
			<div class="form_data">
				<select required name="grade_id" id="grade_id" class="">
					<option value="">Please Select a Grade</option>
					<option value="16" <? echo ($info["grade_id"] == 16 ? "selected" : ""); ?>>Kindergarten</option>
					<option value="1" <? echo ($info["grade_id"] == 1 ? "selected" : ""); ?>>1st Grade</option>
					<option value="2" <? echo ($info["grade_id"] == 2 ? "selected" : ""); ?>>2nd Grade</option>
					<option value="3" <? echo ($info["grade_id"] == 3 ? "selected" : ""); ?>>3rd Grade</option>
					<option value="4" <? echo ($info["grade_id"] == 4 ? "selected" : ""); ?>>4th Grade</option>
					<option value="5" <? echo ($info["grade_id"] == 5 ? "selected" : ""); ?>>5th Grade</option>
					<option value="6" <? echo ($info["grade_id"] == 6 ? "selected" : ""); ?>>6th Grade</option>
					<option value="7" <? echo ($info["grade_id"] == 7 ? "selected" : ""); ?>>7th Grade</option>
					<option value="8" <? echo ($info["grade_id"] == 8 ? "selected" : ""); ?>>8th Grade</option>
					<option value="18" <? echo ($info["grade_id"] == 18 ? "selected" : ""); ?>>Other</option>
				</select>
			</div>

			<?php

				//echo dump($info["institution"]);

				$display = '
					<label class="form_label" for="institution_id">Current Institution <span>*</span></label>
					<div class="form_data" id="test_institution">
						<span id="institution_title">'.$info["institution"]["title"].'</span> - <span id="institution_city">'.$info["institution"]["city"].'</span>, <span id="institution_state">'.$info["institution"]["region_abbreviation"].'</span> - Phone: <span id="institution_phone">'.$info["institution"]["phone"].'</span>
						<input required type="hidden" name="institution_id" id="institution_id" value="'.$info["institution"]["id"].'">
					</div>
				';

				echo $display;

			?>

			<p>
				<input type="submit" value="Update Information">
				<input type="hidden" name="id" value="<?php echo $id; ?>">
			</p>

		</form>

	</div>

	<?php
		echo run_module("change_institution", "modules/acu/schools");
		site_wide_notes('ajax',$GLOBALS['project_info']['path_data']['id'],$id);
	?>

<?php
##################################################
#	Javascript Functions
##################################################
add_js("https://ajax.googleapis.com/ajax/libs/jquery/1.12.3/jquery.min.js", 3);

ob_start();
?>


<script type="text/javascript">
	var j = <?php echo validation_create_json_string(validation_load_file(__DIR__."/validation.json"),"js"); ?>;
	// name of variable should be sent in the validation function
	var v = new validation("v"); 
	v.load_json(j);

	function trigger_show_hide(obj, id) {
		obj.className = (obj.className == 'glyphicons glyphicons-eye-open' ? 'glyphicons glyphicons-eye-close' : 'glyphicons glyphicons-eye-open');
		show_hide(id);
	}

	$("#change_institution").on("click", false, function() {

		$.fancybox.open({
			"type": "inline"
			,"href": "#change_institution_modal"
			,"padding": 30
			,"scrolling": "no"
		});

		return false;

	});

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