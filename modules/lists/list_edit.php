<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger

// if(!logged_in()) { safe_redirect("/login/"); }
// if(!has_access("list_edit")) { back_redirect(); }

post_queue($module_name,'modules/lists/post_files/');

##################################################
#	Validation
##################################################
$id = get_url_param("key");
if(empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/lists/');
}

##################################################
#	DB Queries
##################################################

##################################################
#	Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/lists/");

add_js("markdown.min.js");
add_js("list_functions.js");



$info = array();
if(!empty($_POST)) {
	$info = $_POST;
	$asset_body = $_POST['inputs'];

} else {
	$info = db_fetch("select * from public.list where key='". $id ."'",'Getting List');
	$info['filter_labels'] = json_decode($info['filter_labels'],true);
	$info['filter_orders'] = json_decode($info['filter_orders'],true);

	$q = "
		select markdown
		from public.list_markdown
		where
			active
			and list_id = '". $info['id'] ."'
	";
	$tmp = db_fetch($q,'Getting Markdown');
	$info['markdown'] = $tmp['markdown'];

	$q = "
		select
			public.asset.*
			,list_asset_map.tags
			,list_asset_map.filters
		from public.asset
		join public.list_asset_map on 
			list_asset_map.asset_id = asset.id
			and list_asset_map.list_id = '". $info['id'] ."'
		order by
			asset.id
	";
	$assets = db_query($q,"Getting assets");

	$asset_body = "";
	$filters = [];
	while($row = db_fetch_row($assets)) {
		$tmp = json_decode($row['filters'],true);
		$asset_body .= $row['title'] .";". implode(',',$tmp) ."\n";
	}
}

##################################################
#	Content
##################################################
?>
	<form id="addform" method="post" action="" onsubmit="return validate_list();">
		<h2 class='lists'>Edit List: <?php echo $info['title']; ?></h2>
	  
	 	<div id="messages">
			<?php echo dump_messages(); ?>
		</div>
		
		<div id="list_buttons" class="tabbar">
			<button type="button" class="tablink active" onclick="open_tabs(this,'default','list')">Default</button>
			<button type="button" class="tablink" onclick="open_tabs(this,'md','list')">Information</button>
			<div class="float_right">
				<button class="tablink save">Save List</button>
			</div>
		</div>
		<div id="list_bodies" class="tabbody">
			<div id="default" class="tabs">

				<div class="float_left" style="width: 59%;">

					<label class="form_label" for="title">List Name <span>*</span></label>
					<div class="form_data">
						<input type="text" name="title" id="title" class="xl" value="<?php echo $info['title'] ?? ""; ?>">
					</div>


					<label class="form_label" for="title">Inputs</label>
					<div class="form_data">
						<textarea name="inputs" id="inputs" onchange="example(event,true)" onkeyup="example(event)" style="width: 90%; height: 250px;"><?php echo $asset_body ?? ""; ?></textarea>
						<div style="font-size: 80%;">*Notes: Semicolon ";" Deliminated List - Name; Optional Percentage; Optional Filters</div>
					</div>

				</div>
				<div class="float_left" style="width: 39% padding: 1em;">
<?php
if(!empty($info['filter_orders']) && $info['filter_orders']) {
?>
					<table cellspacing="0" id="filters_table" class="tbl">
						<thead>
							<tr>
								<th>&nbsp;</th>
								<th>Label</th>
								<th>Slug</th>
								<th>Order</th>
							</tr>
						</thead>
						<tbody id="filters_table_tbody">
<?php
	asort($info['filter_orders']);
	$output = '';
	foreach($info['filter_orders'] as $slug => $order) {
		$output .= '
						<tr data-key="'. $slug .'">
							<td>
								<label for="filter_0">
								<input type="checkbox" id="filter_0" name="filters['. $slug .']" onclick="filter_list(\''. $slug .'\')" value="'. $slug .'">
								</label>
							</td>
							<td><input type="text" name="filter_labels['. $slug .']" value="'. $info['filter_labels'][$slug] .'"></td>
							<td>'. $slug .'</td>
							<td><input type="text" name="filter_orders['. $slug .']" value="'. $order .'" class="xs"></td>
						</tr>
	';
	}
	echo $output;
?>
						</tbody>
					</table>
<?php
}
?>
					<div id="example"></div>
				</div>
			</div>
			<div id="md" class="tabs" style="display: none">

				<textarea name="markdown" id="markdown" class="markdown" onkeyup="parse_markdown()"><?php echo $info['markdown'] ?? ''; ?></textarea>
				<article id="preview" class="markdown_body"></article>
				
				<div class="clear mt"></div>
				<button type="button" onclick="example_markdown()">Generate Example Markdown</button>

			</div>

			<div class="clear"></div>
		</div>

	<div class="clear mt"></div>
	<input type="submit" value="Save List">
</form>


<?php

	site_wide_notes('ajax',$GLOBALS['project_info']['path_data']['id'],$id);


##################################################
#	Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">

	function example_markdown() {
		$id('markdown').value = `#Header

Paragraphs are separated by a blank line.

2nd paragraph. *Italic*, **bold**, and \`monospace\`. Itemized lists
look like:

* this one
* that one
* the other one

##Header 2

Here's a numbered list:

1. first item
2. second item
3. third item

`;
		parse_markdown();
	}

	function validate_list() {
		return validate({'title':'List Name','inputs':'Inputs'});
	}

	example(event,true);

</script>

<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################
