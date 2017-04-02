var serialize_options = {
    'input': {'text':1,'hidden':1,'password':1,'button':1,'submit':1,'checkbox':2,'radio':2,'file':0,'reset':0,'x':0}
    ,'textarea': {'textarea':1,'x':0}
    ,'select': {'select-one':1,'select-multiple':3,'x':0}
    ,'button': {'reset':1,'submit':1,'button':1,'x':0}
}

function $id(x) { return document.getElementById(x) || false; }
function $tag(x) { return document.getElementsByTagName(x) || false; }
function $class(x) { return document.querySelector(set_class_dot(x)) || false; }
function $classes(x) { return document.querySelectorAll(set_class_dot(x)) || false; }
function $query(x) { return document.querySelectorAll(x) || false; }

function $error(msg) { console.log("Error: "+ msg); return false; }

function set_class_dot(x) { return (x[0] != "." ? "."+x : x); }
function isfunction(x) { return typeof x == "function"; }

function addclass(elem,classname) {
    if(typeof elem == 'string') { elem = $id(elem); }
    var list = elem.className;
    if(list == '' || typeof list == 'undefined') {
        list = classname;
    } else if(list.trim() != '') {
    	var re = new RegExp('(^| )'+ classname +'( |$)');
    	if(list.match(re)) { return true; }
        list += ' '+ classname;
    }
    elem.className = list;
    return true;
}
function removeclass(elem,classname) {
    if(typeof elem == 'string') { elem = $id(elem); }
    var list = elem.className;
    if(list) {
        var re = new RegExp('(^| )'+ classname +'( |$)','g');
        list = list.replace(re,' ').trim();
        elem.className = list;
    }
    return true;
}

/*
	Options and Examples:
	ajax('/ajax.php',{
		async: true
		,callback: _function_name_
		,debug: false ... or function_name - ajax_debugger
		,type: string (optional: xml, json, string)
		,method: post
		,params: post
	});
*/
function ajax(url,info) {
	var url = (typeof url == 'undefined' ? '' : url.trim());
	if(url == '') { return $error('A URL must be supplied!'); }

	info = info || {};
	info.debug = info.debug || ajax_debugger;
	info.async = info.async || true;
	info.callback = info.callback || '';
    info.callbackData = info.callbackData || '';
	info.success = info.success || '';
    info.type = info.type || 'string';
    info.type = info.type.toLowerCase()
	info.method = info.method || 'POST';
    info.method = info.method.toUpperCase()
    if (info.method == "POST") {
        info.headers = info.headers || {
    		"X-Requested-With": "XMLHttpRequest"
    		,"Content-type": "application/x-www-form-urlencoded"
            // ,'Access-Control-Allow-Headers', '*'
    	    // ,'Access-Control-Allow-Origin', '*'
    	}
    } else {
        info.headers = '';
    }


	info.data = info.data || '';
	info.data = encodeURI(build_http_query(info.data));
	info.data = "ajax_call_type="+ info.type +'&'+ info.data;

	return run_ajax(url,info);
}

function run_ajax(url,info) {
	var ajax = new XMLHttpRequest();

	// if(info['method'].toUpperCase() == "GET") {
	// 	url += "?"+ info['data'];
	// }
	url += "?"+ info.data;
	ajax.open(info.method, url, info.async);

	// Send the proper header information along with the request
	if(Object.keys(info.headers).length) {
		for(i in info.headers) {
			ajax.setRequestHeader(i,info.headers[i]);
		}
	}
	ajax.onreadystatechange = function() {
		if(ajax.readyState == 4 && ajax.status == 200) {
			var r = ajax.responseText;
			if(info.type == 'json') { r = JSON.parse(r); }
			if(isfunction(info.debug)) {
				r.debug = r.debug || '';
				info.debug(r.debug,ajax.responseText.length);
				r.debug = null;
			}
			if(info.callback) {
                // info.callback[0].apply(this, r.concat(info.callback.slice(1)));
                info.callback(r, info.callbackData);
            }
			if(isfunction(info.success)) { info.success(r); }
		}
	}
	ajax.send(info.data);
}

function show(elem) {
	if(typeof elem == 'string') { elem = $id(elem); }
	if(elem) { elem.style.display = 'inline'; }
}
function hide(elem) {
	if(typeof elem == 'string') { elem = $id(elem); }
	if(elem) { elem.style.display = 'none'; }
}
function show_hide(elem) {
	if(typeof elem == 'string') { elem = $id(elem); }
	if(elem.style.display == '' || elem.style.display == 'inline') { elem.style.display = 'block'; }
	elem.style.display = (elem.style.display == 'none' ? 'block' : 'none');
}
function show_hide_display(elem,show_hide_obj,show_text,hide_text) {
    show_hide(elem)
	if(typeof elem.style == 'undefined') {
		elem.style.display == "block";
	}
    show_hide_obj.innerHTML = (elem.style.display == "none" ? show_text : hide_text);
}
function remove_this(obj) { obj.parentNode.parentNode.style.display='none'; }

function build_http_query(params) {
	var t = typeof params, output = "";
	if(t == 'string') { return params; }
	else if(t == 'object' && typeof params[0] != "undefined") {
		return params.join("&");
	} else if(t == 'object') {
		for(var i in params) { output += i +"="+ params[i] +'&'; }
		return output.slice(0,-1);
	}
}

function build_post_variables(id) {
    var params = "";
    if($id(id)) {
	    var elems = $id(id).elements;
	    var current, i, len;
	    for(i=0, len = elems.length; i<len; i++) {
	        current = elems[i];

	        if(current.type == 'button' || current.type == 'submit') { continue; }
	        if(current.type == 'checkbox' || current.type == 'radio') {
	            if(current.checked) { params += current.name +'='+ current.value +'&'; }
	        } else {
	            params += current.name +'='+ current.value + '&';
	        }
	    }
	    // Remove the leading '&'
		return params.slice(0,-1);
    }
    return params;
}

function serialize (form) {
    'use strict';
    var i, j, len, jLen, elem, type, q = [];
    function encode (str) {
        // http://kevin.vanzonneveld.net
        // Tilde should be allowed unescaped in future versions of PHP (as reflected below), but if you want to reflect current
        // PHP behavior, you would need to add ".replace(/~/g, '%7E');" to the following.
        // return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').
        //     replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
        return encodeURIComponent(str);
    }
    function param(e, type) {
        if(type == 1) {
            q[q.length] = encode(e.name) + '=' + encode(e.value);
        } else if(type == 2) {
            if (e.checked) {
                q[q.length] = encode(e.name) + '=' + encode(e.value);
            }
        } else if(type == 3) {
            for (j = 0, jLen = e.options.length; j < jLen; j++) {
                if (e.options[j].selected) {
                    param(e,1);
                }
            }
        }
    }
    if(typeof form == 'string') { form = $id(form); }
    for (i = 0, len = form.elements.length; i < len; i++) {
        elem = form.elements[i];
        if (elem.name === '' || elem.disabled) {
            continue;
        }
        name = elem.nodeName.toLowerCase();
        type = elem.type || 'x';
        param(elem,serialize_options[name][type]);
    }
    return build_http_query(q);
}

