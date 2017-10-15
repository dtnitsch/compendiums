<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

// if(!logged_in()) { safe_redirect("/login/"); }
// $security_check_list = ['lists_list','lists_add','lists_edit','lists_delete'];
// $security_list = has_access(implode(",",$security_check_list)); 
// if(empty($security_list['lists_list'])) { back_redirect(); }

##################################################
#   Validation
##################################################
$pieces = explode('/',$GLOBALS['project_info']['path_data']['path']);
$key = trim($pieces[2]);

library("api.php");
$info = json_decode(call_api_function("get_list",$key),1);

##################################################
#   DB Queries
##################################################
// $q = "select * from public.list where key='". db_prep_sql($key) ."'";
// $info = db_fetch($q,"Getting list information");

##################################################
#   Pre-Content
##################################################
add_js("compendium.js",10);
add_js("markdown.min.js");

$csv_url = $_SERVER['REQUEST_SCHEME'] ."://api.". $_SERVER['SERVER_NAME'] .'/lists/'. $key ."/";
$raw_url = $_SERVER['REQUEST_SCHEME'] ."://api.". $_SERVER['SERVER_NAME'] .'/lists/raw/'. $key ."/";

##################################################
#   Content
##################################################
?>

<div class="clearfix">

	<div class="subheader">
		<div class="float_right">
			<input type="button" onclick="window.location.href='<?php echo $csv_url; ?>'" value="Export to CSV">
			<input type="button" onclick="window.location.href='<?php echo $raw_url; ?>'" value="Export Raw">
		</div>

		<div class="title">List: <?php echo $info['title']; ?></div>
	</div>

	<div class="filters" onclick="show_hide('filter_details')">
		Filters (<span class="filter_count" id="filter_count">0 applied</span>) <span class="small">(<span class="fakeref">show/hide</span>)</span>
	</div>
	<div class="filter_details" id="filter_details" style="display: none;">

		<form id="form_filters" method="" action="" onsubmit="return false;">
			<label for="limit_<?php echo $info['key']; ?>">
				Limit Display: <input type="input" name="limit" id="limit_<?php echo $info['key']; ?>" value="4" class='xs'> 
			</label>

			<label for="randomize_<?php echo $info['key']; ?>">
				<input type="checkbox" name="options" id="randomize_<?php echo $info['key']; ?>" value="randomize"> Randomize
			</label>

			<div id="filters_dynamic" class="mtb"></div>
		</form>

		<div class="mt">
			<input type="button" value="Update List" onclick="build_all_display('listcounter')">
		</div>
	</div>
</div>



<div class='listcounter mt' id="listcounter"></div>

<div class="filters mt" onclick="show_hide('markdown_details')">
	List Details <span class="small">(<span class="fakeref">show/hide</span>)</span>
</div>
<div class="filter_details" id="markdown_details" style="display: none;">

	<div id="markdown" class="markdown" style="padding: 1em; display: block;">
		<div class="clear"></div>
	</div>		

	<div class="clear"></div>
</div>
<?php
##################################################
#   Javascript Functions
##################################################

ob_start();
?>
<script type="text/javascript">
	var assets = <?=json_encode($info)?>;
	// assets['lists']['946s5r1cQN'] = JSON.parse('{"list_title":"Simple List!","randomize":1,"display_limit":20,"filter_count":9,"filters":{"fruit":"fruit","vegetable":"vegetable","dairy":"dairy"},"list_key":"946s5r1cQN","tables":0,"assets":[["Apple",["fruit"]],["Banana",["fruit"]],["Tomato",["fruit","vegetable"]],["Potato",["vegetable"]],["Pineapple",["fruit"]],["Carrot",["vegetable"]],["Cucumber",["vegetable"]],["Cheese",["dairy"]],["Milk",["dairy"]]]}');
	var list_keys = Object.keys(assets['lists']);
	var is_table = Object.keys(assets['lists']);

	var current_asset = assets['lists'][list_keys[0]];

	build_filters(current_asset);
	$id('listcounter').innerHTML = build_display(current_asset);
	// build_all_display();

	parse_markdown_html('markdown',assets['description']);

	function build_filters(info) {
		// 1. Set Limit
		set_limit_display("limit_"+ info['list_key'],info['display_limit']);
		// 2. Randomize by default?
		set_randomize("randomize_"+ info['list_key'],info['randomize']);
		// 3. build filter list
		set_filters_list(info['list_key'],info['filters']);
	}
	function set_limit_display(id,limit) {
		$id(id).value = limit;
	}
	function set_randomize(id,checked) {
		$id(id).checked = (checked ? true : false);
	}
	function set_filters_list(id,arr) {
		var output = '<div class="mb" id="filters_'+ id +'">';
		var cnt = 0;
		for(i in arr) {
			output += '<label for="filter_'+ cnt +'">';
			output += '<input type="checkbox" id="filter_'+ cnt +'" name="filters['+ i +']" onclick="filter_list(\''+ id +'\',\'listcounter\')" value="'+ i +'"> '+ arr[i];
			output += '</label> &nbsp;'; 
			cnt += 1;
		}
		output += '</div>';
		$id("filters_dynamic").innerHTML = output;
	}

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