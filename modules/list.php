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

##################################################
#   DB Queries
##################################################
$q = "select * from public.list where key='". db_prep_sql($key) ."'";
$info = db_fetch($q,"Getting list information");

$info['filter_labels'] = json_decode($info['filter_labels'],true);
$info['filter_orders'] = json_decode($info['filter_orders'],true);

$tmp = db_fetch("select markdown from public.list_markdown where list_id = '". $info['id'] ."'","Getting Markdown");
$info['markdown'] = $tmp['markdown'] ?? '';

$q = "
	select
		public.asset.*
		,list_asset_map.tags
		,list_asset_map.percentage
		,list_asset_map.filters
	from public.asset
	join public.list_asset_map on 
		list_asset_map.asset_id = asset.id
		and list_asset_map.list_id = '". $info['id'] ."'
	order by
		asset.id
";
$res = db_query($q,"Getting list assets");

##################################################
#   Pre-Content
##################################################
// add_css('pagination.css');
// add_js('sortlist.new.js');
add_js("list_functions.js",10);
library("slug.php");
add_js("markdown.min.js");

$assets[$info['id']] = [];
while($row = db_fetch_row($res)) {
	if(empty($assets[$info['id']]['list_title'])) {
		$assets[$info['id']] = [
            'list_title' => $info['title']
            // ,'list_label' => $info['label']
            ,'randomize' => 1
            ,'display_limit' => 20
            ,'filter_count' => 0
            ,'list_id' => $info['id']
            ,'tables' => $info['tables']
            ,'assets' => []
            // ,'tags' => []
            // ,'percentages' => []
            ,'filters' => []
		];
	}
	$assets[$info['id']]['assets'][] = $row['title'];
	// $assets[$info['id']]['tags'][] = $row['tags'];
	if(!empty($row['filters'])) {
		$assets[$info['id']]['filter_count'] += 1;
	}
	$assets[$info['id']]['filters'][] = $row['filters'];
	

	// $assets[$info['id']]['percentages'][] = $row['percentage'];
}

$csv_url = $_SERVER['REQUEST_SCHEME'] ."://api.". $_SERVER['SERVER_NAME'] .'/lists/'. $key ."/";
$raw_url = $_SERVER['REQUEST_SCHEME'] ."://api.". $_SERVER['SERVER_NAME'] .'/lists/raw/'. $key ."/";

##################################################
#   Content
##################################################
?>
<div class='clearfix'>
<div class="float_right">
	<input type="button" onclick="window.location.href='<?php echo $csv_url; ?>'" value="Export to CSV">
	<input type="button" onclick="window.location.href='<?php echo $raw_url; ?>'" value="Export Raw">
</div>
<h2 class='lists'>Lists: <?php echo $info['title']; ?></h2>

<?php
	if(!empty($info['markdown'])) {
?>
<div id="list_buttons" class="tabbar">
	<button type="button" class="tablink active" onclick="open_tabs(this,'default','list')">Default</button>
	<button type="button" class="tablink" onclick="open_tabs(this,'md','list')">Information</button>
</div>
<div id="list_bodies" class="tabbody">
	<div id="default" class="tabs">
<?php
	} // Markdown Check
?>
	<div class="filters" onclick="show_hide('filter_details')">
		Filters (<span class="filter_count" id="filter_count">0 applied</span>)
	</div>
	<div class="filter_details" id="filter_details" style="display: none;">

		<form id="form_filters" method="" action="" onsubmit="return false;">
			<label for="limit_<?php echo $info['key']; ?>">
				Limit Display: <input type="input" name="limit" id="limit_<?php echo $info['key']; ?>" value="20" class='xs'> 
			</label>

			<label for="randomize_<?php echo $info['key']; ?>">
				<input checked type="checkbox" name="options" id="randomize_<?php echo $info['key']; ?>" value="randomize"> Randomize
			</label>
			<!--label for="percentages">
				<input type="checkbox" name="options" id="percentages_<?php echo $info['key']; ?>" value="percentages"> Use Percentages
			</label-->
	    </form>


<?php
if(!empty($info['filter_orders'])) {
	asort($info['filter_orders']);	
}
// echo "<pre>";
// print_r($info);
// echo "<pre>";
	if(!empty($info['filter_orders'])) {
		$output = '
			<div id="custom_filters_'. $info['key'] .'" class="mb">
				<div class="mtb">
					<div><strong>Filters</strong></div>
					<label for="filter_and">
						<input type="radio" id="filter_and" name="and_or" value="and" /> And
					</label> &nbsp; 
					<label for="filter_or">
						<input checked type="radio" id="filter_or" name="and_or" value="or" /> Or
					</label>
				</div>
		';
		$cnt = 0;
		foreach($info['filter_orders'] as $slug => $order) {
			$v = $info['filter_labels'][$slug];
			if(!trim($v)) {
				continue;
			}
			// $slug = convert_to_alias($v);
			$output .= '
			<label for="filter_'. $cnt .'" style="width: 200px; float: left; margin-right: 2em;">
				<input type="checkbox" id="filter_'. $cnt .'" name="filters['. $slug .']" onclick="build_display(\''. $info['key'] .'\')" value="'. $slug .'"> '. $v .'
			</label> 
			';
			$cnt += 1;
		}
		$output .= '</div>';
		echo $output;
	}
?>
	<div class="clear"></div>
		<div class="mb">
			<button type="button" onclick="build_display('<?php echo $info['key']; ?>')">Update</button>
		</div>

	<form id="export_csv" method="post" action="/export/csv/" style='display: none;'>
		<!--label>&nbsp;</label><br>
		<input type="submit" value="Export CSV">
		<input type="hidden" name="query_csv" id="query_csv" value=""-->
	</form>

</div>





		<div class='listcounter mt' id="listcounter" style='display:;'>
<?php
// foreach($assets as $k => $list) {
// 	$l = $list['display_limit'];
// 	$r = $list['randomize'];
// 	$title = (!empty($list['list_label']) ? $list['list_label'] : $list['list_title']);
// 	$output = '
// 		<ol class="list_ordered" id="list_body_'. $k .'">
// 	';
// 	$i = 0;
// 	if($list['tables'] == "t") {
// 		$i = 1;
// 		$output = '
// 		<table cellspacing="0" cellpadding="0" class="tbl">
// 			<thead>
// 				<tr>
// 					<th>'. implode('</th><th>',explode("|",$list['assets'][0])) .'</th>
// 				</tr>
// 			</thead>
// 			<tbody id="list_body_'. $k .'">
// 		';
// 	}
// 	$cnt = 0;
// 	for($len=count($list['assets']); $i<$len; $i++) {
// 		$a = $list['assets'][$i];
// 		$t = json_decode($list['filters'][$i],true);
// 		// $p = $list['percentages'][$i];
// 		foreach($t as $tk => $tv) {
// 			$t[$tk] = convert_to_alias($tv);
// 		}
// 		$t_list = (!empty($t) ? implode(' ',$t) : '');
// 		// if(preg_match("/\[\d*[D|d]\d+\]/",$a,$matches)) {
// 		// 	$a = str_replace($matches[0],$matches[0].":".random($matches[0]),$a);
// 		// }

// 		$display = ($cnt < $list['display_limit'] ? '' : " style='display:none;'");
// 		if($list['tables'] == "t") {
// 			$output .= '<tr data-filters="'. $t_list .'"'. $display .'>
// 				<td>'. implode("</td><td>",explode('|',$a)) .'</td>
// 			</tr>';
// 		} else {
// 			$output .= '<li data-filters="'. implode(' ',$t) .'" data-perc="'. $p .'"'. $display .'>
// 				'. $a .'
// 			</li>';
// 		}
// 		$cnt += 1;
// 	}

// 	if($list['tables'] == "t") {
// 		$output .= "</tbody></table>";
// 	} else {
// 		$output .= "</ol>";
// 	}
// 	echo $output;
// 	unset($output);
// }
?>
	</div>

<?php
	if(!empty($info['markdown'])) {
?>
	</div>
	<div id="md" class="tabs" style="display: none">
		<article id="markdown" class="markdown" style="padding: 1em">
			<?php echo $info['markdown']; ?>
		</article>
	</div>
<?php
	} // Markdown Check
?>

	<div class="clear"></div>
</div>
<?php
##################################################
#   Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">
	var is_table = <?php echo ($info['tables'] == 't' ? 'true' : 'false'); ?>;
	var list_keys = [<?php echo implode(',',array_keys($assets)); ?>];
	var original_rows = {};
	set_original_rows();

	var assets = {};
<?php
	foreach($assets as $k => $v) {
		$output = '';
		foreach($v['assets'] as $k2 => $v2) {
			$output .= ",['". addslashes($v2) ."','". addslashes($v['filters'][$k2]) ."']";
		}

		echo "\nassets['". $info['key'] ."'] = {
			'tables': '". ($v['tables'] == 't' ? 1 : 0) ."'
			,'filter_count': parseInt(". $v['filter_count'] .")
			,'assets': [". substr($output,1) ."]
		};";

	}
?>

function get_keys(key) {
	if($id('randomize_'+ key).checked) {
		return random_keys(key);
	} 
	return assets[key].assets.slice(1,$id('limit_'+ key).value);
}

function random_keys(key) {
	var used_keys = {};
	var keys = [];
	var limit = parseInt($id('limit_'+ key).value);
	var min = assets[key].tables || 0;
	var filters = get_filters(key)
	var arr = build_filtered_list(key,filters);
	var asset_length = arr.length;

	// console.log("Limit: "+ limit);
	// console.log("Asset Length: "+ asset_length);
	// console.log(arr)
	// console.log(filters)
	// return
	if(limit >= asset_length) {
		// console.log("return all");
		while(asset_length--) {
			// console.log("-- asset_length: "+ asset_length +" -- k: "+ arr[asset_length])
			keys[keys.length] = arr[asset_length];
		}
		return shuffle_array(keys);
	} else {
		// console.log("return partial");
		while(keys.length < limit) {
			k = rand(1,asset_length - 1);
			// console.log("min: "+ min +" -- asset_length: "+ asset_length +" -- k: "+ k)
			// console.log("min: "+ min +" -- asset_length: "+ asset_length +" -- k: "+ k)
			if(used_keys[k] == undefined) {
				used_keys[k] = 1;
				keys[keys.length] = arr[k];
			}
		}
		return keys;
	}
}

function build_filtered_list(key,checked) {
	// var checked = get_filters(key);
	if(assets[key].filter_count == 0 || checked.length == 0) {
		return (assets[key].tables ? assets[key].assets.slice(1) : assets[key].assets);
	}

	var filtered_arr = [];
	var and_or = ($id('filter_or').checked ? "or" : "and");
	var a = assets[key].assets;

	for(var i=(assets[key].tables || 0),len=a.length; i<len; i++) {
		if(filter_criteria(and_or, checked, JSON.parse(a[i][1]))) {
			filtered_arr[filtered_arr.length] = assets[key].assets[i];
		}
	}
	return filtered_arr;
}


function fetch_table_assets(key) {
	var keys = get_keys(key);
	var output = '';
	for(var i=0,len=keys.length; i<len; i++) {
		output += `<tr>
			<td>`+ parse_random(keys[i][0].split("|").join("</td><td>")) +`</td>
		</tr>`;
	}
	return output;
}

function fetch_list_assets(key) {
	var keys = get_keys(key);
	var output = '';
	for(var i=0,len=keys.length; i<len; i++) {
		output += '<ol>'+ (keys[i][0].split("|").join("</td><td>")) +'</ol>';
	}
	return output;
}

function build_display(key) {
	var output;
	if(assets[key].tables) {
		output = `
 		<table cellspacing="0" cellpadding="0" class="tbl">
 			<thead>
 				<tr>
 					<th>`+ assets[key].assets[0][0].split('|').join('</th><th>') +`</th>
 				</tr>
 			</thead>
 			<tbody id="list_body_`+ key +`">
 			`+ fetch_table_assets(key) +`
 			</tbody>
 		</table>
		`;
	} else {
		output = `
			<ol class="list_ordered" id="list_body_`+ $k +`">
				`+ fetch_list_assets(key) +`
			</ol>
		`;
	}
	$id('listcounter').innerHTML = output;
}
build_display('<?php echo $info['key']; ?>');

// function filter_list(key) {
// 	var filters = $query('#filters_table input[type=checkbox]');
// 	var filters_length = filters.length;
// 	var checked = [];
// 	for(var i=0,len=filters.length; i<len; i++) {
// 		if(filters[i].checked) {
// 			checked[checked.length] = filters[i].value;
// 		}
// 	}
// 	elems = $query('#filter_examples [data-filters]')

// 	var show;
// 	var checked_length = checked.length;
// 	for(var i=0,len=elems.length; i<len; i++) {
// 		show = (checked_length == 0 ? true : false);
// 		for(j in checked) {
// 			r = new RegExp('(^|\\s)'+ checked[j] + '(\\s|$)');
// 			if(r.test(elems[i].dataset.filters)) {
// 				show = true;
// 				break;
// 			}
// 		}				
// 		elems[i].style.display = (show ? "" : "none");
// 	}
// }

</script>
<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#   Additional PHP Functions
##################################################
// function random($str) {
// 	$str = str_replace(["[","]"],"",$str);
// 	$pieces = explode("d",strtolower($str));
// 	$min = (!(int)$pieces[0] ? 1 : (int)$pieces[0]);
// 	$max = (int)$pieces[1];
// 	return mt_rand($min,$max);
// }
##################################################
#   EOF
##################################################