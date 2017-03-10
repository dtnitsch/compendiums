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

$start_date = date("m/d/Y",strtotime("Previous Sunday - 1 week"));
$end_date = date("m/d/Y", strtotime("Previous Saturday"));

##################################################
#   Content
##################################################
?>
<div class='clearfix'>
	<h2 class='reports'>Report - Heard About</h2>
</div>

<?php include_once("../modules/acu/reports/includes/horizontal_report_nav.php"); ?>

<div class='content_container'>
  <?php echo dump_messages(); ?>
  <fieldset class="filters">
	<form id="form_filters" method="" action="" onsubmit="return false;">

		<div class='inputs float_left'>
			<label for="u.marketing_source"><b>Marketing Source</b></label><br>
			<input type="text" name="filters[u.marketing_source]" id="u.marketing_source">
		</div>

    <div class='inputs float_left'>
      <label for='marketing_source'><b>Marketing Source</b></label><br>
      <?php  echo build_db_select(get_marketing_sources(),"marketing_source", "marketing_source", "marketing_source"); ?>
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
              <th data-col='firstname'>firstname</th>
              <th data-col='lastname'>lastname</th>
              <th data-col='email'>Email</th>
              <th data-col='type'>Registration Type</th>
              <th data-col='marketing_source'>Marketing Source</th>
              <th data-col='created'>Created</th>
            </tr>
          </thead>
          <tbody style='display: none;'>
            <tr>
              <td>{{firstname}}</td>
              <td>{{lastname}}</td>
              <td>{{email}}</td>
              <td>{{type}}</td>
              <td>{{marketing_source}}</td>
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
        ,data: 'apid=3724fee529b91fe9b84b2f532a8d69c9'
        ,filters: 'form_filters'
        ,type: "pagination"
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
</script>
<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#   Additional PHP Functions
##################################################
function build_db_select($res,$name,$disp_name,$id="id") {
    $output = '<select name="filters['. $name .']" id="'. $name .'">';
    $output .= '<option value="">-Select '. ucfirst($disp_name) .'-</option>';
    while($row = db_fetch_row($res)) {
        $output .= '<option value="'. $row[$id] .'">'. $row['title'] .'</option>';
    }
    $output .= '</select>';
    return $output;
}
// get grades
function get_marketing_sources(){

    $q = "
      select
        marketing_source
        ,(marketing_source || ' (' || count(marketing_source) || ')') as title
        ,count(marketing_source) as cnt
      from system.users
      where
        active
        and marketing_source != ''
        and registration_type_id in (1,2)
      group by
        marketing_source
      order by
        cnt desc
    ";
    $result = db_query($q,"Getting Grades");

    if(db_is_error($result)) {
        return false;
    }

    return $result;
}
##################################################
#   EOF
##################################################