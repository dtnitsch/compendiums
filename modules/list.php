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

$q = "
	select
		public.asset.*
		,list_asset_map.tags
		,list_asset_map.percentage
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

$assets[$info['id']] = [];
while($row = db_fetch_row($res)) {
	if(empty($assets[$info['id']]['list_title'])) {
		$assets[$info['id']] = [
            'list_title' => $info['title']
            // ,'list_label' => $info['label']
            ,'randomize' => 1
            ,'display_limit' => 20
            ,'list_id' => $info['id']
            ,'tables' => $info['tables']
            ,'assets' => []
            ,'tags' => []
            ,'percentages' => []
		];
	}
	$assets[$info['id']]['assets'][] = $row['title'];
	$assets[$info['id']]['tags'][] = $row['tags'];
	$assets[$info['id']]['percentages'][] = $row['percentage'];
}

##################################################
#   Content
##################################################
?>
<div class='clearfix'>
	<h2 class='lists'>Lists: <?php echo $info['title']; ?></h2>
  
	<div class="mb">
		<label for="limit">
			Limit Display: <input type="input" name="limit" id="limit_<?php echo $info['key']; ?>" value="20" class='xs'> 
		</label>

		<label for="randomize">
			<input checked type="checkbox" name="options" id="randomize_<?php echo $info['key']; ?>" value="randomize"> Randomize
		</label>
		<label for="percentages">
			<input type="checkbox" name="options" id="percentages_<?php echo $info['key']; ?>" value="percentages"> Use Percentages
		</label>
	</div>

<?php
	$info['tags'] = json_decode($info['tags']);
	if(!empty($info['tags'])) {
		$output = '
			<div id="custom_filters_'. $info['key'] .'" class="mb">
				<div>
					<strong>Filters</strong>
					<label for="filter_and">
						<input type="radio" id="filter_and" name="and_or" value="and" /> And
					</label> &nbsp; 
					<label for="filter_or">
						<input checked type="radio" id="filter_or" name="and_or" value="or" /> Or
					</label>
				</div>
		';
		$cnt = 0;
		foreach($info['tags'] as $v) {
			if(!trim($v)) {
				continue;
			}
			$slug = convert_to_alias($v);
			$output .= '
			<label for="filter_'. $cnt .'">
				<input type="checkbox" id="filter_'. $cnt .'" name="filters['. $slug .']" onclick="build_all_lists(\''. $info['key'] .'\')" value="'. $slug .'"> '. $v .'
			</label> &nbsp; 
			';
			$cnt += 1;
		}
		$output .= '</div>';
		echo $output;
	}
?>

	<div class="mb">
		<button onclick="build_all_lists('<?php echo $info['key']; ?>')">Update</button>
	</div>



		<div class='listcounter' id="listcounter" style='display:;'>
<?php
foreach($assets as $k => $list) {
	$l = $list['display_limit'];
	$r = $list['randomize'];
	$title = (!empty($list['list_label']) ? $list['list_label'] : $list['list_title']);
	$output = '
		<ol class="list_ordered" id="list_body_'. $k .'">
	';
	$i = 0;
	if($list['tables'] == "t") {
		$i = 1;
		$output = '
		<table cellspacing="0" cellpadding="0" class="tbl">
			<thead>
				<tr>
					<th>'. implode('</th><th>',explode("|",$list['assets'][0])) .'</th>
				</tr>
			</thead>
			<tbody id="list_body_'. $k .'">
		';
	}
	$cnt = 0;
	for($len=count($list['assets']); $i<$len; $i++) {
		$a = $list['assets'][$i];
		$t = json_decode($list['tags'][$i]);
		$p = $list['percentages'][$i];
		foreach($t as $tk => $tv) {
			$t[$tk] = convert_to_alias($tv);
		}
		// if(preg_match("/\[\d*[D|d]\d+\]/",$a,$matches)) {
		// 	$a = str_replace($matches[0],$matches[0].":".random($matches[0]),$a);
		// }

		$display = ($cnt < $list['display_limit'] ? '' : " style='display:none;'");
		if($list['tables'] == "t") {
			$output .= '<tr data-filters="'. implode(' ',$t) .'" data-perc="'. $p .'"'. $display .'>
				<td>'. implode("</td><td>",explode('|',$a)) .'</td>
			</tr>';
		} else {
			$output .= '<li data-filters="'. implode(' ',$t) .'" data-perc="'. $p .'"'. $display .'>
				'. $a .'
			</li>';
		}
		$cnt += 1;
	}

	if($list['tables'] == "t") {
		$output .= "</tbody></table>";
	} else {
		$output .= "</ol>";
	}
	echo $output;
	unset($output);
}
?>
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
	var is_table = <?php echo ($info['tables'] == 't' ? 'true' : 'false'); ?>;
	var list_keys = [<?php echo implode(',',array_keys($assets)); ?>];
	var original_rows = {};
	set_original_rows();




// ------------------------------------
// Testing Percentages
// ------------------------------------

// var choices = ['apple', 'banana', 'peach', 'pear', 'orange'];
// var weights = [ 1, 4,  1, 3, 1];

// var tmp = [], thresholds = [], weight;
// var total = 0;
// for(i = 0, len = choices.length; i<len; i++) {
// 	tmp[tmp.length] = [choices[i],weights[i]];
// }
// thresholds = tmp.sort(function(a, b) {
//     return a[1] - b[1];
// }).reverse();
// // console.log(thresholds)
// for(i in thresholds) {
// 	total += thresholds[i][1];
// 	thresholds[i][2] = total;
// }

// console.log(thresholds)
// console.log(total)
function random_by_weight(arr,total) {
	var used_keys = {};
	var used_keys_total = 0;
	var num_keys = Object.keys(arr).length;
	// var total = total_array(arr);
	var r;
	var new_arr = [];
	// console.log(num_keys)
	// console.log(total)
	// console.log(arr);
	// return;
	x = 0;
	while(used_keys_total < num_keys) {
		r = rand(1,total);
		// console.log(r)
		// console.log(used_keys)
		for(var i = 0; i < arr.length; i++) {
			// console.log("i: "+ typeof used_keys[arr[i][0]] + " -- "+ arr[i][1] +" ("+ r +")")
			if(typeof used_keys[arr[i][0]] == "undefined" && r <= arr[i][2]) {
				// console.log("???")
				used_keys[arr[i][0]] = 1;
				used_keys_total += 1;
				new_arr[new_arr.length] = arr[i];
				break;
			}
		}
	}
	
	return new_arr;
}

function build_keys(arr) {
	var output = "";
	for(k in arr) {
		output += arr[k][0]+","
	}
	return output;
}


// ------------------------------------
// Proofs
// ------------------------------------
percs = []

// for(i = 0; i<100000; i++) {
// 	var z = random_by_weight(thresholds,total);
// 	str = build_keys(z);
// 	if(typeof percs[str] == "undefined") {
// 		percs[str] = 0;
// 	}
// 	percs[str] += 1;
// }
// // console.log(choices);
// // console.log(weights);
// y = [];
// for(k in percs) {
// 	y[y.length] = [k,percs[k]];
// }
// y.sort(function(a, b) {
//     return a[1] - b[1];
// }).reverse();

// cnt = 0;
// for(k in y) {
// 	if(cnt++ > 10) {
// 		break;
// 	}
// 	console.log(k +": "+ y[k])
// }

if($id("randomize").checked) {
	build_all_lists();
	
}

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