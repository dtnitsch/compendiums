<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

##################################################
#   Validation
##################################################

##################################################
#   DB Queries
##################################################

##################################################
#   Pre-Content
##################################################
add_css('modal.css');
add_js('modal.js');

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

.tag_table { border-top: 1px solid #ccc; border-right: 1px solid #ccc; width: 100%; }
.tag_table td { border-bottom: 1px solid #ccc; border-left: 1px solid #ccc; padding: 10px; }
.tag_table td.tag_nav { background: #eee; border-left: 1px solid #ccc; width: 230px; }

</style>


<div class='clearfix'>
	<h2 class='compendiums'>Add Compendium</h2>
  
  	<?php echo dump_messages(); ?>
	<form id="addform" method="post" action="" onsubmit="">

		<label class="form_label" for="title">Compendium Name <span>*</span></label>
		<div class="form_data">
			<input type="text" name="title" id="title" value="">
		</div>

	</form>

	<div id="compendium_buttons" class="w3-bar w3-black">
		<button class="w3-bar-item w3-button tablink w3-red" onclick="openCity(this,'London')">London</button>
	</div>
	<div id="compendium_bodies">

		<div id="London" class="w3-container w3-border city" style="display: block;">
			<table cellpadding="0" cellspacing="0" class='tag_table'>
				<tr>
					<td>
						Testing 2
					</td>
					<td class="tag_nav">
						<button onclick="add_list_link(this)">Add List</button>
						<button onclick="add_collection_link(this)">Add Collection</button>
					</td>
				</tr>
			</table>
		</div>

	</div>
	<button onclick="display_modal('modal_tabs');">Add Compendium</button>

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

function add_compendium_buttons(info) {
	var btn = document.createElement("button");
	btn.className = "w3-bar-item w3-button tablink";
	btn.onclick = function() { openCity(this,info.name); }
	btn.innerHTML = info.name;
	
	var div = document.createElement("div");
	div.id = info.name;
	div.className = "w3-container w3-border city";
	div.style.display = "none";
	div.innerHTML = `
		<table cellpadding="0" cellspacing="0" class='tag_table'>
			<tr>
				<td>
					`+ info.name +`
				</td>
				<td class="tag_nav">
					<div>
						<button onclick="add_list_link(this)">Add List</button>
						<button onclick="add_collection_link(this)">Add Collection</button>
					</div>
				</td>
			</tr>
		</table>
	`;

	// console.log(btn)
	// console.log(div)
	$id('compendium_buttons').appendChild(btn);
	$id('compendium_bodies').appendChild(div);
}

function add_list_link(obj) {
	var div = document.createElement("div");
	div.innerHTML = '<a href="#">List</a>';
	obj.parentNode.appendChild(div);
}
function add_collection_link(obj) {
	var div = document.createElement("div");
	div.innerHTML = '<a href="#">Collection</a>';
	obj.parentNode.appendChild(div);
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