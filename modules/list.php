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
	select public.asset.*
	from public.asset
	join public.list_asset_map on 
		list_asset_map.asset_id = asset.id
		and list_asset_map.list_id = '". $info['id'] ."'
	order by
		asset.title
";
$assets = db_query($q,"Getting list assets");


##################################################
#   Pre-Content
##################################################
// add_css('pagination.css');
// add_js('sortlist.new.js');
$list = "";
while($row = db_fetch_row($assets)) {
	$list .= "'". str_replace("'","\'",$row['title']) ."',";
}
$list = substr($list,0,-1);

##################################################
#   Content
##################################################
?>
<div class='clearfix'>
	<h2 class='lists'>Lists: <?php echo $info['title']; ?></h2>
  
	<div class="mb">
		<label for="limit">
			Limit Display: <input type="input" name="limit" id="limit" value="5"> 
		</label>

		<label for="randomize">
			<input checked type="checkbox" name="options" id="randomize" value="randomize"> Randomize
		</label>
	</div>

	<div class="mb">
		<button onclick="build_list()">Update</button>
	</div>

	<ol class='listcounter' id="listcounter">
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
	</ol>

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
	var limit = 0;
	var randomize = 1;

	function display_list(list,num) {
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

	function build_list() {
		var randomized = $id("randomize").checked;
		var limit = parseInt($id("limit").value);
		if(limit > list.length) {
			limit = list.length;
		}

		if(randomized) {
			if(limit) {
				display_list(shuffle(list.slice(),limit));	
			} else {
				display_list(shuffle(list.slice()));	
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