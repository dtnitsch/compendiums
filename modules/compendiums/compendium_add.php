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
add_js("compendium.js",10);

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
<?php #echo run_module("compendium_add_list"); ?>

<?php echo run_module("modal_list"); ?>
<?php echo run_module("list_simple"); ?>

<?php
##################################################
#	Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">

var list_count = 0;
var list_simple_template = "";
var current_asset = [];
var add_to_list = false;

var assets = {
	"lists": {}
}

var info = {
	"name": "Default"
	,"color": "blue"
	,"active": true
};

	
add_compendium_buttons(info);
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

function get_current_tab() {
	var tabs = $query("#compendium_buttons .active");
	if(typeof tabs[0] != "undefined" && typeof tabs[0].id != "undefined") {
		return tabs[0].id.replace("compendium_nav_","");
	}
	return;
}

function search_for_list(list_num) {
	add_to_list = list_num || false;
	reset_modal();
	$id("simple_modal").style.display = "block";
	$id('modal_search').focus();
}

function build_compendium_display(key) {
	var modal_section = get_current_tab();
	var id = 'content-'+ modal_section +'-'+ key;
	current_asset = assets['lists'][key];
	hide_all_displays('content-'+ modal_section +'-');
	show_build_display(id);
	show(id);
}

function hide_all_displays(ids) {
	var elems = $query("[id^='"+ ids +"']");
	for(i in elems) {
		hide(elems[i].id)
	}
}

function add_list(id) {
	console.log(id);
	console.log(current_asset);

	assets['lists'][current_asset.list_key] = current_asset;

	var modal_section = get_current_tab();
	var key = current_asset.list_key;
	var div_id = 'content-'+ modal_section +'-'+ key;
	var filter_details = 'filter-details-'+ modal_section +'-'+ key;
	var filter_count = 'filter-count-'+ modal_section +'-'+ key;
	var randomize = 'randomize-'+ modal_section +'-'+ key;
	var limit = 'limit-'+ modal_section +'-'+ key;
	var filters_dynamic = 'filters-dynamic-'+ modal_section +'-'+ key;
	var listcounter = 'listcounter-'+ modal_section +'-'+ key;

	div = document.createElement("div");
	div.id = div_id;

	var innerHTML = `
	<div class="filters" onclick="show_hide('${filter_details}')">
		Filters (<span class="filter_count" id="${filter_count}">0 applied</span>) <span class="small">(<span class="fakeref">show/hide</span>)</span>
	</div>
	<div class="filter_details" id="${filter_details}" style="display: none;">

		<form id="form_filters" method="" action="" onsubmit="return false;">
			<label for="${limit}">
				Limit Display: <input type="input" name="limit" id="${limit}" value="20" class='xs'> 
			</label>

			<label for="${randomize}">
				<input type="checkbox" name="options" id="${randomize}" value="randomize"> Randomize
			</label>

			<div id="${filters_dynamic}" class="mtb"></div>
		</form>

		<div class="mt">
			<!--input type="button" value="Update List" onclick="build_all_display('${listcounter}')"-->
			<input type="button" value="Update List" onclick="build_compendium_display('${current_asset.list_key}')">
		</div>
	</div>
	<div class='listcounter mt' id="${listcounter}"></div>
	`;

	div.innerHTML = innerHTML;
	$id('content-'+ modal_section).appendChild(div);

	// hide_all_displays('content-'+ modal_section +'-');

	var build_filter_list = {
		'limit_id': 'limit-'+ modal_section +'-'
		,'randomize_id': 'randomize-'+ modal_section +'-'
		,'filter_id': 'filters-'+ modal_section +'-'
	}
	build_filters(current_asset,build_filter_list);
	show_build_display(listcounter);

	// information['lists'][key] = assets[key];
	$id("nav-"+ modal_section).innerHTML += `
	<div onclick="build_compendium_display('${current_asset.list_key}')" style="padding: 10px;border-bottom: 1px solid black; cursor: pointer;">
		`+ current_asset.list_title +`
	</div>
	`;

	// build_compendium_display(current_asset.list_key);

	// var limit = parseInt($id('limit_'+ current_asset.list_key) ? $id('limit_'+ current_asset.list_key).value : current_asset.display_limit);
    // var randomize = ($id('randomize_'+ current_asset.list_key) ? $id('randomize_'+ current_asset.list_key).checked : current_asset.randomize);
	// var checked = (randomize ? ' checked' : '');

	// list_count += 1;
	// tr = document.createElement("tr");
	// tr.id = "list_count_"+ list_count;

	// output = `
	// 	<td>`+ list_count +`</td>
	// 	<td>
	// 		<input type="input" id="label`+ list_count +`" name="list_labels[`+ list_count +`]" placeholder="List Label" value="`+ current_asset.list_title +`" style="width: 90%;">
	// 	</td>
	// 	<td>
	// 		<input type="input" id="key`+ list_count +`" name="list_keys[`+ list_count +`]" placeholder="List Key" value="`+ current_asset.list_key +`" style="width: 90%;">
	// 	</td>
	// 	<td>
	// 		<label for="randomize`+ list_count +`">
	// 			<input`+ checked +` type="checkbox" name="randomize[`+ list_count +`]" id="randomize`+ list_count +`" value="1"> Randomize
	// 		</label>
	// 	</td>
	// 	<td>
	// 		<input type="input" name="display_limit[`+ list_count +`]" value="`+ limit +`" style="width: 32px;">
	// 	</td>
	// 	<td>
	// 		<input type="button" value=" + " onclick="search_for_list(`+ list_count +`)">
	// 		<input type="button" value=" - " onclick="remove_list_row(`+ list_count +`)">
	// 	</td>
	// `;
	// tr.innerHTML = output;
	// $id('lists').appendChild(tr);

	modal_clear(id);
}

function add_list_row(num) {
	console.log("add_list_row(num): "+ num)
}
function remove_list_row(num) {
	$id('list_count_'+ num).outerHTML = "";
}

function show_markdown() {
	$id('md_preview').style.display = "none";
	$id('md').style.display = "";
}

function validate_list() {
	return validate({'title':'Collection Name'});
}

modal_init("modal_tabs");

</script>
<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional ƒHP Functions
##################################################

##################################################
#	EOF
##################################################
?>

<?php
##################################################
#   Javascript Functions
##################################################
// ob_start();
?>

<script type="text/javascript">
/*
var parent_object;
var modal_section;
var submit_bool = false;
// var information = {
// 	"lists": {}
// 	,"compendiums": {}
// }


// function display_compenidum_assets(id,key) {
// 	assets[key] = information['lists'][key];
// 	hide_compendium_content();
// 	$id("content-"+ modal_section +"-"+ key).innerHTML = build_display(key).trim();
// 	show("content-"+ modal_section +"-"+ key);
// }

// function hide_compendium_content() {
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
var info = {
	"name": "Default"
	,"color": "blue"
	,"active": true
};
add_compendium_buttons(info);
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
*/
</script>


<?php
// $js = trim(ob_get_clean());
// if(!empty($js)) { add_js_code($js); }

##################################################
#   Additional PHP Functions
##################################################


##################################################
#   EOF
##################################################