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
$list = "";
$tags = "";
while($row = db_fetch_row($assets)) {
	$list .= "'". str_replace("'","\'",$row['title']) ."',";
	$tags .= $row['tags'] .",";
}
$list = substr($list,0,-1);
$tags = substr($tags,0,-1);

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
		$output = '<div class="mb">';
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
	// $list = "";
	// $output = "<div class='boxof20'>";
	// $cnt = 0;
	// while($row = db_fetch_row($assets)) {
	// 	if(($cnt++%20 == 0) && $cnt > 1) {
	// 		$output .= "</div><div class='boxof20'>";
	// 	}
	// 	$output .= "<li>". $row['title'] ."</li>";
	// 	$list .= "'". str_replace("'","\'",$row['title']) ."',";
	// }
	// $output .= "</div>";
	// $list = substr($list,0,-1);
	// echo $output;
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
	var list = [<?php echo $list; ?>];
	var tags = [<?php echo $tags; ?>];
	var limit = 0;
	var randomize = 1;
	var is_table = <?php echo ($info['tables'] == 't' ? true : false); ?>;

	function display_list_boxof20(list,num) {
		var output = "<div class='boxof20'>";
		var cnt = 0;
		var num = num || list.length;
		for(var i=0,len=num; i<len; i++) {
			if(i && (i % 20 == 0)) {
				output += "</div><div class='boxof20'>";
			}
			output += "<li>"+ list[i] +"</li>";
		}
		output += "</div>";
		$id("listcounter").innerHTML = output;
	}
	
	function display_list(list,num) {
		
		var output = "";
		var num = num || list.length;
		var i,len,inner;
		output += (is_table ? "<table border='1'><thead>" : '<ol class="mt">');

		if(is_table) {
			table_pieces = list[0].split('|');

			for(i=0,len=table_pieces.length; i<len; i++) {
				output += "<th>"+ table_pieces[i].trim() +"</th>";
			}
			output += "</thead><tbody>";

		}

		i = (is_table ? 1 : 0);
		for(len=num; i<len; i++) {
			if(is_table) {
				inner = "";
				table_pieces = list[i].split('|');

				for(var j=0,jlen=table_pieces.length; j<jlen; j++) {
					inner += "<td>"+ table_pieces[j].trim() +"</td>";
				}
			}

			filters = "";
			if(tags[i]) {
				filters = " data-filters='"+ tags[i].join(" ") +"'";
  			}

  			output += (is_table ? '<tr'+ filters +'>'+ inner.trim() +'</tr>' : '<li'+ filters +'>'+ inner_pieces[0].trim() +'</li>');
		}


		output += (is_table ? "</tbody></table>" : "</ol>");
		output += '</div>';
		// console.log(output)
		$id('listcounter').innerHTML = output;
	}

	function build_list() {
		var randomized = $id("randomize").checked;
		var limit = parseInt($id("limit").value);
		var skip_first = (is_table ? true : false);
		if(limit > list.length) {
			limit = list.length;
		}

		if(randomized) {

			if(limit) {
				display_list(shuffle(list.slice(),limit,skip_first));	
			} else {
				display_list(shuffle(list.slice(),0,skip_first));	
			}
		} else {
			if(limit) {
				display_list(list,limit);	
			} else {
				display_list(list);
			}
		}		
	}
	build_list();
	
	function filter_list(key) {
		// console.log("Filter List: "+ key)
		var filters = $query('input[name^=filter]');
		var checked = []
		for(var i=0,len=filters.length; i<len; i++) {
			if(filters[i].checked) {
				checked[checked.length] = filters[i].value;
			}
		}
		// console.log(checked)
		elems = $query('#listcounter [data-filters]')
		// var elems = $query('#filter_examples ol > li');

		console.log(elems);
		var test;
		var checked_length = checked.length;
		var len = elems.length;
		var i = (is_table ? 1 : 0);
		for(; i<len; i++) {
			test = (checked_length == 0 ? true : false);
			console.log(elems[i].dataset.filters)
			for(j in checked) {
				r = new RegExp('(^|\\s)'+ checked[j] + '(\\s|$)');
				if(r.test(elems[i].dataset.filters)) {
					test = true;
					break;
				}
			}
			if(test) {
				elems[i].style.display = "";
			} else {
				elems[i].style.display = "none";
			}
		}
		
	}

function shuffleRows(parent) {

    var tbody = $id("parent");
    var myRows = new Array();
	for (i=tbody.rows.length-1; i>=0; i--) {
	    var theRow = tbody.rows[i];
	    myRows.push(theRow);
	    theRow.parentNode.removeChild(theRow);
    }
    shuffle(myRows);
    for (j=0; j<myRows.length; j++) {
    	tbody.appendChild(myRows[j]);
	}
}
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