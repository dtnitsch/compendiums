<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if (!logged_in()) { safe_redirect("/login/"); }

$security_check_list = ["institutions_list", "institutions_delete", "institutions_edit"];
$security_list = has_access(implode(",",$security_check_list)); 

if (empty($security_list["institutions_list"])) { back_redirect(); }

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

##################################################
#   Content
##################################################
?>
<div class='clearfix'>
	<h2 class='users'>Change Institution</h2>
	<div class='right float_right buttons'></div>
</div>

<div class="content_container">

	<?php echo dump_messages(); ?>

	<fieldset class="filters">

		<form id="form_filters_schools" method="" action="" onsubmit="return false;">

			<div class="inputs float_left">
				<label for="pi.title"><b>Institution</b></label><br>
				<input type="text" name="filters[pi.title]" id="pi.title">
			</div>

			<div class="inputs float_left">
				<label for="pi.city"><b>City</b></label><br>
				<input type="text" name="filters[pi.city]" id="pi.city">
			</div>

			<div class="inputs float_left">
				<label for="state"><b>State</b></label><br>
				<?php echo build_db_select(get_states(),"pi.region_id", "state"); ?>
			</div>

			<div class="inputs float_left">
				<label for="tp.id"><b>Institution Type</b></label><br>
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

			<div class="inputs float_left">
				<label>&nbsp;</label><br>
				<button onclick="filter_results_schools()" class="filter">Filter Results</button>
			</div>

		</form>

	</fieldset>

	<span class='show_pagination'></span>
	<table id='asl_sort_schools' cellpadding='0' cellspacing='0' class='asl_sort'>
		<thead id='asl_sort_head'>
			<tr>
				<th data-col='institution'>Institution</th>
				<th data-col='city'>City</th>
				<th data-col='state'>State</th>
				<th data-col='phone'>Phone</th>
				<th data-col='population'>Population</th>
				<th data-col='institution_type'>Type</th>
				<th data-col='created'>Date Created</th>
			</tr>
		</thead>
		<tbody style='display: none;'>
			<tr onclick="change_institution('{{id}}', '{{institution}}', '{{city}}', '{{state}}', '{{phone}}')">
				<td>{{institution}}</td>
				<td>{{city}}</td>
				<td>{{state}}</td>
				<td>{{phone}}</td>
				<td>{{population}}</td>
				<td>{{institution_type}}</td>
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

	function change_institution(id, institution, city, state, phone) {

		$("#institution_title").text(institution);
		$("#institution_city").text(city);
		$("#institution_state").text(state);
		$("#institution_phone").text(phone);
		$("#institution_id").val(id);

		$("html, body").animate({
			scrollTop: $("#test_institution").offset().top - 30
		}, "fast");

	}

</script>
<?php

$js = trim(ob_get_clean());

if (!empty($js)) { add_js_code($js); }

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