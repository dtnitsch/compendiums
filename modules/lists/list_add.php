<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

// if(!logged_in()) { safe_redirect("/login/"); }
// if(!has_access("list_add")) { back_redirect(); }

post_queue($module_name,'modules/lists/post_files/');

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

// library("validation.php");
// add_js("validation.js");

##################################################
#	Content
##################################################
?>
	<h2 class='lists'>Add List</h2>
  
  	<?php echo dump_messages(); ?>
	<form id="addform" method="post" action="">

		<label class="form_label" for="title">List Name <span>*</span></label>
		<div class="form_data">
			<input type="text" name="title" id="title" value="">
		</div>

		<label class="form_label">Visibility</label>
		<div class="form_data">
			<label for="public"><input type="radio" name="visibility" id="public" value="public"> Public</label>
			<label for="private"><input type="radio" name="visibility" id="private" value="private"> Private</label>
		</div>

		<label class="form_label" for="title">Inputs</label>
		<div class="form_data">
			<textarea name="inputs" id="inputs" style="width: 400px; height: 150px;"></textarea>
			<div style="font-size: 80%;">*Notes: Tab Deliminated List - Name &nbsp; Percentage &nbsp; Tags</div>
		</div>

		<label class="form_label" for="title">Input Options</label>
		<div class="form_data">
			<label for="percentages">
				<input type="checkbox" name="options" id="percentages" value="percentages"> Percentages
			</label>
			&nbsp;
			<label for="tags">
				<input type="checkbox" name="options" id="tags" value="tags"> Tags
			</label>
		</div>
		

			<!--input checked type="radio" name="multipart" value="yes"> Individual
			<input type="radio" name="multipart" value="no"> Multi-Part -->

		<!--input type="button" value="Add List" onclick="addform()"-->
		<input type="submit" value="Add List">
	</form>
	
<?php
##################################################
#	Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">
	// var j = <?php #echo validation_create_json_string(validation_load_file(__DIR__."/validation.json"),"js"); ?>;
	// // name of variable should be sent in the validation function
	// var v = new validation("v"); 
	// v.load_json(j);
</script>

<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional ƒHP Functions
##################################################

##################################################
#	EOF
##################################################
?>