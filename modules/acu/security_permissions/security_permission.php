<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("security_permission_list")) { back_redirect(); }


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
	<h2>Security Permission List</h2>
	<?php echo dump_messages(); ?>

	<fieldset class='filters' id='filters'>
		<form id="form_filters" method="" action="" onsubmit="return false;">
	
		<div class='inputs float_left'>
			<label for='section'>Section</label><br>
			<input type='text' name='filters[section.title]' id='section'>
		</div>
				
		<div class='inputs float_left'>
			<label for='group.title'>Group</label><br>
			<input type='text' name='filters[group.title]' id='group.title'>
		</div>
				
		<div class='inputs float_left'>
			<label for='permission.title'>Security Permission</label><br>
			<input type='text' name='filters[permission.title]' id='permission.title'>
		</div>
				
		<div class='inputs float_left'>
			<label for='permission.alias'>Alias</label><br>
			<input type='text' name='filters[permission.alias]' id='permission.alias'>
		</div>
				
		<div class='inputs float_left'>
			<label for='permission.modified'>Date Modified</label><br>
			<input type='text' name='filters[permission.modified]' id='permission.modified'>
		</div>
				

		<div class='clear'><input type='button' value='Filter Results' onclick='filter_results()'></div>
	</fieldset>
	</form>

	<div class='right'><button onclick='window.location.href="/acu/security-permissions/add/"'>Add New Security Permission</button></div>
	<span class='show_pagination'></span>
	<table id='asl_sort' cellpadding="0" cellspacing="0" class='asl_sort'>
		<thead>
		<tr>
			
			<th col="section">Section</th>
			<th col="grp">Group</th>
			<th col="title">Security Permission</th>
			<th col="alias">Alias</th>
			<th col="modified">Date Modified</th>
			<th nosort class='options' style='width: 1%;'><img src='/library/sortlist/options.gif' /></th>
		</tr>
		</thead>
		<tbody style='display: none;'>
		<tr onclick='window.location="/acu/security-permissions/edit/?id={{id}}"'>
			
			<td>{{section}}</td>
			<td>{{grp}}</td>
			<td>{{title}}</td>
			<td>{{alias}}</td>
			<td>{{modified}}</td>
			<td rel='nolink'>
				[<a href='/acu/security-permissions/delete/?id={{id}}' title="Delete: {{security_permission}}" class='delete'>X</a>]
			</td>
		</tr>
		</tbody>
	</table>
	<span class='show_pagination'></span>
	<div class='right'><button onclick='window.location.href="/acu/security-permissions/add/"'>Add New Security Permission</button></div>

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
        ,data: 'apid=384b12d1fb0698c55678f879482a9325'
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