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
	<h2 class='reports'>Report - Signups</h2>
</div>

<?php include_once("../modules/acu/reports/includes/horizontal_report_nav.php"); ?>

    <div class='content_container'>
        <?php echo dump_messages(); ?>
        <fieldset class="filters">
        <form id="form_filters" method="" action="" onsubmit="return false;">

        	<div class='inputs float_left'>
        		<label for="student_name"><b>Students Name</b></label><br>
        		<input type="text" name="filters[student_name]" id="student_name">
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
                <label for='pi.institution_type_id'><b>Institution Types</b></label><br>
<?php
  echo build_db_select(get_institution_types(),"pi.institution_type_id", "Institution Types");
?>
            </div>

            <div class='inputs float_left'>
                <label for='state'><b>State</b></label><br>
<?php
  echo build_db_select(get_states(),"pi.region_id", "state");
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
                <th data-col="id">User ID</th>
                <th data-col="firstname">First Name</th>
                <th data-col="lastname">Last Name</th>
                <th data-col="email">Email</th>
                <th data-col="institution_id">Institution ID</th>
                <th data-col="institution">Institution</th>
                <th data-col="site_name">Site Name</th>
                <th data-col="registration_type">Registration Type</th>
                <th data-col="city">City</th>
                <th data-col="state">State</th>
                <th data-col="population">Pop.</th>
                <th data-col="sa.created">Created</th>
                <th data-col="registered">Registered</th>
                <th data-col="heard_about">Heard About</th>
                <th data-col="active">Active</th>
            </tr>
            </thead>
            <tbody style='display: none;'>
            <tr>
                <td>{{id}}</td>
                <td><a href="/acu/users/edit/?id={{id}}">{{firstname}}</a></td>
                <td><a href="/acu/users/edit/?id={{id}}">{{lastname}}</a></td>
                <td>{{email}}</td>
                <td><a href="/acu/schools/edit/?id={{institution_id}}">{{institution_id}}</a></td>
                <td><a href="/acu/schools/edit/?id={{institution_id}}">{{institution}}</a></td>
                <td>{{site_name}}</td>
                <td>{{registration_type}}</td>
                <td>{{city}}</td>
                <td>{{state}}</td>
                <td>{{population}}</td>
                <td>{{created}}</td>
                <td>{{registered}}</td>
                <td>{{heard_about}}</td>
                <td>{{active}}</td>
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
        ,data: 'apid=415a98043860cc3764480e0d2de5797c'
        ,filters: 'form_filters'
        ,type: "pagination"
        ,column: "sa.created"
        ,direction: "asc"
        ,callback: function(data) {
            $id('query_csv').value = data.query;
            show($id('export_csv'));
            if(data.output.asl_sort.length == 0) {
              asl_sort.no_results();
            }
        }
        // ,pre_hook: show_hide_columns
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
//get states
function get_states() {

  $q = "select id,title from supplements.regions where active";
  $result = db_query($q,"Getting States");

  if(db_is_error($result)) {
    return false;
  }

  return $result;
}
//get registration types
function get_institution_types() {

  $q = "select id,title from public.institution_types where active";
  $result = db_query($q,"Getting institution types");

  if(db_is_error($result)) {
    return false;
  }

  return $result;
}
##################################################
#   EOF
##################################################