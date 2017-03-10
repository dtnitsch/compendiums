<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("quiz_questions_edit")) { back_redirect(); }

post_queue($module_name,'modules/acu/quiz_questions/post_files/');

##################################################
#	Validation
##################################################
$id = get_page_id();
if(empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/acu/quiz_questions/');
}

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

if(empty($_POST)) {
	$q = "
		select 
			qq.id
			,qq.question
			,qq.number
			,qq.points
			,qq.description
			,f.id as fact_id
			,f.fact
			,json_agg(qqa.is_correct order by qqa.id) as is_correct_array
			,json_agg(qqa.answer order by qqa.id) as answer_array
			,json_agg(qqa.id order by qqa.id) as answer_id_array
		from activities.quiz_questions as qq
		join activities.quiz_question_answers as qqa on
			qqa.quiz_question_id = qq.id
			and qqa.active
		left join activities.quiz_question_fact_map as qqfm on
			qqfm.quiz_question_id = qq.id
		left join public.facts as f on
			f.id = qqfm.fact_id
		where 
			qq.id = '". $id ."'
		group by
			qq.id
			,qq.question
			,f.fact
			,f.id
	";

	$info = db_fetch($q,"Getting Quiz Questions");
	$info["answers"] = json_decode($info["answer_array"],true);
	$info["correct"] = json_decode($info["is_correct_array"],true);
	$info["ids"] = json_decode($info["answer_id_array"],true);
	$answers = array();
	$info['is_correct'] = '';
	foreach($info["answers"] as $k => $v) {
		$answer_id = $info["ids"][$k];
		$answers[$answer_id]["answer"] = $v;
		if(!empty($info["correct"][$k])) {
			$info['is_correct'] = $info["correct"][$k];
		}
	}
	$info["answers"] = $answers;
	unset($info["correct"]);
	unset($info["answer_array"]);
	unset($info["is_correct_array"]);


	$q = "
		select
			active
			,id
			,world_id
			,grade_id
			,theme_id
			,generation_id
		from activities.quiz_question_map
		where
			quiz_question_id = '". $id ."'
			and active
		order by
			world_id
			,grade_id
			,theme_id
			,generation_id
	";
	$res = db_query($q,'Getting Quiz Questions Grid');

	$grid = array();
	while($row = db_fetch_row($res)) {
		$grid[] = $row;
	}
} else {
	$info = $_POST;
	$grid = $_POST['grid'];
	unset($grid['templateid']);
}


##################################################
#	Content
##################################################
?>

	<h2 class='quiz-questions'>Edit Quiz Questions: <span class='small'><?php echo htmlspecialchars($info["question"]); ?></span></h2>
  
  <div class='content_container'>

	<?= quiz_questions_navigation($id,"edit") ?>

	<div id="messages">
		<?php echo dump_messages(); ?>
	</div>

	<form method="post" action="" enctype="multipart/form-data" onsubmit="return v.validate();">

	<label class="form_label">Question<span>*</span></label>
	<div class="form_data">
		<input required type="text" name="question" id="question" class="input_full" value="<?php if(!empty($info["question"])) { echo htmlspecialchars($info["question"]); } ?>">
		<br>
		<div id="img_box_question" class="hide image_upload"><img src="" id="img_question"><input type="file" name="question_uploads_images[question]" id="question_uploads_images" style="visibility: hidden; position: absolute;" onchange="previewFile(this,'question')"></div>
		<label for="question_uploads_images" class="label_button">Upload Images</label>
		<br>
		<label for="question_uploads_audio" class="label_button">Upload Audio</label><input type="file" name="question_uploads_audio[question]" id="question_uploads_audio" style="visibility: hidden; position: absolute;">
	</div>

	<label class="form_label">Number</label>
	<div class="form_data">
	<input required type="text" name="number" id="number" class="input_tiny" value="<?php if(!empty($info["number"])) { echo htmlspecialchars($info["number"]); } ?>">
	</div>

	<label class="form_label">Points</label>
	<div class="form_data">
	<input required type="text" name="points" id="points" class="input_tiny" value="<?php if(!empty($info["points"])) { echo htmlspecialchars($info["points"]); } ?>">
	</div>


	<label class="form_label" for="description">Description</label><br>
	<div class="form_data">
		<textarea name="description" id="description" class="input_xxlarge"><?php if(!empty($info["description"])) { echo $info["description"]; } ?></textarea>
	</div>

	<label class="form_label" for="fact">Fact</label><br>
	<div class="form_data">
		<textarea name="fact" id="fact" class="input_xxlarge"><?php if(!empty($info["fact"])) { echo $info["fact"]; } ?></textarea>
		<br>
		<div id="img_box_fact" class="hide image_upload"><img src="" id="img_fact"><input type="file" name="fact_uploads_images[fact]" id="fact_uploads_images" style="visibility: hidden; position: absolute;" onchange="previewFile(this,'fact')"></div>
		<label for="fact_uploads_images" class="label_button">Upload Images</label>
		<br>
		<label for="fact_uploads_audio" class="label_button">Upload Audio</label><input type="file" name="fact_uploads_audio[fact]" id="fact_uploads_audio" style="visibility: hidden; position: absolute;">
	</div>
  <div class='question_headline clearfix'>
    <h3>Answers</h3>
    <div class="float_right">
      <!--<button onclick='add_answer()' class='add'>Add Another Answer</button>-->
      <input type="button" value="Add Another Answer" onclick="add_answer()" class="add">
    </div>
  </div>

	<div class="answer_container">
		<div id="answer_box">
<?php
/*
			<div id="img_box_'. $k .'" class="hide image_upload">
				<img src="" id="img_'. $k .'"><input type="file" name="answers_uploads_images['. $k .']" id="answers_uploads_images_'. $k .'" style="visibility: hidden; position: absolute;" onchange="previewFile(this,'. $k .')">
			</div>
			<label for="answers_uploads_images_'. $k .'" class="label_button">Upload Images</label>
			<br>
			<label for="answers_uploads_audio_'. $k .'" class="label_button">Upload Audio</label><input type="file" name="answers_uploads_audio['. $k .']" id="answers_uploads_audio_'. $k .'" style="visibility: hidden; position: absolute;">

 */

	$output = '';
	$cnt = 1;
	foreach($info['answers'] as $k => $v) {
		if($k == 'new') { continue; }
		$is_correct = ($info['is_correct'] == $k ? " checked" : "");
		$output .= '
		<div class="inputs mb">
			<label for="answer_'. $k .'"><b>Answer '. $cnt .'</b></label> <label for="is_correct_'. $k .'"><input type="radio" name="is_correct" id="is_correct_'. $k .'"'. $is_correct .' value="'. $k .'"> Correct Answer</label><br>
			<input type="text" name="answers['. $k .'][answer]" id="answer_'. $k .'" class="input_full" value="'. $v['answer'] .'"> 
			<br>
			<div id="media_queue_'. $k .'"></div>
			<input class="files" type="file" name="file_upload['. $k .']" />
			<input type="hidden" class="upl_id" name="upl_id['. $k .']" />
		</div>
		';
		# <input type="file" name="answers_uploads_audio['. $k .']" value="Upload Audio"> 
		$cnt++;
	}
	if(!empty($info['answers']['new'])) {
		foreach($info['answers']['new'] as $k => $v) {
			$is_correct = ($info['is_correct'] == 'new_'.$k ? " checked" : "");
			$output .= '
			<div class="inputs mb">
				<label for="answer_'. $k .'"><b>Answer '. $cnt .'</b></label> <label for="is_correct_'. $k .'"><input type="radio" name="is_correct" id="is_correct_'. $k .'"'. $is_correct .' value="new_'. $k .'"> Correct Answer</label><br>
				<input type="text" name="answers[new]['. $k .'][answer]" id="answer_'. $k .'" class="input_full" value="'. $v['answer'] .'"> 
				<br>
				<div id="img_box_'. $k .'" class="hide image_upload"><img src="" id="img_'. $k .'"><input type="file" name="answers_uploads_images['. $k .']" id="answers_uploads_images_'. $k .'" style="visibility: hidden; position: absolute;" onchange="previewFile(this,'. $k .')"></div>
				<label for="answers_uploads_images_'. $k .'" class="label_button">Upload Images</label>
				<br>
				<label for="answers_uploads_audio_'. $k .'" class="label_button">Upload Audio</label><input type="file" name="answers_uploads_audio['. $k .']" id="answers_uploads_audio_'. $k .'" style="visibility: hidden; position: absolute;">
			</div>
			';
			# <input type="file" name="answers_uploads_audio['. $k .']" value="Upload Audio"> 
			$cnt++;
		}
	}

	echo $output;
	unset($output);
?>
		</div>
		
		<input type="hidden" id="phone_count" value="<?php echo $cnt; ?>">
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
 			<input type="hidden" id="initial_grid" value='<?php echo json_encode($grid); ?>'>
		</div>
		<div style="display: none;">
<?php
	$cnt = 0;
	echo build_db_select(get_worlds(),"world_id");
	echo build_db_select(get_themes(),"theme_id");
	echo build_db_select(get_grades(),"grade_id");
	echo build_db_select(get_generations(),"generation_id");
?>
		</div>
		<input type="hidden" id="grid_count" value="0">
	</div>

	<p>
		<input type="submit" value="Update Information">		
		<input type='hidden' name='id' value='<?php echo $id; ?>'>
		<input type='hidden' name='fact_id' value='<?php echo $info['fact_id']; ?>'>
	</p>

	</form>

</div>
<?php

	run_module('password');

	site_wide_notes('ajax',$GLOBALS['project_info']['path_data']['id'],$id);

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

	// v.optional('password1',false);
	// v.optional('password2',false);

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
		// div.innerHTML += '<br><div id="img_box_'+ cnt +'" class="hide image_upload"><img src="" id="img_'+ cnt +'"><img src="" id="img_'+ cnt +'"><input type="file" name="answers_uploads_images['+ cnt +']" id="answers_uploads_images_'+ cnt +'" style="visibility: hidden; position: absolute;" onchange="previewFile(this,'+ cnt +')"></div>';
		// div.innerHTML += '<label for="answers_uploads_images_'+ cnt +'" class="label_button">Upload Images</label>';
		// div.innerHTML += '<br><label for="answers_uploads_audio_'+ cnt +'" class="label_button">Upload Audio</label><input type="file" name="answers_uploads_audio['+ cnt +']" id="answers_uploads_audio_'+ cnt +'" style="visibility: hidden; position: absolute;">';

		div.innerHTML += '<div id="media_queue_'+ cnt +'"></div>';
		div.innerHTML += '<input class="files" type="file" name="file_upload['+ cnt +']" />';
		div.innerHTML += '<input type="hidden" class="upl_id" name="upl_id['+ cnt +']" />';

		$id("answer_box").appendChild(div);

		// append the child, then add the eventListener
		$('input[name="file_upload['+ cnt +']"]').uploadifive({
	    	'auto' : true
    		,'multi' : false
	        ,'formData'     : {
	            'apid' : '2e3e15529de3e903c16ab83793017a31'
	            ,'action' : 'upload'
	            ,'config_id' : 2
	        }
			,'queueID' : 'media_queue_'+ cnt
	        ,'uploadScript' : '/ajax.php'
	        // ,'uploadScript' : 'lib/media/media_library.ajax.php'
	        ,'onUploadComplete': function(file, data){

				var data = jQuery.parseJSON(data);
				var response = data.response;

				$('input[name="upl_id['+ cnt +']"]').val(response.upl_id);

			}
	    });

		$id("phone_count").value = (cnt + 1);	
	}

	function initial_grid() {
		var objects = JSON.parse($id('initial_grid').value);
		// console.log(objects)
		for(var i in objects) {
			add_grid(objects[i]['id'],objects[i]['world_id'],objects[i]['theme_id'],objects[i]['grade_id'],objects[i]['generation_id'])
			i++;
		}
	}

	function add_grid(id,world_id,theme_id,grade_id,generation_id) {
		var cnt = parseInt($id("grid_count").value);
		var tr = document.createElement("tr");	
		var td_count = document.createElement("td");
		var td_worlds = document.createElement("td");
		var td_themes = document.createElement("td");
		var td_grades = document.createElement("td");
		var td_generations = document.createElement("td");
		var td_delete = document.createElement("td");
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
		
		td_delete.innerHTML = '<a href="javascript:void(0);" onclick="this.parentNode.parentNode.innerHTML=\'\'" class="delete"></a>';

		tr.appendChild(td_count);
		tr.appendChild(td_worlds);
		tr.appendChild(td_themes);
		tr.appendChild(td_grades);
		tr.appendChild(td_generations);
		tr.appendChild(td_delete);
		$id("grid").appendChild(tr);
		$id("grid_count").value = (cnt + 1);	
	}

	initial_grid();




 // $('#media_background').uploadifive({
	//     	'auto' : true
	//     	,'multi' : false
	//         ,'formData'     : {
	//             'apid' : '2e3e15529de3e903c16ab83793017a31'
	//             ,'action' : 'upload'
	//             ,'config_id' : 1

	//         }
	// 		,'queueID' : 'media_background_queue'
	//         ,'uploadScript' : 'lib/media/media_library.ajax.php'
	//         ,'onUploadComplete': function(file, data){

	// 			var data = jQuery.parseJSON(data);
	// 			var response = data.response;

	// 			$('input[name="media_background_upl_id"]').val(response.upl_id);

	// 			//proc_upload_response(data);

	// 			//console.log(media['items']);

	// 			// if(typeof ajax_debugger == 'function' && typeof data['debug'] != 'undefined') {
	// 			// 	ajax_debugger(data['debug'],JSON.stringify(data['debug']).length);
	// 			// 	data['debug'] = null;					
	// 			// }

	// 		}
	//     });

	//     $('#media_question').uploadifive({
	//     	'auto' : true
	//     	,'multi' : false
	//         ,'formData'     : {
	//             'apid' : '2e3e15529de3e903c16ab83793017a31'
	//             ,'action' : 'upload'
	//             ,'config_id' : 3

	//         }
	// 		,'queueID' : 'media_question_queue'
	//         ,'uploadScript' : 'lib/media/media_library.ajax.php'
	//         ,'onUploadComplete': function(file, data){

	// 			var data = jQuery.parseJSON(data);
	// 			var response = data.response;

	// 			$('input[name="media_background_upl_id"]').val(response.upl_id);

	// 			//proc_upload_response(data);
				
	// 		}
	//     });

	//     $('#media_voice_over').uploadifive({
	//     	'auto' : true
	//     	,'multi' : false
	//         ,'formData'     : {
	//             'apid' : '2e3e15529de3e903c16ab83793017a31'
	//             ,'action' : 'upload'

	//         }
	// 		,'queueID' : 'media_voice_over_queue'
	//         ,'uploadScript' : 'lib/media/media_library.ajax.php'
	//         ,'onUploadComplete': function(file, data){

	// 			var data = jQuery.parseJSON(data);
	// 			var response = data.response;

	// 			$('input[name="media_background_upl_id"]').val(response.upl_id);

	// 			//proc_upload_response(data);
				
	// 		}
	//     });

		$(".files").each(function(index, elem) {
			id = parseInt(this.name.replace('file_upload[',''));

		    $('input[name="file_upload['+ id +']"]').uploadifive({
		    	'auto' : true
	    		,'multi' : false
		        ,'formData'     : {
		            'apid' : '2e3e15529de3e903c16ab83793017a31'
		            ,'action' : 'upload'
		            ,'config_id' : 2
		        }
				,'queueID' : 'media_queue_'+ id
		        ,'uploadScript' : '/ajax.php'
		        // /,'uploadScript' : 'lib/media/media_library.ajax.php'
		        ,'onUploadComplete': function(file, data){

					var data = jQuery.parseJSON(data);
					var response = data.response;

					$('input[name="upl_id['+ id +']"]').val(response.upl_id);

				}
		    });

		});
   

</script>

<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional PHP Functions
##################################################

// Array
// (
//     [0] => Array
//         (
//             [active] => t
//             [id] => 1
//             [world_id] => 15
//             [grade_id] => 7
//             [theme_id] => 27
//             [generation_id] => 1
//         )

//     [1] => Array
//         (
//             [active] => t
//             [id] => 2
//             [world_id] => 15
//             [grade_id] => 8
//             [theme_id] => 27
//             [generation_id] => 1
//         )
// )

function build_db_select($res,$name) {
	$output = '<select name="grid[templateid]['. $name .']" id="'. $name .'_templateid">';
	$output .= '<option value="">-Select '. ucfirst($name) .'-</option>';
	while($row = db_fetch_row($res)) {
		$output .= '<option value="'. $row['id'] .'">'. $row['title'] .'</option>';
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

	$q = "select id,title from public.generations where active";
	$result = db_query($q,"Getting Generations");

	if(db_is_error($result)) {
		return false;
	}

	return $result;
}



##################################################
#	EOF
##################################################
