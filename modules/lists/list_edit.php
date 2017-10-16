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
add_js("lists.js");



$info = array();
if(!empty($_POST)) {
	$info = $_POST;
	$asset_body = $_POST['inputs'];

} else {
	$info = db_fetch("select * from public.list where key='". $id ."'",'Getting List');
	$info['filter_labels'] = json_decode($info['filter_labels'],true);
	$info['filter_orders'] = json_decode($info['filter_orders'],true);

	$q = "
		select
			public.asset.*
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
		$filters = [];
		foreach($tmp as $v) {
			$filters[] = $info['filter_labels'][$v];
		}
		$asset_body .= $row['title'] .";". implode(',',$filters) ."\n";
	}
}

##################################################
#	Content
##################################################
?>

<form id="addform" method="post" action="" onsubmit="return validate_list();">

<div class="subheader">
	<div class="float_right">
		<input type="submit" value="Edit List">
	</div>

	<div class="title">Edit List: <?php echo $info['title']; ?></div>
</div>

<a href="#messages"></a>
<div id="messages"></div>

<div id="inputs_box">

	<label class="form_label" for="title">List Name <span>*</span></label>
	<div class="form_data">
		<input type="text" name="title" id="title" class="xl" value="<?php echo $info['title'] ?? ""; ?>">
	</div>

	<div class="float_right small">
		<input type="button" value="Simple List Example" onclick="show_simple_example()">
		<input type="button" value="Table List Example" onclick="show_table_example()" style="margin-top:2px">
		<input type="button" value="Show Preview" onclick="show_preview()">
	</div>

			<label class="form_label" for="title">Inputs</label>
			<div class="form_data">
				<!--textarea name="inputs" id="inputs" onchange="example(event,true)" onkeyup="example(event)" style="width: 90%; height: 250px;"></textarea-->
				<textarea name="inputs" id="inputs" style="width: 100%; height: 150px;"><?php echo $asset_body ?? ""; ?></textarea>
				<div class="small">
					* ";" Semicolon seperator for filters<br>
					* "|" Pipe between values for "Tables"


				</div>
			</div>
		</div>


		<div id="preview_box" style="display: none;">
			<div class="float_right">
				<input type="button" value="Show Inputs" onclick="show_inputs()">
			</div>
			<div>
				<b>Preview</b>: <span id="preview_title"></span>
			</div>

			<table cellspacing="0" id="filters_table" class="tbl" style='margin-bottom: 1em;'>
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th>Label</th>
						<th>Slug</th>
						<th>Order</th>
					</tr>
				</thead>
				<tbody id="filters_table_tbody"></tbody>
			</table>
			<div id="example"></div>
		</div>

		<div class='hr' style='margin: 20px; border-bottom: 1px solid #ccc;'></div>

		<div id="md">

			<div class="float_right small">
				<input type="button" value="Generate Example Markdown" onclick="example_markdown()">
				<input type="button" value="Show Markdown preview" onclick="show_markdown_preview()">
			</div>

			<label class="form_label" for="markdown">Markdown Description</label>
			<textarea name="markdown" id="markdown" class="" style="width: 100%; height: 100px;"><?php echo $info['description'] ?? ""; ?></textarea>
			<div class="small">

			</div>
		</div>

		<div id="md_preview" style="display: none;">

			<div class="float_right">
				<input type="button" value="Edit Markdown" onclick="show_markdown()">
			</div>
			<div class="clear"></div>
			<article id="preview" class="" style="border: 1px solid #333; padding: 10px"></article>

		</div>



		<div class="mt">
			<input type="submit" value="Add List">
		</div>
	</div>


<div class="clear mt"></div>
<!--input type="submit" value="Save List"-->
</form>

<?php

	site_wide_notes('ajax',$GLOBALS['project_info']['path_data']['id'],$id);


##################################################
#	Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">
function show_preview() {
	preview_list("inputs","example",'filters_table');
	title = $id('title').value || "<em>None Given</em>";
	$id('preview_title').innerHTML = title;
	$id('inputs_box').style.display = "none";
	$id('preview_box').style.display = "";

}
function show_inputs() {
	$id('preview_box').style.display = "none";
	$id('inputs_box').style.display = "";
}

function show_markdown_preview() {
	parse_markdown();
	$id('md').style.display = "none";
	$id('md_preview').style.display = "";
}
function show_markdown() {
	$id('md_preview').style.display = "none";
	$id('md').style.display = "";
}

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
}

function validate_list() {
	preview_list("inputs","example",'filters_table');
	return validate({'title':'List Name','inputs':'Inputs'});
}
function show_simple_example() {
	$id('inputs').value = `Apple;Fruit
Banana;Fruit
Tomato;Fruit,Vegetable
Potato;Vegetable
Pineapple;Fruit
Carrot;Vegetable
Cucumber;Vegetable
Cheese;Dairy
Milk;Dairy`;
}
function show_table_example() {
	$id('inputs').value = `Name|Cost|Color
Apple|$1.29|Red, Green, or Yellow;Fruit,Red,Green,Yellow
Banana|$0.85|Yellow;Fruit,Yellow
Tomato|$0.44|Red;Fruit,Vegetable,Red
Potato|$0.16|Brown;Vegetable
Pineapple|$2.02|Brown with Green Leaves;Fruit, Brown, Green
Carrot|$0.02|Orange;Vegetable, Orange
Cucumber|$0.09|Green;Vegetable, Green
Cheese|$3.41|Cream Color;Dairy, Cream, White, Yellow
Milk|$2.95|White;Dairy,White`;
}

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
