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
add_js("list_functions.js",10);
add_js("markdown.min.js");

##################################################
#	Content
##################################################
?>

	<h2 class='collections'>Add Collection</h2>


	<div id="collection_buttons" class="w3-bar w3-black mt">
		<button type="button" class="w3-bar-item w3-button tablink w3-red" onclick="collection_open_tabs(this,'collection_default')">Default</button>
		<button type="button" class="w3-bar-item w3-button tablink" onclick="collection_open_tabs(this,'collection_md')">Markdown</button>
		<div class="float_right">
			<button class="w3-bar-item w3-button tablink" style="background: green;">Save List</button>
		</div>
	</div>
	<div id="collection_bodies" style='padding: 1em; border: 1px solid #ccc;'>

		<div id="collection_default" class="w3-container w3-border tabs">

	  	<?php echo dump_messages(); ?>
		<form id="addform" method="post" action="" onsubmit="">

			<label class="form_label" for="title">Collection Name <span>*</span></label>
			<div class="form_data">
				<input type="text" name="title" id="title" value="">
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

			<p>
				<input type="submit" value="Add Collection">
			</p>
	</div>
	<div id="collection_md" class="w3-container w3-border tabs" style="display: none">
		<form id="addform" method="post" action="" onsubmit="">
		<textarea name="markdown" id="markdown" class="float_left" style="width: 47%; height: 200px;" onkeyup="parse_markdown()">
** Markdown List Preview ** 

Some useful information about setting up previews here...
Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
</textarea>
		</form>
		<article id="preview" class="markdown-body float_left" style="width: 47%; border: 1px solid #ccc; margin-left: 1em; padding: 1em"></article>
		<div class="clear mt"></div>
	</div>




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

// function set_key(val) {
// 	$id('key'+modal_id).value = val;
// 	modal_clear();
// }

	function parse_markdown() {
		var markdown = document.getElementById('markdown').value;
		var preview = document.getElementById('preview');

		preview.innerHTML = micromarkdown.parse(markdown);
	}
	parse_markdown();

	function collection_open_tabs(evt, tabname) {
		var i, x, tablinks;

		x = document.getElementById('collection_bodies').getElementsByClassName("tabs");
		for (i = 0; i < x.length; i++) {
			x[i].style.display = "none";
		}
		tablinks = document.getElementById('collection_buttons').getElementsByClassName("tablink");
		for (i = 0; i < x.length; i++) {
			tablinks[i].className = tablinks[i].className.replace(" w3-red", ""); 
		}
		document.getElementById(tabname).style.display = "block";
		evt.className += " w3-red";
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