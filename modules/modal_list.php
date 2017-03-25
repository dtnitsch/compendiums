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
				<div class="float_right">
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
				</div>

			</div>

			<div id="modal_list_page" class="float:left;">
				<div class='listcounter' id="listcounter"></div>
			</div>

			<input type="button" id="add_list_button" value="Add List" style='display: none;' onclick="add_new_list()">
			<input type="button" id="add_multi_button" value="Add Multi-List" style='display: none;' onclick="add_new_multi_list()">
			<span id="mutli-titles"></span>
			<div class="clear"></div>


		</div> <!-- end modal inner -->
	</div>
</div>

	
<?php
ob_start();
?>
<style>
/* The Modal (background) */
.modal {
	display: none; /* Hidden by default */
	position: fixed; /* Stay in place */
	z-index: 1; /* Sit on top */
	padding-top: 100px; /* Location of the box */
	left: 0;
	top: 0;
	width: 100%; /* Full width */
	height: 100%; /* Full height */
	background-color: rgb(0,0,0); /* Fallback color */
	background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
	/*overflow: auto;*/ /* Enable scroll if needed */
	/*max-height: 100%;*/
	overflow-y: auto;
	max-height: calc(100vh - 100px);
}

/* Modal Content */
.modal_outer {
	background-color: #fefefe;
	margin: auto;
	padding: 5px;
	width: 80%;
	background-color: rgb(0,0,0); /* Fallback color */
	background-color: rgba(0,0,0,0.1); /* Black w/ opacity */
}
.modal_inner {
	background-color: #fefefe;
	margin: auto;
	padding: 10px;
	border: 1px solid #666;
	border-top: none;
}

/* The Close Button */
.close {
	color: #aaaaaa;
	float: right;
	font-size: 20px;
	font-weight: bold;
}

.close:hover,
.close:focus {
	color: #000;
	text-decoration: none;
	cursor: pointer;
}
.modal_header {
	font-size: 130%;
	border: 1px solid #666;
	background: #eee;
	color: #666;
	padding: 10px;
}
</style>
<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_css_code($js); }


##################################################
#	Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">
function modal_init(id) {
	id = id || "simple_modal";
	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
		var modal = $id(id);
		if (event.target == modal) {
			modal_clear(id);
		}
	}
}
modal_init();

function modal_clear(id) {
	id = id || "simple_modal";
	$id('modal_search').value = "";
	$id('modal_search_results').innerHTML = "";
	$id(id).style.display = "none";
}

var search_cache = {}
var search_data = ""
function modal_search(val) {
	if(val.trim() == "") {
		$id('modal_search_results').innerHTML = "";
		return;
	}
	search_data = "apid=bca4b7dad46a1d984ec7975274671955&val="+ val;
	if(typeof search_cache[search_data] == "undefined") {
		ajax('/ajax.php',{
			type: 'json'
			,data: search_data
			,success: parse_modal_search
		});
	} else {
		parse_modal_search(search_cache[search_data],true)
	}
}
function parse_modal_search(data,cached) {
	// console.log(data.output)

	output = "<ul style='border-top: 1px solid #ccc; margin: 0; padding: 0; list-style-type: none;'>";
	for(var i=0,len=data.output.length; i<len; i++) {
		info = data.output[i]
		output += `<li style='border: 1px solid #ccc; border-top: none; padding: 2px 4px; background: #fff;'><a href="javascript:void(0);" onclick="modal_list_page('`+ info.key +`');">`+ info.title +`</a></li>`;
	}
	output += "</ul>";
	$id('modal_search_results').innerHTML = output;
	if(!cached)	{
		search_cache[search_data] = data;
	}
}


function modal_list_page(val) {
	if(val.trim() == "") {
		return;
	}
	search_data = "apid=ff15890b1815ec8d9eaf91ad22a5286e&val="+ val;
	ajax('/ajax.php',{
		type: 'json'
		,data: search_data
		,success: display_modal_list_page
	});
}
function display_modal_list_page(res) {
	$id('modal_list_page').innerHTML = res.output.html;
	list_keys = [res.output.info.id];
	returned_info = res.output.info;
	set_original_rows();
	$id('add_list_button').style.display = "";
	$id('add_multi_button').style.display = "";
}

function add_new_list() {
	var limit = $id("limit").value;
	var checked = $id("randomize").checked;
	if(returned_info_multi.length) {
		add_list(returned_info_multi,limit,checked);	
	} else {
		add_list(returned_info,limit,checked);
	}
	
	modal_clear();
}

function add_new_multi_list() {
	var limit = $id("limit").value;
	var checked = $id("randomize").checked;
	returned_info_multi[returned_info_multi.length] = {returned_info,limit,checked};

	var output = $id('mutli-titles').innerHTML;
	if(output != "") {
		output += ", ";
	}
	$id('mutli-titles').innerHTML = output + returned_info['title'];
}

var list_keys = [];
var original_rows = {};
var returned_info = {}
var returned_info_multi = []


function reset_modal() {
	list_keys = [];
	original_rows = {};
	returned_info = {}
	returned_info_multi = []
	$id('mutli-titles').innerHTML = '';
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