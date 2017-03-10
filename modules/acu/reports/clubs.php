<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
if(!logged_in()) { safe_redirect("/login/"); }
$security_check_list = ['schools_list','schools_delete'];
$security_list = has_access(implode(",",$security_check_list)); 
if(empty($security_list['schools_list'])) { back_redirect(); }

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
	<h2 class='reports'>Report - Clubs</h2>
	<?php echo dump_messages(); ?>
  </div>

<?php include_once("../modules/acu/reports/includes/horizontal_report_nav.php"); ?>

<div class='content_container'>
      <?php echo dump_messages(); ?>
      <fieldset class="filters">
      <form id="form_filters" method="" action="" onsubmit="return false;">

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
                  <label for="population"><b>Minimum Population</b></label><br>
                  <input type="text" name="filters[population]" id="population" value='1'>
              </div>

              <div class='inputs float_left'>
                  <label for="registered_count"><b>Minimum Registered</b></label><br>
                  <input type="text" name="filters[registered_count]" id="registered_count" value='1'>
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
      <table id='asl_sort' cellpadding='0' cellspacing='0' class='asl_sort'>
          <thead id='asl_sort_head'>
            <tr>
              <th data-col='institution_id'>Institution ID</th>
              <th data-col='institution'>Institution</th>
              <th data-col='site_name'>Site Name</th>
              <th data-col='user_id'>User ID</th>
              <th data-col='user_name'>User Name</th>
              <th data-col='user_email'>User Email</th>
              <th data-col='city'>City</th>
              <th data-col='state'>State</th>
              <th data-col='phone'>Phone</th>
              <th data-col='created'>Created</th>
              <th data-col='heard_about'>Heard About</th>
              <th data-col='population'>Population</th>
              <th data-col='registered_count'>Registered</th>
              <th data-col='registered_percent'>Registered %</th>
              <th data-col='participating_count'>Participating</th>
              <th data-col='participating_percent'>Participating %</th>
              <th data-col='activities_played'>Activity Count</th>
              <th data-col='total_score'>Total Score</th>
              <th data-col='average_score'>Avg. Score</th>
            </tr>
          </thead>
          <tbody style='display: none;'>
            <tr>
              <td><a href="/acu/schools/edit/?id={{institution_id}}" title="{{institution_id}}">{{institution_id}}</a></td>
              <td><a href="/acu/schools/edit/?id={{institution_id}}" title="{{institution}}">{{institution}}</a></td>
              <td>{{site_name}}</td>
              <td><a href="/acu/users/edit/?id={{user_id}}">{{user_id}}</a></td>
              <td><a href="/acu/users/edit/?id={{user_id}}">{{user_name}}</a></td>
              <td>{{user_email}}</td>
              <td>{{city}}</td>
              <td>{{state}}</td>
              <td>{{phone}}</td>
              <td>{{created}}</td>
              <td>{{heard_about}}</td>
              <td>{{population}}</td>
              <td>{{registered_count}}</td>
              <td>{{registered_percent}}</td>
              <td>{{participating_count}}</td>
              <td>{{participating_percent}}</td>
              <td>{{activities_played}}</td>
              <td>{{total_score}}</td>
              <td>{{average_score}}</td>
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
        ,data: 'apid=bc5f559e76a2986c2aca0996cbab459e'
        ,filters: 'form_filters'
        ,type: "pagination"
        // ,pre_hook: show_hide_columns
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
//get states
function get_states() {

  $q = "select id,title from supplements.regions where active";
  $result = db_query($q,"Getting States");

  if(db_is_error($result)) {
    return false;
  }

  return $result;
}
##################################################
#   EOF
##################################################