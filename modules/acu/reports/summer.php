<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
if(!logged_in()) { safe_redirect("/login/"); }
if(empty(has_access('reports_scores'))) { back_redirect(); }

##################################################
#   Validation
##################################################

##################################################
#   DB Queries
##################################################

##################################################
#   Pre-Content
##################################################
add_css('pikaday.css',1000,'/js/pikaday/css/');
add_css('triangle.css',1001,'/js/pikaday/css/');
add_css('pagination.css');
add_js('sortlist.new.js');
add_js('pikaday.js',1000,'/js/pikaday/');
add_js('//cdnjs.cloudflare.com/ajax/libs/moment.js/2.5.1/moment.min.js');


$start_date = date("m/d/Y",strtotime("Previous Sunday - 1 week"));
$end_date = date("m/d/Y", strtotime("Previous Saturday"));

##################################################
#   Content
##################################################
?>
<div class='clearfix'>
	<h2 class='reports'>Report - Combined Scores</h2>
  </div>

<?php include_once("../modules/acu/reports/includes/horizontal_report_nav.php"); ?>

<div class='content_container'>
    <?php echo dump_messages(); ?>
    <fieldset class="filters">
	<form id="form_filters" method="" action="" onsubmit="return false;">

		<div class='inputs float_left'>
			<label for="sas.student_firstname"><b>First Name</b></label><br>
			<input type="text" name="filters[sas.student_firstname]" id="sas.student_firstname">
		</div>

        <div class='inputs float_left'>
            <label for="sas.student_lastname"><b>Last Name</b></label><br>
            <input type="text" name="filters[sas.student_lastname]" id="sas.student_lastname">
        </div>

        <div class='inputs float_left'>
            <label for="user_name"><b>Adult</b></label><br>
            <input type="text" name="filters[user_name]" id="user_name">
        </div>

        <div class='inputs float_left'>
            <label for="sas.user_email"><b>Users Email</b></label><br>
            <input type="text" name="filters[sas.user_email]" id="sas.user_email">
        </div>

        <div class='inputs float_left'>
            <label for="pi.title"><b>Institution</b></label><br>
            <input type="text" name="filters[pi.title]" id="pi.title">
        </div>

        <div class='inputs float_left'>
            <label for='grade'><b>Grade</b></label><br>
			<?php  echo build_db_select(get_grades(),"grade", "grade"); ?>
        </div>

        <div class='inputs float_left'>
            <label for="start_date"><b>Start Date</b></label><br>
            <input type="text" name="filters[start_date]" id="start_date" value='<?php echo $start_date; ?>'>
        </div>

        <div class='inputs float_left'>
            <label for="end_date"><b>End Date</b></label><br>
            <input type="text" name="filters[end_date]" id="end_date" value='<?php echo $end_date; ?>'>
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
			<thead id="asl_sort_head">
				<tr>
					<th data-col="student_id">Student ID</th>
					<th data-col="firstname">Firstname</th>
					<th data-col="lastname">Lastname</th>
					<th data-col="grade_id">Grade</th>
					<th data-col="user_name">Teacher</th>
					<th data-col="user_email">Email</th>
					<th data-col="institution">Institution</th>
					<th data-col="institution_type">Reg. Type</th>
                    <th data-col="worlds_score">Worlds Score</th>
                    <th data-col="siu_score">StepItUp Score</th>
					<th data-col="total_score">Total Score</th>
					<th data-col="activity_count">Activity Count</th>
					<th data-col="created">Registered</th>
					<th data-col="institution_phone">Institution Phone</th>
					<th data-col="user_phone">user Phone</th>
				</tr>
			</thead>
			<tbody style='display: none;'>
				<tr>
					<td>{{student_id}}</td>
					<td>{{firstname}}</td>
					<td>{{lastname}}</td>
					<td>{{grade}}</td>
					<td><a href="/acu/users/edit/?id={{user_id}}" title="Edit User">{{user_name}}</a></td>
					<td><a href="/acu/users/edit/?id={{user_id}}" title="Edit User">{{user_email}}</a></td>
					<td><a href="/acu/schools/edit/?id={{institution_id}}" title="Edit Instititution">{{institution}}</a></td>
					<td>{{institution_type}}</td>
                    <td>{{worlds_score}}</td>
                    <td>{{siu_score}}</td>
					<td>{{total_score}}</td>
					<td>{{activity_count}}</td>
					<td>{{created}}</td>
					<td>{{institution_phone}}</td>
					<td>{{user_phone}}</td>
				</tr>
			</tbody>
		</table>
        <span class='show_pagination'></span>
    </div>
</div>

<?php
##################################################
#   Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">

    asl_sort = sortlist().remote;
    asl_sort.init('/ajax.php',{
        id:'asl_sort'
        ,data: 'apid=af104faa51ee22cca291880ffe41f7c7'
        ,filters: 'form_filters'
        ,type: "pagination"
        ,columns: "user_id"
        ,column: "total_score"
        ,direction: "desc"
        ,callback: function(data) {
            $id('query_csv').value = data.query;
            show($id('export_csv'));
            if(data.output.asl_sort.length == 0) {
              asl_sort.no_results();
            }
        }
    });

    filter_results();

    function filter_results() {
        asl_sort.sort(asl_sort,true);
    }


    var pickerTriangle = new Pikaday({
        field: document.getElementById('start_date'),
        theme: 'triangle-theme',
        firstDay: 1,
        format: 'MM/DD/YYYY',
        minDate: new Date('2008-01-01'),
        maxDate: new Date('2020-12-31'),
        yearRange: [2008, 2020]
    });
    var pickerTriangle = new Pikaday({
        field: document.getElementById('end_date'),
        theme: 'triangle-theme',
        firstDay: 1,
        format: 'MM/DD/YYYY',
        minDate: new Date('2008-01-01'),
        maxDate: new Date('2020-12-31'),
        yearRange: [2008, 2020]
    });
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