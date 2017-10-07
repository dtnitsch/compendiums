<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

post_queue($module_name,'modules/compendiums/post_files/');

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
add_js("lists.js",10);

##################################################
#   Content
##################################################
?>
<style type="text/css">

.tab_table { width: 100%; }
.tab_table td { padding: 10px; vertical-align: top; }
.tab_table td.tab_nav { background: #eee; border-left: 1px solid #ccc; width: 230px; }

</style>


<div class='clearfix'>
<?php echo dump_messages(); ?>

<div class="subheader">
	<div class="float_right">
		<input type="button" value="Create Compendium Tab" onclick="display_modal('modal_tabs');">
	</div>

	<div class="title">Compendium: Default <span class="small">(<a href="#">Edit</a>)</span></div>
</div>
  
<div id="compendium_buttons" class="tabbar"></div>
<div id="compendium_bodies" class="tabbody" style="padding: 0px; margin: 0;"></div>

<div class='listcounter mt' id="listcounter_master"></div>


<?php echo run_module("modal_tabs"); ?>
<?php echo run_module("compendium_add_list"); ?>

<?php
##################################################
#   Javascript Functions
##################################################
ob_start();
?>

<script type="text/javascript">
var parent_object;
var modal_section;
var submit_bool = false;
var information = {
	"lists": {}
	,"compendiums": {}
}


function display_compenidum_assets(id,key) {
	assets[key] = information['lists'][key];
	hide_compendium_content();
	$id("content-"+ modal_section +"-"+ key).innerHTML = build_display(key).trim();
	show("content-"+ modal_section +"-"+ key);
}

function hide_compendium_content() {
	divs = $query("#content-"+ modal_section +" [id^='content-"+ modal_section +"-']");
	for(i in divs) {
		hide(divs[i].id);
	}
}

// function submit_form() {
// 	return submit_bool;
// }

// function read_for_submit() {
// 	submit_bool = true;
// 	$id('addform').submit();
// }

function display_modal(id) {
	id = id || "compendium_add_list_modal";
	modal_init(id);
	if(typeof reset_modal == "function") { reset_modal(); }
	$id(id).style.display = "block";
}

function search_for_list(obj,section) {
	parent_object = obj;
	modal_section = section;
	modal_init("compendium_add_list_modal");
	if(typeof reset_modal == "function") {
		reset_modal();
	}
	$id("compendium_add_list_modal").style.display = "block";
	$id('modal_search').focus();
}
modal_init("modal_tabs");

function open_tabs(evt, tabname) {
  var i, x, tablinks;
  // Close all tabs
  x = document.getElementsByClassName("tabs");
  for (i = 0; i < x.length; i++) {
     x[i].style.display = "none";
  }
  // Reset all "red" nav links
  tablinks = document.getElementsByClassName("tablink");
  for (i = 0; i < tablinks.length; i++) {
      tablinks[i].className = tablinks[i].className.replace(" active", ""); 
  }
  // open clicked tab
  document.getElementById(tabname).style.display = "block";
  // Set nav
  evt.className += " active";

  modal_section = slug(tabname)
}

function add_compendium_buttons(info) {
	var btn = document.createElement("button");
	var active = (typeof info.active != undefined && info.active ? true : false);
	var alias = slug(info.name);
	btn.className = "tablink";
	if(active) {
		btn.className += " active";
	}
	btn.id = "compendium_nav_"+ alias;
	btn.onclick = function() { open_tabs(this,info.name); }
	btn.innerHTML = info.name;

	var div = document.createElement("div");
	div.id = info.name;
	div.className = "tabs";
	div.style.display = (active ? "" : "none");
	div.innerHTML = `
		<table cellpadding="0" cellspacing="0" class='tab_table'>
			<tr>
				<td id="content-`+ alias +`"></td>
				<td class="tab_nav">
					<div id="nav-`+ alias +`">
					</div>
					<div class="mt">
						<input type="button" value="Add List" onclick="search_for_list(this,'`+ alias +`')">
						<input type="button" value="Add Collection" onclick="search_for_collection(this,'`+ alias +`')">
						<input type="button" value="Add Note">
						<input type="hidden" name="sections[`+ alias +`]" value="`+ info.name +`" />
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

function add_collection_link(obj) {
	var div = document.createElement("div");
	div.innerHTML = '<a href="#">Collection</a>';
	obj.parentNode.appendChild(div);
	modal_clear('modal_list');
}

function add_list(info,limit,randomize,multi) {
	var list_body = $id('list_body');
	var div = document.createElement("div");

	div.innerHTML = '<input type="text" name="lists['+ modal_section +']['+ info.key +']" value="'+ info.title +'" />';
	parent_object.parentNode.appendChild(div);
	modal_clear('compendium_add_list_modal');
}

var info = {
	"name": "Default"
	,"color": "blue"
	,"active": true
};
add_compendium_buttons(info);
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