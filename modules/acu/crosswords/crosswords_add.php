<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("quiz_questions_add")) { back_redirect(); }

post_queue($module_name,'modules/acu/quiz_questions/post_files/');

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

library("validation.php");
add_js("validation.js");

##################################################
#	Content
##################################################
?>
	<h2 class='quiz-questions'>Create Quiz Questions</h2>
  
  <div class='content_container'>

	<div id="messages">
		<?php echo dump_messages(); ?>
	</div>

	<form method="post" action="" onsubmit="return v.validate();">
 
	<label class="form_label">Title <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="question" id="question" value="<?php if(!empty($info["question"])) { echo $info["question"]; } ?>">
	</div>

	<div class="form_data">
		<label for="description" class="form_label">Description</label><br>
		<textarea name="description" id="description"><?php if(!empty($info["description"])) { echo $info["description"]; } ?></textarea>
	</div>

	<p>
		<input type="submit" value="Create Quiz Questions">		
	</p>

	</form>

</div>
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