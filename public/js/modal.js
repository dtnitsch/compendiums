function modal_init(id) {
	id = id || "modal_tabs";
	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
		var modal = $id(id);
		if (event.target == modal) {
			modal_clear(id);
		}
	}
}

function display_modal(id) {
	id = id || "simple_modal";
	if(typeof reset_modal == "function") { reset_modal(); }
	$id(id).style.display = "block";
}


function modal_clear(id) {
	id = id || "modal_tabs";
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

function add_new_list(id) {
	var limit = $query("[id^=limit_]")[0].value;
	var checked = $query("[id^=randomize_]")[0].checked;
	if(returned_info_multi.length) {
		add_list(returned_info_multi,limit,checked);	
	} else {
		add_list(returned_info,limit,checked);
	}
	
	modal_clear(id);
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