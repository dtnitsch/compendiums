<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
// Check mulitple security modules in one DB call
$security_check_list = ['dynamic_content_list','dynamic_content_add','dynamic_content_edit','dynamic_content_delete'];
$security_list = has_access(implode(",",$security_check_list)); 
if(empty($security_list['dynamic_content_list'])) { back_redirect(); }

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
<div class='clearfix'>
	<h2 class='dynamic-content'>Dynamic Content List</h2>
  
  <div class='right float_right buttons'>
    <button onclick='window.location.href="/acu/dynamic-content/add/"' class='add'>Add New Dynamic Content</button>
  </div>
</div>

<div class='content_container'>
<?php echo dump_messages(); ?>
	<fieldset class='filters' id='filters'>
	<form id="form_filters" method="" action="" onsubmit="return false;">

		<div class='inputs float_left'>
			<label for='title'><b>Dynamic Content</b></label><br>
			<input type='text' name='filters[title]' id='title'>
		</div>

		<div class='inputs float_left'>
			<label for='alias'><b>Alias</b></label><br>
			<input type='text' name='filters[alias]' id='alias'>
		</div>

		<div class='inputs float_left'>
			<label for='dynamic_content_type'><b>Dynamic Content Type</b></label><br>
			<input type='text' name='filters[dynamic_content_type]' id='dynamic_content_type'>
		</div>

		<div class='inputs float_left'>
			<label for='modified'><b>Date Modified</b></label><br>
			<input type='text' name='filters[modified]' id='modified'>
		</div>

		<div class='inputs float_left'>
          <label>&nbsp;</label><br>
          <button onclick='filter_results()' class='filter'>Filter Results</button>
        </div>

	</form>

	<form id="export_csv" method="post" action="/export/csv/" style='display: none;'>
		<label>&nbsp;</label><br>
		<input type="submit" value="Export CSV">
		<input type="hidden" name="query_csv" id="query_csv" value="">
	</form>

	</fieldset>

	<span class='show_pagination'></span>
	<table id='asl_sort' cellpadding="0" cellspacing="0" class='asl_sort'>
		<thead>
		<tr>
			<th data-col="title">Dynamic Content</th>
			<th data-col="alias">Alias</th>
			<th data-col="dynamic_content_type">Dynamic Content Type</th>
			<th data-col="modified">Date Modified</th>
			<th class='options' style='width: 1%;'><img src='/images/options.png' /></th>
		</tr>
		</thead>
		<tbody style='display: none;'>
		<tr>
			<td><a href="/acu/dynamic-content/edit/?id={{id}}">{{title}}</a></td>
			<td>{{alias}}</td>
			<td>{{dynamic_content_type}}</td>
			<td>{{modified}}</td>
			<td rel='nolink' class='options'>
				<a href='/acu/dynamic-content/delete/?id={{id}}' title="Delete: {{dynamic_content}}" class='delete'></a>
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
		,data: 'apid=3d2eb91c6c1a0a150901eaefae479296'
		,filters: 'form_filters'
		,type: "pagination"
        ,column: "title"
		,callback: function(data) {
			$id('query_csv').value = data.query;
			show($id('export_csv'));
		}
	});

	function filter_results() {
		asl_sort.sort(asl_sort,true);
	}
	// True is needed to show it's a custom call
	asl_sort.sort(asl_sort,true);

	// Onfocus
    $id("title").focus();

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