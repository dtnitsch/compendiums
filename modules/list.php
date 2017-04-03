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
            ,'list_id' => $info['id']
            ,'tables' => $info['tables']
            ,'assets' => []
            ,'tags' => []
            ,'percentages' => []
            ,'filters' => []
		];
	}
	$assets[$info['id']]['assets'][] = $row['title'];
	$assets[$info['id']]['tags'][] = $row['tags'];
	$assets[$info['id']]['filters'][] = $row['filters'];
	$assets[$info['id']]['percentages'][] = $row['percentage'];
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
			<label for="limit">
				Limit Display: <input type="input" name="limit" id="limit_<?php echo $info['key']; ?>" value="20" class='xs'> 
			</label>

			<label for="randomize">
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
				<input type="checkbox" id="filter_'. $cnt .'" name="filters['. $slug .']" onclick="build_all_lists(\''. $info['key'] .'\')" value="'. $slug .'"> '. $v .'
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
			<button type="button" onclick="build_all_lists('<?php echo $info['key']; ?>')">Update</button>
		</div>

	<form id="export_csv" method="post" action="/export/csv/" style='display: none;'>
		<!--label>&nbsp;</label><br>
		<input type="submit" value="Export CSV">
		<input type="hidden" name="query_csv" id="query_csv" value=""-->
	</form>

</div>





		<div class='listcounter mt' id="listcounter" style='display:;'>
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
		$t = json_decode($list['filters'][$i],true);
		$p = $list['percentages'][$i];
		foreach($t as $tk => $tv) {
			$t[$tk] = convert_to_alias($tv);
		}
		$t_list = (!empty($t) ? implode(' ',$t) : '');
		// if(preg_match("/\[\d*[D|d]\d+\]/",$a,$matches)) {
		// 	$a = str_replace($matches[0],$matches[0].":".random($matches[0]),$a);
		// }

		$display = ($cnt < $list['display_limit'] ? '' : " style='display:none;'");
		if($list['tables'] == "t") {
			$output .= '<tr data-filters="'. $t_list .'" data-perc="'. $p .'"'. $display .'>
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

<?php
	if(!empty($info['markdown'])) {
?>
	</div>
	<div id="md" class="tabs" style="display: none">
		<article id="markdown" class="markdown" style="padding: 1em">
			<?php echo $info['markdown']; ?>
			<div class="clear"></div>!!
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