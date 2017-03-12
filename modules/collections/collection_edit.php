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
$id = get_url_param("key");
if(empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/collections/');
}

##################################################
#	DB Queries
##################################################
$info = array();
$info = db_fetch("select * from public.collection where key='". $id ."'",'Getting Collection');
$q = "
	select
		collection_list_map.*
		,list.key
	from public.collection_list_map
	join public.list on
		list.id = collection_list_map.list_id
	where
		collection_list_map.collection_id = '". $info['id'] ."'
";
$lists = db_query($q,"Getting Lists");


##################################################
#	Pre-Content
##################################################
if(!empty($_POST)) {
	$info = $_POST;
}

// add_js('sortlist.new.js');

##################################################
#	Content
##################################################
?>

	<h2 class='collections'>Edit Collection: <?php echo $info['title']; ?></h2>
  
  	<?php echo dump_messages(); ?>
	<form id="addform" method="post" action="" onsubmit="">

		<label class="form_label" for="title">Collection Name <span>*</span></label>
		<div class="form_data">
			<input type="text" name="title" id="title" value="<?php echo $info['title']; ?>">
		</div>

		<table id="lists">
			<thead>
				<tr>
					<th></th>
					<th>List Key</th>
					<th>Label</th>
					<th>Randomize</th>
					<th>Display Limit</th>
					<th>Search</th>
				</tr>
			</thead>
			<tbody id="list_body">
<?php
	$output = "";
	$list_count = 0;
	while($row = db_fetch_row($lists)) {
		$list_count += 1;
		$randomize = ($row['randomize'] == 't' ? " checked ": '');
		$output .= '
		<tr>
			<td>'. $list_count .'</td>
			<td>
				<input type="input" id="key'. $list_count .'" name="list_keys['. $list_count .']" placeholder="List Key" value="'. $row['key'] .'">
			</td>
			<td>
				<input type="input" id="label'. $list_count .'" name="list_labels['. $list_count .']" placeholder="List Label" value="'. $row['label'] .'">
			</td>
			<td>
				<label for="randomize'. $list_count .'">
					<input '. $randomize .' type="checkbox" name="randomize['. $list_count .']" id="randomize'. $list_count .'" value="1"> Randomize
				</label>
			</td>
			<td>
				<input type="input" name="display_limit['. $list_count .']" value="'. $row['display_limit'] .'" style="width: 40px;">
			</td>
			<td>
				<input type="button" value="Search for List" onclick="search_for_list('. $list_count .')">
			</td>
		';
	}
	echo $output;
	$output = "";
?>
			</tbody>
		</table>

		<p>
			<input type="button" value="Add Lists" onclick="add_list()">
		</p>

		<p>
			<input type="submit" value="Edit Collection">
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

var list_count = <?php echo $list_count; ?>;
function add_list() {
	var list_body = $id('list_body');
	var output = '';
	var cnt = 5 - (list_count % 5);
	var tr;

	while(cnt--) {
		list_count += 1;
		tr = document.createElement("tr");
		output = `
			<td>`+ list_count +`</td>
			<td>
				<input type="input" id="key`+ list_count +`" name="list_keys[`+ list_count +`]" placeholder="List Key">
			</td>
			<td>
				<input type="input" id="label`+ list_count +`" name="list_labels[`+ list_count +`]" placeholder="List Label">
			</td>
			<td>
				<label for="randomize`+ list_count +`">
					<input checked type="checkbox" name="randomize[`+ list_count +`]" id="randomize`+ list_count +`" value="1"> Randomize
				</label>
			</td>
			<td>
				<input type="input" name="display_limit[`+ list_count +`]" value="0" style="width: 40px;">
			</td>
			<td>
				<input type="button" value="Search for List" onclick="search_for_list(`+ list_count +`)">
			</td>
		`;
		tr.innerHTML = output;
		lists.appendChild(tr);
	}
}

// add_list();

var modal_id = 0;
function search_for_list(id) {
	modal_id = id;
	$id("simple_modal").style.display = "block";
	$id('modal_search').focus();
}

function set_key(val) {
	$id('key'+modal_id).value = val;
	modal_clear();
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