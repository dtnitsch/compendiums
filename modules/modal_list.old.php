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
<div id="simple_modal" class="modal">
	<!-- Modal content -->
	<div class="modal_outer">
		<div class='modal_header'>
			<span class="close" onclick="hide('simple_modal')">&times;</span>
			Search for a List
		</div>
		<div class="modal_inner">
			<div id="modal_details">
			<table cellpadding="0" cellspacing="0" border="0" class="basic_table">
				<tr>
					<td id="modal_list_page" style="padding-right: 10px;">&nbsp;</td>
					<td style="width: 200px;">
						<div>
							Search
							<input type="text" id="modal_search" placeholder="Search Lists" onkeyup="modal_search(this.value)">
							<div style="position:relative; top: 0px; left: 0px;" id="modal_search_results"></div>
						</div>

						<div>
							<h3>Newest Lists</h3>
							<ul>
<?php
	$output = "";
	while($row = db_fetch_row($top_5_lists)) {
		$output .= '<li><a href="javascript:void(0);" onclick="modal_list_page(\''. $row['key'] .'\');">'. $row['title'] .'</a></li>';
	}
	echo $output;
?>					
							</ul>
						</div>
						<div>
							<h3>My Favorite Lists</h3>
							<ul>
								<li><a href="javascript:void(0);" onclick="modal_list_page('One');">One</a></li>
								<li><a href="javascript:void(0);" onclick="modal_list_page('Two');">Two</a></li>
								<li><a href="javascript:void(0);" onclick="modal_list_page('Three');">Three</a></li>
							</ul>
						</div>
						<div>
							<h3>Popular Lists</h3>
							<ul>
								<li><a href="javascript:void(0);" onclick="modal_list_page('One');">One</a></li>
								<li><a href="javascript:void(0);" onclick="modal_list_page('Two');">Two</a></li>
								<li><a href="javascript:void(0);" onclick="modal_list_page('Three');">Three</a></li>
							</ul>
						</div>
					</td>
				</tr>
			</table>


			<input type="button" id="add_list_button" value="Add List" style='display: none;' onclick="add_new_list('simple_modal')">
			<input type="button" id="add_multi_button" value="Add Multi-List" style='display: none;' onclick="add_new_multi_list('simple_modal')">
			<span id="mutli-titles"></span>
			<div class="clear"></div>


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
	modal_init("simple_modal");
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