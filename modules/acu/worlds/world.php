<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
$security_check_list = ['worlds_list','worlds_add','worlds_edit','worlds_delete'];
$security_list = has_access(implode(",",$security_check_list)); 
if(empty($security_list['worlds_list'])) { back_redirect(); }

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
	<h2 class='worlds'>World List</h2>
  
  <?php
    $add_button = "";
    if(!empty($security_list["worlds_add"])) {
        $add_button = "<button onclick='window.location.href=\"/acu/worlds/add/\"' class='add'>Add New World</button>";
    }

    $edit_onclick = "";
    if(!empty($security_list['worlds_edit'])) {
        $edit_onclick = " onclick='window.location=\"/acu/worlds/edit/?id={{id}}\"'";
    }

    $delete_link = "";
    if(!empty($security_list['worlds_delete'])) {
        $delete_link = '<a href="/acu/worlds/delete/?id={{id}}" title="Delete: {{path}}" class="delete"></a>';
    }
?>
  
  <div class='right float_right buttons'>
<?php
      #<button onclick='export_csv("asl_sort","visible")' class='export'>Export Visible</button>
      #<button onclick='export_csv("asl_sort","all")' class='export'>Export All</button>
?>
		<?= $add_button ?>
  </div>
</div>
<div class='content_container'>
<?php echo dump_messages(); ?>
	<fieldset class='filters' id='filters'>
	<form id="form_filters" method="" action="" onsubmit="return false;">
		
		<div class='inputs float_left'>
			<label for='world'><b>World</b></label><br>
			<input type='text' name='filters[world]' id='world'>
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
			
			<th data-col="title">World</th>
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

<script type="text/javascript">

	asl_sort = sortlist().remote;
	asl_sort.init('/ajax.php',{
		id:'asl_sort'
		,data: 'apid=f08e25bbed8a398a8f500dab97f4ed9d'
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
    $id("world").focus();

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