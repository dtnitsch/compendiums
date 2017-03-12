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

		<div class="float_left" style="width: 450px;">
			<label class="form_label" for="title">List Name <span>*</span></label>
			<div class="form_data">
				<input type="text" name="title" id="title" value="">
			</div>

			<!--label class="form_label">Visibility</label>
			<div class="form_data">
				<label for="public"><input type="radio" name="visibility" id="public" value="public"> Public</label>
				<label for="private"><input type="radio" name="visibility" id="private" value="private"> Private</label>
			</div-->

			<label class="form_label" for="title">Inputs</label>
			<div class="form_data">
				<textarea name="inputs" id="inputs" onchange="show_example()" onkeyup="show_example()" style="width: 400px; height: 150px;">one
two
three</textarea>
				<div style="font-size: 80%;">*Notes: Tab Deliminated List - Name &nbsp; Percentage &nbsp; Tags</div>
			</div>

			<!--label class="form_label" for="title">Input Options</label>
			<div class="form_data">
				<label for="percentages">
					<input type="checkbox" name="options" id="percentages" value="percentages"> Percentages
				</label>
				&nbsp;
				<label for="tags">
					<input type="checkbox" name="options" id="tags" value="tags"> Tags
				</label>
			</div->
			

				<!--input checked type="radio" name="multipart" value="yes"> Individual
				<input type="radio" name="multipart" value="no"> Multi-Part -->

			<!--input type="button" value="Add List" onclick="addform()"-->
			</div>
		<div id="example" class="float_left" style="width: 300px; padding: 1em;"></div>
		<div class="clear"></div>
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

	function show_example() {
		var pieces = $id('inputs').value.trim().split("\n");
		var len = pieces.length;
		var output = "<strong>Example Output</strong>";
		output += '<ol class="mt">';
		if(len > 10) {
			len = 10;
		}
		for(var i=0; i<len; i++) {
			output += '<li>'+ pieces[i] +'</li>';
		}
		output += "</ol></div>"
		$id('example').innerHTML = output;
	}

	// function validate_list() {
	// 	var pieces = $id('inputs').value.trim().split("\n");
	// 	var line_pieces, num, end_with_nums;
	// 	var output = {};
	// 	for(var i=0, len=pieces.length; i<len; i++) {
	// 		line_pieces = pieces[i].split(',');
	// 		num = 0;
	// 		if(line_pieces.length > 1 && is_numeric(line_pieces[line_pieces.length - 1])) {
	// 			end_with_nums += 1;
	// 			hund_perc += parseFloat(line_pieces[line_pieces.length - 1]);
	// 			num = parseFloat(line_pieces[line_pieces.length - 1])
	// 		}
	// 		// line_pieces.pop();
	// 		output[line_pieces.join(',')] = num;
	// 	}
	// 	console.log(output)
	// 	if(end_with_nums > (len/2)) {
	// 		if(hund_perc < 99.0 || hund_perc > 101.0) {
	// 			return false;
	// 		} else {
	// 			messages({info: ["Making Container"]});
	// 			// return Container(output,{type:"percentage"})
	// 			return true;
	// 		}
	// 	}
	// 	return true;
	// }


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