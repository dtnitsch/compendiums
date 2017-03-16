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

$q = "
	select
		public.asset.id
		,public.asset.title as asset
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
		collection_list_map.list_id
		,asset.id
";
$assets_res = db_query($q,"Getting collection assets");

$assets = array();
$list_id = 0;
while($row = db_fetch_row($assets_res)) {
	if($row['list_id'] != $list_id) {
		$assets[$row['list_id']] = [
			"list_title" => $row['list_title']
			,"list_label" => $row['label']
			,"randomize" => ($row['randomize'] == "t" ? 1 : 0)
			,"display_limit" => $row['display_limit']
			,"list_id" => $row['list_id']
			,"tables" => $row['tables']
			,"assets" => []
			,"tags" => []
		];
		$list_id = $row['list_id'];
	}
	$assets[$row['list_id']]['assets'][] = $row['asset'];
	$assets[$row['list_id']]['tags'][] = $row['tags'];
}
// echo "<pre>";
// print_r($assets);
// die();

##################################################
#   Pre-Content
##################################################
// add_css('pagination.css');
// add_js('sortlist.new.js');

##################################################
#   Content
##################################################
?>
<div class='clearfix'>
	<h2 class='lists'>Lists: <?php echo $info['title']; ?></h2>
  
	<div class="mb">
		<label for="limit">
			Expand at: <input type="input" name="split_on_count" id="split_on_count" value="3"> 
		</label>
	</div>

	<div class="mb">
		<button onclick="build_all_lists()">Update</button>
	</div>

		<div class='listcounter' id="listcounter" style='display:;'>
<?php
foreach($assets as $k => $list) {
	$l = $list['display_limit'];
	$r = $list['randomize'];
	$title = (!empty($list['list_label']) ? $list['list_label'] : $list['list_title']);
	$output = '
		<strong>'. $title .'</strong><br>
		<ol class="list_ordered" id="list_body_'. $k .'" data-limit="'. $l .'" data-randomize="'. $r .'">
	';
	$i = 0;
	if($list['tables'] == "t") {
		$i = 1;
		$output = '
		<strong>'. $title .'</strong><br>
		<table cellspacing="0" cellpadding="0" class="list_table">
			<thead>
				<tr>
					<th>'. implode('</th><th>',explode("|",$list['assets'][0])) .'</th>
				</tr>
			</thead>
			<tbody id="list_body_'. $k .'" data-limit="'. $l .'" data-randomize="'. $r .'">
		';
	}
	$cnt = 0;
	for($len=count($list['assets']); $i<$len; $i++) {
		$a = $list['assets'][$i];
		$t = json_decode($list['tags'][$i]);


		$display = ($cnt < $list['display_limit'] ? '' : " style='display:none;'");
		if($list['tables'] == "t") {
			$output .= '<tr data-filters="'. implode(' ',$t) .'"'. $display .'>
				<td>'. implode("</td><td>",explode('|',$a)) .'</td>
			</tr>';
		} else {
			$output .= '<li data-filters="'. implode(' ',$t) .'"'. $display .'>
				'. $a .'
			</li>';
		}
		$cnt += 1;
	}

	if($list['tables'] == "t") {
		$output .= "</tbody></table>";
	} else {
		$output .= "</ol>";
	}
	echo $output;
	unset($output);
}
?>
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
	var list_keys = [<?php echo implode(',',array_keys($assets)); ?>];
	console.log(list_keys);

	function double_shuffle(id) {
		id = id || 'list_body';
		shuffle_all_rows();
		shuffle_all_rows();
	}
	function shuffle_all_rows() {
		var id,list_rows,row,i;
		for(key in list_keys) {
			id = 'list_body_'+ list_keys[key];
			shuffle_rows(id);
		}
	}
	function shuffle_rows(id) {
		var id = id || 'list_body';
	    var list_rows = ($id(id).rows ? $id(id).rows : $query('#'+ id +' li'));
	    var rows = new Array();
		var row;
		for (var i=list_rows.length-1; i>=0; i--) {
		    row = list_rows[i];
		    rows.push(row);
		    row.parentNode.removeChild(row);
	    }
	    shuffle(rows);
	    for (i=0; i<rows.length; i++) {
	    	$id(id).appendChild(rows[i]);
		}
	}
	function reset_all_tables() {
		var id,list_rows,row,i;
		for(key in list_keys) {
			id = 'list_body_'+ list_keys[key];
			reset_table(id,key);
		}
	}
	function reset_table(id,key) {
		var id = id || 'list_body';
	    var list_rows = ($id(id).rows ? $id(id).rows : $query('#'+ id +' li'));
		var row;
		for (i=list_rows.length-1; i>=0; i--) {
		    row = list_rows[i];
		    row.parentNode.removeChild(row);
	    }
	    for (i=original_rows[key].length - 1; i >= 0; i--) {
	    	$id(id).appendChild(original_rows[i]);
		}
	}
	function set_original_rows() {
		var id,list_rows,row,i;
		for(key in list_keys) {
			id = 'list_body_'+ list_keys[key];
			original_rows[key] = [];
		    list_rows = ($id(id).rows ? $id(id).rows : $query('#'+ id +' li'));
			for (i=list_rows.length-1; i>=0; i--) {
			    row = list_rows[i];
			    original_rows[key].push(row);
		    }
		}
	}

	function get_filters() {
		var filters = $query('#custom_filters input[name^=filter]');
		var checked = [];
		for(var i=0,len=filters.length; i<len; i++) {
			if(filters[i].checked) {
				checked[checked.length] = filters[i].value;
			}
		}
		return checked;
	}

	function build_all_lists() {
		var id,list_rows,row,i;
		for(key in list_keys) {
			id = 'list_body_'+ list_keys[key];
			build_list(id,key);
		}
	}
	function build_list(id,limit,randomize) {
		var id = id || 'list_body';
	    var list_rows = ($id(id).rows ? $id(id).rows : $query('#'+ id +' li'));

		var limit = parseInt($id(id).dataset.limit);
		var randomize = $id(id).dataset.randomize;

		var checked;

		if(limit < 0 || limit > list_rows.length) {
			limit = list_rows.length;
		}

		if(randomize) {
			shuffle_rows(id);
		} else {
			reset_table(id);
		}

		// checked = get_filters();
		// r = new RegExp('(^|\\s)('+ checked.join("|") +')(\\s|$)');

		// console.log(checked)
		// console.log(limit)
		// console.log(randomize)
		// console.log(list_rows)

		cnt = 0;
		for(var i=0; i<list_rows.length; i++) {
			// if(checked.length == 0) {
				list_rows[i].style.display = (cnt < limit ? "" : "none");
				cnt += 1;
			// } else {
			// 	if(r.test(list_rows[i].dataset.filters)) {
			// 		list_rows[i].style.display = (cnt < limit ? "" : "none");
			// 		cnt += 1;
			// 	} else {
			// 		list_rows[i].style.display = "none";
			// 	}
			// }
		}

		
	}

	set_original_rows();
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