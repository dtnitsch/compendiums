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
	<h2 class='reports'>Report - Step It Up</h2>
	<?php echo dump_messages(); ?>
  </div>

<?php include_once("../modules/acu/reports/includes/horizontal_report_nav.php"); ?>

<div class='content_container'>
    <fieldset class="filters">
	<form id="form_filters" method="" action="" onsubmit="return false;">

		<div class='inputs float_left'>
			<label for="ss.firstname"><b>Students Name</b></label><br>
			<input type="text" name="filters[ss.firstname]" id="ss.firstname">
		</div>

        <div class='inputs float_left'>
            <label for="user_name"><b>Users Name</b></label><br>
            <input type="text" name="filters[user_name]" id="user_name">
        </div>

        <div class='inputs float_left'>
            <label for="sa.email"><b>Users Email</b></label><br>
            <input type="text" name="filters[sa.email]" id="sa.email">
        </div>

        <div class='inputs float_left'>
            <label for="pi.title"><b>Institution</b></label><br>
            <input type="text" name="filters[pi.title]" id="pi.title">
        </div>

        <div class='inputs float_left'>
            <label for="pi.city"><b>City</b></label><br>
            <input type="text" name="filters[pi.city]" id="pi.city">
        </div>

        <div class='inputs float_left'>
            <label for='state'><b>State</b></label><br>
<?php
    echo build_db_select(get_states(),"pi.region_id", "state");
?>
        </div>

        <div class='inputs float_left'>
            <label for="pi.address1"><b>Address</b></label><br>
            <input type="text" name="filters[pi.address1]" id="pi.address1">
        </div>

        <div class='inputs float_left'>
            <label for='grade'><b>Grade</b></label><br>
<?php
    echo build_db_select(get_grades(),"ss.grade_id", "grade");
?>
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
                <th data-col="user_id">User ID</th>
                <th data-col="total_score">Total Score</th>
                <th data-col="student_name">Student Name</th>
                <th data-col="grade">Grade</th>
                <th data-col="institution_type">Institution Type</th>
                <th data-col="institution">Institution</th>
                <th data-col="site_name">Site Name</th>
                <th data-col="user_name">User Name</th>
                <th data-col="user_email">User Email</th>
                <th data-col="city">City</th>
                <th data-col="state">State</th>
                <th data-col="activity_count">Activity Count</th>
                <th data-col="created">Created</th>
            </tr>
            </thead>
            <tbody style='display: none;'>
            <tr>
                <td>{{student_id}}</td>
                <td>{{user_id}}</td>
                <td>{{total_score}}</td>
                <td>{{student_name}}</td>
                <td>{{grade}}</td>
                <td>{{institution_type}}</td>
                <td>{{institution}}</td>
                <td>{{site_name}}</td>
                <td><a href="/acu/users/edit/?id={{user_id}}" title="{{user_name}}">{{user_name}}</a></td>
                <td>{{user_email}}</td>
                <td>{{city}}</td>
                <td>{{state}}</td>
                <td>{{activity_count}}</td>
                <td>{{created}}</td>
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
        ,data: 'apid=a36d568dca18684fdb21e73e43b70c4e'
        ,filters: 'form_filters'
        ,type: "pagination"
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

// get states
function get_states(){

    $q = "select id,title from supplements.regions where active";
    $result = db_query($q,"Getting regions");

    if(db_is_error($result)) {
        return false;
    }

    return $result;
}
##################################################
#   EOF
##################################################