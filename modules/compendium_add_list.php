<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

##################################################
#	Validation
##################################################

##################################################
#	DB Queries
##################################################
$q = "select title,key from public.list where active order by id desc limit 5 ";
$top_5_lists = db_query($q,"Getting Top 5 Lists");

// $q = "select title,key from public.collection where active order by id desc limit 5 ";
// $top_5_collections = db_query($q,"Getting Top 5 Collections");


##################################################
#	Pre-Content
##################################################
// $info = (!empty($_POST) ? $_POST : array());
add_js('modal.js');
add_css('modal.css');
// add_js("markdown.min.js");


##################################################
#	Content
##################################################
?>
<!-- The Modal -->
<div id="compendium_add_list_modal" class="modal">
	<!-- Modal content -->
	<div class="modal_outer">
		<div class='modal_header'>
			<span class="close" onclick="hide('compendium_add_list_modal')">&times;</span>
			Append a List!@#
		</div>

		<div class="modal_inner">
			<div id="modal_details">
				<div id="modal_search">
				<div>
					Search
					<input type="text" id="modal_search" placeholder="Search Lists" onkeyup="modal_search(this.value)">
					<div style="position:relative; top: 0px; left: 0px;" id="modal_search_results"></div>
				</div>
				<div class="clear"></div>

				<div style="float: left; width: 30%;">
					<h3>Newest Lists</h3>
					<ul>
<?php
	$output = "";
	while($row = db_fetch_row($top_5_lists)) {
		$output .= '<li><a href="javascript:void(0);" onclick="modal_show_preview(\''. $row['key'] .'\');">'. $row['title'] .'</a></li>';
	}
	echo $output;
?>					
					</ul>
				</div>
				<div style="float: left; width: 30%; border-left: 1px solid #ccc; padding-left: 10px;">
					<h3>My Favorite Lists</h3>
					<ul>
						<li><a href="javascript:void(0);" onclick="modal_show_preview('One');">One</a></li>
						<li><a href="javascript:void(0);" onclick="modal_show_preview('Two');">Two</a></li>
						<li><a href="javascript:void(0);" onclick="modal_show_preview('Three');">Three</a></li>
					</ul>
				</div>
				<div style="float: left; width: 30%; border-left: 1px solid #ccc; padding-left: 10px;">
					<h3>Popular Lists</h3>
					<ul>
						<li><a href="javascript:void(0);" onclick="modal_show_preview('One');">One</a></li>
						<li><a href="javascript:void(0);" onclick="modal_show_preview('Two');">Two</a></li>
						<li><a href="javascript:void(0);" onclick="modal_show_preview('Three');">Three</a></li>
					</ul>
				</div>
				<div class="clear"></div>

				<input type="button" id="add_list_button" value="Add to Compendium" style='display: none;' onclick="add_new_list('compendium_add_list_modal')">
				<div class="clear"></div>

			</div> <!-- Search and Lists -->
			<div id="modal_show_preview">
				<div id="modal_preview_box" class="mb" >Preview</div>
				<input type="button" value="Back to Search" onclick="modal_show_search();">
				<input type="button" value="Add to Compendium" onclick="add_compendium_list()">
			</div>
		</div> <!-- end modal inner -->
	</div>
</div>

	
<?php

##################################################
#	Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">
	var assets = {};
	// modal_init("compendium_add_list_modal");
    hide("modal_show_preview");
	function modal_show_search() {
		hide("modal_show_preview");
		show("modal_search");
	}
	function modal_show_preview(key) {
		// $id("modal_preview_box").innerHTML = key;
		modal_list_page(key);
		hide("modal_search");
		show("modal_show_preview");
    }
    function add_compendium_list() {
		// console.log(returned_info);
		// console.log(modal_section)
		key = Object.keys(assets)[0];
		// console.log(assets)

		$id("content-"+modal_section).innerHTML += `
			<div id="content-`+ modal_section +`-`+ key +`"></div>
		`;

		information['lists'][key] = assets[key];
		$id("nav-"+ modal_section).innerHTML += `
		<div onclick="display_compenidum_assets('listcounter_master','`+ key +`')" style="padding: 10px;border-bottom: 1px solid black; cursor: pointer;">
			`+ returned_info.title +`
		</div>
		`;

    }
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