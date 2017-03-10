/*
	The Javascript needed for the show hide functions within the debugger
*/
function _showhide_debug(uid) {
	uid = (typeof uid != 'undefined' ? uid : '');
	var di = document.getElementById('debuginfo'+uid);
	if(di.style.display == 'none' || di.style.display == '') {
		di.style.display = 'block';
	} else {
		di.style.display = "none";
	}
}

function _showhide_context(val) {
	var con = document.getElementById('display_context_'+val);
	if(con.className == 'hide_context') {
		con.className = 'context';
	} else {
		con.className = "hide_context";
	}
}

function _showhide_backtrace(val) {
	var con = document.getElementById('display_backtrace_'+val);
	if(con.className == 'hide_backtrace') {
		con.className = 'backtrace';
	} else {
		con.className = "hide_backtrace";
	}
}

function bytes_to_size(bytes) {
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes == 0) return 'n/a';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
};

function number_with_commas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

var context_cnt = 0;
function ajax_debugger(debug,payload_size) {
	if(typeof payload_size == 'undefined') { payload_size = '0'; }
    var output = '';
    //output += "<div class='error_debug_box'>";
    output += "<div class='error_debug_title' onclick='_showhide_debug(\""+ debug['unique'] +"\");'>";
    output += "<strong>AJAX Debug Information</strong>: ("+ debug['error_counter'] +")";
    output += " -- <strong>Page Load Time</strong>: "+ debug['page_load_time'].toFixed(4);
    
    output += " -- <strong>Query Load Times</strong> ("+  debug['total_queries'] +" queries) ";
    output += debug['total_query_time'].toFixed(4);

    output += " -- <strong>Total Memory Usage</strong>: "+ debug['memory_usage'];
    output += " -- <strong>Payload Size</strong>: "+ bytes_to_size(payload_size) +" ("+ number_with_commas(payload_size) +")";
    output += "</div>";

	output += "<table cellspacing='0' cellpadding='0' id='debuginfo"+ debug['unique'] +"' class='error_debug_table'>";
	output += "<tr>";

//	var cnt = 0;
	for(var i in debug['all_errors']) {
		x = debug['all_errors'][i];

		context = '';
		if(typeof x['ContextCode'] == 'object') {
			var debug_counter = 0;
			for(var j in x['ContextCode']) {
				debug_counter++;
				context += "<div>"+ debug_counter +": ";
				context += "<strong>"+ j +"</strong>: ";


				if(typeof x['ContextCode'][j] != 'number' && (typeof x['ContextCode'][j] == 'array' || typeof x['ContextCode'][j] == 'object' || x['ContextCode'][j].length)) {
					// Dump Array?
					context += _dump(x['ContextCode'][j]);
				} else {
					context += x['ContextCode'][j];
				}
				context += "<div>";
			}
		} else if(typeof x['ContextCode'] == 'string') {
			context = x['ContextCode'];
		}

		output += "<tr>";
		output += "<td class='error_type "+ debug['error_css'][x['Level']] +"'>"+ x['Level'] +"</td>";

		backtrace = '';
		if(typeof x['Backtrace'] == 'string') {
			backtrace = x['Backtrace'];
			output += "<td class='show_more' id='backtrace_ajax"+ context_cnt +"'><a href='javascript:_showhide_backtrace(\"ajax"+ context_cnt +"\")'>Backtrace</a></td>";
		}

		output += "<td class='show_more' id='show_more_"+ context_cnt +"'>"+ (context != '' ? "<a href='javascript:_showhide_context(\"ajax"+ context_cnt +"\")'>Show More</a>" : '') +"</td>";
		output += "<td class='error_details'><span class='undln'>"+ x['Message'] + (x['count'] > 0 ? ' <span class="error_debug_redtext">('+ x['count'] +'x times)</span>' : '') +"</span><span class='page_line'><strong>File</strong>: "+ x['File'] +" - <strong>Line</strong>: "+ x['Line'] +"</span></td>";
		output += "</tr>";

		if(backtrace != '') { 
			output += "<tr>";
			output += "<td id='display_backtrace_ajax"+ context_cnt +"' class='hide_backtrace' colspan='4'><pre>"+ backtrace +"</pre></td>";	
			output += "</tr>";
		}
		if(context != '') { 
			output += "<tr>";
			output += "<td id='display_context_ajax"+ context_cnt +"' class='hide_context' colspan='4'><pre>"+ context +"</pre></td>";
			output += "</tr>";
		}

		//cnt++;
		context_cnt++;
	}
	output += "</table>";

	var div = document.createElement('div');
	div.innerHTML = output;
	div.style.marginTop = "10px";

	$classes('error_debug_box')[0].appendChild(div);

}

function _dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;
	
	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";
	
	if(typeof(arr) == 'object') { //Array/Hashes/Objects 
		for(var item in arr) {
			var value = arr[item];
			
			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...";
				dumped_text += _dump(value,level+1);
			} else {
				dumped_text += "\n"+ level_padding + "'" + item + "' => \"" + value + "\"";
			}
		}
	} else { //Stings/Chars/Numbers etc.
		dumped_text = arr;
	}
	return dumped_text;
}