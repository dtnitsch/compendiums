function build_all_display(id) {
	var output = '';
	for(key in assets['lists']) {
		output += build_display(assets['lists'][key]);
	}
	$id(id).innerHTML = output.trim();
}

function build_display(info,options) {
	var output = '<div>';
	// if(typeof options != "undefined" && typeof options.show_labels != "undefined") {
	// 	let filters = '';
	// 	filters += (filters ? ', ' : '') + "Display Limit: "+ asset_display(key,'display_limit');
	// 	filters += (filters ? ', ' : '') + "Randomize: "+ (asset_display(key,'randomize') ? 'Yes' : 'No');
	// 	filters = '<div class="float_right small">'+ filters +'</div>';
	// 	output = `<div class="mt" style="border: 1px solid #ccc; background: #fff; padding: 3px 5px; cursor: pointer;" onclick="show_hide('collection_`+ key +`')">`+ filters + assets[key]['list_label'] +`</div><div id="collection_`+ key +`">`;
	// }

	if(info.tables) {
		output += '<table cellspacing="0" cellpadding="0" class="tbl mb">';
        output += '<thead><tr><th>';
        output += info.assets[0][0].split('|').join('</th><th>')
        output += '</th></tr></thead>';
        output += '<tbody id="list_body_'+ info.list_key +'">';
        output += build_table_assets(info)
        output += '</tbody></table>';

    } else {
		output += '<ol class="list_ordered" id="list_body_'+ info['list_key'] +'">';
        output += build_list_assets(info)
        output += '</ol>';

	}
	output += '</div>';
	return output;
}

function build_table_assets(arr) {
	var list = filter_asset_list(arr);
	
	var output = '';
	for(var i=0,len=list.length; i<len; i++) {
		output += '<tr data-filters="'+ list[i][1] +'">';
		output += '<td>'+ parse_random(list[i][0].split("|").join("</td><td>")) +'</td>';
		output += '</tr>';
	}
	return output;
}


function build_list_assets(arr) {
    var list = filter_asset_list(arr);
    
	var output = '';
	for(var i=0,len=list.length; i<len; i++) {
		output += '<li data-filters="'+ list[i][1] +'">'+ parse_random(list[i][0]) +'</li>';
	}
	return output;
}

function filter_asset_list(arr) {
	var list = []

    var limit = parseInt($id('limit_'+ arr.list_key) ? $id('limit_'+ arr.list_key).value : arr.display_limit);
    var randomize = ($id('randomize_'+ arr.list_key) ? $id('randomize_'+ arr.list_key).checked : arr.randomize);
    var filters = get_filters('filters_'+ arr.list_key);
    var list = build_filtered_list(arr,filters);

    // 4. randomize if needed 
    if(randomize) {
        list = shuffle(list)
    }
    
	// 5. Limit list if needed
    list = limit_list(list,limit);
    
	return list;
}

function get_filters(id) {
	var filters = $query('#'+ id +' input[name^=filter]:checked');
	var checked = [];
	for(var i=0,len=filters.length; i<len; i++) {
		if(filters[i].checked) {
			checked[checked.length] = filters[i].value;
		}
	}
	return checked;
}


function build_filtered_list(arr,checked) {
	if(arr.filter_count == 0 || checked.length == 0) {
		return arr.assets.slice(arr.tables);
	}

	var list = [];
	var existing = [];

	for(var i=arr.tables,len=arr.assets.length; i<len; i++) {
		for(filter in arr.assets[i][1]) {
            if(checked.indexOf(arr.assets[i][1][filter]) != -1) {
				if(typeof existing[arr.assets[i]] == "undefined") {
					list[list.length] = arr.assets[i];
					existing[arr.assets[i]] = 1;
				}
            }
        }
    }

	return list;
}

function limit_list(arr,limit) {
    var len = arr.length;

    if(limit >= len) {
        limit = len;
    }
    
    return arr.slice(0,limit);
}


// Random Dice Roller
function parse_random(s) {
	var re = /\[(\d*[D|d]\d+)\](:\d+)?/g;
	var m, pieces, total;
	var s2 = s;
	
	m = s.split(/(\[\d*[D|d]\d+\])/);
	for(var i=0,len=m.length; i<len; i++) {
		if(m[i][0] == "[") {
			s2 = m[i].substring(1,m[i].length - 1)
			pieces = s2.toLowerCase().split("d");
			min = (!parseInt(pieces[0]) ? 1 : parseInt(pieces[0]));
			max = parseInt(pieces[1]);
			total = 0;
			while(min--) {
				total += rand(max);
			}
			m[i] += ":"+ total;
		}
		if(m[i][0] == ":" && is_numeric(m[i][1])) {
			m[i] = m[i].replace(/\:\d+/,'');
		}
	}

	return m.join("");
}


function filter_list(arr,id) {
	$id(id).innerHTML = build_display(arr);
}

// ---------------------------------------------------------
// Markdown Functions
// ---------------------------------------------------------

function parse_markdown(id,preview) {
	var markdown = document.getElementById(id || 'markdown');
	var preview = preview || 'preview';
	if(!document.getElementById(preview)) {
		markdown.innerHTML = micromarkdown.parse(markdown.innerHTML.trim());
		return true;
	}
	var markdown = markdown.value;
	var preview = document.getElementById(preview);
	preview.innerHTML = micromarkdown.parse(markdown);
}

function parse_markdown_html(id,html) {
	if(html == "") {
		html = "No Description Available";
	}
	var markdown = document.getElementById(id || 'markdown');
	markdown.innerHTML = micromarkdown.parse(html);
}

// ---------------------------------------------------------
// Page Functions
// ---------------------------------------------------------
function show_build_display(id,bf) {
	var bf = (typeof bf == "undefined" ? true : false);
	if(bf) {
		build_filters(current_asset);
	}
	$id(id).innerHTML = build_display(current_asset);
}

function build_filters(info) {
	// 1. Set Limit
	set_limit_display("limit_"+ info['list_key'],info['display_limit']);
	// 2. Randomize by default?
	set_randomize("randomize_"+ info['list_key'],info['randomize']);
	// 3. build filter list
	set_filters_list(info['list_key'],info['filters']);
}
function set_limit_display(id,limit) {
	$id(id).value = limit;
}
function set_randomize(id,checked) {
	$id(id).checked = (checked ? true : false);
}
function set_filters_list(id,arr) {
	var output = '<div class="mb" id="filters_'+ id +'">';
	var cnt = 0;
	for(i in arr) {
		output += '<label for="filter_'+ cnt +'">';
		output += '<input type="checkbox" id="filter_'+ cnt +'" name="filters['+ i +']" onclick="filter_list(current_asset,\'listcounter\')" value="'+ i +'"> '+ arr[i];
		output += '</label> &nbsp;'; 
		cnt += 1;
	}
	output += '</div>';
	$id("filters_dynamic").innerHTML = output;
}
