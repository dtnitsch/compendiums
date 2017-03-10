<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
$security_check_list = ['themes_list','themes_add','themes_edit','themes_delete'];
$security_list = has_access(implode(",",$security_check_list)); 
if(empty($security_list['themes_list'])) { back_redirect(); }

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
	<h2 class='themes'>Themes List</h2>
  
  <div class='right float_right buttons'>
<?php
      #<button onclick='export_csv("asl_sort","visible")' class='export'>Export Visible</button>
      #<button onclick='export_csv("asl_sort","all")' class='export'>Export All</button>
?>
    <button onclick='window.location.href="/acu/themes/add/"' class='add'>Add New Themes</button>
  </div>
</div>
  
  <div class='content_container'>
  <?php echo dump_messages(); ?>
	<fieldset class='filters' id='filters'>
	<form id="form_filters" method="" action="" onsubmit="return false;">
		
		<div class='inputs float_left'>
			<label for='title'><b>Themes</b></label><br>
			<input type='text' name='filters[title]' id='title'>
		</div>
				
		<div class='inputs float_left'>
			<label for='alias'><b>Alias</b></label><br>
			<input type='text' name='filters[alias]' id='alias'>
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
			
			<th data-col="title">Themes</th>
			<th data-col="alias">Alias</th>
			<th data-col="modified">Date Modified</th>
			<th class='options' style='width: 1%;'>&nbsp;</th>
		</tr>
		</thead>
		<tbody style='display: none;'>
		<tr onclick='window.location="/acu/themes/edit/?id={{id}}"'>
			<td>{{title}}</td>
			<td>{{alias}}</td>
			<td>{{modified}}</td>
			<td rel='nolink' class='options'>
				<a href='/acu/themes/delete/?id={{id}}' title="Delete: {{themes}}" class='delete'></a>
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
        ,data: 'apid=83915d1254927f41241e8630890bec6e'
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

	// function save_query(data) {
	// 	asl['asl_sort']['query'] = '';
	// 	if(typeof data['query'] != 'undefined' && data['query'] != '') {
	// 		asl['asl_sort']['query'] = data['query'];
	// 	}
	// }

	// Onfocus
    $id("title").focus();

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