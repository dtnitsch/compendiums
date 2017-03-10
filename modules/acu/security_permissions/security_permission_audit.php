<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("security_permission_audit")) { back_redirect(); }


##################################################
#   Validation
##################################################
$id = get_page_id();
if(empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/acu/security-permissions/');
}

##################################################
#   DB Queries
##################################################
$info = db_fetch("select * from security.permissions where id='". $id ."'",'Getting Security Permission');

##################################################
#   Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/security_permissions/");

add_css('pagination.css');
add_js('sortlist.js');

##################################################
#   Content
##################################################
?>
	<h2>Audit Security Permission: <?php echo $info["title"]; ?></h2>

	<?= security_permissions_navigation($id,"audit") ?>

	<?php echo dump_messages(); ?>

	<form id="form_filters" method="" action="" onsubmit="return false;">
	<fieldset class='filters' id='filters'>
		<div class='inputs float_left'>
			<label for='column_name'>Column</label><br>
			<input type='text' name='filters[column_name]' id='column_name'>
		</div>

		<div class='clear'><input type='button' value='Filter Results' onclick='filter_results()'></div>
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
		<tr onclick='window.location="/acu/security-permissions/edit/?id={{id}}"'>
			<td>{{column_name}}</td>
			<td>{{full_name}}</td>
			<td>{{old_value}}</td>
			<td>{{new_value}}</td>
			<td>{{created}}</td>
		</tr>
		</tbody>
	</table>
	<span class='show_pagination'></span>
<?php
	site_wide_notes('ajax',$GLOBALS['project_info']['path_data']['id'],$id);
?>

<?php
##################################################
#	Javascript Functions
##################################################
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
		,data: 'apid=7a3c99435bf1e75d6743c6fa88b41554&id=<?php echo GET_PAGE_ID(); ?>'
		,type: 'pagination'
		<?php if(!empty($GLOBALS['debug_options']['enabled'])) { echo ",debug: true"; } ?>
	});
	asl_sort('asl_sort');

	function filter_results() {
		asl_sort('asl_sort');
	}
</script>
<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################
?>