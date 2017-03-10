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
add_css('pagination.css');
add_js('sortlist.new.js');

add_css('pikaday.css',1000,'/js/pikaday/css/');
add_css('triangle.css',1001,'/js/pikaday/css/');
add_js('//cdnjs.cloudflare.com/ajax/libs/moment.js/2.5.1/moment.min.js');
add_js('pikaday.js',1000,'/js/pikaday/');

$start_date = date("m/d/Y",strtotime("Previous Sunday - 1 week"));
$end_date = date("m/d/Y", strtotime("Previous Saturday"));


##################################################
#   Content
##################################################
?>
<div class='clearfix'>
	<h2 class='reports'>Report - Games</h2>
</div>

<?php include_once("../modules/acu/reports/includes/horizontal_report_nav.php"); ?>

<div class='content_container'>
    <?php echo dump_messages(); ?>
    <fieldset class="filters">
	<form id="form_filters" method="" action="" onsubmit="return false;">

		<div class='inputs float_left'>
			<label for="sas.firstname"><b>Students Name</b></label><br>
			<input type="text" name="filters[sas.firstname]" id="sas.firstname">
		</div>

        <div class='inputs float_left'>
            <label for="user_name"><b>Users Name</b></label><br>
            <input type="text" name="filters[user_name]" id="user_name">
        </div>

        <div class='inputs float_left'>
            <label for="sas.email"><b>Users Email</b></label><br>
            <input type="text" name="filters[sas.email]" id="sas.email">
        </div>

        <div class='inputs float_left'>
            <label for="sas.institution"><b>Institution</b></label><br>
            <input type="text" name="filters[sas.institution]" id="sas.institution">
        </div>

        <div class='inputs float_left'>
            <label for="sas.grade_id"><b>Grade</b></label><br>
            <input type="text" name="filters[sas.grade_id]" id="sas.grade_id">
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
                <th data-col="student_firstname">Student</th>
                <th data-col="grade_id">Grade</th>
                <th data-col="user_name">Adult</th>
                <th data-col="user_email">Email</th>
                <th data-col="institution">Institution</th>
                <th data-col="activity_count">Games Played</th>
                <th data-col="original_score">Pre-Multiplier Score</th>
                <th data-col="total_score">Total Score</th>
            </tr>
            </thead>
            <tbody style='display: none;'>
            <tr>
                <td>{{student_firstname}}</td>
                <td>{{grade_id}}</td>
                <td><a href="/acu/users/edit/?id={{user_id}}">{{user_name}}</a></td>
                <td>{{user_email}}</td>
                <td><a href="/acu/schools/edit/?id={{institution_id}}">{{institution}}</a></td>
                <td>{{activity_count}}</td>
                <td>{{original_score}}</td>
                <td>{{total_score}}</td>
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
        ,data: 'apid=b9099f70e1c628877757f544658f9d55'
        ,filters: 'form_filters'
        ,type: "pagination"
        ,column: "total_score"
        ,direction: "desc"
        // ,pre_hook: show_hide_columns
        ,callback: function(data) {
            $id('query_csv').value = data.query;
            show($id('export_csv'));
            if(data.output.asl_sort.length == 0) {
              asl_sort.no_results();
            }
        }
    });

    function filter_results() {
        asl_sort.sort(asl_sort,true);
    }

    filter_results();

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

##################################################
#   EOF
##################################################