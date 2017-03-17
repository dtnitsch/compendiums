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

##################################################
#	Content
##################################################
?>

	<h2 class='collections'>Add Collection</h2>
  
  	<?php echo dump_messages(); ?>
	<form id="addform" method="post" action="" onsubmit="">

		<label class="form_label" for="title">Collection Name <span>*</span></label>
		<div class="form_data">
			<input type="text" name="title" id="title" value="">
		</div>

		<table cellspacing="0" cellpadding="0" id="lists" class="list_table">
			<thead>
				<tr>
					<th></th>
					<th>Label</th>
					<th>List Key</th>
					<th>Randomize</th>
					<th>Display Limit</th>
					<th>Search</th>
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
function add_list(info,limit,randomize) {
	var list_body = $id('list_body');
	var output = '';
	var tr;
	var checked = (randomize ? " checked" : "");

	list_count += 1;
	tr = document.createElement("tr");


	output = `
		<td>`+ list_count +`</td>
		<td>
			<input type="input" id="label`+ list_count +`" name="list_labels[`+ list_count +`]" placeholder="List Label" value="`+ info.title +`">
		</td>
		<td>
			<input type="input" id="key`+ list_count +`" name="list_keys[`+ list_count +`]" placeholder="List Key" value="`+ info.key +`">
		</td>
			<td>
				<label for="randomize`+ list_count +`">
					<input`+ checked +` type="checkbox" name="randomize[`+ list_count +`]" id="randomize`+ list_count +`" value="1"> Randomize
				</label>
			</td>
			<td>
				<input type="input" name="display_limit[`+ list_count +`]" value="`+ limit +`" style="width: 40px;">
			</td>
		<td>-</td>
	`;
	tr.innerHTML = output;
	lists.appendChild(tr);
}

var modal_id = 0;
function search_for_list(id) {
	modal_id = id;
	$id("simple_modal").style.display = "block";
	$id('modal_search').focus();
}

// function set_key(val) {
// 	$id('key'+modal_id).value = val;
// 	modal_clear();
// }
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