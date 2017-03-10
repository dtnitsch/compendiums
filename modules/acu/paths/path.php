<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
$security_check_list = ['path_list','path_add','path_edit','path_delete'];
$security_list = has_access(implode(",",$security_check_list)); 
if(empty($security_list['path_list'])) { back_redirect(); }
if(!has_access("path_list")) { back_redirect(); }

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
	<h2 class='paths'>Path List</h2>
  
  <?php
    $add_button = "";
    if(!empty($security_list["path_add"])) {
        $add_button = "<button onclick='window.location.href=\"/acu/paths/add/\"' class='add'>Add New Path</button>";
    }

    $edit_onclick = "";
    if(!empty($security_list['path_edit'])) {
        $edit_onclick = " onclick='window.location=\"/acu/paths/edit/?id={{id}}\"'";
    }

    $delete_link = "";
    if(!empty($security_list['path_delete'])) {
        $delete_link = '<a href="/acu/paths/delete/?id={{id}}" title="Delete: {{path}}" class="delete"></a>';
    }
?>
  
  <div class='right float_right buttons'>
      <button onclick='export_csv("asl_sort","visible")' class='export'>Export Visible</button>
      <button onclick='export_csv("asl_sort","all")' class='export'>Export All</button>
      <?= $add_button ?>
    </div>
</div>

<div class='content_container'>
<?php echo dump_messages(); ?>
	<fieldset class='filters' id='filters'>
	<form id="form_filters" method="" action="" onsubmit="return false;">
		<div class='inputs float_left'>
			<label for='path'><b>Path</b></label><br>
			<input type='text' name='filters[path]' id='path'>
		</div>
				
		<div class='inputs float_left'>
			<label for='module_name'><b>Module Name</b></label><br>
			<input type='text' name='filters[module_name]' id='module_name'>
		</div>
				
		<div class='inputs float_left'>
			<label for='template'><b>Template</b></label><br>
			<input type='text' name='filters[template]' id='template'>
		</div>
				
		<div class='inputs float_left'>
			<label for='title'><b>Title</b></label><br>
			<input type='text' name='filters[title]' id='title'>
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
			<th data-col="path">Path</th>
			<th data-col="module_name">Module Name</th>
			<th data-col="template">Template</th>
			<th data-col="title">Title</th>
			<th data-col="modified">Date Modified</th>
			<th class='options' style='width: 1%;'><img src='/images/options.png' /></th>
		</tr>
		</thead>
		<tbody style='display: none;'>
		<tr<?= $edit_onclick ?>>
			
			<td>{{path}}</td>
			<td>{{module_name}}</td>
			<td>{{template}}</td>
			<td>{{title}}</td>
			<td>{{modified}}</td>
			<td rel='nolink' class='options'><?= $delete_link ?></td>
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
<!--script type="text/javascript" src="/js/sortlist_local.data.js"></script-->
<script type="text/javascript">


	asl_sort = sortlist().remote;
	asl_sort.init('/ajax.php',{
		id:'asl_sort'
		,data: 'apid=71395ff031577e3a21b972de5fe6b0a4'
		,filters: 'filters'
		,type: "pagination"
	});

	function filter_results() {
		asl_sort.sort(asl_sort,true);
	}
	// True is needed to show it's a custom call
	asl_sort.sort(asl_sort,true);

	// var sortlist_local = sortlist().local;
	// sortlist_local.init(list,"sortlist_local");

	// asl_sort_setup({
	// 	url: '/ajax.php'
	// 	,id: 'asl_sort'
	// 	,cache: true
	// 	,filters: 'filters'
	// 	,data: 'apid=71395ff031577e3a21b972de5fe6b0a4'
	// 	,type: 'pagination'
	// 	,callback: save_query
	// 	<?php if(!empty($GLOBALS['debug_options']['enabled'])) { echo ",debug: true"; } ?>
	// });
	// asl_sort('asl_sort');

	// function filter_results() {
	// 	asl_sort('asl_sort');
	// }

	// function save_query(data) {
	// 	asl['asl_sort']['query'] = '';
	// 	if(typeof data['query'] != 'undefined' && data['query'] != '') {
	// 		asl['asl_sort']['query'] = data['query'];
	// 	}
	// }

	// function export_csv(table_id,type) {
	// 	if(typeof asl[table_id]['query'] != 'undefined' && asl[table_id]['query'] != '') {
	// 		// var custom_columns = ['id','title','description']
	// 		var data = 'apid=1a3873edb1f3643c2c60ff495780bb9a&type='+ type +'&query='+ asl[table_id]['query'];
	// 		ajax({
	// 			url: '/ajax.php'
	// 			,debug: true
	// 			,data: data
	// 			,type: 'json'
	// 			,success: function(data) {
	// 				console.log("export csv")
	// 				console.log(data)
	// 				//var info = JSON.parse(data.output)
	// 			}
	// 		});

	// 	}
	// }
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