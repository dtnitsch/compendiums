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
// $pieces = explode('/',$GLOBALS['project_info']['path_data']['path']);
// $key = trim($pieces[2]);


##################################################
#   DB Queries
##################################################
// $q = "select * from public.collection where key='". db_prep_sql($key) ."'";
// $info = db_fetch($q,"Getting collection information");

// $q = "
// 	select
// 		public.asset.id
// 		,public.asset.title as asset
// 		,collection_list_map.id as collection_list_map_id
// 		,collection_list_map.list_id
// 		,collection_list_map.collection_id
// 		,collection_list_map.label
// 		,collection_list_map.randomize
// 		,collection_list_map.display_limit
// 		,list_asset_map.tags
// 		,list.title as list_title
// 		,list.tables as tables
// 	from public.asset
// 	join public.list_asset_map on 
// 		list_asset_map.asset_id = asset.id
// 	join public.collection_list_map on
// 		collection_list_map.list_id = list_asset_map.list_id
// 		and collection_list_map.collection_id = '". $info['id'] ."'
// 	join public.list on
// 		list.id = collection_list_map.list_id
// 	order by
// 		collection_list_map.id
// 		,asset.id
// ";
// $assets_res = db_query($q,"Getting collection assets");

// $assets = array();
// while($row = db_fetch_row($assets_res)) {
// 	$id = $row['list_id'] ."-". $row['collection_list_map_id'];
// 	if(empty($assets[$id])) {
// 		$assets[$id] = [
// 			"list_title" => $row['list_title']
// 			,"list_label" => $row['label']
// 			,"randomize" => ($row['randomize'] == "t" ? 1 : 0)
// 			,"display_limit" => $row['display_limit']
// 			,"list_id" => $row['list_id']
// 			,"tables" => $row['tables']
// 			,"assets" => []
// 			,"tags" => []
// 		];
// 	}
// 	$assets[$id]['assets'][] = $row['asset'];
// 	$assets[$id]['tags'][] = $row['tags'];
// }

##################################################
#   Pre-Content
##################################################
// add_css('pagination.css');
// add_js('sortlist.new.js');
// add_js("list_functions.js",10);
// $split_on_count = 3;

$sections = [
	"Environment" => [
		"Weather" => "1"
		,"Location" => "2"
	]
	,"People" => [
		"Male Names" => [
			"All Races" => "z"
			,"Humans" => "y"
			,"Elves" => "x"
			,"Dwarves" => "w"
		]
		,"Female Names" => [
			"All Races" => "v"
			,"Humans" => "u"
			,"Elves" => "t"
			,"Dwarves" => "s"
			,"Other Languages" => [
				"French" => ["Baggette","Hon hon hon"]
				,"Hungarian" => ["Jo regelt"]
			]
		]
	]
	,"Places" => [
		"Names" => [
			"Taverns" => "3"
			,"Shops" => "4"
			,"Continents" => "5"
			,"Ships" => "6"
			,"Drinks and Foods" => "7"

		]
	]
	,"Items" => [
		"Equipment" => "8"
		,"Book Names" => "9"
	]
	,"Other" => [
		"Feelings" => "a"
		,"Colors" => "b"
		,"Holidays" => "c"
	]
];



// zoe eden nitsch
##################################################
#   Content
##################################################
?>
<style type="text/css">
	ul.compendium { list-style-type: none; }
	ul.compendium li { }
	.ul1 { padding-left: 5px; }
	.ul2 { padding-left: 10px; }
	.ul3 { padding-left: 15px; }
	.ul4 { padding-left: 20px; }
	.ul5 { padding-left: 25px; }
	.ul6 { padding-left: 30px; }
	.ul8 { padding-left: 35px; }
	.ul9 { padding-left: 40px; }
</style>
<div class='clearfix'>
	<h2 class='lists'>Compendiums</h2>

	<div id="ul_navs">
		<?php echo recursive_menu($sections); ?>
	</div>

</div>
<?php
##################################################
#   Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">
function show_hide_children(obj) {
	node = obj.parentNode.getElementsByTagName('ul');
	if(typeof node[0] != "undefined") {
		node = node[0];
	}
	node.style.display = (node.style.display == "none" ? "" : "none");
}

//Returns true if it is a DOM node
function isNode(o){
  return (
    typeof Node === "object" ? o instanceof Node : 
    o && typeof o === "object" && typeof o.nodeType === "number" && typeof o.nodeName==="string"
  );
}

//Returns true if it is a DOM element    
function isElement(o){
	return (
		typeof HTMLElement === "object" ? o instanceof HTMLElement : //DOM2
		o && typeof o === "object" && o !== null && o.nodeType === 1 && typeof o.nodeName==="string"
	);
}

function menus(type) {
	type = type || "";
	var lis = document.getElementById("ul_navs").getElementsByTagName("li");
	for(var i=0, len=lis.length; i<len; i++) {
		if(lis[i].childNodes.length == 1) {
			if(type == "" || type == "toggle") {
				lis[i].style.display = (lis[i].style.display == "none" ? "" : "none");	
			} else if(type == "open") {
				lis[i].style.display = "";
			} else if(type == "close") {
				lis[i].style.display = "none";
			}
		}
		
	}
}
</script>
<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#   Additional PHP Functions
##################################################
function recursive_menu($arr,$output="",$level=1) {
	$output = '<ul class="compendium ul'. $level .'">';
	foreach($arr as $k => $v) {
		if(is_array($v)) {
			$output .= '<li>';
			$output .= '<a href="javascript:void(0);" onclick="show_hide_children(this)">'. $k .'</a>';
			$output .= recursive_menu($v, $output, $level + 1);
		} else {
			$output .= '<li>';
			$output .= $v;
		}
		
		$output .= '</li>';
	}
	$output .= '</ul>';
	return $output;
}


##################################################
#   EOF
##################################################