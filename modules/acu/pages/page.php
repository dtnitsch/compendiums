<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

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
	<h2>Page List</h2>
	<?php echo DUMP_MESSAGES(); ?>

<div class='overlay'>
	fasdf
	<div class='box'>
		!!!
	</div>
</div>

	<fieldset class='filters' id='filters'>
	<form id="form_filters" method="" action="" onsubmit="return false;">
		<div class='inputs float_left'>
			<label for='page'>Page</label><br>
			<input type='text' name='filters[page]' id='page'>
		</div>
				
		<div class='inputs float_left'>
			<label for='alias'>Alias</label><br>
			<input type='text' name='filters[alias]' id='alias'>
		</div>
				

		<div class='clear'><input type='button' value='Filter Results' onclick='filter_results()'></div>
	</fieldset>
	</form>

	<div class='right'><button onclick='window.location.href="/acu/pages/add/"'>Add New Page</button></div>
	<span class='show_pagination'></span>
	<table id='asl_sort' cellpadding="0" cellspacing="0" class='asl_sort'>
		<thead>
		<tr>
			
			<th col="page">Page</th>
			<th col="alias">Alias</th>
			<th col="modified">Date Modified</th>
			<th class='options' style='width: 1%;'><img src='/images/options.png' /></th>
		</tr>
		</thead>
		<tbody style='display: none;'>
		<tr onclick='window.location="/acu/pages/edit/?id={{id}}"'>
			
			<td>{{page}}</th>
			<td>{{alias}}</th>
			<td>{{modified}}</th>
			<td rel='nolink'>
				[<a href='/acu/pages/delete/?id={{id}}' title="Delete: {{page}}" class='delete'>X</a>]
			</td>
		</tr>
		</tbody>
	</table>
	<span class='show_pagination'></span>
	<div class='right'><button onclick='window.location.href="/acu/pages/add/"'>Add New Page</button></div>

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
		,data: 'apid=b76ace59e039eb2f0c980e0ec740c19b'
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