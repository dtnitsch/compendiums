<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

$security_check_list = ['prizes_list','prizes_add','prizes_edit','prizes_delete'];
$security_list = has_access(implode(",",$security_check_list)); 
if(empty($security_list['prizes_list'])) { back_redirect(); }
if(!has_access("prizes_list")) { back_redirect(); }

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

##################################################
#   Content
##################################################
?>
	<h2>Prize List</h2>
	<?php echo dump_messages(); ?>

	<fieldset class='filters' id='filters'>
	<form id="form_filters" method="" action="" onsubmit="return false;">
		<div class='inputs float_left'>
			<label for='prize'>Prize</label><br>
			<input type='text' name='filters[prize]' id='prize'>
		</div>

				

		<div class='clear'></div>
		<input type='button' value='Filter Results' onclick='filter_results()'>
	</fieldset>
	</form>

<?php
    $add_button = "";
    if(!empty($security_list["prizes_add"])) {
        $add_button = "<button onclick='window.location.href=\"/acu/prizes/add/\"'>Add New Prize</button>";
    }

    $edit_onclick = "";
    if(!empty($security_list['prizes_edit'])) {
        $edit_onclick = " onclick='window.location=\"/acu/prizes/edit/?id={{id}}\"'";
    }

    $delete_link = "";
    if(!empty($security_list['prizes_delete'])) {
        $delete_link = '[<a href="/acu/prizes/delete/?id={{id}}" title="Delete: {{path}}" class="delete">X</a>]';
    }
?>

	<div class='right'>
		<input type='button' value='Export Visible' onclick='export_csv("asl_sort","visible")'>
		<input type='button' value='Export All' onclick='export_csv("asl_sort","all")'>

		<?= $add_button ?>
	</div>
	<span class='show_pagination'></span>
	<table id='asl_sort' cellpadding="0" cellspacing="0" class='asl_sort'>
		<thead>
		<tr>
			
			<th data-col="title">Prize</th>
			<th data-col="alias">Alias</th>
			<th data-col="modified">Date Modified</th>
			<th class='options' style='width: 1%;'><img src='/images/options.png' /></th>
		</tr>
		</thead>
		<tbody style='display: none;'>
		<tr<?= $edit_onclick ?>>
			
			<td>{{title}}</td>
			<td>{{alias}}</td>
			<td>{{modified}}</td>
			<td rel='nolink'><?= $delete_link ?></td>
		</tr>
		</tbody>
	</table>
	<span class='show_pagination'></span>

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
		,data: 'apid=bd97af0f9c54f69754e9dcb34fc0fc10'
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
if(!empty($js)) { add_js_code($js); }

##################################################
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################