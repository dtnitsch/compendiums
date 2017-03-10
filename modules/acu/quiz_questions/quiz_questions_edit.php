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
			$info['is_correct'] = $info["ids"][$k];
		}
	}
	$info["answers"] = $answers;
	unset($info["correct"]);
	unset($info["answer_array"]);
	unset($info["is_correct_array"]);

	$info["media"] = get_quiz_question_media_by_id($id);

	$q = "
		select
			map.active
			,map.id
			,map.quiz_question_id as question_id
			,map.world_id
			,map.grade_id
			,map.theme_id
			,map.generation_id
			,q.number as question_number
			,w.alias as world_alias
			,t.alias as theme_alias
		from activities.quiz_question_map as map
		join activities.quiz_questions as q on
			q.id = map.quiz_question_id
		join public.worlds as w on
			w.id = map.world_id
		join public.themes as t on
			t.id = map.theme_id
		where
			map.quiz_question_id = '". $id ."'
			and map.active
		order by
			map.world_id
			,map.grade_id
			,map.theme_id
			,map.generation_id
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

	<label class="form_label" for="fact">Fact</label><br>
	<div class="form_data">
		<textarea name="fact" id="fact" class="input_xxlarge"><?php if(!empty($info["fact"])) { echo $info["fact"]; } ?></textarea>
		<br>
<?php 

	$fact_voiceover = '<input type="hidden" name="media[fact_voiceover][upload_id]['.$info["fact_id"].']" />';
	if (isset($info["media"]['fact_voiceover'])) {
		$fact_voiceover = display_quiz_media($info["media"]['fact_voiceover'], $info["fact_id"]);
	}

	$fact_media = '<input type="hidden" name="media[fact_media][upload_id]['.$info["fact_id"].']" />';
	if (isset($info["media"]['fact_media'])) {
		$fact_media = display_quiz_media($info["media"]['fact_media'], $info["fact_id"]);
	}

	$output = '
		<div id="fact_uploads_images_queue"></div>
		<div id="fact_uploads_audio_queue"></div>
		<input id="fact_uploads_audio" type="file" name="fact_uploads_audio" />
		'.$fact_voiceover.'<br />
		<input id="fact_uploads_images" type="file" name="fact_uploads_images" />
		'.$fact_media.'
	';

	echo $output;

	unset($output, $fact_voiceover, $fact_media);

?>
	</div>

	<label class="form_label">Question<span>*</span></label>

	<div class="form_data">
		<textarea required name="question" id="question" class="input_xxlarge"><?php if(!empty($info["question"])) { echo htmlspecialchars($info["question"]); } ?></textarea>
		<br>

<?php

	$question_voiceover = '<input type="hidden" name="media[question_voiceover][upload_id]" />';
	if(isset($info["media"]['question_voiceover'])) {
		$question_voiceover = display_quiz_media($info["media"]['question_voiceover']);
	}

	$question_media = '<input type="hidden" name="media[question_media][upload_id]" />';
	if(isset($info["media"]['question_media'])) {
		$question_media = display_quiz_media($info["media"]['question_media']);
	}

	$output = '
		<div id="question_uploads_images_queue"></div>
		<div id="question_uploads_audio_queue"></div>
		<input id="question_uploads_audio" type="file" name="question_uploads_audio" />
		'.$question_voiceover.'<br />
		<input id="question_uploads_images" type="file" name="question_uploads_images" />
		'.$question_media.'
	';

	echo $output;

	unset($output, $question_voiceover, $question_media);

?>
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

	<div class='question_headline clearfix'>
		<h3>Answers</h3>
		<div class="float_right">
			<input type="button" value="Add Another Answer" onclick="add_answer()" class="add">
		</div>
	</div>

	<div class="answer_container">
		<div id="answer_box">
		<label class="form_label">Answer Image</label><br />
		<div id="answer_uploads_images_queue"></div>
		<input id="answer_uploads_images" type="file" name="answer_uploads_images" />
<?php 

	$answer_image = '<input type="hidden" name="media[answer_image][upload_id]" />';
	if(isset($info["media"]['answer_image'])) {
		$answer_image = display_quiz_media($info["media"]['answer_image']);
	}

	echo $answer_image;
	unset($answer_image);

?>
	<br />
<?php

	$output = '';
	$cnt = 1;
	foreach($info['answers'] as $k => $v) {

		if($k == 'new') { continue; }

		$is_correct = ($info['is_correct'] == $k ? " checked" : "");

		$answer_voiceover = '<input type="hidden" name="media[answer_voiceover][upload_id]['.$k.']" />';
		if(isset($info["media"]['answer_voiceover'][$k])) {
			$answer_voiceover = display_quiz_media($info["media"]['answer_voiceover'][$k], $k);
		}

		$answer_media = '<input type="hidden" name="media[answer_media][upload_id]['.$k.']" />';
		if(isset($info["media"]['answer_media'][$k])) {
			$answer_media = display_quiz_media($info["media"]['answer_media'][$k], $k);
		}

		$output .= '
		<div class="inputs mb">
			<label for="answer_'. $k .'"><b>Answer '. $cnt .'</b></label> <label for="is_correct_'. $k .'"><input type="radio" name="is_correct" id="is_correct_'. $k .'"'. $is_correct .' value="'. $k .'"> Correct Answer</label><br>
			<input type="text" name="answers['. $k .'][answer]" id="answer_'. $k .'" class="input_full" value="'. $v['answer'] .'"> 
			<br>
			<div id="media_files_queue_'. $k .'"></div>
			<div id="audio_files_queue_'. $k .'"></div>
			<input class="audio_files" type="file" name="audio_upload['. $k .']" />
			'.$answer_voiceover.' <br />
			<input class="media_files" type="file" name="media_upload['. $k .']" /> 
			'.$answer_media.'
		</div>
		';

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
				echo build_db_select(get_worlds(), "world_id", "world_alias");
				echo build_db_select(get_themes(), "theme_id", "theme_alias");
				echo build_db_select(get_grades(), "grade_id", "grade_id");
				echo build_db_select_generation(get_generations(), "generation_id", "generation_id");
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

<div id="preview_question_modal" style="display: none;">

	<div id="flash_container"></div>

</div>

<div id="preview_modal" style="display: none;">

	<div id="preview_container"></div>

</div>

<?php

	run_module('password');
	site_wide_notes('ajax',$GLOBALS['project_info']['path_data']['id'],$id);

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
	            ,'quiz_question_id' : <?php echo $id; ?>
	        }
			,'buttonText'   : 'Upload Media'
	        ,'buttonClass'  : 'label_button'
	        //,'fileType' : 'image/*'
			,'queueID' : 'media_files_queue_'+ id
	        ,'uploadScript' : '/ajax.php'
	        ,'onUploadComplete': function(file, data){

				var data = $.parseJSON(data);

				if (typeof ajax_debugger == "function" && typeof data.debug != "undefined") {
					ajax_debugger(data.debug, JSON.stringify(data.debug).length);
					data.debug = null;
				}

				$(this).parents().eq(1).find("a.preview.image").data("source", data.output);

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
	            ,'quiz_question_id' : <?php echo $id; ?>
	        }
			,'buttonText'   : 'Upload Voiceover'
	        ,'buttonClass'  : 'label_button'
	        ,'fileType' : 'audio/*'
			,'queueID' : 'audio_files_queue_'+ id
	        ,'uploadScript' : '/ajax.php'
	        ,'onUploadComplete': function(file, data){

				var data = $.parseJSON(data);

				if (typeof ajax_debugger == "function" && typeof data.debug != "undefined") {
					ajax_debugger(data.debug, JSON.stringify(data.debug).length);
					data.debug = null;
				}

				$(this).parents().eq(1).find("a.preview.audio").data("source", data.output);

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
	        ,'quiz_question_id' : <?php echo $id; ?>
        }
		,'queueID' : 'question_uploads_images_queue'
		,'uploadScript' : '/ajax.php'
		,'buttonText'   : 'Upload Media'
        ,'buttonClass'  : 'label_button'
        //,'fileType' : 'image/*'
        ,'onUploadComplete': function(file, data) {

			var data = $.parseJSON(data);

			if (typeof ajax_debugger == "function" && typeof data.debug != "undefined") {
				ajax_debugger(data.debug, JSON.stringify(data.debug).length);
				data.debug = null;
			}

			$(this).parents().eq(1).find("a.preview.image").data("source", data.output);

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
	        ,'quiz_question_id' : <?php echo $id; ?>
        }
		,'queueID' : 'question_uploads_audio_queue'
		,'uploadScript' : '/ajax.php'
		,'buttonText'   : 'Upload Voiceover'
        ,'buttonClass'  : 'label_button'
        ,'fileType' : 'audio/*'
        ,'onUploadComplete': function(file, data){

			var data = $.parseJSON(data);

			if (typeof ajax_debugger == "function" && typeof data.debug != "undefined") {
				ajax_debugger(data.debug, JSON.stringify(data.debug).length);
				data.debug = null;
			}

			$(this).parents().eq(1).find("a.preview.audio").data("source", data.output);

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
	        ,'quiz_question_id' : <?php echo $id; ?>
        }
		,'queueID' : 'fact_uploads_images_queue'
		,'uploadScript' : '/ajax.php'
		,'buttonText'   : 'Upload Media'
        ,'buttonClass'  : 'label_button'
        //,'fileType' : 'image/*'
        ,'onUploadComplete': function(file, data){

			var data = $.parseJSON(data);

			if (typeof ajax_debugger == "function" && typeof data.debug != "undefined") {
				ajax_debugger(data.debug, JSON.stringify(data.debug).length);
				data.debug = null;
			}

			$(this).parents().eq(1).find("a.preview.image").data("source", data.output);

			$('input[name="media[fact_media][upload_id][<?php echo $info["fact_id"]; ?>]"]').val(data.upload_id);
			
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
	        ,'quiz_question_id': <?php echo $id; ?>
        }
		,'queueID' : 'fact_uploads_audio_queue'
		,'uploadScript' : '/ajax.php'
		,'buttonText'   : 'Upload Voiceover'
        ,'buttonClass'  : 'label_button'
        ,'fileType' : 'audio/*'
        ,'onUploadComplete': function(file, data){

			var data = $.parseJSON(data);

			if (typeof ajax_debugger == "function" && typeof data.debug != "undefined") {
				ajax_debugger(data.debug, JSON.stringify(data.debug).length);
				data.debug = null;
			}

			$(this).parents().eq(1).find("a.preview.audio").data("source", data.output);

			$('input[name="media[fact_voiceover][upload_id][<?php echo $info["fact_id"]; ?>]"]').val(data.upload_id);
			
		}
    });

    $('#answer_uploads_images').uploadifive({
    	'auto' : true
    	,'multi' : false
        ,'formData'     : {
            'apid' : '2e3e15529de3e903c16ab83793017a31'
            ,'action' : 'upload'
	        ,'type' : 'answer_image'
	        ,'quiz_question_id' : <?php echo $id; ?>
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

			$(this).parents().eq(1).find("a.preview.image").data("source", data.output);

			$('input[name="media[answer_image][upload_id]"]').val(data.upload_id);
			
		}
    });

	$("body").on("click", ".preview_question", function() {

		var question_id = $(this).data("question-id"),
			question_number = $(this).data("question-number"),
			grade_id = $(this).data("grade-id"),
			generation_id = $(this).data("generation-id"),
			theme_alias = $(this).data("theme-alias"),
			world_alias = $(this).data("world-alias");

		run_question_preview("#flash_container", question_id, question_number, grade_id, generation_id, theme_alias, world_alias);

		$.fancybox.open({
			"type": "inline"
			,"href": "#preview_question_modal"
			,"padding": 30
			,"scrolling": "no"
		});

		return false;

	});

/* Dynamic question previewer relies on aliases currently. Data in select menus are ids. */
/*
	$("body").on("change", "select.world_alias", function() {
		$(this).parents().eq(1).find(".preview_question").data("world-alias", $(this).val());
	});

	$("body").on("change", "select.theme_alias", function() {
		$(this).parents().eq(1).find(".preview_question").data("theme-alias", $(this).val());
	});

	$("body").on("change", "select.grade_id", function() {
		$(this).parents().eq(1).find(".preview_question").data("grade-id", $(this).val());
	});

	$("body").on("change", "select.generation_id", function() {
		$(this).parents().eq(1).find(".preview_question").data("generation-id", $(this).val());
	});
*/

	function run_question_preview(container_id, question_id, question_number, grade_id, generation_id, theme_alias, world_alias) {

		var alternate_content = "",
			swf_path = "/swf/quiz_multiformat";

		if (typeof AC_FL_RunContent != "function" || typeof DetectFlashVer != "function") {
			// Is missing ac_runactivecontent.js include
			alert("This page is blocking required code to function.");
		} else {

			// Check for compatible version of Flash Player
			if (DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision)) {
				// Embed the flash movie
				$(container_id).html(AC_FL_RunContent(
					"codebase", "http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0"
					,"width", "640"
					,"height", "480"
					,"src", swf_path
					,"quality", "high"
					,"pluginspage", "http://www.macromedia.com/go/getflashplayer"
					,"align", "middle"
					,"play", "true"
					,"loop", "true"
					,"scale", "showall"
					,"wmode", "opaque"
					,"devicefont", "false"
					,"id", "flashcontainer"
					,"bgcolor", "#000"
					,"name", "container"
					,"menu", "true"
					,"allowScriptAccess", "sameDomain"
					,"allowFullScreen", "false"
					,"movie", swf_path
					,"salign", ""
					,"FlashVars", "world="+world_alias+"&question_id="+question_id+"&question_number="+question_number+"&is_guest=true&user_id=0&grade="+grade_id+"&student_id=0&playasclassroom=false&generation="+generation_id+"&theme="+theme_alias
				));
			} else {
				// Flash Player not installed or does not meet site requirements. Append alternate content below.
				alternateContent += '<h2>Clever Crazes for Kids requires Adobe Flash to view this content. <a href="http://get.adobe.com/flashplayer/">Click here</a> to download Adobe Flash</h2>';
				$(container_id).html(alternateContent);
			}

		}
	}

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
	            ,'quiz_question_id' : <?php echo $id; ?>
	        }
			,'buttonText'   : 'Upload Media'
	        ,'buttonClass'  : 'label_button'
	        //,'fileType' : 'image/*'
	        ,'queueID' : 'media_files_queue_'+ cnt
	        ,'uploadScript' : '/ajax.php'
	        ,'onUploadComplete': function(file, data){

				var data = jQuery.parseJSON(data);

	            if(typeof ajax_debugger == 'function') {
	                ajax_debugger(data['debug'],JSON.stringify(data['debug']).length);
	                data['debug'] = null;                   
	            }

				$(this).parents().eq(1).find("a.preview.image").data("source", data.output);

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
	            ,'position' : cnt
	            ,'voiceover' : true
	        	,'quiz_question_id' : <?php echo $id; ?>
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

	            $(this).parents().eq(1).find("a.preview.audio").data("source", data.output);

				$('input[name="media[answer_voiceover][upload_id]['+ data.position +']"]').val(data.upload_id);

			}
	    });

		$id("phone_count").value = (cnt + 1);	
	}

	function initial_grid() {
		var objects = JSON.parse($id('initial_grid').value);
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

		td_options.innerHTML = '<a href="#" data-question-id="'+question_id+'" data-question-number="'+question_number+'" data-grade-id="'+grade_id+'" data-generation-id="'+generation_id+'" data-theme-alias="'+theme_alias+'" data-world-alias="'+world_alias+'" class="preview_question" title="Preview Question">Preview</a>';
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

	$("body").on("click", ".preview", function() {

		if ($(this).data("source").length > 0) {

			var source = $(this).data("source"),
				html = "";

			if ($(this).hasClass("audio")) {
				html += '<audio controls>';
				html += '<source src="'+source+'">';
				html += 'Your browser does not support the audio element.';
				html += '</audio>';
			} else if ($(this).hasClass("image")) {
				html += '<img src="'+source+'">';
			} else if ($(this).hasClass("video")) {
				html += '<video controls>';
				html += '<source src="'+source+'">';
				html += 'Your browser does not support the video tag.';
				html += '</video>';
			}


			if (html.length > 0) {

				$("#preview_container").html(html);

				$.fancybox.open({
					"type": "inline"
					,"href": "#preview_modal"
					,"padding":15
					,"scrolling": "no"
				});

			}

		}

		return false;

	});

	initial_grid();
   
</script>

<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional PHP Functions
##################################################

function build_db_select($res, $name, $class) {

	$display = "";

	$display = '
		<select name="grid[templateid]['.$name.']" id="'.$name.'_templateid" class="'.$class.'">
			<option value="">-Select '.ucfirst($name).'-</option>
	';

	while ($row = db_fetch_row($res)) {
		$display .= '<option value="'.$row["id"].'">'.$row["title"].'</option>';
	}

	$display .= '</select>';

	return $display;

}

function build_db_select_generation($res,$name) {
	$output = '<select name="grid[templateid]['. $name .']" id="'. $name .'_templateid">';
	$output .= '<option value="">-Select '. ucfirst($name) .'-</option>';
	while($row = db_fetch_row($res)) {
		if ($row['active'] == 'f' || !$row['active']){
			$row['title'] .= ' (inactive)';
		}
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
