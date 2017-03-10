<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
// if(!has_access("quiz_questions_add")) { back_redirect(); }

post_queue($module_name,'modules/acu/quiz_questions/post_files/');

$question_id   = (isset($_GET['question_id']) ? $_GET['question_id'] : 0);

##################################################
#	Validation
##################################################

##################################################
#	DB Queries
##################################################

// get worlds
function get_worlds(){

	$q = "select * from public.worlds where active and title != ''";

	$result = db_query($q,"Getting Worlds");

	$array 	= array();

    while($row = db_fetch_row($result)){
		$array[] = $row;
    }

	return $array;

}

// get themes
function get_themes(){

	$q = "select * from public.themes where active and title != ''";

	$result = db_query($q,"Getting Themes");

	$array 	= array();

    while($row = db_fetch_row($result)){
		$array[] = $row;
    }

	return $array;

}

// get generations
function get_generations(){

	$q = "select * from public.generations where active";

	$result = db_query($q,"Getting Generations");

	$array 	= array();

    while($row = db_fetch_row($result)){
		$array[] = $row;
    }

	return $array;

}

// get grades
function get_grades(){

	$q = "select * from public.grades where active";

	$result = db_query($q,"Getting Grades");

	$array 	= array();

    while($row = db_fetch_row($result)){
		$array[] = $row;
    }

	return $array;

}


##################################################
#	Pre-Content
##################################################

function show_worlds(){

	$display = '';

	$worlds = get_worlds();

	if(isset($worlds)){

		$display .= '
			<select name="worlds" class="input_xxlarge">
			<option value="">Worlds</option>
		';

		foreach($worlds as $row) {
			$display .= '<option value="'.$row['id'].'">'.stripslashes($row['title']).'</option>';
		}

		$display .= '</select>';

	}

	return $display;

}

function show_themes(){

	$display = '';

	$themes = get_themes();

	if(isset($themes)){

		$display .= '
			<select name="themes" class="input_xxlarge">
			<option value="">Themes</option>
		';

		foreach($themes as $row) {
			$display .= '<option value="'.$row['id'].'">'.stripslashes($row['title']).'</option>';
		}

		$display .= '</select>';

	}

	return $display;

}

function show_generations(){

	$display = '';

	$generations = get_generations();

	if(isset($generations)){

		$display .= '
			<select name="generations" class="input_xxlarge">
			<option value="">Generations</option>
		';

		foreach($generations as $row) {
			$display .= '<option value="'.$row['id'].'">'.stripslashes($row['title']).'</option>';
		}

		$display .= '</select>';

	}

	return $display;

}

function show_grades(){

	$display = '';

	$grades = get_grades();

	if(isset($grades)){

		$display .= '
			<select name="grades" class="input_xxlarge">
			<option value="">Grades</option>
		';

		foreach($grades as $row) {
			$display .= '<option value="'.$row['id'].'">'.stripslashes($row['title']).'</option>';
		}

		$display .= '</select>';

	}

	return $display;

}

function show_matrix(){

	$display = '<tbody id="matrix">';

	$display .= '</tbody>';

	return $display;

}


function show_builder(){

	$display = '
			<div id="question_builder2">
			<table id="asl_sort" cellpadding="0" cellspacing="0" class="asl_sort">
				<thead id="matrix_controls"></thead>
				<tbody id="matrix"></tbody>
			</table>
			</div>
	';

	return $display;

}



$info = (!empty($_POST) ? $_POST : array());

// library("validation.php");

// add_js("validation.js");
add_js("jquery.js", 1);
add_js("jquery.validate.min.js");
add_js("jquery.uploadifive.min.js");

##################################################
#	Content
##################################################

function show_output(){

	$display = '
		<h2>Create Quiz Questions</h2>

		<div id="messages">'.dump_messages().'</div>

		<form name="question_builder" method="post" action="">
			<fieldset>
				<label class="form_label">Question<span>*</span></label>
				<div class="form_data">
					<input  type="text" name="question" id="question" />
				</div>

				<label class="form_label">Add Media</label>
				<input id="media_question" type="file" name="media_question" />
				<div id="media_question_queue"></div>
				<input type="hidden" name="media_question_upl_id" />

				<label class="form_label">Add Background Image</label>
				<input id="media_background" type="file" name="media_background" />
				<div id="media_background_queue"></div>
				<input type="hidden" name="media_background_upl_id" />

				<label class="form_label">Add Voice Over</label>
				<input id="media_voice_over" type="file" name="media_voice_over" />
				<div id="media_voice_over_queue"></div>
				<input type="hidden" name="media_voice_over_upl_id" />

			</fieldset>

			<fieldset>
				<legend>Add Answers</legend>

				<ul class="list_answers">
					<li style="display:none;">
						<label class="form_label">Answer<span>*</span></label>
						<div class="form_data">
							<input  type="text" name="temp_answer[XXX]" />
						</div>
						<div class="is_correct">
							<input  type="checkbox" name="temp_is_correct[XXX]" value="1" />
							Correct Answer
						</div>
						<div id="media_queue_XXX"></div>
						<input class="temp_ontheflyfiles" type="file" name="temp_file_upload[XXX]" />
						<input type="hidden" class="upl_id" name="temp_upl_id[XXX]" />
					</li>
					<li>
						<label class="form_label">Answer<span>*</span></label>
						<div class="form_data">
							<input type="text" name="answer[XXX]" />
						</div>
						<div class="is_correct">
							<input  type="checkbox" name="is_correct[XXX]" value="1" />
							Correct Answer
						</div>
						<div id="media_queue_XXX"></div>
						<input class="files" type="file" name="file_upload[XXX]" />
						<input type="hidden" class="upl_id" name="upl_id[XXX]" />
		            </li>
		        </ul>

				<a href="#" class="add_answer">Add Another Answer</a><br>

			</fieldset>

			'.show_builder().'


			<!--<a href="#" title="Save Changes" class="button save">Save Changes</a>-->
			<input type="submit" value="Save Changes">

		</form>


	';

	//<p><input type="submit" value="Create Quiz Questions"></p>

	return $display;

}

function clean_newlines($val) {
    $val = str_replace("\n", '', $val);
    $val = str_replace("\r", '', $val);
    $val = str_replace("\r\n", '', $val);
	$val = addslashes($val);

    return clean_spaces($val);
}

function clean_spaces($val){

	$val = preg_replace('/\s\s+/',' ',$val);
	return $val;

}



echo show_output();




##################################################
#	Javascript Functions
##################################################
ob_start();
?>


<script type="text/javascript">

	// var j = <?php //echo validation_create_json_string(validation_load_file(__DIR__."/validation.json"),"js"); ?>;
	// // name of variable should be sent in the validation function
	// var v = new validation("v"); 
	// v.load_json(j);

	var formElement = '#question_builder2'
		,question_id = '<?php echo $question_id; ?>'
		,media = {'items' : []}
		,apid = 'apid=2e3e15529de3e903c16ab83793017a31'
		,worlds_string = '<?php echo clean_newlines(show_worlds()); ?>'
		,themes_string = '<?php echo clean_newlines(show_themes()); ?>'
		,generations_string = '<?php echo clean_newlines(show_generations()); ?>'
		,grades_string = '<?php echo clean_newlines(show_grades()); ?>';


	$(function() {

		show_matrix();

		function show_matrix() {

			var content = '<tr>';
				content += '<th>'+ worlds_string + '</th>';
				content += '<th>'+ themes_string + '</th>';
				content += '<th>'+ generations_string + '</th>';
				content += '<th>'+ grades_string + '</th>';
				content += '<th><a href="#" class="add_option">GO</a></th>';
				content += '</tr>';

			$('#matrix_controls').html(content);

			return false;

		}

        $('body').on("click", '.add_option', function(){

			var $element = $('#matrix_controls');

			var world_id = $element.find('select[name=worlds]').val();
			var theme_id = $element.find('select[name=themes]').val();
			var generation_id = $element.find('select[name=generations]').val();
			var grade_id = $element.find('select[name=grades]').val();

			//alert(parseInt(world_id + theme_id + grade_id + generation_id));

			if(parseInt(world_id + theme_id + grade_id + generation_id) > 0){

	        	var temp_worlds = '<th>'+ worlds_string + '</th>';
	        		temp_worlds = temp_worlds.replace('value="'+ world_id +'"', 'value="'+ world_id +'" selected');

	        	var temp_themes = '<th>'+ themes_string + '</th>';
	        		temp_themes = temp_themes.replace('value="'+ theme_id +'"', 'value="'+ theme_id +'" selected');

	        	var temp_generations = '<th>'+ generations_string + '</th>';
	        		temp_generations = temp_generations.replace('value="'+ generation_id +'"', 'value="'+ generation_id +'" selected');

	        	var temp_grades = '<th>'+ grades_string + '</th>';
	        		temp_grades = temp_grades.replace('value="'+ grade_id +'"', 'value="'+ grade_id +'" selected');

				var content = '';
					content += '<tr>';
					content += temp_worlds;
					content += temp_themes;
					content += temp_generations;
					content += temp_grades;
					content += '<th>X</th>';
					content += '</tr>';

				$('#matrix').append(content);

			} else {
				alert('select a dropdown');

			}

			return false;

        });


	    $('#media_background').uploadifive({
	    	'auto' : true
	    	,'multi' : false
	        ,'formData'     : {
	            'apid' : '2e3e15529de3e903c16ab83793017a31'
	            ,'action' : 'upload'
	            ,'config_id' : 1

	        }
			,'queueID' : 'media_background_queue'
	        ,'uploadScript' : 'lib/media/media_library.ajax.php'
	        ,'onUploadComplete': function(file, data){

				var data = jQuery.parseJSON(data);
				var response = data.response;

				$('input[name="media_background_upl_id"]').val(response.upl_id);

				//proc_upload_response(data);

				//console.log(media['items']);

				// if(typeof ajax_debugger == 'function' && typeof data['debug'] != 'undefined') {
				// 	ajax_debugger(data['debug'],JSON.stringify(data['debug']).length);
				// 	data['debug'] = null;					
				// }

			}
	    });

	    $('#media_question').uploadifive({
	    	'auto' : true
	    	,'multi' : false
	        ,'formData'     : {
	            'apid' : '2e3e15529de3e903c16ab83793017a31'
	            ,'action' : 'upload'
	            ,'config_id' : 3

	        }
			,'queueID' : 'media_question_queue'
	        ,'uploadScript' : 'lib/media/media_library.ajax.php'
	        ,'onUploadComplete': function(file, data){

				var data = jQuery.parseJSON(data);
				var response = data.response;

				$('input[name="media_background_upl_id"]').val(response.upl_id);

				//proc_upload_response(data);
				
			}
	    });

	    $('#media_voice_over').uploadifive({
	    	'auto' : true
	    	,'multi' : false
	        ,'formData'     : {
	            'apid' : '2e3e15529de3e903c16ab83793017a31'
	            ,'action' : 'upload'

	        }
			,'queueID' : 'media_voice_over_queue'
	        ,'uploadScript' : 'lib/media/media_library.ajax.php'
	        ,'onUploadComplete': function(file, data){

				var data = jQuery.parseJSON(data);
				var response = data.response;

				$('input[name="media_background_upl_id"]').val(response.upl_id);

				//proc_upload_response(data);
				
			}
	    });

		$(".files").each(function(index, elem) {

    		var temp_content = $(this).closest('li').html();

    		var temp_content = temp_content.replace(/XXX/g, index);

    		$(this).closest('li').html(temp_content);

		    $('input[name="file_upload['+ index +']"]').uploadifive({
		    	'auto' : true
	    		,'multi' : false
		        ,'formData'     : {
		            'apid' : '2e3e15529de3e903c16ab83793017a31'
		            ,'action' : 'upload'
		            ,'config_id' : 2
		        }
				,'queueID' : 'media_queue_'+ index
		        ,'uploadScript' : 'lib/media/media_library.ajax.php'
		        ,'onUploadComplete': function(file, data){

					var data = jQuery.parseJSON(data);
					var response = data.response;

					$('input[name="upl_id['+ index +']"]').val(response.upl_id);

				}
		    });

		});
   

        $('body').on("click", '.add_answer', function(){

			var count = $('ul.list_answers li').length;
				count = parseInt(count - 1);

			//console.log(count);

			var temp_content = $("ul.list_answers li:eq(0)").html();

    		var temp_content = temp_content.replace(/XXX/g, count);
    		var content = temp_content.replace(/temp_/g, '');

			$('ul.list_answers  > li:last-child').after('<li>' + content + '</li>');

		    $('input[name="file_upload['+ count +']"]').uploadifive({
		    	'auto' : true
	    		,'multi' : false
		        ,'formData'     : {
		            'apid' : '2e3e15529de3e903c16ab83793017a31'
		            ,'action' : 'upload'
		            ,'config_id' : 2
		        }
				,'queueID' : 'media_queue_'+ count
		        ,'uploadScript' : 'lib/media/media_library.ajax.php'
		        ,'onUploadComplete': function(file, data){

					var data = jQuery.parseJSON(data);
					var response = data.response;

					$('input[name="upl_id['+ count +']"]').val(response.upl_id);

				}
		    });

			return false;

        });



		// if return is pressed within the form - submit!
		// $('body').keydown(function(e) {
		// 	if(e.keyCode == "13" && $(this).is('input')) {
		// 		if($(formElement).valid()){
		// 			submit_Registration();
		// 		}
		// 		return false;
		// 	}
		// });

		// submit registration
        $('body').on("click", formElement +' a.save, #save_changes', function(){
			//if($(formElement).valid()){
				submit_Registration();
			//}
			return false;
		});


		// submit registration
		function submit_Registration() {

		    // prepare form submission
			var form = 'action=update';
				form += '&question_id=' + question_id;
				form += '&' + $(formElement).serialize();
				form += '&media=' + encodeURIComponent(JSON.stringify(media));
				
			console.log(form);
			return false;

			// post registration
			$.ajax({
				type: "POST",
				url: "/cck-test.process.php",
				data: form,
				dataType: "json",
				success: function(data) {

					// if(data.success) {
					// 	$('#registration').html('Thank you for your registration.');
					// }

					// if(typeof ajax_debugger == 'function') {
					// 	ajax_debugger(data['debug'],JSON.stringify(data['debug']).length);
					// 	data['debug'] = null;					
					// }

				}		
			});

			return false;

		}






	});


$(document).load(function(){
    $('option:selected').attr('selected', false);
});


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