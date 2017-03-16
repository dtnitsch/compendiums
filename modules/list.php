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
$q = "select * from public.list where key='". db_prep_sql($key) ."'";
$info = db_fetch($q,"Getting list information");

$q = "
	select
		public.asset.*
		,list_asset_map.tags
	from public.asset
	join public.list_asset_map on 
		list_asset_map.asset_id = asset.id
		and list_asset_map.list_id = '". $info['id'] ."'
	order by
		asset.id
";
$assets = db_query($q,"Getting list assets");


##################################################
#   Pre-Content
##################################################
// add_css('pagination.css');
// add_js('sortlist.new.js');
$list = [];
while($row = db_fetch_row($assets)) {
	$list[] = [
		"title" => $row['title']
		,"tags" => json_decode($row['tags'])
	];
}

##################################################
#   Content
##################################################
?>
<div class='clearfix'>
	<h2 class='lists'>Lists: <?php echo $info['title']; ?></h2>
  
	<div class="mb">
		<label for="limit">
			Limit Display: <input type="input" name="limit" id="limit" value="20" class='xs'> 
		</label>

		<label for="randomize">
			<input checked type="checkbox" name="options" id="randomize" value="randomize"> Randomize
		</label>
	</div>

<?php
	$info['tags'] = json_decode($info['tags']);
	if(!empty($info['tags'])) {
		$output = '<div id="custom_filters" class="mb">';
		$cnt = 0;
		foreach($info['tags'] as $v) {
			$output .= '
			<label for="filter_'. $cnt .'">
				<input type="checkbox" id="filter_'. $cnt .'" name="filters['. $v .']" onclick="filter_list(\''. $v .'\')" value="'. $v .'"> '. $v .'
			</label> &nbsp; 
			';
			$cnt += 1;
		}
		$output .= '</div>';
		echo $output;
	}
?>

	<div class="mb">
		<button onclick="build_list()">Update</button>
	</div>



	<div class='listcounter' id="listcounter">
<?php
	$output .= '<ol>';
	$i = 0;
	if($info['tables'] == "t") {
		$i = 1;
		$output = '
		<table border="1">
			<thead>
				<tr>
					<th>'. implode('</th><th>',explode("|",$list[0]['title'])) .'</th>
				</tr>
			</thead>
			<tbody id="list_tbody">
		';
	}
	for($len=count($list); $i<$len; $i++) {
		$row = $list[$i];
		if($info['tables'] == "t") {
			$output .= '<tr data-filters="'. implode(' ',$row['tags']) .'">
				<td>'. implode("</td><td>",explode('|',$row['title'])) .'</td>
			</tr>';
		} else {
			$output .= '<li data-filters="'. implode(' ',$row['tags']) .'">
				'. $row['title'] .'
			</li>';
		}
	}

	if($info['tables'] == "t") {
		$output .= "</tbody></table>";
	} else {
		$output .= "</ol>";
	}
	echo $output;
	unset($output);
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
	var is_table = <?php echo ($info['tables'] == 't' ? true : false); ?>;
	var original_rows = [];

	function double_shuffle(id) {
		id = id || 'list_tbody';
		shuffle_rows(id);
		shuffle_rows(id);
	}
	function shuffle_rows(id) {
		var id = id || 'list_tbody';
	    var tbody = $id(id);
	    var rows = new Array();
		var row;
		for (var i=tbody.rows.length-1; i>=0; i--) {
		    row = tbody.rows[i];
		    rows.push(row);
		    row.parentNode.removeChild(row);
	    }
	    shuffle(rows);
	    for (i=0; i<rows.length; i++) {
	    	tbody.appendChild(rows[i]);
		}
	}
	function reset_table(id) {
		var id = id || 'list_tbody';
	    var tbody = $id(id);
		var row;
		for (i=tbody.rows.length-1; i>=0; i--) {
		    row = tbody.rows[i];
		    row.parentNode.removeChild(row);
	    }
	    for (i=original_rows.length - 1; i >= 0; i--) {
	    	tbody.appendChild(original_rows[i]);
		}
	}
	function set_original_rows(id) {
		var id = id || 'list_tbody';
	    var tbody = $id(id);
		var row;
		for (var i=tbody.rows.length-1; i>=0; i--) {
		    row = tbody.rows[i];
		    original_rows.push(row);
	    }
	}

	function filter_list(key) {
		build_list();
	}
	// 	// console.log("Filter List: "+ key)
	// 	var filters = $query('input[name^=filter]');
	// 	var checked = []
	// 	for(var i=0,len=filters.length; i<len; i++) {
	// 		if(filters[i].checked) {
	// 			checked[checked.length] = filters[i].value;
	// 		}
	// 	}
	// 	console.log(checked)
	// 	return;
	// 	elems = $query('#listcounter [data-filters]')
	// 	// var elems = $query('#filter_examples ol > li');

	// 	console.log(elems);
	// 	var test;
	// 	var checked_length = checked.length;
	// 	var len = elems.length;
	// 	var i = (is_table ? 0 : 0);
	// 	for(; i<len; i++) {
	// 		test = (checked_length == 0 ? true : false);
	// 		// console.log(elems[i].dataset.filters)
	// 		for(j in checked) {
	// 			r = new RegExp('(^|\\s)'+ checked[j] + '(\\s|$)');
	// 			if(r.test(elems[i].dataset.filters)) {
	// 				test = true;
	// 				break;
	// 			}
	// 		}
	// 		if(test) {
	// 			elems[i].style.display = "";
	// 		} else {
	// 			elems[i].style.display = "none";
	// 		}
	// 	}
	// }

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

	function build_list(id) {
		var id = id || 'list_tbody';
	    var tbody = $id(id);

		var limit = parseInt($id('limit').value);
		var randomize = $id('randomize').checked;

		var checked;

		if(limit < 0 || limit > tbody.rows.length) {
			limit = tbody.rows.length;
		}

		if(randomize) {
			shuffle_rows();
		} else {
			reset_table();
		}

		checked = get_filters();
		r = new RegExp('(^|\\s)('+ checked.join("|") +')(\\s|$)');

		cnt = 0;
		for(var i=0; i<tbody.rows.length; i++) {
			if(checked.length == 0) {
				tbody.rows[i].style.display = (cnt < limit ? "" : "none");
				cnt += 1;
			} else {
				if(r.test(tbody.rows[i].dataset.filters)) {
					tbody.rows[i].style.display = (cnt < limit ? "" : "none");
					cnt += 1;
				} else {
					tbody.rows[i].style.display = "none";
				}
			}
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