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
		output += `<li style='border: 1px solid #ccc; border-top: none; padding: 2px 4px; background: #fff;'><a href="javascript:void(0);" onclick="modal_show_preview('`+ info.key +`');">`+ info.title +`</a></li>`;
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

	if(list_simple_template == "") {
		list_simple_template = $id('list_simple_details').innerHTML;
		$id('list_simple_details').innerHTML = "";
	}
	current_asset = res.output;
	var template = list_simple_template;
	template = template.replace(/{{title}}/g,res.output.list_title);
	template = template.replace(/{{key}}/g,res.output.list_key);
	$id('modal_preview_box').innerHTML = template;

	show('add_list_button');
	if($id('add_multi_button')) { show('add_multi_button') }
	
	show_build_display('listcounter');
}

// function add_new_list(id) {
//     var limit = parseInt($id('limit_'+ current_asset.list_key) ? $id('limit_'+ current_asset.list_key).value : current_asset.display_limit);
//     var randomize = ($id('randomize_'+ current_asset.list_key) ? $id('randomize_'+ current_asset.list_key).checked : current_asset.randomize);
// 	add_list(returned_info,limit,randomize);

// 	modal_clear(id);
// }


var list_keys = [];
var original_rows = {};


function reset_modal() {
	list_keys = [];
	original_rows = {};
	$id('modal_preview_box').innerHTML = '';
	modal_show_search();
}