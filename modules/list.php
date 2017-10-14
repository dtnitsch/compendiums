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

$q = "
	select
		public.asset.*
--		,list_asset_map.tags
--		,list_asset_map.tags
--		,list_asset_map.percentage
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
add_js("lists.js",10);
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
	$assets[$info['id']]['assets'][$info['id']][] = $row['title'];
	// $assets[$info['id']]['tags'][] = $row['tags'];
	if(!empty($row['filters'])) {
		$assets[$info['id']]['filter_count'] += 1;
	}
	$assets[$info['id']]['filters'][$info['id']][] = $row['filters'];
	

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
	
</div>

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
				Limit Display: <input type="input" name="limit" id="limit_<?php echo $info['key']; ?>" value="20" class='xs'> 
			</label>

			<label for="randomize_<?php echo $info['key']; ?>">
				<input checked type="checkbox" name="options" id="randomize_<?php echo $info['key']; ?>" value="randomize"> Randomize
			</label>

			<div id="filters_dynamic" class="mtb">
<?php
	$info['tags'] = json_decode($info['tags']);
	if(!empty($info['tags'])) {
		$output = '<div class="mb">';
		$cnt = 0;
		foreach($info['tags'] as $v) {
			$output .= '
			<label for="filter_'. $cnt .'">
				<input type="checkbox" id="filter_'. $cnt .'" name="filters['. $v .']" onclick="filter_list(\'filters_dynamic\',\'listcounter\')" value="'. $v .'"> '. $v .'
			</label> &nbsp; 
			';
			$cnt += 1;
		}
		$output .= '</div>';
		echo $output;
	}
?>
	</div>
	    </form>


<?php
	if(!empty($info['filter_orders'])) {
		asort($info['filter_orders']);	
	}
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
				<input type="checkbox" id="filter_'. $cnt .'" name="filters['. $slug .']" onclick="build_all_display()" value="'. $slug .'"> '. $v .'
			</label> 
			';
			$cnt += 1;
		}
		$output .= '</div>';
		echo $output;
	}
?>
	<div class="clear"></div>

</div>

<div class="mtb">
	<input type="button" value="Update List" onclick="build_all_display()">
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
	var is_table = <?php echo ($info['tables'] == 't' ? 'true' : 'false'); ?>;
	var list_keys = [<?php echo implode(',',array_keys($assets)); ?>];

	var assets = {};
<?php
	foreach($assets as $k => $v) {
		$output = '';
		if(empty($v['assets'])) {
			continue;
		}
		foreach($v['assets'] as $k2 => $v2) {
			$output2 = '';
			foreach($v2 as $k3 => $v3) {
				$output2 .= ",['". addslashes($v3) ."',". $v['filters'][$k2][$k3] ."]\n";
			}
			$output .= ",'". $k2 ."':[". substr($output2,1) ."]";
		}

		echo "\nassets['". $info['key'] ."'] = {
			'tables': '". ($v['tables'] == 't' ? true : false) ."'
			,'filter_count': ". $v['filter_count'] ."
			,'assets': {". substr($output,1) ."}	
		};";

	}
?>

	// build_display('<?php echo $info['key']; ?>');
	build_all_display();

	parse_markdown_html('markdown',<?php echo json_encode($info['description']); ?>);
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