<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
if(!logged_in()) { safe_redirect("/login/"); }
$security_check_list = ['institutions_list','institutions_delete','institutions_edit'];
$security_list = has_access(implode(",",$security_check_list)); 
if(empty($security_list['institutions_list'])) { back_redirect(); }

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
add_js('//cdnjs.cloudflare.com/ajax/libs/moment.js/2.5.1/moment.min.js');
add_js('pikaday.js',1000,'/js/pikaday/');

add_css('pagination.css');
add_js('sortlist.new.js');

/* $start_date = date('m/d/Y',strtotime('Previous Sunday - 1 week'));
if(!empty($_GET['start_date'])) {
  $start_date = date('m/d/Y',strtotime($_GET['start_date']));
}

$end_date = date('m/d/Y',strtotime('Previous Sunday'));
if(!empty($_GET['end_date'])) {
  $end_date = date('m/d/Y',strtotime($_GET['end_date']));
} */

##################################################
#   Content
##################################################
?>
<div class='clearfix'>
  <h2 class='users'>Schools List</h2>
    
    <?php
    // No adds for institutions
    // $add_button = "";

    $edit_onclick = "";
    if(!empty($security_list['institutions_edit'])) {
        $edit_onclick = " onclick='window.location=\"/acu/schools/edit/?id={{id}}\"'";
    }

    $delete_link = "";
    if(!empty($security_list['institutions_delete'])) {
        $delete_link = '<a href="/acu/schools/delete/?id={{id}}" title="Delete: {{firstname}} {{lastname}}" class="delete"></a>';
    }
?>
    
    <div class='right float_right buttons'>
<?php
      #<button onclick='export_csv("asl_sort_schools","visible")' class='export'>Export Visible</button>
      #<button onclick='export_csv("asl_sort_schools","all")' class='export'>Export All</button>
      // echo $add_button;
?>

		<button onclick='window.location.href="/acu/schools/add/"' class='add'>Add New School</button>
    </div>
</div>

<?php include_once("../modules/acu/reports/includes/horizontal_report_nav.php"); ?>

<div class='content_container'>

	<?php echo dump_messages(); ?>

	<fieldset class="filters">

		<form id="form_filters_schools" method="" action="" onsubmit="return false;">

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
				<?php echo build_db_select(get_states(),"pi.region_id", "state"); ?>
			</div>

			<div class="inputs float_left">
				<label for="tp.id"><b>Type</b></label><br>
				<select name="filters[tp.id]" id="tp.id">
					<option value="">All</option>
					<option value="1" selected>School</option>
					<option value="2">Club</option>
					<option value="3">After School Program</option>
					<option value="4">Home-School</option>
					<option value="5">Kids in Needs Foundation</option>
					<option value="6">Parents</option>
				</select>
			</div>

			<div class='inputs float_left'>
				<label>&nbsp;</label><br>
				<button onclick='filter_results_schools()' class='filter'>Filter Results</button>
			</div>

		</form>

		<form id="export_csv" method="post" action="/export/csv/" style='display: none;'>
			<label>&nbsp;</label><br>
			<input type="submit" value="Export CSV">
			<input type="hidden" name="query_csv" id="query_csv" value="">
		</form>

</fieldset>

      <span class='show_pagination'></span>
      <table id='asl_sort_schools' cellpadding='0' cellspacing='0' class='asl_sort'>
          <thead id='asl_sort_head'>
            <tr>
              <th data-col='institution'>School</th>
              <th data-col='city'>City</th>
              <th data-col='state'>State</th>
              <th data-col='phone'>Phone</th>
              <th data-col='population'>Population</th>
              <th data-col='institution_type'>Type</th>
              <th data-col='created'>Date Created</th>
              <th class='options' style='width: 1%;'><img src='/images/options.png' /></th>
            </tr>
          </thead>
          <tbody style='display: none;'>
            <tr<?= $edit_onclick ?>>
              <td>{{institution}}</td>
              <td>{{city}}</td>
              <td>{{state}}</td>
              <td>{{phone}}</td>
              <td>{{population}}</td>
              <td>{{institution_type}}</td>
              <td>{{created}}</td>
              <td rel='nolink' class='options'>
                    <a href='/acu/schools/delete/?id={{id}}' title="Delete: {{schools}}" class='delete'></a>
              </td>
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

    asl_sort_schools = sortlist().remote;
    asl_sort_schools.init('/ajax.php',{
        id:'asl_sort_schools'
        ,data: 'apid=4bde24e707ac1a5128ee5c6b99ee2f34'
        ,filters: 'form_filters_schools'
        ,type: "pagination"
        ,column: "institution"
        ,callback: function(data) {
            $id('query_csv').value = data.query;
            show($id('export_csv'));
        }
        // ,pre_hook: show_hide_columns
    });

    asl_sort_schools.sort(asl_sort_schools,true);

    function filter_results_schools() {
        asl_sort_schools.sort(asl_sort_schools,true);
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