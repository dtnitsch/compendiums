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

$start_date = date('m/d/Y',strtotime('Previous Sunday'));
$end_date = date('m/d/Y',strtotime('Saturday this Week'));

##################################################
#   Content
##################################################
?>
<div class='clearfix'>
	<h2 class='reports'>Report - Participating</h2>
</div>
<div class='content_container'>
  <?php echo dump_messages(); ?>
	<fieldset class="filters">
	<form id="form_filters" method="" action="" onsubmit="return false;">
		
		<div class='inputs float_left'>
			<label for="pi.marketing_source"><b>Marketing Source</b></label><br>
			<input type="text" name="filters[pi.marketing_source]" id="pi.marketing_source">
		</div>
		
    <div class='inputs float_left'>
      <label>&nbsp;</label><br>
      <button onclick='filter_results()' class='filter'>Filter Results</button>
    </div>

	</fieldset>
	</form>

      <span class='show_pagination'></span>
      <table id='asl_sort' cellpadding='0' cellspacing='0' class='asl_sort'>
          <thead id='asl_sort_head'>
            <tr>
              <th data-col='marketing_source'>Marketing Source</th>
              <th data-col='registered_count'>Total</th>
            </tr>
          </thead>
          <tbody style='display: none;'>
            <tr>
              <td>{{marketing_source}}</td>
              <td>{{registered_count}}</td>
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
        ,data: 'apid=6a99c43d5d17471449c30920ecac2206'
        ,filters: 'form_filters'
        ,type: "pagination"
        // ,pre_hook: show_hide_columns
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

##################################################
#   EOF
##################################################