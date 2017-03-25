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



// zoe eden nitsch
##################################################
#   Content
##################################################
?>
<style type="text/css">
.w3-bar{width:100%;overflow:hidden}.w3-center .w3-bar{display:inline-block;width:auto}
.w3-bar .w3-bar-item{padding:8px 16px;float:left;width:auto;border:none;outline:none;display:block}
.w3-bar .w3-dropdown-hover,.w3-bar .w3-dropdown-click{position:static;float:left}
.w3-bar .w3-button{white-space:normal}
.w3-bar-block .w3-bar-item{width:100%;display:block;padding:8px 16px;text-align:left;border:none;outline:none;white-space:normal}
.w3-bar-block.w3-center .w3-bar-item{text-align:center}.w3-block{display:block;width:100%}
.w3-black,.w3-hover-black:hover{color:#fff!important;background-color:#000!important}

.w3-btn,.w3-button{border:none;display:inline-block;outline:0;padding:8px 16px;vertical-align:middle;overflow:hidden;text-decoration:none;
color:inherit;background-color:inherit;text-align:center;cursor:pointer;white-space:nowrap}
.w3-btn:hover{box-shadow:0 8px 16px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19)}
.w3-btn,.w3-button{-webkit-touch-callout:none;-webkit-user-select:none;-khtml-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}   
.w3-disabled,.w3-btn:disabled,.w3-button:disabled{cursor:not-allowed;opacity:0.3}.w3-disabled *,:disabled *{pointer-events:none}

.w3-red,.w3-hover-red:hover{color:#fff!important;background-color:#f44336!important}
.w3-container:after,.w3-container:before,.w3-panel:after,.w3-panel:before,.w3-row:after,.w3-row:before,.w3-row-padding:after,.w3-row-padding:before,
.w3-cell-row:before,.w3-cell-row:after,.w3-clear:after,.w3-clear:before,.w3-bar:before,.w3-bar:after
{content:"";display:table;clear:both}
.w3-container{padding:0.01em 16px}
</style>

<div class='clearfix'>
	<h2 class='lists'>Compendiums</h2>

	  <div id="compendium_buttons" class="w3-bar w3-black">
	    <button class="w3-bar-item w3-button tablink w3-red" onclick="openCity(this,'London')">London</button>
	    <button class="w3-bar-item w3-button tablink" onclick="openCity(this,'Paris')">Paris</button>
	    <button class="w3-bar-item w3-button tablink" onclick="openCity(this,'Tokyo')">Tokyo</button>
	  </div>
	  
	  <div id="compendium_bodies">
		  <div id="London" class="w3-container w3-border city">
		    <h2>London</h2>
		    <p>London is the capital city of England.</p>
		  </div>

		  <div id="Paris" class="w3-container w3-border city" style="display:none">
		    <h2>Paris</h2>
		    <p>Paris is the capital of France.</p> 
		  </div>

		  <div id="Tokyo" class="w3-container w3-border city" style="display:none">
		    <h2>Tokyo</h2>
		    <p>Tokyo is the capital of Japan.</p>
		  </div>
		</div>
	</div>
</div>
<?php
##################################################
#   Javascript Functions
##################################################
ob_start();
?>

<script>
function openCity(evt, cityName) {
  var i, x, tablinks;
  x = document.getElementsByClassName("city");
  for (i = 0; i < x.length; i++) {
     x[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablink");
  for (i = 0; i < x.length; i++) {
      tablinks[i].className = tablinks[i].className.replace(" w3-red", ""); 
  }
  document.getElementById(cityName).style.display = "block";
  evt.className += " w3-red";
}

function add_compendium_buttons(name) {
	var btn = document.createElement("button");
	btn.className = "w3-bar-item w3-button tablink";
	btn.onclick = function() { openCity(this,name); }
	btn.innerHTML = name;
	
	var div = document.createElement("div");
	div.id = name;
	div.className = "w3-container w3-border city";
	div.style.display = "none";
	div.innerHTML = `
		    <h2>`+ name +`</h2>
		    <p>`+ name +` is the capital of Somewhere.</p>
	`;

	// console.log(btn)
	// console.log(div)
	$id('compendium_buttons').appendChild(btn);
	$id('compendium_bodies').appendChild(div);

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