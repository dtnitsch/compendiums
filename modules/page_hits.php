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
add_css('pagination.css');
add_js('sortlist.new.js');
add_js('sortlist_local.data.js');

##################################################
#   Content
##################################################
?>
	<h2>Page Hit List</h2>
	<?php echo dump_messages(); ?>

	<form id="form_filters" method="" action="" onsubmit="return false;">
	<fieldset class='filters' id='filters'>
		
		<div class='inputs float_left'>
			<label for='path'>Path</label><br>
			<input type='text' name='filters[path]' id='path'>
		</div>
				
		<div class='inputs float_left'>
			<label for='params'>Parameters</label><br>
			<input type='text' name='filters[params]' id='params'>
		</div>
				
		<div class='inputs float_left'>
			<label for='session_id'>Session ID</label><br>
			<input type='text' name='filters[session_id]' id='session_id'>
		</div>
				
		<div class='inputs float_left'>
			<label for='created'>Date Created!</label><br>
			<input type='text' name='filters[created]' id='created'>
		</div>
				
		<div class='clear'><input type='submit' value='Filter Results' onclick='filter_results()'></div>
	</fieldset>
	</form>

<h3>Ajax Table Sortlist</h3>
	<div class='right'><button onclick='window.location.href="/acu/security-roles/add/"'>Add New Security Role</button></div>
	<table id='asl_sort' cellpadding="0" cellspacing="0" class='asl_sort'>
		<thead>
		<tr>
			
			<th data-col="id">ID</th>
			<th data-col="path">Path</th>
			<th data-col="params">Parameters</th>
			<th data-col="session_id">Session ID</th>
			<th data-col="created">Date Created</th>
			<th class='options' style='width: 1%;'><img src='/images/options.gif' /></th>
		</tr>
		</thead>
		<tbody style='display: none;'>
		<tr>
			<td><input type="checkbox" name="ids[]" class="ids" id="ids_{{id}}" value="{{id}}"></td>
			<td>{{path}}</td>
			<td>{{params}}</td>
			<td>{{session_id}}</td>
			<td>{{created}}</td>
			<td rel='nolink'>
				[<a href='/acu/security-roles/delete/?id={{id}}' title="Delete: {{security_role}}" class='delete'>X</a>]
			</td>
		</tr>
		</tbody>
	</table>
	<div class='right'><button onclick='window.location.href="/acu/security-roles/add/"'>Add New Security Role</button></div>



<h3>Local Table Sortlist</h3>
<table id='sortlist_local' cellpadding="0" cellspacing="0" class='asl_sort'>
	<thead>
	<tr>
		<th data-col="_id">ID</th>
		<th data-col="balance" data-col-type="money">Balance</th>
		<th data-col="age" data-col-type="numeric">Age</th>
		<th data-col="company">Company</th>
		<th data-col="favoriteFruit">Favorite Fruit</th>
		<th class='options' style='width: 1%;'><img src='/images/options.png' /></th>
	</tr>
	</thead>
	<tbody style='display: none;'>
	<tr>
		<td>{{_id}} - {{index}}</td>
		<td>{{balance}}</td>
		<td>{{age}}</td>
		<td>{{company}}</td>
		<td>{{favoriteFruit}}</td>
		<td rel='nolink'>
			[<a href='/acu/security-roles/delete/?id={{_id}}' title="Delete: {{company}}" class='delete'>X</a>]
		</td>
	</tr>
	</tbody>
</table>


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
		,data: 'apid=a9068764f45c45d5799d85488d242fac'
		,filters: 'filters'
		,type: "pagination"
	});

	function filter_results() {
		asl_sort.sort(asl_sort,true);
	}
	// True is needed to show it's a custom call
	asl_sort.sort(asl_sort,true);






	var start,end;
	var sortlist_local = sortlist().local;
	sortlist_local.init(list,{
	    pre_hook: function() { start = microtime(true); }
	    ,post_hook: function() { end = microtime(true); }
	    ,oncomplete: function() { console.log("Complete: "+ (end - start)); }
	    ,columns: ["index","age","stuff"]
	    ,type: "pagination"
	});

	sortlist_local.add_sort_type("money",function(a, b) {
	    aa = parseFloat(a[1].replace(/[^\-\d\.]/g,""));
	    bb = parseFloat(b[1].replace(/[^\-\d\.]/g,""));
	    return (aa > bb ? 1 : -1);
	});

function microtime(get_as_float) {
  //  discuss at: http://phpjs.org/functions/microtime/
  // original by: Paulo Freitas
  // improved by: Dumitru Uzun (http://duzun.me)
  //   example 1: timeStamp = microtime(true);
  //   example 1: timeStamp > 1000000000 && timeStamp < 2000000000
  //   returns 1: true
  //   example 2: /^0\.[0-9]{1,6} [0-9]{10,10}$/.test(microtime())
  //   returns 2: true

  if (typeof performance !== 'undefined' && performance.now) {
    var now = (performance.now() + performance.timing.navigationStart) / 1e3;
    if (get_as_float) return now;

    // Math.round(now)
    var s = now | 0;
    return (Math.round((now - s) * 1e6) / 1e6) + ' ' + s;
  } else {
    var now = (Date.now ? Date.now() : new Date()
      .getTime()) / 1e3;
    if (get_as_float) return now;

    // Math.round(now)
    var s = now | 0;
    return (Math.round((now - s) * 1e3) / 1e3) + ' ' + s;
  }
}




	// asl_sort_setup({
	// 	url: '/ajax.php'
	// 	,id: 'asl_sort'
	// 	,column: 'path'
	// 	,order: 'desc'
	// 	//,display_count: 10
	// 	,cache: true
	// 	,filters: 'filters'
	// 	,data: 'apid=a9068764f45c45d5799d85488d242fac'
	// 	,type: 'pagination'
	// 	<?php if(!empty($GLOBALS['debug_options']['enabled'])) { echo ",debug: true"; } ?>
	// });
	// asl_sort('asl_sort');

	// function filter_results() {
	// 	asl_sort('asl_sort');
	// }
	// 
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