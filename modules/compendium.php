<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

##################################################
#   Validation
##################################################
$pieces = explode('/',$GLOBALS['project_info']['path_data']['path']);
$key = trim($pieces[2]);

##################################################
#   DB Queries
##################################################
$q = "select * from public.compendium where key='". $key ."'";
$info = db_fetch($q,"Getting Compendium");

$q = "select * from public.compendium_list_map where compendium_id = '". $info['id'] ."'";
$res = db_query($q,"Getting Compendiums list map");

$list_map = [];
while($row = db_fetch_row($res)) {
	$list_map[$row['section']][$row['key']] = $row;
}


##################################################
#   Pre-Content
##################################################
add_css('modal.css');
add_js('modal.js');
add_js("list_functions.js",10);

$sections = json_decode($info['sections']);

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
.w3-container{padding:0.01em}

.tag_table { border-right: 1px solid #ccc; width: 100%; }
.tag_table td { border-bottom: 1px solid #ccc; border-left: 1px solid #ccc; padding: 10px; }
.tag_table td.tag_nav { background: #eee; border-left: 1px solid #ccc; width: 230px; }
.tag_table tr { vertical-align: top; }

</style>

<div class='clearfix'>
	<h2 class='lists'>Compendium</h2>

	<div id="compendium_buttons" class="w3-bar w3-black">
		<!--button class="w3-bar-item w3-button tablink w3-red" onclick="openCity(this,'London')">London</button-->
<?php

$output = '';
$divs = [];
foreach($sections as $k => $v) {
	$active = '';
	$display = ' style="display: none;"';
	if($output == "") {
		$active = ' w3-red';
		$display = '';
	}

	$output .= '<button class="w3-bar-item w3-button tablink'. $active .'" onclick="openCity(this,\''. $k .'\')">'. $v .'</button>';
	$divs[$k] = '
		<div id="'. $k .'" class="w3-container w3-border city"'. $display .'>
		<table cellpadding="0" cellspacing="0" class="tag_table">
			<tr>
				<td id="'. $k .'_content">
					'. $v .'
				</td>
				<td class="tag_nav">
';
	foreach($list_map[$k] as $key => $r) {
		$divs[$k] .= '<div><a href="javascript:void(0);" onclick="modal_getter(\''. $k .'\',\''. $key .'\','. $r['list_id'] .')">'. $r['label'] .'</a></div>';
	}

	$divs[$k] .= '
				</td>
			</tr>
		</table>
		</div>
	';
}

echo $output;
?>
	</div>

	<div id="compendium_bodies">
<?php 
foreach($divs as $v) {
	echo $v;
}
 ?>
	</div>
</div>

<?php echo run_module("modal_tabs"); ?>

<?php
##################################################
#   Javascript Functions
##################################################
ob_start();
?>

<script type="text/javascript">
function display_modal(id) {
	id = id || "simple_modal";
	if(typeof reset_modal == "function") { reset_modal(); }
	$id(id).style.display = "block";
}

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


var sec = '';
var list_keys = [];
function modal_getter(section,val,list_id) {
	if(val.trim() == "") {
		return;
	}
	list_keys = [list_id];
	sec = section;
	search_data = "apid=ff15890b1815ec8d9eaf91ad22a5286e&val="+ val;
	ajax('/ajax.php',{
		type: 'json'
		,data: search_data
		,success: display_modal_getter
	});
}
function display_modal_getter(res) {
	$id(sec +'_content').innerHTML = res.output.html;
	// list_keys = [res.output.info.id];
	// returned_info = res.output.info;
	set_original_rows();
// 	$id('add_list_button').style.display = "";
// 	$id('add_multi_button').style.display = "";
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