// Global list of filters
var list_filters = {}

function preview_list(id,output_id,filter_table_id) {
	var lines = $id(id).value.trim().split("\n");
	var filters, inner, elem, inner;
	var is_table = (lines[0].indexOf('|') != -1 ? true : false);
	var html = '';

	// Wipe the filters list and re-load it
	list_filters = {}

	if(is_table) {
		html = build_preview_table(lines);
	} else {
		html = build_preview_simple(lines);
	}

	if(html) {
		$id(filter_table_id).style.display = 'none';
		if(build_filters_table(filter_table_id,output_id)) {
			$id(filter_table_id).style.display = '';
		}
		$id(output_id).innerHTML = html;
	}

	// $info(output)

}

function build_elements(pieces,elem) {
	inner = "";
	for(var i=0,len=pieces.length; i<len; i++) {
		inner += '<'+ elem +'>'+ pieces[i].trim() +'</'+ elem +'>';
	}
	return inner;
}

function clean_filters(val) {
	output = [];
	if(val == undefined) {
		return "";
	}
	tags = val.split(',');
	for(var i=0,len=tags.length; i<len; i++) {
		tag = slug(tags[i]);

		output[output.length] = tag
		list_filters[tag] = tags[i].trim();
	}
	return output;
}


function build_preview_table(lines) {

	var html = '';
	for(var i=0,len=lines.length; i<len; i++) {
		current_line = lines[i].trim();
		// ignore empty spaces
		if(current_line == '') {
			continue;
		}

		// Split lines by filters
		pieces = current_line.split(';');

		// Clean the filters if we have any
		filters = clean_filters(pieces[1]);

		table_pieces = pieces[0].split('|');
		inner = "";

		// If we have not yet created a "Header" section...
		if(html == "") {
			html = '<table cellspacing="0" cellpadding="0" class="tbl"><thead><tr>';
			html += build_elements(table_pieces,"th")
			html += '</tr></thead><tbody>';
			continue;
		}

		inner = build_elements(table_pieces,"td")
		if(filters != '') {
			filters = ' data-filters="'+ filters.join(' ') +'"';
		}
		html += '<tr'+ filters +'>'+ inner +'</tr>';
	}
	html += "</tbody></table>"

	return html;
}

function build_preview_simple(lines) {

	var html = "<ol>";
	for(var i=0,len=lines.length; i<len; i++) {
		current_line = lines[i].trim();
		// ignore empty spaces
		if(current_line == '') {
			continue;
		}

		// Split lines by filters
		pieces = current_line.split(';');

		// Clean the filters if we have any
		filters = clean_filters(pieces[1]);

		if(filters != '') {
			filters = ' data-filters="'+ filters.join(' ') +'"';
		}
		html += '<li'+ filters +'>'+ pieces[0].trim() +'</li>';
	}
	html += "<ol>";

	return html;
}








// ---------------------------------------------------------
// Filters are awkward -- we don't want to lose all the filters every time a change is made...
// ---------------------------------------------------------

filters = list_filters;
function build_filters_table(filter_table_id,example_list_id) {
	filters = list_filters;
	var cnt = Object.keys(filters).length;
	if(!Object.keys(filters).length) {
		return false;
	}
	cnt = 0;
	var template = `
		<tr data-key="{{key}}">
			<td class="onepercent">
				<label for="filter_{{cnt}}">
				<input type="checkbox" id="filter_{{cnt}}" name="filters[{{key}}]" onclick="filter_list('{{filter_table_id}}','{{example_list_id}}')" value="{{key}}">
				</label>
			</td>
			<td><input type="text" name="filter_labels[{{key}}]" value="{{filter_key}}"></td>
			<td>{{key}}</td>
			<td><input type="text" name="filter_order[{{key}}]" value="{{cnt}}" class="xs"></td>
		</tr>
	`;
	var output = "";
	for(key in filters) {
		tmp = template.replace(/{{cnt}}/g,cnt);
		tmp = tmp.replace(/{{key}}/g,key);
		tmp = tmp.replace(/{{filter_table_id}}/g,filter_table_id);
		tmp = tmp.replace(/{{example_list_id}}/g,example_list_id);
		tmp = tmp.replace(/{{filter_key}}/g,filters[key]);
		output += tmp
		cnt += 5;
	}

	$id('filters_table_tbody').innerHTML = output;
	output = null;

	return true;
}


function filter_list(filter_table_id,example_list_id) {
	checked = get_checked_filters(filter_table_id);
	var show_all = (Object.keys(checked).length ? 0 : 1);
	elems = $query('#'+ example_list_id +' [data-filters]')

	var show;
	for(var i=0,len=elems.length; i<len; i++) {
		show = false;
		if(show_all) {
			elems[i].style.display = "";
			continue;
		}
		for(filter in checked) {
			r = new RegExp('(^|\\s)'+ filter + '(\\s|$)');
			if(r.test(elems[i].dataset.filters)) {
				show = true;
				break;
			}
		}
		elems[i].style.display = (show ? "" : "none");
	}
}

function get_checked_filters(key) {
	var filters = $query('#'+ key +' input[type=checkbox]:checked');
	var checked = [];
	for(var i=0,len=filters.length; i<len; i++) {
		checked[filters[i].value] = 1;
	}
	return checked;
}


//
// function filters_table_tbody_values() {
// 	var trs = $id('filters_table_tbody').getElementsByTagName('tr');
// 	var filters = {};
//
// 	for(var i=0,len=trs.length; i<len; i++) {
// 		if(trs[i].dataset.key) {
// 			filters[trs[i].dataset.key] = 1;
// 		}
// 	}
// 	return filters;
// }
//
// function delete_filters_table_tbody_values(tags) {
// 	var trs = $id('filters_table_tbody').getElementsByTagName('tr');
//
// 	for(var i=0,len=trs.length; i<len; i++) {
// 		if(trs[i] != undefined && tags[trs[i].dataset.key] != undefined) {
// 			trs[i].parentNode.removeChild(trs[i]);
// 		}
// 	}
// }















function build_all_display() {
	var output = '';
	var keys = Object.keys(assets)
	for(k in keys) {
		key = keys[k];
		output += build_display(key);
	}
	$id('listcounter').innerHTML = output.trim();
}


function build_display(key) {
	var output;

	if(assets[key].tables) {
		okeys = Object.keys(assets[key].assets);

		output = `
 		<table cellspacing="0" cellpadding="0" class="tbl mb">
 			<thead>
 				<tr>
 					<th>`+ assets[key].assets[okeys[0]][0][0].split('|').join('</th><th>') +`</th>
 				</tr>
 			</thead>
 			<tbody id="list_body_`+ key +`">
 			`+ fetch_table_assets(key) +`
 			</tbody>
 		</table>
		`;
	} else {

		output = `
			<ol class="list_ordered" id="list_body_`+ key +`">
				`+ fetch_list_assets(key) +`
			</ol>
		`;
	}
	return output;
	// $id('listcounter').innerHTML = output;
}

function fetch_list_assets(key) {
	var keys = get_keys(key);
	var output = '';
	for(var i=0,len=keys.length; i<len; i++) {
		// output += '<ol>'+ (keys[i][0].split("|").join("</td><td>")) +'</ol>';
		output += '<ol>'+ keys[i] +'</ol>';
	}
	return output;
}

function get_keys(key) {
	var limit = parseInt(assets[key].display_limit ? assets[key].display_limit : $id('limit_'+ key).value);
	var randomize = assets[key].randomize ? assets[key].randomize : $id('randomize_'+ key).checked;

	if(randomize) {
		return random_keys(key);
	}
	return serial_list(key);
}

function serial_list(key) {
	// console.log("key")
	var filters = get_filters(key);
	var arr = build_filtered_list(key,filters);
	var limit = (assets[key].display_limit ? assets[key].display_limit : $id('limit_'+ key).value);
	// var asset_length = arr.length;

	output = []
	for(i in arr[0]) {
		if(output.length >= limit) {
			break;
		}
		output.push(arr[0][i][0])
	}

	// $id('filter_count').innerHTML = filters.length +" applied";
	return output;
}

function get_filters(list_key) {
	var filters = $query('#custom_filters_'+ list_key +' input[name^=filter]');
	var checked = [];
	for(var i=0,len=filters.length; i<len; i++) {
		if(filters[i].checked) {
			checked[checked.length] = filters[i].value;
		}
	}
	return checked;
}


function build_filtered_list(key,checked) {
	var okeys = Object.keys(assets[key].assets);
	if(assets[key].filter_count == 0 || checked.length == 0) {
		var arr = [];
		for(i in okeys) {
			if(assets[key].tables) {
				arr.push(assets[key].assets[okeys[0]].slice(1));
			} else {
				arr.push(assets[key].assets[okeys[i]])
			}
		}
		// var len = (assets[key].display_limit ? assets[key].display_limit : assets[key].filter_count);
		return arr;
	}

	var filtered_arr = [[]];
	var and_or = ($id('filter_or').checked ? "or" : "and");
	var a = assets[key].assets[okeys[0]];

	for(var i=(assets[key].tables ? 1 : 0),len=a.length; i<len; i++) {
		if(filter_criteria(and_or, checked, JSON.parse(a[i][1]))) {
			// filtered_arr[filtered_arr.length] = assets[key].assets[i];
			// console.log("A:")
			// console.log(i)
			// console.log(a[i])
			filtered_arr[0][filtered_arr[0].length] = a[i];
		}
	}
	// console.log(filtered_arr)
	return filtered_arr;
}


function random_keys(key) {
	var used_keys = {};
	var keys = [];
	var limit = parseInt(assets[key].display_limit ? assets[key].display_limit : $id('limit_'+ key).value);
	var min = assets[key].tables || 0;
	var filters = get_filters(key);
	var arr = build_filtered_list(key,filters);
	var asset_length = arr[0].length;

	$id('filter_count').innerHTML = filters.length +" applied";

	if(limit >= asset_length) {
		limit = asset_length;
	}

	var new_array = shuffle(arr[0].slice(0));

	keys = [];
	for(var i=0; i<limit; i++) {
		keys[keys.length] = new_array[i][0];
	}
	return keys;
}

function fetch_table_assets(key) {
	var keys = get_keys(key);
	var output = '';
	okeys = Object.keys(assets[key].assets);

	for(var i=0,len=keys.length; i<len; i++) {
		output += `<tr>
			<td>`+ parse_random(keys[i].split("|").join("</td><td>")) +`</td>
		</tr>`;
	}
	return output;
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
	var markdown = document.getElementById(id || 'markdown');
	markdown.innerHTML = micromarkdown.parse(html);
}
