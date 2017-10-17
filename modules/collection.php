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

library("api.php");
$info = json_decode(call_api_function("get_collection",$key),1);

$q = "select * from public.collection where key='". db_prep_sql($key) ."'";
$info = db_fetch($q,"Getting collection information");

$tmp = db_fetch("select markdown from public.collection_markdown where collection_id = '". $info['id'] ."'","Getting Markdown");
$info['markdown'] = $tmp['markdown'] ?? '';


$q = "
	select
		public.asset.id
		,public.asset.title as asset
		,collection_list_map.id as collection_list_map_id
		,collection_list_map.connected
		,collection_list_map.is_multi
		,collection_list_map.list_id
		,collection_list_map.collection_id
		,collection_list_map.label
		,collection_list_map.randomize
		,collection_list_map.display_limit
		,list_asset_map.filters
		,list.title as list_title
		,list.tables as tables
	from public.asset
	join public.list_asset_map on 
		list_asset_map.asset_id = asset.id
	join public.collection_list_map on
		collection_list_map.list_id = list_asset_map.list_id
		and collection_list_map.collection_id = '". $info['id'] ."'
	join public.list on
		list.id = collection_list_map.list_id
	order by
		collection_list_map.id
		,collection_list_map.connected
		,asset.id
";

$assets_res = db_query($q,"Getting collection assets");

$assets[] = [];
while($row = db_fetch_row($assets_res)) {
	$id = $row['list_id'] ."-". $row['collection_list_map_id'];
	if($row['connected']) {
		$id = 'multi_'. $row['connected'];
	}
	if(empty($assets[$id]['list_title'])) {
		$assets[$id] = [
			"list_title" => $row['list_title']
			,"list_label" => $row['label']
			,"randomize" => ($row['randomize'] == "t" ? 1 : 0)
			,"display_limit" => $row['display_limit']
			,"list_id" => $row['list_id']
			,"tables" => $row['tables']
			,"connected" => $row['connected']
			,'filter_count' => 0
            ,'assets' => []
            // ,'tags' => []
            // ,'percentages' => []
            ,'filters' => []
		];
	}
	$assets[$id]['assets'][$row['list_id']][] = $row['asset'];
	if(!empty($row['filters'])) {
		$assets[$id]['filter_count'] += 1;
	}
	$assets[$id]['filters'][$row['list_id']][] = $row['filters'];
	

	// $assets[$info['id']]['percentages'][] = $row['percentage'];
}
// echo "<pre>";
// print_r($assets);
// echo "<pre>";

##################################################
#   Pre-Content
##################################################
// add_css('pagination.css');
// add_js('sortlist.new.js');
add_js("lists.js",10);
add_js("markdown.min.js");
$split_on_count = 3;

$csv_url = $_SERVER['REQUEST_SCHEME'] ."://api.". $_SERVER['SERVER_NAME'] .'/collections/'. $key ."/";
$raw_url = $_SERVER['REQUEST_SCHEME'] ."://api.". $_SERVER['SERVER_NAME'] .'/collections/raw/'. $key ."/";

##################################################
#   Content
##################################################
?>
<div class="subheader">
<div class="float_right">
	<input type="button" onclick="window.location.href='<?php echo $csv_url; ?>'" value="Export to CSV">
	<input type="button" onclick="window.location.href='<?php echo $raw_url; ?>'" value="Export Raw">
</div>

<div class="title">Collection: <?php echo $info['title']; ?></div>
</div>


	<div class="mb">
		<input type="button" value="Update List(s)" onclick="build_all_display({'show_labels':true})">	
	</div>

	<div class='listcounter' id="listcounter" style=''></div>

	<div class="mt">
		<input type="button" value="Update List(s)" onclick="build_all_display({'show_labels':true})">	

	</div>

<div class="filters mt" onclick="show_hide('markdown_details')">
	Collection Details <span class="small">(<span class="fakeref">show/hide</span>)</span>
</div>
<div class="filter_details" id="markdown_details" style="display: none;">

	<div id="markdown" class="markdown" style="padding: 1em; display: block;">
		<div class="clear"></div>
	</div>		

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
	var original_rows = {};
	var list_keys = ['<?php echo implode("','",array_keys($assets)); ?>'];
	var assets = {};
	// var tags = {};

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
				$output2 .= ",['". addslashes($v3) ."','". $v['filters'][$k2][$k3] ."']";
			}
			$output .= ",'x". $k2 ."':[". substr($output2,1) ."]";
		}

		echo "\nassets['". $k ."'] = {
			'tables': '". ($v['tables'] == 't' ? true : false) ."'
			,'list_label': '". htmlentities($v['list_label']) ."'
			,'filter_count': ". $v['filter_count'] ."
			,'display_limit': ". $v['display_limit'] ."
			,'randomize': ". $v['randomize'] ."
			,'connected': ". $v['connected'] ."
			,'assets': {". substr($output,1) ."}	
		};";

	}

?>

	// set_original_rows();
	build_all_display({'show_labels':true});
	parse_markdown_html('markdown',<?php echo json_encode($info['markdown']); ?>);
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