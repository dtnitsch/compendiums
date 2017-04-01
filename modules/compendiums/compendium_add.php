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
add_js("list_functions.js",10);

##################################################
#   Content
##################################################
?>
<style type="text/css">


.tag_table { border-right: 1px solid #ccc; width: 100%; }
.tag_table td { border-bottom: 1px solid #ccc; border-left: 1px solid #ccc; padding: 10px; }
.tag_table td.tag_nav { background: #eee; border-left: 1px solid #ccc; width: 230px; }

</style>


<div class='clearfix'>
	<h2 class='compendiums'>Add Compendium</h2>
  
  	<?php echo dump_messages(); ?>
	<form id="addform" method="post" action="" onsubmit="return submit_form();">

		<label class="form_label" for="title">Compendium Name <span>*</span></label>
		<div class="form_data">
			<input type="text" name="title" id="title" value="">
		</div>

		<input type="button" value="Create Compendium Tab" onclick="display_modal('modal_tabs');">

		<div id="compendium_buttons" class="w3-bar w3-black mt">
			<!--button class="w3-bar-item w3-button tablink w3-red" onclick="open_compendium_tab(this,'default')">Default</button>
			<input type="hidden" name="sections[default]" value="Default" /-->
		</div>
		<div id="compendium_bodies">

			<!--div id="default" class="w3-container w3-border city" style="display: block;">
				<table cellpadding="0" cellspacing="0" class='tag_table'>
					<tr>
						<td>
							Testing 2
						</td>
						<td class="tag_nav">
							<button onclick="search_for_list(this,'default')">Add List</button>
							<button onclick="search_for_collection(this,'default')">Add Collection</button>
						</td>
					</tr>
				</table>
			</div-->

		</form>
	</div>

	<div class="mt">
		<input type="submit" value="Save Compendium" onclick="read_for_submit();">
	</div>
</div>



<?php echo run_module("modal_tabs"); ?>
<?php echo run_module("modal_list"); ?>

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

function submit_form() {
	return submit_bool;
}

function read_for_submit() {
	submit_bool = true;
	$id('addform').submit();
}

function display_modal(id) {
	id = id || "simple_modal";
	modal_init(id);
	if(typeof reset_modal == "function") { reset_modal(); }
	$id(id).style.display = "block";
}

function search_for_list(obj,section) {
	parent_object = obj;
	modal_section = section;
	modal_init("simple_modal");
	if(typeof reset_modal == "function") {
		reset_modal();
	}
	$id("simple_modal").style.display = "block";
	$id('modal_search').focus();
}
modal_init("modal_tabs");

function open_compendium_tab(evt, cityName) {
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
	btn.onclick = function() { open_compendium_tab(this,info.name); }
	btn.innerHTML = info.name;
	var alias = slug(info.name,'_');

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
						<button onclick="search_for_list(this,'`+ alias +`')">Add List</button>
						<button onclick="search_for_collection(this,'`+ alias +`')">Add Collection</button>
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
	modal_clear('simple_modal');
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