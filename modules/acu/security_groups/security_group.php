<?php
##################################################
#   Document Setup and Security
##################################################
if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("security_group_list")) { back_redirect(); }
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
#if(!HAS_ACCESS('security_group_list')) { BACK_REDIRECT(); }

##################################################
#   Validation
##################################################

##################################################
#   DB Queries
##################################################

##################################################
#   Pre-Content
##################################################
ADD_CSS('pagination.css');
ADD_JS('sortlist.js');

##################################################
#   Content
##################################################
?>
	<h2>Security Group List</h2>
	<?php echo DUMP_MESSAGES(); ?>

	<fieldset class='filters' id='filters'>
	<form id="form_filters" method="" action="" onsubmit="return false;">
		<div class='inputs float_left'>
			<label for='title'>Security Group</label><br>
			<input type='text' name='filters[title]' id='title'>
		</div>
				
		<div class='inputs float_left'>
			<label for='alias'>Alias</label><br>
			<input type='text' name='filters[alias]' id='alias'>
		</div>
				
		<div class='inputs float_left'>
			<label for='modified'>Date Modified</label><br>
			<input type='text' name='filters[modified]' id='modified'>
		</div>
				

		<div class='clear'><input type='button' value='Filter Results' onclick='filter_results()'></div>
	</fieldset>
	</form>

	<div class='right'><button onclick='window.location.href="/acu/security-groups/add/"'>Add New Security Group</button></div>
	<span class='show_pagination'></span>
	<table id='asl_sort' cellpadding="0" cellspacing="0" class='asl_sort'>
		<thead>
		<tr>
			
			<th col="title">Security Group</th>
			<th col="alias">Alias</th>
			<th col="modified">Date Modified</th>
			<th nosort class='options' style='width: 1%;'><img src='/library/sortlist/options.gif' /></th>
		</tr>
		</thead>
		<tbody style='display: none;'>
		<tr onclick='window.location="/acu/security-groups/edit/?id={{id}}"'>
			
			<td>{{title}}</th>
			<td>{{alias}}</th>
			<td>{{modified}}</th>
			<td rel='nolink'>
				[<a href='/acu/security-groups/delete/?id={{id}}' title="Delete: {{security_group}}" class='delete'>X</a>]
			</td>
		</tr>
		</tbody>
	</table>
	<span class='show_pagination'></span>
	<div class='right'><button onclick='window.location.href="/acu/security-groups/add/"'>Add New Security Group</button></div>

<?php
##################################################
#   Javascript Functions
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
		,data: 'apid=a8469e90b1d018cede0385f1eb461e3b'
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
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################