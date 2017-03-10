<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("admin_schools_edit")) { back_redirect(); }

post_queue($module_name,'modules/acu/schools/post_files/');

##################################################
#	Validation
##################################################
$id = get_page_id();
if(empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/acu/schools/');
}

##################################################
#	DB Queries
##################################################

$q = "select * from public.institutions where id = '". $id ."'";
$info = db_fetch($q,"Getting Insititution");

$q = "select id,title from public.institution_types where active";
$institution_types_res = db_query($q,"Getting Institution Types");

$q = "select id,title from supplements.regions where active and country_id = 236 order by title";
$regions_res = db_query($q,"Getting Regions");

$q = "select id,title from supplements.countries where active";
$countries_res = db_query($q,"Getting Countries");

$q = "select count(institution_id) as cnt from system.students where institution_id = '". $id ."' group by institution_id";
$info['counts'] = db_fetch($q,"Getting Insititution Counts");

##################################################
#	Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/schools/");

library("validation.php");
add_js("validation.js");

if(!empty($_POST)) {
	$info = $_POST;
}
##################################################
#	Content
##################################################
?>
	<h2 class='schools'>Edit School: <?php echo $info["title"]; ?></h2>

	<div class='content_container'>

		<?= school_navigation($id,"edit") ?>

		<div id="messages">
			<?= dump_messages() ?>
		</div>

		<form method="post" action="" onsubmit="return v.validate();"> <!-- return false; return v.validate() -->

		<div class='float_right' style='width: 50%;'>
			<label class="form_label" for="email">School ID</label>
			<div class="form_text">
				<?php echo $id; ?>
			</div>

			<label class="form_label" for="email">Email</label>
			<div class="form_data">
				<input type="text" name="email" id="email" class="input_large" value="<?php echo (!empty($info["email"]) ? $info["email"] : ''); ?>">
			</div>

			<label class="form_label" for="phone">Phone Number</label>
			<div class="form_data">
				<input type="text" name="phone" id="phone" class="input_medium" value="<?php echo (!empty($info["phone"]) ? $info["phone"] : ''); ?>">
			</div>

			<label class="form_label" for="population">Population</label>
			<div class="form_data">
				<input type="text" name="population" id="population" class="input_small" value="<?php echo (!empty($info["population"]) ? $info["population"] : 0); ?>">
			</div>

			<label class="form_label" for="population">Registered Count</label>
			<div class="form_text">
				<?php echo (!empty($info['counts']['cnt']) ? $info['counts']['cnt'] : 0); ?>
			</div>

			<label class="form_label" for="population">Registered Percentage</label>
			<div class="form_text">
				<?php
					$perc = 0;
					if(!empty($info['counts']['cnt']) && !empty($info["population"])) {
						$perc = ($info['counts']['cnt'] / $info["population"]) * 100;
					}
					echo number_format($perc,2)."%";
				?>
			</div>

		</div>

		<label class="form_label" for="title">Institution Title <span>*</span></label>
		<div class="form_data">
			<input type="text" name="title" id="title" class="input_xxlarge" value="<?php if(!empty($info["title"])) { echo $info["title"]; } ?>">
		</div>

		<label class="form_label" for="institution_type_id">Institution Type <span>*</span></label>
		<div class="form_data">
			<?php
				echo select_builder(
					$institution_types_res
					,'institution_type_id'
					,array(
						"display" => "-Select Institution Type-"
						,"selected" => $info['institution_type_id']
					));
			?>
		</div>

		<label class="form_label" for="site_name">Site Name</label>
		<div class="form_data">
			<input type="text" name="site_name" id="site_name" class="input_xlarge" value="<?php if(!empty($info["site_name"])) { echo $info["site_name"]; } ?>">
		</div>

		<label class="form_label" for="address1">Address</label>
		<div class="form_data">
			<input type="text" name="address1" id="address1" class="input_xlarge" value="<?php if(!empty($info["address1"])) { echo $info["address1"]; } ?>">
		</div>

		<label class="form_label" for="address2">Suite/Apt</label>
		<div class="form_data">
			<input type="text" name="address2" id="address2" class="input_xlarge" value="<?php if(!empty($info["address2"])) { echo $info["address2"]; } ?>">
		</div>

		<label class="form_label" for="city">City</label>
		<div class="form_data">
			<input type="text" name="city" id="city" class="input_xlarge" value="<?php if(!empty($info["city"])) { echo $info["city"]; } ?>">
		</div>

		<label class="form_label" for="region_id">State</label>
		<div class="form_data">
			<?php
				echo select_builder(
					$regions_res
					,'region_id'
					,array(
						"display" => "-Select State-"
						,"selected" => $info['region_id']
					));
			?>
		</div>

		<label class="form_label" for="postal_code">Postal Code <span>*</span></label>
		<div class="form_data">
			<input type="text" name="postal_code" id="postal_code" class="input_small" value="<?php if(!empty($info["postal_code"])) { echo $info["postal_code"]; } ?>">
		</div>


		<label class="form_label" for="country_id">Country</label>
		<div class="form_data">
			<?php
				echo select_builder(
					$countries_res
					,'country_id'
					,array(
						"display" => "-Select Country-"
						,"selected" => $info['country_id']
					));
			?>
		</div>


		<p>
			<input type="submit" value="Update Information">
			<input type='hidden' name='id' value='<?php echo $id; ?>'>
		</p>

		</form>

	</div>

<?php
	echo run_module("user", "modules/acu/users");
	echo run_module("students", "modules/acu/students");
	site_wide_notes('ajax',$GLOBALS['project_info']['path_data']['id'],$id);
?>

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

	// v.custom("email_db", function() {
	// 	return false;
	// },"JS EMail thingy failed");

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