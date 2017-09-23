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
add_js("lists.js",10);
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
							<th>Randomize</th>
							<th>Display Limit</th>
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

<?php
##################################################
#	Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">

var list_count = 0;
function add_list(info,limit,randomize,multi) {
	var list_body = $id('list_body');
	var output = '';
	var tr, i, len;
	var checked = (randomize ? " checked" : "");
	var title = '';
	var key = '';
	var is_multi = 0;

	list_count += 1;
	tr = document.createElement("tr");

	if(typeof info.title == "undefined" && info.length) {
		is_multi = 1;
		for(i = 0,len=info.length; i<len; i++) {
			title += info[i].returned_info.title +",";
			key += info[i].returned_info.key +",";
		}
		title = title.substring(0,title.length - 1);
		key = key.substring(0,key.length - 1);
	} else {
		title = info.title;
		key = info.key;
	}


	output = `
		<td>`+ list_count +`</td>
		<td>
			<input type="input" id="label`+ list_count +`" name="list_labels[`+ list_count +`]" placeholder="List Label" value="`+ title +`">
		</td>
		<td>
			<input type="input" id="key`+ list_count +`" name="list_keys[`+ list_count +`]" placeholder="List Key" value="`+ key +`">
		</td>
		<td>
			<label for="randomize`+ list_count +`">
				<input`+ checked +` type="checkbox" name="randomize[`+ list_count +`]" id="randomize`+ list_count +`" value="1"> Randomize
			</label>
			<input type="hidden" name="is_multi" value="`+ is_multi +`" />
		</td>
		<td>
			<input type="input" name="display_limit[`+ list_count +`]" value="`+ limit +`" style="width: 40px;">
		</td>
	`;
	tr.innerHTML = output;
	lists.appendChild(tr);
}

// var modal_id = 0;
// var multi = 0;
function search_for_list() {
	// multi = multi || 0;
	// console.log("Multi: "+ multi)
	// modal_id = id;
	if(typeof reset_modal == "function") {
		reset_modal();
	}
	$id("simple_modal").style.display = "block";
	$id('modal_search').focus();
}
modal_init("simple_modal");

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