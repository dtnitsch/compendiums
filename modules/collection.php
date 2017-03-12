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
		,list.title as list_title
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
		,asset.title
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
			,"assets" => []
		];
		$list_id = $row['list_id'];
	}
	$assets[$row['list_id']]['assets'][$row['id']] = $row['asset'];
}


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
		<button onclick="build_list()">Update</button>
	</div>

	<div class='listcounter' id="listcounter">
<?php

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
	var lists = <?php echo json_encode($assets); ?>;
	console.log(lists)



	function display_list(list,num) {
		var split_on_count = $id('split_on_count').value;
		var cnt = 0;
		var num = num || list.length;
		output = "";
		if(num >= split_on_count) {
			output += "<ol>";
			for(var i=0,len=num; i<len; i++) {
				output += "<li>"+ list[i] +"</li>";
			}
			output += "</ol>";
		} else {	
			for(var i=0,len=num; i<len; i++) {
				output += list[i] +",";
			}
		}
		return output.substring(0,output.length - 1);
	}

	function build_list() {
		var randomize,limit,sliced,output;
		$id("listcounter").innerHTML = "";
		for(list_id in lists) {
			list = lists[list_id];
			randomize = parseInt(list.randomize);
			limit = parseInt(list.display_limit);
			if(limit > list.length) {
				limit = list.length;
			}
			var boxof20 = document.createElement("div");
			// boxof20.className = 'boxof20';
			output = "<strong>"+ list.list_label +"</strong>: ";

			sliced = [];
			for(var k in list.assets) {
				sliced[sliced.length] = list.assets[k];
			}

			if(randomize) {
				if(limit) {
					output += display_list(shuffle(sliced,limit));	
				} else {
					output += display_list(shuffle(sliced));	
				}
			} else {
				if(limit) {
					output += display_list(sliced,limit);	
				} else {
					output += display_list(sliced);
				}
			}		
			boxof20.innerHTML = output;
			$id("listcounter").appendChild(boxof20);

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