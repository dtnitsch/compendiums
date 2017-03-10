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
library("functions.php",$GLOBALS["root_path"] ."modules/acu/quiz_questions/");

add_js("jquery.js", 1);
add_js("jquery.validate.min.js");
add_js("jquery.uploadifive.min.js");

library("validation.php");
add_js("validation.js");

##################################################
#	Content
##################################################
?>

<h2 class='quiz-questions'>Add Quiz Questions:</span></h2>
  
<div class='content_container'>

	<div id="messages">
		<?php echo dump_messages(); ?>
	</div>

	<form method="post" action="" enctype="multipart/form-data" onsubmit="return v.validate();">

	<label class="form_label">Question<span>*</span></label>

	<div class="form_data">
		<input required type="text" name="question" id="question" class="input_full">
		<br>
		<div id="question_uploads_images_queue"></div>
		<div id="question_uploads_audio_queue"></div>

		<input id="question_uploads_audio" type="file" name="question_uploads_audio" /><br />
		<input id="question_uploads_images" type="file" name="question_uploads_images" />

		<input type="hidden" name="media[question_media][upload_id]" />
		<input type="hidden" name="media[question_voiceover][upload_id]" />
	</div>

	<label class="form_label">Number</label>
	<div class="form_data">
	<input required type="text" name="number" id="number" class="input_tiny">
	</div>

	<label class="form_label">Points</label>
	<div class="form_data">
	<input required type="text" name="points" id="points" class="input_tiny" value="100">
	</div>

	<label class="form_label" for="description">Description</label><br>
	<div class="form_data">
		<textarea name="description" id="description" class="input_xxlarge"></textarea>
	</div>

	<label class="form_label" for="fact">Fact</label><br>
	<div class="form_data">
		<textarea name="fact" id="fact" class="input_xxlarge"></textarea>
		<br>
		<div id="fact_uploads_images_queue"></div>
		<div id="fact_uploads_audio_queue"></div>

		<input id="fact_uploads_audio" type="file" name="fact_uploads_audio" /><br />
		<input id="fact_uploads_images" type="file" name="fact_uploads_images" />

		<input type="hidden" name="media[fact_voiceover][upload_id][1]" />
		<input type="hidden" name="media[fact_media][upload_id][1]" />
	</div>

	<div class='question_headline clearfix'>
		<h3>Answers</h3>
		<div class="float_right">
			<input type="button" value="Add Another Answer" onclick="add_answer()" class="add">
		</div>
	</div>

	<div class="answer_container">
		<div id="answer_box">
			<label class="form_label">Answer Image</label><br />
			<input id="answer_uploads_images" type="file" name="answer_uploads_images" />
			<div id="answer_uploads_images_queue"></div>
			<input type="hidden" name="media[answer_image][upload_id]" />
			<br />
			<div class="inputs mb">
				<label for="answer_1"><b>Answer 1</b></label>
				<label for="is_correct_1"><input type="radio" name="is_correct" id="is_correct_1" value="1"> Correct Answer</label><br>
				<input type="text" name="answers[1][answer]" id="answer_1" class="input_full" > 
				<br>
				<div id="media_files_queue_1"></div>
				<div id="audio_files_queue_1"></div>

				<input class="audio_files" type="file" name="audio_upload[1]" /><br />
				<input class="media_files" type="file" name="media_upload[1]" />

				<input type="hidden" name="media[answer_media][upload_id][1]" />
				<input type="hidden" name="media[answer_voiceover][upload_id][1]" />
			</div>
		</div>
		<input type="hidden" id="phone_count" value="2">
	</div>

	<div class='question_headline clearfix'>
		<h3>Availability Grid</h3>
		<div class="float_right">
			<!--<button onclick='add_grid()' class='add'>Add Another Grid</button>-->
			<input type="button" value="Add Another Grid" onclick="add_grid()" class="add">
		</div>
	</div>
	
	<div class="grid_container clear">
		<div id="grid_box">
			<table cellpadding="0" cellspacing="0" border="0" id="grid" class="grid">
	 			<tr>
	 				<th>&nbsp;</th>
	 				<th>World</th>
	 				<th>Theme</th>
	 				<th>Grade</th>
	 				<th>Generation</th>
	 				<th>&nbsp;</th>
	 			</tr>
 			</table>
		</div>
		<div style="display: none;">
<?php
	$cnt = 0;
	echo build_db_select(get_worlds(),"world_id");
	echo build_db_select(get_themes(),"theme_id");
	echo build_db_select(get_grades(),"grade_id");
	echo build_db_select_generation(get_generations(),"generation_id");
?>
		</div>
		<input type="hidden" id="grid_count" value="0">
	</div>

	<p><input type="submit" value="Update Information"></p>

	</form>

</div>

<div id="preview_question_modal" style="display: none;">

	<div id="flash_container"></div>

</div>

<?php

//run_module('password');

//site_wide_notes('ajax',$GLOBALS['project_info']['path_data']['id'],$id);

add_js("https://ajax.googleapis.com/ajax/libs/jquery/1.12.3/jquery.min.js", 3);
add_css("fancybox/jquery.fancybox.css");
add_js("fancybox/jquery.fancybox.pack.js", 4);
add_js("ac_runactivecontent.js", 5);
add_js("cck2.js", 6);

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

	$(".media_files").each(function(index, elem) {

		var id = parseInt(this.name.replace('media_upload[',''));

	    $('input[name="media_upload['+ id +']"]').uploadifive({
	    	'auto' : true
    		,'multi' : false
	        ,'formData'     : {
	            'apid' : '2e3e15529de3e903c16ab83793017a31'
	            ,'action' : 'upload'
	            ,'type' : 'answer_media'
	            ,'position' : id
	        }
			,'buttonText'   : 'Upload Media'
	        ,'buttonClass'  : 'label_button'
	        //,'fileType' : 'image/*'
			,'queueID' : 'media_files_queue_'+ id
	        ,'uploadScript' : '/ajax.php'
	        ,'onUploadComplete': function(file, data){

				if (typeof ajax_debugger == "function" && typeof data.debug != "undefined") {
					ajax_debugger(data.debug, JSON.stringify(data.debug).length);
					data.debug = null;
				}

				var data = $.parseJSON(data);

				$('input[name="media[answer_media][upload_id]['+ data.position +']"]').val(data.upload_id);

			}
	    });

	});

	$(".audio_files").each(function(index, elem) {

		var id = parseInt(this.name.replace('audio_upload[',''));

	    $('input[name="audio_upload['+ id +']"]').uploadifive({
	    	'auto' : true
    		,'multi' : false
	        ,'formData'     : {
	            'apid' : '2e3e15529de3e903c16ab83793017a31'
	            ,'action' : 'upload'
	            ,'type' : 'answer_voiceover'
	            ,'position' : id
	            ,'voiceover' : true
	        }
			,'buttonText'   : 'Upload Voiceover'
	        ,'buttonClass'  : 'label_button'
	        ,'fileType' : 'audio/*'
			,'queueID' : 'audio_files_queue_'+ id
	        ,'uploadScript' : '/ajax.php'
	        ,'onUploadComplete': function(file, data){

				if (typeof ajax_debugger == "function" && typeof data.debug != "undefined") {
					ajax_debugger(data.debug, JSON.stringify(data.debug).length);
					data.debug = null;
				}

				var data = $.parseJSON(data);

				$('input[name="media[answer_voiceover][upload_id]['+ data.position +']"]').val(data.upload_id);

			}
	    });

	});

    $('#question_uploads_images').uploadifive({
    	'auto' : true
    	,'multi' : false
        ,'formData'     : {
            'apid' : '2e3e15529de3e903c16ab83793017a31'
            ,'action' : 'upload'
	        ,'type' : 'question_media'
        }
		,'queueID' : 'question_uploads_images_queue'
		,'uploadScript' : '/ajax.php'
		,'buttonText'   : 'Upload Images'
        ,'buttonClass'  : 'label_button'
        ,'fileType' : 'image/*'
        ,'onUploadComplete': function(file, data){

			var data = jQuery.parseJSON(data);

			if (typeof ajax_debugger == "function" && typeof data.debug != "undefined") {
				ajax_debugger(data.debug, JSON.stringify(data.debug).length);
				data.debug = null;
			}

			$('input[name="media[question_media][upload_id]"]').val(data.upload_id);
			
		}
    });

    $('#question_uploads_audio').uploadifive({
    	'auto' : true
    	,'multi' : false
        ,'formData'     : {
            'apid' : '2e3e15529de3e903c16ab83793017a31'
            ,'action' : 'upload'
	        ,'type' : 'question_voiceover'
            ,'voiceover' : true
        }
		,'queueID' : 'question_uploads_audio_queue'
		,'uploadScript' : '/ajax.php'
		,'buttonText'   : 'Upload Voiceover'
        ,'buttonClass'  : 'label_button'
        ,'fileType' : 'audio/*'
        ,'onUploadComplete': function(file, data){

			var data = jQuery.parseJSON(data);

			if (typeof ajax_debugger == "function" && typeof data.debug != "undefined") {
				ajax_debugger(data.debug, JSON.stringify(data.debug).length);
				data.debug = null;
			}

			$('input[name="media[question_voiceover][upload_id]"]').val(data.upload_id);
			
		}
    });

    $('#fact_uploads_images').uploadifive({
    	'auto' : true
    	,'multi' : false
        ,'formData'     : {
            'apid' : '2e3e15529de3e903c16ab83793017a31'
            ,'action' : 'upload'
	        ,'type' : 'fact_media'
        }
		,'queueID' : 'fact_uploads_images_queue'
		,'uploadScript' : '/ajax.php'
		,'buttonText'   : 'Upload Images'
        ,'buttonClass'  : 'label_button'
        ,'fileType' : 'image/*'
        ,'onUploadComplete': function(file, data){

			var data = jQuery.parseJSON(data);

            if (typeof ajax_debugger == 'function') {
                ajax_debugger(data['debug'],JSON.stringify(data['debug']).length);
                data['debug'] = null;                   
            }

			$('input[name="media[fact_media][upload_id][1]"]').val(data.upload_id);
			
		}
    });

    $('#fact_uploads_audio').uploadifive({
    	'auto' : true
    	,'multi' : false
        ,'formData'     : {
            'apid' : '2e3e15529de3e903c16ab83793017a31'
            ,'action' : 'upload'
	        ,'type' : 'fact_voiceover'
            ,'voiceover' : true
        }
		,'queueID' : 'fact_uploads_audio_queue'
		,'uploadScript' : '/ajax.php'
		,'buttonText'   : 'Upload Voiceover'
        ,'buttonClass'  : 'label_button'
        ,'fileType' : 'audio/*'
        ,'onUploadComplete': function(file, data){

			var data = jQuery.parseJSON(data);

            if (typeof ajax_debugger == 'function') {
                ajax_debugger(data['debug'],JSON.stringify(data['debug']).length);
                data['debug'] = null;                   
            }

			$('input[name="media[fact_voiceover][upload_id][1]"]').val(data.upload_id);
			
		}
    });

    $('#answer_uploads_images').uploadifive({
    	'auto' : true
    	,'multi' : false
        ,'formData'     : {
            'apid' : '2e3e15529de3e903c16ab83793017a31'
            ,'action' : 'upload'
	        ,'type' : 'answer_image'
        }
		,'queueID' : 'answer_uploads_images_queue'
		,'uploadScript' : '/ajax.php'
		,'buttonText'   : 'Upload Image'
        ,'buttonClass'  : 'label_button'
        ,'fileType' : 'image/*'
        ,'onUploadComplete': function(file, data){

			var data = $.parseJSON(data);

			if (typeof ajax_debugger == "function" && typeof data.debug != "undefined") {
				ajax_debugger(data.debug, JSON.stringify(data.debug).length);
				data.debug = null;
			}

			$('input[name="media[answer_image][upload_id]"]').val(data.upload_id);
			
		}
    });

// $(function() {

// });

	function previewFile(obj,cnt) {
		var preview = $id('img_'+ cnt);
		var file    = obj.files[0];
		var reader  = new FileReader();

		reader.onloadend = function () {
			//preview.style.height = '100px';
			//preview.style.width = '75px';
			preview.src = reader.result;
			removeclass($id('img_box_'+ cnt),"hide");
		}

		if (file) {
			reader.readAsDataURL(file);
		} else {
			preview.src = "";
		}
	}

	function add_answer() {
		var cnt = parseInt($id("phone_count").value);
		var div = document.createElement("div");	

		div.className = "inputs mb";
		div.innerHTML = '<label for="description"><b>Answer '+ cnt +'</b></label> <label for="is_correct_new_'+ cnt +'"><input type="radio" name="is_correct" id="is_correct_new_'+ cnt +'" value="new_'+ cnt +'"> Correct Answer</label><br>';
		div.innerHTML += '<input type="text" name="answers[new]['+ cnt +'][answer]" id="answer_'+ cnt +'" class="input_full" value=""> ';

		div.innerHTML += '<div id="media_files_queue_'+ cnt +'"></div>';
		div.innerHTML += '<div id="audio_files_queue_'+ cnt +'"></div>';

		div.innerHTML += '<input class="files" type="file" name="audio_upload['+ cnt +']" /><br />';
		div.innerHTML += '<input class="files" type="file" name="media_upload['+ cnt +']" />';

		div.innerHTML += '<input type="hidden" name="media[answer_media][upload_id]['+ cnt +']" />';
		div.innerHTML += '<input type="hidden" name="media[answer_voiceover][upload_id]['+ cnt +']" />';

		$id("answer_box").appendChild(div);

		// append the child, then add the eventListener
		$('input[name="media_upload['+ cnt +']"]').uploadifive({
	    	'auto' : true
    		,'multi' : false
	        ,'formData'     : {
	            'apid' : '2e3e15529de3e903c16ab83793017a31'
	            ,'action' : 'upload'
	            ,'type' : 'answer_media'
	            ,'position' : cnt
	        }
			,'buttonText'   : 'Upload Media'
	        ,'buttonClass'  : 'label_button'
	        ,'queueID' : 'media_files_queue_'+ cnt
	        ,'uploadScript' : '/ajax.php'
	        ,'onUploadComplete': function(file, data){

				var data = jQuery.parseJSON(data);

	            if(typeof ajax_debugger == 'function') {
	                ajax_debugger(data['debug'],JSON.stringify(data['debug']).length);
	                data['debug'] = null;                   
	            }

				$('input[name="media[answer_media][upload_id]['+ data.position +']"]').val(data.upload_id);

			}
	    });

		// append the child, then add the eventListener
		$('input[name="audio_upload['+ cnt +']"]').uploadifive({
	    	'auto' : true
    		,'multi' : false
	        ,'formData'     : {
	            'apid' : '2e3e15529de3e903c16ab83793017a31'
	            ,'action' : 'upload'
	            ,'type' : 'answer_voiceover'
	            ,'voiceover' : true
	            ,'position' : cnt
	        }
			,'buttonText'   : 'Upload Voiceover'
	        ,'buttonClass'  : 'label_button'
	        ,'fileType' : 'audio/*'
	        ,'queueID' : 'audio_files_queue_'+ cnt
	        ,'uploadScript' : '/ajax.php'
	        ,'onUploadComplete': function(file, data){

				var data = jQuery.parseJSON(data);

	            if(typeof ajax_debugger == 'function') {
	                ajax_debugger(data['debug'],JSON.stringify(data['debug']).length);
	                data['debug'] = null;                   
	            }

				$('input[name="media[answer_voiceover][upload_id]['+ data.position +']"]').val(data.upload_id);

			}
	    });

		$id("phone_count").value = (cnt + 1);	
	}

	function initial_grid() {

		var objects = JSON.parse($id('initial_grid').value);

		//console.log(objects)
		for(var i in objects) {
			add_grid(objects[i]['id'], objects[i]['question_id'], objects[i]['question_number'], objects[i]['world_id'], objects[i]['world_alias'], objects[i]['theme_id'], objects[i]['theme_alias'], objects[i]['grade_id'], objects[i]['generation_id'])
			i++;
		}

	}

	function add_grid(id, question_id, question_number, world_id, world_alias, theme_id, theme_alias, grade_id, generation_id) {
		var cnt = parseInt($id("grid_count").value);
		var tr = document.createElement("tr");	
		var td_count = document.createElement("td");
		var td_worlds = document.createElement("td");
		var td_themes = document.createElement("td");
		var td_grades = document.createElement("td");
		var td_generations = document.createElement("td");
		var td_options = document.createElement("td");
		//var td_delete = document.createElement("td");
		var tmp = '';
		var hidden_id = '';

		id = id || 'new';
		world_id = world_id || '';
		theme_id = theme_id || '';
		grade_id = grade_id || '';
		generation_id = generation_id || '';

		td_count.innerHTML = (cnt + 1);
		
		hidden_id = '<input type="hidden" name="grid['+ cnt +'][id]" value="'+ id +'">';

		tmp = $id('world_id_templateid').outerHTML;
		tmp = tmp.replace(/templateid/g,cnt);
		if(world_id != '') {
			// tmp = tmp.replace('['+ cnt +']','['+ id +']');
			tmp = tmp.replace('value="'+ world_id +'"','value="'+ world_id +'" selected');
		}
		tmp += hidden_id; // one time for the hidden value
		td_worlds.innerHTML = tmp;

		tmp = $id('theme_id_templateid').outerHTML;
		tmp = tmp.replace(/templateid/g,cnt);
		if(theme_id != '') {
			// tmp = tmp.replace('['+ cnt +']','['+ id +']');
			tmp = tmp.replace('value="'+ theme_id +'"','value="'+ theme_id +'" selected');
		}
		td_themes.innerHTML = tmp;
		
		tmp = $id('grade_id_templateid').outerHTML;
		tmp = tmp.replace(/templateid/g,cnt);
		if(grade_id != '') {
			// tmp = tmp.replace('['+ cnt +']','['+ id +']');
			tmp = tmp.replace('value="'+ grade_id +'"','value="'+ grade_id +'" selected');
		}
		td_grades.innerHTML = tmp;
		
		tmp = $id('generation_id_templateid').outerHTML;
		tmp = tmp.replace(/templateid/g,cnt);
		if(generation_id != '') {
			// tmp = tmp.replace('['+ cnt +']','['+ id +']');
			tmp = tmp.replace('value="'+ generation_id +'"','value="'+ generation_id +'" selected');
		}
		td_generations.innerHTML = tmp;

		//td_options.innerHTML = '<a href="#" data-question-id="'+question_id+'" data-question-number="'+question_number+'" data-grade-id="'+grade_id+'" data-generation-id="'+generation_id+'" data-theme-alias="'+theme_alias+'" data-world-alias="'+world_alias+'" class="preview_question" title="Preview Question">Preview</a>';
		td_options.innerHTML += '<a href="javascript:void(0);" onclick="this.parentNode.parentNode.innerHTML=\'\'" class="delete"></a>';

		tr.appendChild(td_count);
		tr.appendChild(td_worlds);
		tr.appendChild(td_themes);
		tr.appendChild(td_grades);
		tr.appendChild(td_generations);
		tr.appendChild(td_options);
		$id("grid").appendChild(tr);
		$id("grid_count").value = (cnt + 1);	
	}

	add_grid()

</script>

<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional PHP Functions
##################################################

function build_db_select($res,$name,$selected="") {
	
	$output = '<select name="grid[templateid]['. $name .']" id="'. $name .'_templateid">';
	$output .= '<option value="">-Select '. ucfirst($name) .'-</option>';
	while($row = db_fetch_row($res)) {
		$output .= '<option value="'. $row['id'] .'"';
		if ($selected == $row['id']) {
			$output .= ' selected="selected"';
		}
		$output .= '">'. $row['title'] .'</option>';
	}
	$output .= '</select>';
	return $output;
}

function build_db_select_generation($res,$name,$selected="") {
	$output = '<select name="grid[templateid]['. $name .']" id="'. $name .'_templateid">';
	$output .= '<option value="">-Select '. ucfirst($name) .'-</option>';
	while($row = db_fetch_row($res)) {
		if ($row['active'] == 'f' || !$row['active']){
			//$row['title'] .= ' (inactive)';
		}
		$output .= '<option value="'. $row['id'] .'"';
		if ($selected == $row['id']) {
			$output .= ' selected="selected"';
		}
		$output .= '">'. $row['title'] .'</option>';
	}
	$output .= '</select>';
	return $output;
}

// get worlds
function get_worlds() {

	$q = "select id,(title || ' (' || alias || ')') as title from public.worlds where active and title != ''";
	$result = db_query($q,"Getting Worlds");

	if(db_is_error($result)) {
		return false;
	}

	return $result;
}

// get themes
function get_themes() {

	$q = "select id,(title || ' (' || alias || ')') as title from public.themes where active";
	$result = db_query($q,"Getting Themes");

	if(db_is_error($result)) {
		return false;
	}

	return $result;
}

// get grades
function get_grades(){

	$q = "select id,title from public.grades where active";
	$result = db_query($q,"Getting Grades");

	if(db_is_error($result)) {
		return false;
	}

	return $result;
}


// get generations
function get_generations() {

	$q = "
		select
			id
			,title
			,active
		from public.generations
		where
			title != 'Default'
	";
	$result = db_query($q,"Getting Generations");

	if(db_is_error($result)) {
		return false;
	}
	return $result;
}



##################################################
#	EOF
##################################################
