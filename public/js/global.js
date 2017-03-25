/* Generic set of JS that will be used often around the site */

//window.onload = function(){ function_name1(); function_name2(); };

function $id(x) { return document.getElementById(x) || false; }
function $tag(x) {
	x = x || false;
	// if(!x) { return false; }
	return (x.toString() ? document.getElementsByTagName(x) : false);
}
function $class(x) {
	x = x || false;
	if(!x) { return false; }
	return document.querySelector(set_class_dot(x)) || false;
}
function $classes(x) {
	x = x || false;
	if(!x) { return false; }
	return document.querySelectorAll(set_class_dot(x)) || false;
}
function set_class_dot(x) {
	if(x[0] != ".") { return "."+x; }
	return x;
}
function $query(x) { return document.querySelector(x) || false; }
function $queryAll(x) { return document.querySelectorAll(x) || false; }

function isfunction(x) {
	var z = typeof x;
	if(z == "function") { return true; }
	if(z == "string" && typeof window[x] == "function") { return true; }
	return false;
}
function isarray(x) { return x instanceof Array; }
function isset(x) { return (typeof(x)=='undefined' || x===null ? false : true); }
function emptyobject(obj) {
	for(var prop in obj) {
        if(obj.hasOwnProperty(prop))
            return false;
    }
    return true;
}
function addclass(elem,classname) {
    if(typeof elem == 'string') { elem = $id(elem); }
    var list = elem.className || '';
    if(list != '') {
    	var re = new RegExp('(^| )'+ classname +'( |$)');
    	if(list.match(re)) { return false; }
    }
    list += ' '+ classname;
    elem.className = list.trim();
    return true;
}
function removeclass(elem,classname) {
    if(typeof elem == 'string') { elem = $id(elem); }
    var list = elem.className || '';
    var re = new RegExp('(^| )'+ classname +'( |$)','g');
    list = list.replace(re,' ').trim();
    elem.className = list;
    return true;
}

/*
	Options and Examples:
	ajax('/ajax.php',{
		async: true
		,callback: _function_name_
		,debug: false
		,type: string (optional: xml, json, string)
		,method: post
		,params: post
	});
*/
function ajax(info) {
	var url = (typeof info['url'] == 'undefined' ? '' : info['url'].trim());
	if(url == '') {
		alert('A URL must be supplied!');
		return;
	}
	var tmp = '';
	var async = (typeof info['async'] != 'undefined' ? info['async'] : true);
	var callback = (typeof info['callback'] != 'undefined' ? info['callback'] : '');
	var debug = (typeof info['debug'] != 'undefined' ? info['debug'] : false);
	var type = (typeof info['type'] != 'undefined' ? info['type'].toLowerCase() : 'string');
	var success = (typeof info['success'] != 'undefined' ? info['success'] : '');
	var method = (typeof info['method'] != 'undefined' ? info['method'] : 'post');
	info['data'] = (typeof info['data'] != 'undefined' ? info['data'] + "&ajax_call_type="+ type : "ajax_call_type="+ type);
	data = encodeURI(build_http_query(info['data']));

	if(typeof method != 'undefined') {
		tmp = method.toUpperCase().trim();
		if(tmp == 'GET' || tmp == 'POST') { method = tmp; }
	}

	return run_ajax(url,method,data,async,callback,type,debug,success);
}

function start_ajax() {
	try {
		return new XMLHttpRequest(); // All Others
	} catch (e) {
		// Internet Explorer
		try { return new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try { return new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {
				alert("Your browser does not support AJAX!");
			}
		}
	}
	return false;
}

function run_ajax(url,method,data,async,callback,type,debug,success) {
	var ajax = start_ajax();
	if(ajax) {
		if(method.toUpperCase() == "GET") {
			url += "?"+ data;
		}
		ajax.open(method, url, async);

		//Send the proper header information along with the request
		ajax.setRequestHeader("X-Requested-With", "XMLHttpRequest");
		ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

		ajax.onreadystatechange = function() {
			if(ajax.readyState == 4 && ajax.status == 200) {
				var r = ajax.responseText;
				if(type == 'json') { r = JSON.parse(r);	}
				if(debug && typeof r.debug != "undefined" && r.debug) {
					if(typeof ajax_debugger == "function" && typeof r['debug'] != 'undefined') {
						ajax_debugger(r['debug'],r.length);
						// console.log(JSON.stringify(r['output']).length)
						r['debug'] = null;
					}
				}
				if(callback != '') { callback(r); }
				if(typeof success == 'function') { success(r); }
				else { return r; }
			}
		}
		ajax.send(data);
	}
}

function build_http_query(params) {
	var t = typeof params;
	if(t == 'string') { return params; }
	else if(t == 'array') {
		output = '';
		len = params.length;
		i=0;
		while(i<len) { output += params[i++] +'&'; }
		return output.slice(0,-1);
	} else if(t == 'object') {
		output = '';
		for(var key in params) { output += key +"="+ params[key] +'&'; }
		return output.slice(0,-1);
	}
}

function show(elem) {
	if(typeof elem == 'string') { elem = $id(elem); }
	elem.style.display = 'inline';
}
function hide(elem) {
	if(typeof elem == 'string') { elem = $id(elem); }
	elem.style.display = 'none';
}
function show_hide(elem) {
	if(typeof elem == 'string') { elem = $id(elem); }
	if(elem.style.display == '' || elem.style.display == 'inline') { elem.style.display = 'inherit'; }
	elem.style.display = (elem.style.display == 'none' ? 'inherit' : 'none');
}

function remove_this(obj) { obj.parentNode.parentNode.style.display='none'; }

function build_post_variables(id) {
    var params = "";
    if($id(id)) {
	    var elems = $id(id).elements;
	    var len = elems.length;
	    var i = 0;
	    while(i < len) {
	        var current = elems[i];

	        if(current.type == 'button' || current.type == 'submit') { i++; continue; }
	        if(current.type == 'checkbox' || current.type == 'radio') {
	            if(current.checked) { params += "&"+ current.name +'='+ current.value; }
	        } else {
	            params += "&"+ current.name +'='+ current.value;
	        }
	        i++;
	    }
	    // Remove the leading '&'
		return params.substring(1);
    }
    return params;
}

function serialize (form) {
    'use strict';
    var i, j, len, jLen, formElement, q = [];
    function urlencode (str) {
        // http://kevin.vanzonneveld.net
        // Tilde should be allowed unescaped in future versions of PHP (as reflected below), but if you want to reflect current
        // PHP behavior, you would need to add ".replace(/~/g, '%7E');" to the following.
        return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').
            replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
    }
    function addNameValue(name, value) {
        q.push(urlencode(name) + '=' + urlencode(value));
    }
    if(typeof form == 'string') { form = $id(form); }
    if (!form || !form.nodeName || form.nodeName.toLowerCase() !== 'form') {
        throw 'You must supply a form element';
    }
    for (i = 0, len = form.elements.length; i < len; i++) {
        formElement = form.elements[i];
        if (formElement.name === '' || formElement.disabled) {
            continue;
        }
        switch (formElement.nodeName.toLowerCase()) {
        case 'input':
            switch (formElement.type) {
            case 'text':
            case 'hidden':
            case 'password':
            case 'button': // Not submitted when submitting form manually, though jQuery does serialize this and it can be an HTML4 successful control
            case 'submit':
                addNameValue(formElement.name, formElement.value);
                break;
            case 'checkbox':
            case 'radio':
                if (formElement.checked) {
                    addNameValue(formElement.name, formElement.value);
                }
                break;
            case 'file':
                // addNameValue(formElement.name, formElement.value); // Will work and part of HTML4 "successful controls", but not used in jQuery
                break;
            case 'reset':
                break;
            }
            break;
        case 'textarea':
            addNameValue(formElement.name, formElement.value);
            break;
        case 'select':
            switch (formElement.type) {
            case 'select-one':
                addNameValue(formElement.name, formElement.value);
                break;
            case 'select-multiple':
                for (j = 0, jLen = formElement.options.length; j < jLen; j++) {
                    if (formElement.options[j].selected) {
                        addNameValue(formElement.name, formElement.options[j].value);
                    }
                }
                break;
            }
            break;
        case 'button': // jQuery does not submit these, though it is an HTML4 successful control
            switch (formElement.type) {
            case 'reset':
            case 'submit':
            case 'button':
                addNameValue(formElement.name, formElement.value);
                break;
            }
            break;
        }
    }
    return q.join('&');
}