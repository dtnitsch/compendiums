<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
$security_check_list = ['students_list','students_delete'];
$security_list = has_access(implode(",",$security_check_list)); 
if(empty($security_list['students_list'])) { back_redirect(); }

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

add_css('pikaday.css',1000,'/js/pikaday/css/');
add_css('triangle.css',1001,'/js/pikaday/css/');
add_js('//cdnjs.cloudflare.com/ajax/libs/moment.js/2.5.1/moment.min.js');
add_js('pikaday.js',1000,'/js/pikaday/');

$info = $_GET;
$info["institution_id"] = (!empty($info["institution_id"]) ? $info["institution_id"] : "");
$info["user_id"] = (!empty($info["user_id"]) ? $info["user_id"] : "");
$info["activity_start_date"] = (!empty($info["activity_start_date"]) ? $info["activity_start_date"] : "");
$info["activity_end_date"] = (!empty($info["activity_end_date"]) ? $info["activity_end_date"] : "");
$info['created_start'] = '';
$info['created_end'] = '';

$time = time();
if(empty($info["activity_start_date"])) {
  $info["activity_start_date"] = date('m/d/Y',(date('w', $time) == 0) ? $time : strtotime('last sunday', $time));
}
if(empty($info["activity_end_date"])) {
  $info["activity_end_date"] = date('m/d/Y',(date('w', $time) == 6) ? $time : strtotime('next saturday', $time));
}

// If this page is loading from the user edit section, set this value
if(strpos($_SERVER['SCRIPT_URI'],'/acu/users/edit/') !== false && !empty($_GET['id'])) {
  $info['user_id'] = (int)$_GET['id'];
}

// If this page is loading from the school edit section, set this value
if(strpos($_SERVER['SCRIPT_URI'],'/acu/schools/edit/') !== false && !empty($_GET['id'])) {
  $info['institution_id'] = (int)$_GET['id'];
}

##################################################
#   Content
##################################################
?>
<div class='clearfix'>
  <h2 class='users'>Student List</h2>

    <?php
    $add_button = "";
    if(!empty($security_list['admin_students_add'])) {
        $add_button = "<button onclick='window.location.href=\"/acu/students/add/\"' class='add'>Add New Student</button>";
    }

    $edit_onclick = "";
    if(!empty($security_list['admin_students_edit'])) {
        $edit_onclick = " onclick='window.location=\"/acu/students/edit/?id={{id}}\"'";
    }

    $delete_link = "";
    if(!empty($security_list['admin_students_delete'])) {
        $delete_link = '<a href="/acu/students/delete/?id={{id}}" title="Delete: {{firstname}} {{lastname}}" class="delete"></a>';
    }
?>

    <div class='right float_right buttons'>
<?php
      #<button onclick='export_csv("asl_sort_students","visible")' class='export'>Export Visible</button>
      #<button onclick='export_csv("asl_sort_students","all")' class='export'>Export All</button>
      echo $add_button;
?>
		<button onclick='window.location.href="/acu/importers/students/"' class="add">Student Importer</button>
	</div>
</div>

  <div class='content_container'>
  <?php echo dump_messages(); ?>
  <fieldset class='filters' id='filters'>
	<form id="form_filters_students" method="" action="" onsubmit="return false;">

		<div class='inputs float_left'>
			<label for='firstname'><b>Firstname</b></label><br>
			<input type='text' name='filters[firstname]' id='firstname'>
		</div>

    <div class='inputs float_left'>
      <label for='lastname'><b>Lastname</b></label><br>
      <input type='text' name='filters[lastname]' id='lastname'>
    </div>


      <div class='inputs float_left'>
            <label for='grade'><b>Grade</b></label><br>
<?php
    echo build_db_select(get_grades(),"ss.grade_id", "grade");
?>
        </div>

    <div class='inputs float_left'>
      <label for='school'><b>School</b></label><br>
      <input type='text' name='filters[school]' id='school'>
    </div>

    <div class='inputs float_left'>
      <label for='institution_id'><b>School ID</b></label><br>
      <input type='text' name='filters[institution_id]' id='institution_id' value="<?php echo $info['institution_id']; ?>">
    </div>

    <div class='inputs float_left'>
      <label for='user_id'><b>User ID</b></label><br>
      <input type='text' name='filters[user_id]' id='user_id' value="<?php echo $info['user_id']; ?>">
    </div>

    <div class='inputs float_left'>
      <label for='created_start'><b>Created Start Date</b></label><br>
      <input type='text' name='filters[created_start]' id='created_start' value="<?php echo $info['created_start']; ?>">
    </div>

    <div class='inputs float_left'>
      <label for='created_end'><b>Created End Date</b></label><br>
      <input type='text' name='filters[created_end]' id='created_end' value="<?php echo $info['created_end']; ?>">
    </div>


		<div class='inputs float_left'>
      <label>&nbsp;</label><br>
      <button onclick='filter_results_students()' class='filter'>Filter Results</button>
    </div>

	</form>

	<form id="export_csv" method="post" action="/export/csv/" style='display: none;'>
		<label>&nbsp;</label><br>
		<input type="submit" value="Export CSV">
		<input type="hidden" name="query_csv" id="query_csv" value="">
	</form>

	</fieldset>

  <span class='show_pagination'></span>
	<table id='asl_sort_students' cellpadding='0' cellspacing='0' class='asl_sort'>
		<thead id='asl_sort_students_head'>
			<tr>
				<td style="background: #2e3192;"><input type="checkbox" class="select_all" title="Select All"></td>
				<th data-col='id'>Student ID</th>
				<th data-col='firstname'>Firstname</th>
				<th data-col='lastname'>Lastname</th>
				<th data-col='user_name'>User Name</th>
				<th data-col='gender'>Gender</th>
				<th data-col='grade'>Grade</th>
				<th data-col='school'>School</th>
				<th data-col='total_score'>Total Score</th>
				<th data-col='created'>Date Created</th>
				<th class='options' style='width: 1%;'><img src='/images/options.png' /></th>
			</tr>
		</thead>
		<tbody style='display: none;'>
			<tr>
				<td><input type="checkbox" class="select_id" data-student-id="{{id}}"></td>
				<td>{{id}}</td>
				<td><a href="/acu/students/edit/?id={{id}}">{{firstname}}</a></td>
				<td><a href="/acu/students/edit/?id={{id}}">{{lastname}}</a></td>
				<td><a href="/acu/users/edit/?id={{user_id}}">{{user_name}}</a></td>
				<td>{{gender}}</td>
				<td>{{grade}}</td>
				<td><a href="/acu/schools/edit/?id={{institution_id}}">{{school}}</a></td>
				<td>{{total_score}}</td>
				<td>{{created}}</td>
				<td rel='nolink' class='options'>
				<a href='/acu/students/delete/?id={{id}}' title="Delete: {{firstname}}" class='delete'></a>
				</td>
			</tr>
		</tbody>
	</table>
  <span class='show_pagination'></span>
<button class="delete_selected">Delete Selected</button>
</div>

<div id="delete_selected_modal" style="display: none;">

	<h2>Are you sure?</h2>

	<p>Clicking yes will permanently delete all selected students.</p>

	<button class="delete_confirm" style="background: #008000; color: #FFFFFF;">Yes</button>
	<button class="delete_cancel" style="background: #800000; color: #FFFFFF;">Cancel</button>

</div>

<?php

//echo md5('classrooms.ajax.php');

add_js("https://ajax.googleapis.com/ajax/libs/jquery/1.12.3/jquery.min.js", 3);
add_css("fancybox/jquery.fancybox.css");
add_js("fancybox/jquery.fancybox.pack.js", 4);

##################################################
#   Javascript Functions
##################################################
ob_start();
?>

<script type="text/javascript">

	////////////////////////////////
	// MULTI DELETE FUNCTIONALITY //
	////////////////////////////////
	$("body").on("click", ".select_all", function() {

		if ($(this).prop("checked") == true) {
			$("table#asl_sort_students input.select_id").prop("checked", true);
		} else {
			$("table#asl_sort_students input.select_id").prop("checked", false);
		}

	});

	$("body").on("click", ".select_id", function() {

		if ($(this).prop("checked") == false && $("body input.select_all").prop("checked") == true) {
			$("body input.select_all").prop("checked", false);
		}

	});

	$("body").on("click", ".delete_selected", function() {

		$.fancybox.open({
			"type": "inline"
			,"href": "#delete_selected_modal"
			,"padding": 30
			,"scrolling": "no"
		});

		return false;

	});

	$("body").on("click", ".delete_confirm", function() {

		var data = [];

		$("table#asl_sort_students input.select_id").each(function(idx, val) {
			if ($(val).prop("checked") == true) {
				data.push($(val).data("student-id"));
			}
		});

		$.ajax({
			"type": "POST"
			,"url": "/ajax.php"
			,"data": {
				"apid": "eac157467b5c14c99a267a23e575d8f8"
				,"students_delete_bulk": true
				,"data": data
			}
			,"dataType": "json"
			,"success": function(rtn) {

				if (typeof ajax_debugger == "function" && typeof rtn.debug != "undefined") {
					ajax_debugger(rtn.debug, JSON.stringify(rtn.debug).length);
					rtn.debug = null;
				}

				if (rtn.success) {

					asl_sort_students.loading();
					asl_sort_students.create_sorted_list();

					$.fancybox.close();
					$("body input.select_all").prop("checked", false);

				}

			}
		});

		return false;

	});

	$("body").on("click", ".delete_cancel", function() {
		$.fancybox.close();
	});
	// END MULTI DELETE //

	asl_sort_students = sortlist().remote;

	asl_sort_students.init('/ajax.php',{
		id:'asl_sort_students'
		,data: 'apid=49e0175341961da38a2f73aca35512a0'
		,filters: 'form_filters_students'
		,type: "pagination"
		,column: "ss.created"
		,direction: "desc"
		,callback: function(data) {
			$id('query_csv').value = data.query;
			show($id('export_csv'));
			if (data.output.asl_sort_students.length == 0) {
				asl_sort_students.no_results();
			}
		}
	});

	function filter_results_students() {
		asl_sort_students.sort(asl_sort_students,true);
	}
    // True is needed to show it's a custom call
    asl_sort_students.sort(asl_sort_students,true);

	// function save_query(data) {
	// 	asl['asl_sort_students']['query'] = '';
	// 	if(typeof data['query'] != 'undefined' && data['query'] != '') {
	// 		asl['asl_sort_students']['query'] = data['query'];
	// 	}
	// }
  var pickerTriangle = new Pikaday({
        field: document.getElementById('created_start'),
        theme: 'triangle-theme',
        firstDay: 1,
        format: 'MM/DD/YYYY',
        minDate: new Date('2008-01-01'),
        maxDate: new Date('2020-12-31'),
        yearRange: [2008, 2020]
    });
    var pickerTriangle = new Pikaday({
        field: document.getElementById('created_end'),
        theme: 'triangle-theme',
        firstDay: 1,
        format: 'MM/DD/YYYY',
        minDate: new Date('2008-01-01'),
        maxDate: new Date('2020-12-31'),
        yearRange: [2008, 2020]
    });
	// Onfocus
    //$id("title").focus();

</script>

<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#   Additional PHP Functions
##################################################
function build_db_select($res,$name,$disp_name) {
    $output = '<select name="filters['. $name .']" id="'. $name .'">';
    $output .= '<option value="">-Select '. ucfirst($disp_name) .'-</option>';
    while($row = db_fetch_row($res)) {
        $output .= '<option value="'. $row['id'] .'">'. $row['title'] .'</option>';
    }
    $output .= '</select>';
    return $output;
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
##################################################
#   EOF
##################################################