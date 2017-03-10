<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger

if(!logged_in()) { safe_redirect("/login/"); }
// Check mulitple security modules in one DB call
$security_check_list = ['quiz_questions_list','quiz_questions_add','quiz_questions_edit','quiz_questions_delete'];
$security_list = has_access(implode(",",$security_check_list));
if(empty($security_list['quiz_questions_list'])) { back_redirect(); }

##################################################
#   Validation
##################################################

##################################################
#   DB Queries
##################################################

##################################################
#   Pre-Content
##################################################
add_css('pagination.css');
add_js('sortlist.new.js');

##################################################
#   Content
##################################################
?>
<div class='clearfix'>
	<h2 class='quiz-questions'>Quiz Questions List</h2>

  <div class='right float_right buttons'>
<?php
      #<button onclick='export_csv("asl_sort","visible")' class='export'>Export Visible</button>
      #<button onclick='export_csv("asl_sort","all")' class='export'>Export All</button>
?>
		<button onclick='window.location.href="/acu/quiz-questions/add/"' class='add'>Add New Questions</button>
	</div>
</div>
<div class='content_container'>
<?php echo dump_messages(); ?>
	<fieldset class='filters' id='filters'>
	<form id="form_filters" method="" action="" onsubmit="return false;">

		<div class='inputs float_left'>
			<label for='question_id'><b>ID</b></label><br>
			<input type='text' name='filters[qq.id]' id='question_id'>
		</div>
		<div class='inputs float_left'>
			<label for='question'><b>Question</b></label><br>
			<input type='text' name='filters[qq.question]' id='question'>
		</div>
		<div class='inputs float_left'>
			<label for='world'><b>World</b></label><br>
<?php
	echo build_db_select(get_worlds(),"qqm.world_id", "world");
?>
		</div>
		<div class='inputs float_left'>
			<label for='themes'><b>Themes</b></label><br>
<?php
	echo build_db_select(get_themes(),"qqm.theme_id", "theme");
?>
		</div>
		<div class='inputs float_left'>
			<label for='grade'><b>Grade</b></label><br>
<?php
	echo build_db_select(get_grades(),"qqm.grade_id", "grade");
?>
		</div>
		<div class='inputs float_left'>
			<label for='generation'><b>Generation</b></label><br>
<?php
	echo build_db_select_generation(get_generations(), "qqm.generation_id", "generation");
?>
		</div>
		<div class='inputs float_left'>
      <label>&nbsp;</label><br>
      <button onclick='filter_results()' class='filter'>Filter Results</button>
    </div>
	</form>

	<form id="export_csv" method="post" action="/export/csv/" style='display: none;'>
		<label>&nbsp;</label><br>
		<input type="submit" value="Export CSV">
		<input type="hidden" name="query_csv" id="query_csv" value="">
	</form>

	</fieldset>

	<span class='show_pagination'></span>
	<table id='asl_sort' cellpadding="0" cellspacing="0" class='asl_sort'>
		<thead>
		<tr>
			<th data-col="question_number">Number</th>
			<th data-col="question">Question</th>
			<th data-col="world">World</th>
			<th data-col="theme">Themes</th>
			<th data-col="grade">Grade</th>
			<th data-col="generation">Gen</th>
			<th data-col="modified" style='min-width: 150px;'>Date Modified</th>
			<th data-col="id">ID</th>
			<th class='options' style='width: 1%;'><img src='/images/options.png' /></th>
		</tr>
		</thead>
		<tbody style='display: none;'>
		<tr>
			<td>{{question_number}}</td>
			<td><a href="/acu/quiz-questions/edit/?id={{id}}" target="_blank">{{question}}</a></td>
			<td>{{world}}</td>
			<td>{{theme}}</td>
			<td>{{grade}}</td>
			<td>{{generation}}</td>
			<td>{{modified}}</td>
			<td>{{id}}</td>
			<td rel='nolink' class='options'>
				<a href="/acu/quiz-questions/edit/?id={{id}}" class="view" title="View Question">View</a>
				<!--a href="#" data-question-id="{{id}}" data-question-number="{{}}" data-grade-id="{{grade}}" data-generation-id="{{generation}}" data-theme-alias="{{theme}}" data-world-alias="{{world}}" class="preview_question" title="Preview Question">Preview</a-->
				<a href="/acu/quiz-questions/delete/?id={{id}}" class="delete" title="Delete Question"></a>
			</td>
		</tr>
		</tbody>
	</table>
	<span class='show_pagination'></span>
</div>

<div id="preview_question_modal" style="display: none;">

	<div id="flash_container"></div>

</div>

<?php
##################################################
#   Javascript Functions
##################################################
add_js("https://ajax.googleapis.com/ajax/libs/jquery/1.12.3/jquery.min.js", 3);
add_css("fancybox/jquery.fancybox.css");
add_js("fancybox/jquery.fancybox.pack.js", 4);
add_js("ac_runactivecontent.js", 5);
add_js("cck2.js", 6);

ob_start();
?>

<script type="text/javascript">

	$(function() {

	});

	$("body").on("click", ".preview_question", function() {

		console.log($(this).data());

		$.fancybox.open({
			"type": "inline"
			,"href": "#preview_question_modal"
			,"padding": 30
			,"scrolling": "no"
		});

		//run_question_preview("#flash_container", "ar", 6, 46, 8, 2, "go");

		return false;

	});

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


    asl_sort = sortlist().remote;
    asl_sort.init('/ajax.php',{
        id:'asl_sort'
        ,data: 'apid=7bbe4be3cc9d9ea4c384843d7263afca'
        ,filters: 'form_filters'
        ,type: "pagination"
        ,column: "question"
        ,direction: "asc"
		,callback: function(data) {
			$id('query_csv').value = data.query;
			show($id('export_csv'));
		}
    });

    function filter_results() {
        asl_sort.sort(asl_sort,true);
    }
    // True is needed to show it's a custom call
    asl_sort.sort(asl_sort,true);

	// Onfocus
    $id("question").focus();

</script>

<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#   Additional PHP Functions
##################################################

function build_db_select($res, $name, $disp_name) {
	$output = '<select name="filters['. $name .']" id="'. $name .'">';
	$output .= '<option value="">-Select '. ucfirst($disp_name) .'-</option>';
	while($row = db_fetch_row($res)) {
		$output .= '<option value="'. $row['id'] .'">'. $row['title'] .'</option>';
	}
	$output .= '</select>';
	return $output;
}

function build_db_select_generation($res, $name, $disp_name) {
	$output = '<select name="filters['. $name .']" id="'. $name .'">';
	$output .= '<option value="">-Select '. ucfirst($disp_name) .'-</option>';
	while($row = db_fetch_row($res)) {
		if ($row['active'] == 'f' || !$row['active']){
			$row['title'] .= " (inactive)";
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

	$res = db_query($q,"Getting Generations");

	if (db_is_error($res)) {
		return false;
	}

	return $res;

}

##################################################
#   EOF
##################################################