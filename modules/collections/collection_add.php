<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

// if(!logged_in()) { safe_redirect("/login/"); }
// if(!has_access("collection_add")) { back_redirect(); }

post_queue($module_name,'modules/collections/post_files/');

##################################################
#	Validation
##################################################

##################################################
#	DB Queries
##################################################

##################################################
#	Pre-Content
##################################################
$info = (!empty($_POST) ? $_POST : array());

// add_js('sortlist.new.js');
add_js("compendium.js",10);
add_js("markdown.min.js");

##################################################
#	Content
##################################################
?>
	<form id="addform" method="post" action="" onsubmit="return validate_list();">

<div class="subheader">
	<div class="float_right">
		<input type="submit" value="Add Collection">
	</div>

	<div class="title">Add Collection</div>
</div>
  		<a href="#messages"></a>
  		<div id="messages">
			<?php echo dump_messages(); ?>
		</div>

				<label class="form_label" for="title">Collection Name <span>*</span></label>
				<div class="form_data">
					<input type="text" name="title" id="title" class="xl" value="<?php echo $info['title'] ?? ""; ?>">
				</div>

				<table cellspacing="0" cellpadding="0" id="lists" class="tbl">
					<thead>
						<tr>
							<th></th>
							<th>Label</th>
							<th>List Key</th>
							<th style='width: 1px; padding-right: 10px;'>Randomize</th>
							<th style='width: 1px; padding-right: 10px;'>Limit</th>
							<th style='width: 1px;'>Options</th>
						</tr>
					</thead>
					<tbody id="list_body">

					</tbody>
				</table>

				<p>
					<input type="button" value="Add Lists" onclick="search_for_list()">
				</p>

			<div class='hr' style='margin: 20px; border-bottom: 1px solid #ccc;'></div>
			
					<div id="md">
			
						<div class="float_right small">
							<input type="button" value="Generate Example Markdown" onclick="example_markdown()">
							<input type="button" value="Show Markdown preview" onclick="show_markdown_preview()">
						</div>
			
						<label class="form_label" for="markdown">Markdown Description</label>
						<textarea name="markdown" id="markdown" class="" style="width: 100%; height: 100px;"></textarea>
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
		</div>

			
		<div class="clear mt"></div>
		<input type="submit" value="Add Collection">
	</form>


<?php echo run_module("modal_list"); ?>
<?php echo run_module("list_simple"); ?>

<?php
##################################################
#	Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">

var list_count = 0;
var list_simple_template = "";
var current_asset = [];
var add_to_list = false;

function search_for_list(list_num) {
	add_to_list = list_num || false;
	reset_modal();
	$id("simple_modal").style.display = "block";
	$id('modal_search').focus();
}

function add_list(id) {

	if(add_to_list) {
		chain_list(id,add_to_list);
		return;
	}

	var limit = parseInt($id('limit_'+ current_asset.list_key) ? $id('limit_'+ current_asset.list_key).value : current_asset.display_limit);
    var randomize = ($id('randomize_'+ current_asset.list_key) ? $id('randomize_'+ current_asset.list_key).checked : current_asset.randomize);
	var checked = (randomize ? ' checked' : '');

	list_count += 1;
	tr = document.createElement("tr");
	tr.id = "list_count_"+ list_count;

	output = `
		<td>`+ list_count +`</td>
		<td>
			<input type="input" id="label`+ list_count +`" name="list_labels[`+ list_count +`]" placeholder="List Label" value="`+ current_asset.list_title +`" style="width: 90%;">
		</td>
		<td>
			<input type="input" id="key`+ list_count +`" name="list_keys[`+ list_count +`]" placeholder="List Key" value="`+ current_asset.list_key +`" style="width: 90%;">
		</td>
		<td>
			<label for="randomize`+ list_count +`">
				<input`+ checked +` type="checkbox" name="randomize[`+ list_count +`]" id="randomize`+ list_count +`" value="1"> Randomize
			</label>
		</td>
		<td>
			<input type="input" name="display_limit[`+ list_count +`]" value="`+ limit +`" style="width: 32px;">
		</td>
		<td>
			<input type="button" value=" + " onclick="search_for_list(`+ list_count +`)">
			<input type="button" value=" - " onclick="remove_list_row(`+ list_count +`)">
		</td>
	`;
	tr.innerHTML = output;
	$id('lists').appendChild(tr);

	modal_clear(id);
}

function add_list_row(num) {
	console.log("add_list_row(num): "+ num)
}
function remove_list_row(num) {
	$id('list_count_'+ num).outerHTML = "";
}

function chain_list(id,num) {
	$id('label'+ num).value += ", "+ current_asset.list_title;
	$id('key'+ num).value += ", "+ current_asset.list_key;
	modal_clear(id);
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
	return validate({'title':'Collection Name'});
}
</script>
<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional ƒHP Functions
##################################################

##################################################
#	EOF
##################################################
?>