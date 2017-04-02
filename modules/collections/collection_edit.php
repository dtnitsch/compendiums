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
		,q.key_agg
	from public.collection_list_map
	join public.list on
		list.id = collection_list_map.list_id
	join (
		select string_agg(key,',') as key_agg, connected
		from public.collection_list_map
		join public.list on
			list.id = collection_list_map.list_id
		where
			collection_list_map.collection_id = '". $info['id'] ."'
		group by
			connected
	) as q on
		q.connected = collection_list_map.connected

	where
		collection_list_map.collection_id = '". $info['id'] ."'
	order by
		collection_list_map.connected
		,collection_list_map.id
";
$lists = db_query($q,"Getting Lists");


##################################################
#	Pre-Content
##################################################
if(!empty($_POST)) {
	$info = $_POST;
}

// add_js('sortlist.new.js');
add_js("list_functions.js",10);
add_js("markdown.min.js");

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
				</tr>
			</thead>
			<tbody id="list_body">
<?php
	$output = "";
	$list_count = 0;
	$connected = '';
	while($row = db_fetch_row($lists)) {
		if($connected == $row['connected']) {
			continue;
		}
		$connected = $row['connected'];
		$list_count += 1;
		$randomize = ($row['randomize'] == 't' ? " checked ": '');
		$output .= '
		<tr>
			<td>'. $list_count .'</td>
			<td>
				<input type="input" id="key'. $list_count .'" name="list_keys['. $list_count .']" placeholder="List Key" value="'. $row['key_agg'] .'">
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

		';
	}
	echo $output;
	$output = "";
?>
			</tbody>
		</table>

		<p>
			<input type="button" value="Add Lists" onclick="search_for_list()">
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
	// parse_markdown();

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