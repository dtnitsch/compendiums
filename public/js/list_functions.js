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

function get_filters() {
	var filters = $query('#custom_filters input[name^=filter]');
	var checked = [];
	for(var i=0,len=filters.length; i<len; i++) {
		if(filters[i].checked) {
			checked[checked.length] = filters[i].value;
		}
	}
	return checked;
}

function build_all_lists() {
	var id,list_rows,row,i;
	for(key in list_keys) {
		id = 'list_body_'+ list_keys[key];
		build_list(id,list_keys[key]);
	}
}
function build_list(id,key) {
	var id = id || 'list_body';
    var list_rows = ($id(id).rows ? $id(id).rows : $query('#'+ id +' li'));

	var limit = ($id(id).dataset && $id(id).dataset.limit ? parseInt($id(id).dataset.limit) : $id('limit').value);
	var randomize = ($id(id).dataset && $id(id).dataset.randomize ? parseInt($id(id).dataset.randomize) : $id('randomize').checked);

	var checked;

	if(limit < 0 || limit > list_rows.length) {
		limit = list_rows.length;
	}

	if(randomize) {
		shuffle_rows(id,key);
	} else {
		reset_table(id,key);
	}

	checked = get_filters();
	r = new RegExp('(^|\\s)('+ checked.join("|") +')(\\s|$)');

	cnt = 0;
	for(var i=0; i<list_rows.length; i++) {
		if(checked.length == 0) {
			list_rows[i]
			list_rows[i].style.display = (cnt < limit ? "" : "none");
			list_rows[i].innerHTML = parse_random(list_rows[i].innerHTML);
			cnt += 1;
		} else {
			if(r.test(list_rows[i].dataset.filters)) {
				list_rows[i].style.display = (cnt < limit ? "" : "none");
				list_rows[i].innerHTML = parse_random(list_rows[i].innerHTML);
				cnt += 1;
			} else {
				list_rows[i].style.display = "none";
			}
		}
	}
}

function parse_random(s) {
	var re = /\[(\d*[D|d]\d+)\](:\d+)?/g;
	var m, pieces, total;
	var s2 = s;

	do {
	    m = re.exec(s2);
	    if (m) {
	        pieces = m[1].toLowerCase().split("d");

	        min = (!parseInt(pieces[0]) ? 1 : parseInt(pieces[0]));
	        max = parseInt(pieces[1]);
	        total = 0;
	        while(min--) {
	        	total += rand(max);
	        }
	        s = s.replace(m[0],'['+ m[1]+"]:"+total);
	    }
	} while (m);
	return s
}