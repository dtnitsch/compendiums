<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("schools_add")) { back_redirect(); }

post_queue($module_name, "modules/acu/schools/post_files/");

##################################################
#	Validation
##################################################

##################################################
#	DB Queries
##################################################
$q = "select id,title from supplements.regions where active and country_id = 236 order by title";
$regions_res = db_query($q, "Getting Regions");

$q = "select id,title from supplements.countries where active";
$countries_res = db_query($q, "Getting Countries");

##################################################
#	Pre-Content
##################################################
$info = (!empty($_POST) ? $_POST : array());

library('directory_structure.php');
$templates = directory_list( $GLOBALS['root_path'] ."templates/" );

##################################################
#	Content
##################################################
?>
	<h2 class="schools">Create School</h2>
  
  <div class='content_container'>
	<?php echo dump_messages(); ?>

	<form method="post" action="">

		<div class="form_data">
			<label for="name" class="form_label">School Name<span>*</span></label><br>
			<input type="text" name="name" id="name" class="input_xxlarge">
		</div>

		<div class="form_data">
			<label for="address" class="form_label">Address<span>*</span></label><br>
			<input type="text" name="address" id="address" class="input_xxlarge">
		</div>

		<div class="form_data">
			<label for="city" class="form_label">City<span>*</span></label><br>
			<input type="text" name="city" id="city" class="input_xxlarge">
		</div>

		<label class="form_label" for="region_id">State<span>*</span></label>
		<div class="form_data">
			<?php
				echo select_builder(
					$regions_res
					,'region_id'
					,array(
						"display" => "-Select State-"
						,"selected" => false
					));
			?>
		</div>

		<div class="form_data">
			<label for="postal_code" class="form_label">Postal Code<span>*</span></label><br>
			<input type="text" name="postal_code" id="postal_code" class="input_xxlarge">
		</div>

		<label class="form_label" for="country_id">Country<span>*</span></label>
		<div class="form_data">
			<?php
				echo select_builder(
					$countries_res
					,'country_id'
					,array(
						"display" => "-Select Country-"
						,"selected" => false
					));
			?>
		</div>

		<div class="form_data">
			<label for="phone" class="form_label">Phone Number<span>*</span></label><br>
			<input type="text" name="phone" id="phone" class="input_xxlarge">
		</div>

		<div class="form_data">
			<label for="population" class="form_label">Population<span>*</span></label><br>
			<input type="number" name="population" id="population" class="input_xxlarge">
		</div>

		<p>
			<input type="submit" value="Create School">		
		</p>

	</form>
</div>
<?php
##################################################
#	Javascript Functions
##################################################
ob_start();
?>

<?php
$js = trim(ob_get_clean());
if(!empty($js)) { ADD_JS_CODE($js); }

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################
?>