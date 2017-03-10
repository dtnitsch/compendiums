<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
if(!HAS_ACCESS('dynamic_content_type_list')) { BACK_REDIRECT(); }

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
ADD_JS('sortlist.new.js');

##################################################
#   Content
##################################################
?>
<div class='clearfix'>
	<h2 class='dynamic-content-types'>Dynamic Content Type List</h2>
  
  <div class='right float_right buttons'>
    <button onclick='window.location.href="/acu/dynamic-content-type/add/"' class='add'>Add New Dynamic Content Type</button>
  </div>
</div>

<div class='content_container'>
<?php echo DUMP_MESSAGES(); ?>
	<fieldset class='filters' id='filters'>
	<form id="form_filters" method="" action="" onsubmit="return false;">
		<div class='inputs float_left'>
			<label for='title'><b>Dynamic Content Type</b></label><br>
			<input type='text' name='filters[title]' id='title'>
		</div>
				
		<div class='inputs float_left'>
			<label for='alias'><b>Alias</b></label><br>
			<input type='text' name='filters[alias]' id='alias'>
		</div>
				
		<div class='inputs float_left'>
			<label for='modified'><b>Date Modified</b></label><br>
			<input type='text' name='filters[modified]' id='modified'>
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

			<th data-col="title">Dynamic Content Type</th>
			<th data-col="alias">Alias</th>
			<th data-col="modified">Date Modified</th>
			<th class='options' style='width: 1%;'><img src='/images/options.png' /></th>
		</tr>
		</thead>
		<tbody style='display: none;'>
		<tr onclick='window.location="/acu/dynamic-content-type/edit/?id={{id}}"'>

			<td>{{title}}</td>
			<td>{{alias}}</td>
			<td>{{modified}}</td>
			<td rel='nolink' class='options'>
				<a href='/acu/dynamic-content-type/delete/?id={{id}}' title="Delete: {{dynamic_content_type}}" class='delete'></a>
			</td>
		</tr>
		</tbody>
	</table>
	<span class='show_pagination'></span>
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
		,data: 'apid=06a0fc087756944595785e90f79ecad5'
		,filters: 'filters'
		,type: "pagination"
	});

	function filter_results() {
		asl_sort.sort(asl_sort,true);
	}
	// True is needed to show it's a custom call
	asl_sort.sort(asl_sort,true);

</script>

<?php
$js = trim(ob_get_clean());
if(!empty($js)) { ADD_JS_CODE($js); }

##################################################
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################