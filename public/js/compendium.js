function build_all_display(id,options) {
	var output = '';
	var options = options || {};
	used_multis = {};
	for(key in assets['lists']) {
		output += build_display(assets['lists'][key],options);
	}
	$id(id).innerHTML = output.trim();
}

function build_display(info,options) {
	var output = '<div>';
	
	if(info.is_multi) {
		// already used this multi part?  Skip it
		if(typeof used_multis['used_'+ info.connected] != "undefined") {
			return "";
		}
		// make sure this multi actually exists
		if(typeof assets['is_multi'][info.connected] == "undefined") {
			return "";
		}
		// set used multi to not repeat it
		used_multis['used_'+ info.connected] = 1;

		output += '<div class="mt"><strong>'+ info['list_label'] +'</strong></div>';
		output += '<ol class="list_ordered" id="list_body_'+ info['list_key'] +'">';
        output += build_multi_assets(assets['is_multi'][info.connected]);
        output += '</ol>';

	} else if(info.tables) {
		output += '<table cellspacing="0" cellpadding="0" class="tbl mt">';
        output += '<thead><tr><th>';
        output += info.assets[0][0].split('|').join('</th><th>')
        output += '</th></tr></thead>';
        output += '<tbody id="list_body_'+ info.list_key +'">';
        output += build_table_assets(info);
        output += '</tbody></table>';

    } else {
		// if(typeof options.show_header != "undefined" && options.show_header) {
			output += '<div class="mt"><strong>'+ info['list_title'] +'</strong></div>';
		// }
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

function build_multi_assets(lists) {
	var shuffled_lists = [];
	var output = '';
	var display = '';
	var display_limit = 0;
	for(i in lists) {
		list = lists[i];
		shuffled_lists[shuffled_lists.length] = filter_asset_list(assets['lists'][list]);
		display_limit = assets['lists'][list]['display_limit'];
	}
	for(var i=0,len=display_limit; i<len; i++) {
		display = [];
		for(list in shuffled_lists) {
			pos = i % shuffled_lists[list].length;
			// console.log("shuffled_lists[list]["+i+"]: ", shuffled_lists[list])
			display[display.length] = parse_random(shuffled_lists[list][pos][0]);
		}
		output += '<li>'+ display.join(" ") +'</li>';
	}
	
	return output;	
}

function filter_asset_list(arr) {
	var list = []

	modal_section = (typeof get_current_tab == "function" ? get_current_tab() : '');

	var randomize = (modal_section ? 'randomize-'+ modal_section +'-' : 'randomize_');
	var limit = (modal_section ? 'limit-'+ modal_section +'-' : 'limit_');
	var filters_dynamic = (modal_section ? 'filters-dynamic-'+ modal_section +'-' : 'filters_');

	var limit = parseInt($id(limit + arr.list_key) ? $id(limit + arr.list_key).value : arr.display_limit);
    var randomize = ($id(randomize + arr.list_key) ? $id(randomize + arr.list_key).checked : arr.randomize);
    var filters = get_filters(filters_dynamic + arr.list_key);
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

function build_filters(info,filter_ids) {
	var filter_ids = filter_ids || {};
	var limit_id = filter_ids['limit_id'] || "limit_";
	var randomize_id = filter_ids['randomize_id'] || "randomize_";
	var filter_id = filter_ids['filter_id'] || "filters_";
	// 1. Set Limit
	set_limit_display(limit_id + info['list_key'],info['display_limit']);
	// 2. Randomize by default?
	set_randomize(randomize_id + info['list_key'],info['randomize']);
	// 3. build filter list
	set_filters_list(filter_id + info['list_key'],info['filters']);
}
function set_limit_display(id,limit) {
	$id(id).value = limit;
}
function set_randomize(id,checked) {
	$id(id).checked = (checked ? true : false);
}
function set_filters_list(id,arr) {
	var output = '<div class="mb" id="'+ id +'">';
	var cnt = 0;
	for(i in arr) {
		output += '<label for="'+ id +'-'+ cnt +'">';
		output += '<input type="checkbox" id="'+ id +'-'+ cnt +'" name="filters['+ i +']" onclick="filter_list(current_asset,\'listcounter\')" value="'+ i +'"> '+ arr[i];
		output += '</label> &nbsp;'; 
		cnt += 1;
	}
	output += '</div>';
	$id("filters_dynamic").innerHTML = output;
}
