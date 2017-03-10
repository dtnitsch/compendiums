<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("quiz_questions_audit")) { back_redirect(); }

##################################################
#   Validation
##################################################
$id = GET_PAGE_ID();
if(empty($id)) {
	WARNING_MESSAGE("An error occured while trying to edit this record:  Missing Requred ID");
	SAFE_REDIRECT('/acu/quiz_questions/');
}

##################################################
#   DB Queries
##################################################
$info = db_fetch("select * from public.quiz_questions where id='". $id ."'",'Getting Themes');

##################################################
#   Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/quiz_questions/");

ADD_CSS('pagination.css');
ADD_JS('sortlist.js');

##################################################
#   Content
##################################################
?>
	<h2 class='quiz-questions'>Audit Themes: <?php echo $info["title"]; ?></h2>
  
  <div class='content_container'>

	<?= quiz_questions_navigation($id,"audit") ?>

	<?php echo DUMP_MESSAGES(); ?>

	<form id="form_filters" method="" action="" onsubmit="return false;">
	<fieldset class='filters' id='filters'>
		<div class='inputs float_left'>
			<label for='column_name'><b>Column</b></label><br>
			<input type='text' name='filters[column_name]' id='column_name'>
		</div>

		<div class='inputs float_left'>
      <label>&nbsp;</label><br>
      <button onclick='filter_results()' class='filter'>Filter Results</button>
    </div>
	</fieldset>
	</form>

	<span class='show_pagination'></span>
	<table id='asl_sort' cellpadding="0" cellspacing="0" class='asl_sort'>
		<thead>
		<tr>
			<th col="column_name">Column Name</th>
			<th col="full_name">Full Name</th>
			<th col="old_value">Old Value</th>
			<th col="new_value">New Value</th>
			<th col="created">Date Created</th>
		</tr>
		</thead>
		<tbody style='display: none;'>
		<tr onclick='window.location="/acu/quiz_questions/edit/?id={{id}}"'>
			<td>{{column_name}}</td>
			<td>{{full_name}}</td>
			<td>{{old_value}}</td>
			<td>{{new_value}}</td>
			<td>{{created}}</td>
		</tr>
		</tbody>
	</table>
	<span class='show_pagination'></span>
</div>
<?php
	site_wide_notes('ajax',$GLOBALS['project_info']['path_data']['id'],$id);
?>


<?php
##################################################
#	Javascript Functions
##################################################
$closure = function() {
	ob_start();
?>
<script type="text/javascript">
	asl_sort_setup({
		url: '/ajax.php'
		,id: 'asl_sort'
		//,column: 'id'
		//,order: 'asc'
		//,display_count: 10
		,cache: true
		,filters: 'filters'
		,data: 'apid=ab670853684215e21c66f2c5bf77db24&id=<?php echo GET_PAGE_ID(); ?>'
		,type: 'pagination'
		<?php if(!empty($GLOBALS['debug_options']['enabled'])) { echo ",debug: true"; } ?>
	});
	asl_sort('asl_sort');

	function filter_results() {
		asl_sort('asl_sort');
	}
</script>
<?php
	return trim(ob_get_clean());
};
$js = $closure();
if(!empty($js)) { ADD_JS_CODE($js); }

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################
?>