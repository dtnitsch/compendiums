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
		,list_asset_map.tags
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

$assets = array();
while($row = db_fetch_row($assets_res)) {
	$id = $row['list_id'] ."-". $row['collection_list_map_id'];
	if($row['connected']) {
		$id = 'multi_'. $row['connected'];
	}
	if(empty($assets[$id])) {
		$assets[$id] = [
			"list_title" => $row['list_title']
			,"list_label" => $row['label']
			,"randomize" => ($row['randomize'] == "t" ? 1 : 0)
			,"display_limit" => $row['display_limit']
			,"list_id" => $row['list_id']
			,"tables" => $row['tables']
			,"connected" => $row['connected']
			,"assets" => []
			,"tags" => []
		];
	}
	$assets[$id]['assets'][$row['list_id']][] = $row['asset'];
	$assets[$id]['tags'][$row['list_id']][] = $row['tags'];
}

##################################################
#   Pre-Content
##################################################
// add_css('pagination.css');
// add_js('sortlist.new.js');
add_js("list_functions.js",10);
add_js("markdown.min.js");
$split_on_count = 3;

$csv_url = $_SERVER['REQUEST_SCHEME'] ."://api.". $_SERVER['SERVER_NAME'] .'/collections/'. $key ."/";
$raw_url = $_SERVER['REQUEST_SCHEME'] ."://api.". $_SERVER['SERVER_NAME'] .'/collections/raw/'. $key ."/";

##################################################
#   Content
##################################################
?>
<div class="float_right">
	<input type="button" onclick="window.location.href='<?php echo $csv_url; ?>'" value="Export to CSV">
	<input type="button" onclick="window.location.href='<?php echo $raw_url; ?>'" value="Export Raw">
</div>
<h2 class='lists'>Collection: <?php echo $info['title']; ?></h2>

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


	<!--div class="mb">
		<label for="limit">
			Expand at: <input type="input" name="split_on_count" id="split_on_count" value="<?php echo $split_on_count; ?>"> 
		</label>
	</div-->

		<div class="mb">
			<input type="button" value="Randomize List(s)" onclick="generate_all_lists()">	
		</div>

		<div class='listcounter' id="listcounter" style=''>
<?php
foreach($assets as $k => $list) {
	$l = $list['display_limit'];
	$r = $list['randomize'];
	$title = (!empty($list['list_label']) ? $list['list_label'] : $list['list_title']);

	$output = '<div class="mt"><strong>'. $title .'</strong></div>';

	if($list['tables'] == "t") {
		$output .= '
		<table cellspacing="0" cellpadding="0" class="tbl">
			<thead>
				<tr>
					<th>'. implode('</th><th>',explode("|",$list['assets'][$list['list_id']][0])) .'</th>
				</tr>
			</thead>
			<tbody id="list_body_'. $k .'" data-limit="'. $l .'" data-randomize="'. $r .'">
			</tbody>
		</table>
		';
	} else {
		$output .= '
			<br><ol class="list_ordered" id="list_body_'. $k .'" data-limit="'. $l .'" data-randomize="'. $r .'"></ol>
		';
	}
	echo $output;
	unset($output);
}
?>
		<div class="mb">
			<!--button onclick="build_all_lists()">Randomize List</button-->
			<input type="button" value="Randomize List(s)" onclick="generate_all_lists()">	

		</div>
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
	var original_rows = {};
	var list_keys = ['<?php echo implode("','",array_keys($assets)); ?>'];
	var assets = {};
	var tags = {};
<?php
	$output = '';
	foreach($assets as $k => $v) {
		$cnt = 0;
		echo "\nassets['". $k ."'] = {}; ";
		foreach($v['assets'] as $k2 => $v2) {

			$output = "\nassets['". addslashes($k) ."']['". addslashes($k2) ."'] = [";
			foreach($v2 as $v3) {
				$output .= "'". addslashes($v3) ."',";
			}
			echo substr($output,0,-1) .'];';
		}
		echo "\ntags['". $k ."'] = {}; ";
		foreach($v['tags'] as $k2 => $v2) {

			$output = "\ntags['". $k ."']['". $k2 ."'] = [";
			foreach($v2 as $v3) {
				$output .= "'". $v3 ."',";
			}
			echo substr($output,0,-1) .'];';
		}
	}
?>

	set_original_rows();
	generate_all_lists();
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