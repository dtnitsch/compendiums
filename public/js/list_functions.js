function example(e,force) {
	var force = force || false;
	// 13 = enter, 8 = backspace
	if(!force && (e.keyCode != 13 && e.keyCode != 8)) {
		return;
	}
	var pieces = $id('inputs').value.trim().split("\n");
	var filters, inner, elem, inner;
	var is_table = (pieces[0].indexOf('|') != -1 ? true : false);
	var html = '';

	for(var i=0,len=pieces.length; i<len; i++) {
		if(pieces[i].trim() == '') {
			continue;
		}
		inner = '';
		inner_pieces = pieces[i].split(';');

		if(is_table) {
			table_pieces = inner_pieces[0].split('|');
			elem = (i == 0 ? 'th' : 'td');
			for(var j=0,jlen=table_pieces.length; j<jlen; j++) {
				inner += '<'+ elem +'>'+ table_pieces[j].trim() +'</'+ elem +'>';
			}
			if(i == 0) {
				inner += '</thead><tbody>';
			}
		}

		filters = '';
		if(inner_pieces[1] != undefined) {
			tags = inner_pieces[1].split(',');
			for(var j=0,jlen=tags.length; j<jlen; j++) {
				tag = tags[j].trim();
				filters += slug(tag,'_') +' ';
			}
			if(filters != '') {
				filters = ' data-filters="'+ filters.trim() +'"';
			}
		}

		html += (is_table ? '<tr'+ filters +'>'+ inner.trim() +'</tr>' : '<li'+ filters +'>'+ inner_pieces[0].trim() +'</li>');
	}

	if(html.trim() != '') {
		var output = '<div id="filter_examples">';

		output += (is_table ? '<table cellspacing="0" cellpadding="0" class="tbl"><thead>' : '<ol class="mt">');
		output += html;
		output += (is_table ? '</tbody></table>' : '</ol>');
		output += '</div>';
		$id('example').innerHTML = output;
		if(build_filters_table()) {							
			$id('filters_table').style.display = '';
		}
	}
}


function unique_tags() {
	var pieces = $id('inputs').value.trim().split("\n");
	var inner_pieces, tags, tag;
	var output = {};
	for(var i=0,len=pieces.length; i<len; i++) {
		inner_pieces = pieces[i].split(';');
		if(inner_pieces[1] != undefined) {
			tags = inner_pieces[1].split(",");
			for(var j=0,jlen=tags.length; j<jlen; j++) {
				tag = slug(tags[j],'_');
				output[tag] = tags[j];
			}
			} else {
			continue;
		}
	}
	return output;
}  


function build_filters_table() {
	var tags = unique_tags();
	var existing_tag = filters_table_tbody_values();
	var rem_tags = filters_table_tbody_values();
	var cnt = Object.keys(tags).length;
	var tr;
	if(cnt == 0) {
		return false;
	}

	var cnt = 0;
	for(key in tags) {
		key = key.trim();
		if(!key) { continue; }
		alias = slug(key,'_');
		// console.log(alias)
		if(existing_tag[alias] == undefined) {
			tr = document.createElement('tr');
			tr.setAttribute('data-key',alias);
			tr.innerHTML = `
				<td>
					<label for="filter_`+ cnt +`">
					<input type="checkbox" id="filter_`+ cnt +`" name="filters[`+ alias +`]" onclick="filter_list('`+ alias +`')" value="`+ alias +`">
					</label>
				</td>
				<td><input type="text" name="filter_labels[`+ key +`]" value="`+ tags[key] +`"></td>
				<td>`+ key +`</td>
				<td><input type="text" name="filter_order[`+ key +`]" value="`+ cnt +`" class="xs"></td>
			`;
			$id('filters_table_tbody').appendChild(tr);
		} else {
			delete rem_tags[alias];
		}
		cnt += 5;
	}

	if(Object.keys(rem_tags).length) {
		delete_filters_table_tbody_values(rem_tags);
	}
	return true;
}


function filters_table_tbody_values() {
	var trs = $id('filters_table_tbody').getElementsByTagName('tr');
	var filters = {};

	for(var i=0,len=trs.length; i<len; i++) {
		if(trs[i].dataset.key) {
			filters[trs[i].dataset.key] = 1;
		}
	}
	return filters;
}


function delete_filters_table_tbody_values(tags) {
	var trs = $id('filters_table_tbody').getElementsByTagName('tr');

	for(var i=0,len=trs.length; i<len; i++) {
		if(trs[i] != undefined && tags[trs[i].dataset.key] != undefined) {
			trs[i].parentNode.removeChild(trs[i]);
		}
	}
}



function filter_list(key) {
	var filters = $query('#filters_table input[type=checkbox]');
	var filters_length = filters.length;
	var checked = [];
	for(var i=0,len=filters.length; i<len; i++) {
		if(filters[i].checked) {
			checked[checked.length] = filters[i].value;
		}
	}
	elems = $query('#filter_examples [data-filters]')

	var show;
	var checked_length = checked.length;
	for(var i=0,len=elems.length; i<len; i++) {
		show = (checked_length == 0 ? true : false);
		for(j in checked) {
			r = new RegExp('(^|\\s)'+ checked[j] + '(\\s|$)');
			if(r.test(elems[i].dataset.filters)) {
				show = true;
				break;
			}
		}				
		elems[i].style.display = (show ? "" : "none");
	}
}


function open_tabs(evt, tabname, type) {
	if(!document.getElementById('preview') && document.getElementById('markdown')) {
		parse_markdown();
	}
	var i, x, tablinks;
	var type = type || 'list';
	x = document.getElementById(type +'_bodies').getElementsByClassName("tabs");
	for (i = 0; i < x.length; i++) {
		x[i].style.display = "none";
	}
	tablinks = document.getElementById(type +'_buttons').getElementsByClassName("tablink");
	for (i = 0; i < x.length; i++) {
		tablinks[i].className = tablinks[i].className.replace(" active", ""); 
	}
	document.getElementById(tabname).style.display = "block";
	evt.className += " active";
}

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

function shuffle_array(arr) {
    for (var i = arr.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var temp = arr[i];
        arr[i] = arr[j];
        arr[j] = temp;
    }
    return arr;
}
function double_shuffle_array(arr) {
	return shuffle_array(shuffle_array(arr))
}

function double_shuffle(id) {
	id = id || 'list_body';
	shuffle_all_rows();
	shuffle_all_rows();
}
function shuffle_all_rows() {
	var id,list_rows,row,i;
	for(key in list_keys) {
		id = 'list_body_'+ list_keys[key];
		shuffle_rows(id);
	}
}
function shuffle_rows(id) {
	var id = id || 'list_body';
    var list_rows = ($id(id).rows ? $id(id).rows : $query('#'+ id +' li'));
    var rows = new Array();
	var row;
	for (var i=list_rows.length-1; i>=0; i--) {
	    row = list_rows[i];
	    rows.push(row);
	    row.parentNode.removeChild(row);
    }
    shuffle(rows);
    for (i=0; i<rows.length; i++) {
    	$id(id).appendChild(rows[i]);
	}
}
function reset_all_tables() {
	var id,list_rows,row,i;
	for(key in list_keys) {
		id = 'list_body_'+ list_keys[key];
		reset_table(id,list_keys[key]);
	}
}
function reset_table(id,key) {
	var id = id || 'list_body';
    var list_rows = ($id(id).rows ? $id(id).rows : $query('#'+ id +' li'));
	var row;
	for (i=list_rows.length-1; i>=0; i--) {
	    row = list_rows[i];
	    row.parentNode.removeChild(row);
    }
    for (i=original_rows[key].length - 1; i >= 0; i--) {
    	$id(id).appendChild(original_rows[key][i]);
	}
}
function set_original_rows() {
	var id,list_rows,row,i;
	for(key in list_keys) {
		id = 'list_body_'+ list_keys[key];
		original_rows[list_keys[key]] = [];
	    list_rows = ($id(id).rows ? $id(id).rows : $query('#'+ id +' li'));
		for (i=list_rows.length-1; i>=0; i--) {
		    row = list_rows[i];
		    original_rows[list_keys[key]].push(row);
	    }
	}
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

function build_all_lists(list_key) {
	var id,list_rows,row,i;
	for(key in list_keys) {
		id = 'list_body_'+ list_keys[key];
		build_list(id,list_keys[key],list_key);
	}
}
function build_list(id,key,list_key) {
	var id = id || 'list_body';
    var list_rows = ($id(id).rows ? $id(id).rows : $query('#'+ id +' li'));

	var limit = ($id(id).dataset && $id(id).dataset.limit ? parseInt($id(id).dataset.limit) : $id('limit_'+ list_key).value);
	var randomize = ($id(id).dataset && $id(id).dataset.randomize ? parseInt($id(id).dataset.randomize) : $id('randomize_'+ list_key).checked);
	var filter_count = 0;
	var checked;
	var and_or, r, display;

	if(limit < 0 || limit > list_rows.length) {
		limit = list_rows.length;
	}

	if(randomize) {
		shuffle_rows(id,key);
	} else {
		reset_table(id,key);
	}

	checked = get_filters(list_key);
	filter_count = checked.length;
	if(filter_count) {
		and_or = ($id('filter_or').checked ? "or" : "and");
		// if(and_or == "or") {
		// 	r = new RegExp('(^|\\s)('+ checked.join("|") +')(\\s|$)');
		// }
	}
	$id('filter_count').innerHTML = filter_count +" applied";
	
	cnt = 0;
	for(var i=0; i<list_rows.length; i++) {
		// No filters
		if(checked.length == 0) {
			list_rows[i].style.display = (cnt < limit ? "" : "none");
			list_rows[i].innerHTML = parse_random(list_rows[i].innerHTML);
			cnt += 1;
		} else {
			// Some filters
			// console.log("---")
			// console.log(and_or)
			// console.log(checked)
			// console.log(list_rows[i].dataset.filters.split(" "))
			display = "none";
			// console.log(filter_criteria(and_or, checked, list_rows[i].dataset.filters.split(" ")))
			if(cnt < limit && filter_criteria(and_or, checked, list_rows[i].dataset.filters.split(" "))) {
				// console.log("---")
				// console.log(list_rows[i])
				// console.log(and_or)
				// console.log(checked)
				// console.log(list_rows[i].dataset.filters.split(" "))
				list_rows[i].innerHTML = parse_random(list_rows[i].innerHTML);
				display = "";
				cnt += 1;
			}
			list_rows[i].style.display = display;
		}
	}
	paint_rows(id);
}

function filter_criteria(and_or,list1,list2) {
	var map = {};
	// var pieces = list2.split(" ");
	var matches = 0;

	// console.log(and_or)
	// console.log(list1)
	// console.log(list2)

	// No need to check, they can't be the same
	if(and_or == "and" && list1.length > list2.length) {
		return false;
	}

	for(var i=0,len=list1.length; i<len; i++) {
		map[list1[i]] = 1;
	}
	for(var i=0,len=list2.length; i<len; i++) {
		if(typeof map[list2[i]] != "undefined") {
			matches += 1;
		}
	}

	if(and_or == "or" && matches > 0) {
		return true
	} else if(and_or == "and" && matches == list1.length ) {
		return true;
	}
	return false;
}

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

		// s = s.replace(m[i],'['+ m[i]+"]:"+total);
		// s = s.replace(m[0],'['+ m[1]+"]:"+total);
	}
	// do {
	//     m = re.exec(s2);
	//     console.log(m)

	//     if (m) {
	//         pieces = m[1].toLowerCase().split("d");

	//         min = (!parseInt(pieces[0]) ? 1 : parseInt(pieces[0]));
	//         max = parseInt(pieces[1]);
	//         total = 0;
	//         while(min--) {
	//         	total += rand(max);
	//         }
	//         s = s.replace(m[0],'['+ m[1]+"]:"+total);
	//     }
	// } while (m);
	return m.join("");
}






// function generate_all_lists() {
// 	var id,list_rows,row,i;
// 	for(key in list_keys) {
// 		id = 'list_body_'+ list_keys[key];
// 		generate_lists(id,list_keys[key]);
// 	}
// }

// function generate_lists(id,key) {
// 	var obj;
// 	var output = '';
// 	var length, a, t, i, is_table, f_body;
// 	var used = {};
// 	var data = [];

// 	// console.log(id)
// 	// console.log(key)
// 	// console.log(assets)
// 	// console.log(assets[key])

// 	for(k in assets[key]) {
// 		// console.log(assets[key])
// 		// console.log(assets[key][k])
// 		// console.log(assets[key][k].length)
// 		output = '';
// 		// data = [];
// 		used = [];
// 		obj = $id(id);
// 		// console.log(obj)
// 		// return;
// 		limit = parseInt(assets[key][k].length < obj.dataset.limit ? assets[key][k].length : obj.dataset.limit);
// 		length = parseInt(assets[key][k].length) - 1;
// 		is_table = (assets[key][k][0].indexOf('|') != -1 ? true : false);
// 		// for(var i=0,len=limit; i<len; i++) {
// 		// tmp = parseInt(length)
// 		min = (is_table ? 1 : 0);
// 		xxx = 0;
// 		len = 0;

// 		// console.log(limit)
// 		// console.log(length)
// 		// console.log(is_table)

// 		while(len < limit) {
// 			r = rand(1,length);
// 			t = JSON.parse(tags[key][k][r]);
// 			a = assets[key][k][r];

// 			len = Object.keys(used).length + 1;
// 			// console.log(Object.keys(used).length)

// 			// console.log(a)
// 			// console.log(k)
// 			// console.log(r)
// 			// console.log(used[a])
// 			// return;
// 			xxx += 1
// 			if(xxx > 20) {
// 				console.log(limit)
// 				console.log(used)
// 				console.log("ENDED")
// 				return;
// 			}
// 			if(typeof used[a] != "undefined") {
// 				// limit += 1;
// 				continue;
// 			}
// 			used[a] = 1;
// 			if(typeof data[len] == "undefined") {
// 				data[len] = "";
// 			}
// 			data[len] += parse_random(a) +' ';

// 			f_body = (Object.keys(t).length ? t.join(" ") : '')
// 			if(is_table) {
// 				a = a.split("|").join("</td><td>")

// 				output += `
// 					<tr data-filters="`+ f_body +`">
// 						<td>{{`+ len +`}}</td>
// 					</tr>
// 				`;
// 			} else {
// 				output += `
// 					<li data-filters="`+ f_body +`">
// 						{{`+ len +`}}
// 					</li>
// 				`;				
// 			}

// 		}
// 	}
// console.log(data)
// console.log(data.length)
// 	// for(var i=0, len=data.length; i<len; i++) {
// 	for(i in data) {
// 		if(is_table) {
// 			data[i] = data[i].split("|").join("</td><td>")
// 		}
// 		output = output.replace("{{"+ i +"}}", data[i]);
// 	}

// 	$id(id).innerHTML = output;
// }




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

	// console.log(arr)
	// console.log(asset_length)
	// if(limit >= asset_length) {
	// 	// console.log("###")
	// 	while(asset_length--) {
	// 		keys[keys.length] = arr[0][asset_length][0];
	// 		// console.log(asset_length)
	// 	}
	// 	return shuffle_array(keys);
	// } else {
		// console.log("!!!")
		while(keys.length < limit) {
			tmp = [];
			val = '';
			for(i in arr) {
				tmpk = rand(1,arr[i].length - 1);
				tmp.push(tmpk);
				val += arr[i][tmpk][0] +" ";
				// console.log(arr[i][tmpk][0])
			}
			k = tmp.join('_');
			// console.log(k)
			// console.log(val.trim())
			// console.log("-------")
			if(used_keys[k] == undefined) {
				used_keys[k] = 1;
				keys[keys.length] = val.trim();
			}
			// console.log("KL: "+ keys.length)
		}
		return keys;
	// }
}

function build_filtered_list(key,checked) {
	// var checked = get_filters(key);
	// console.log('build_filtered_list')
	// console.log(assets[key])
	// console.log(assets[key].assets)
	// console.log(checked)
	// return;
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


function fetch_table_assets(key) {
	var keys = get_keys(key);
	var output = '';
	okeys = Object.keys(assets[key].assets);
	// console.log('fetch_table_assets')
	// console.log(keys)

	for(var i=0,len=keys.length; i<len; i++) {
		output += `<tr>
			<td>`+ parse_random(keys[i].split("|").join("</td><td>")) +`</td>
		</tr>`;
	}
	return output;
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

function build_all_display() {
	var output = '';
	var keys = Object.keys(assets)
	for(k in keys) {
		key = keys[k];
		output += build_display(key);
	}
	$id('listcounter').innerHTML = output.trim();
}


