var asl = {};
var asl_current_sort = {}
var asl_templates = {};
var asl_sorts = { 'asc': '&#x25B2;', 'desc': '&#x25BC;' }
var cached_sort = {};
var pagination = {};



function asl_sort_setup(info) {
    
    id = (typeof info['id'] != 'undefined' ? info['id'] : 'asl_sort');
    asl[id] = {}
    a = asl[id];
    a['id'] = id;
    a['url'] = (typeof info['url'] != 'undefined' ? info['url'].trim() : '/ajax.php');
    //a['apid'] = (typeof info['apid'] != 'undefined' ? info['apid'] : '');
    a['display_count'] = (typeof info['display_count'] != 'undefined' ? info['display_count'] : 20);
    a['column'] = (typeof info['column'] != 'undefined' ? info['column'] : '');
    a['order'] = (typeof info['order'] != 'undefined' ? info['order'] : '');
    a['filters'] = (typeof info['filters'] != 'undefined' ? info['filters'] : '');
    a['debug'] = (typeof info['debug'] != 'undefined' ? info['debug'] : false);
    a['callback'] = (typeof info['callback'] != 'undefined' ? info['callback'] : '');
    // options: sortlist, pagination (Default: sortlist)
    a['type'] = (typeof info['type'] != 'undefined' ? info['type'] : 'string').toLowerCase();
    if(a['type'] != 'string' && a['type'] != 'pagination') { a['type'] != 'string'; }

    // Pagination Section
    a['pagination_nums'] = (typeof info['pagination_nums'] != 'undefined' ? info['pagination_nums'] : 5);
    a['pagination_details'] = (typeof info['pagination_details'] != 'undefined' ? info['pagination_details'] : true);

    a['pagination_class'] = ['show_pagination'];
    if(typeof info['pagination_class'] != 'undefined') {
        if(typeof info['pagination_class'] == 'string') { a['pagination_class'] = [info['pagination_class']]; }
        if(typeof info['pagination_class'] == 'array') { a['pagination_class'] = info['pagination_class']; }
    }
    if(a['type'] == 'pagination') { pagination_setup(a['id'],a['pagination_nums']); }
    
    a['cache'] = (typeof info['cache'] != 'undefined' ? info['cache'] : false);
    a['success'] = (typeof info['success'] != 'undefined' ? info['success'] : '');
    a['data'] = (typeof info['data'] != 'undefined' ? info['data'] : '');

    asl_table_setup(a);

}

function asl_table_setup(a) {
    //console.log("asl_table_setup")
    var tbl = '';
    var tbody = '';
    var th = '';
    var ths = '';
    if(!$id(a['id'])) { return false; }
    tbl = $id(a['id']);
    
    ths = tbl.getElementsByTagName('th');
    ths_len = ths.length;

    tbody = tbl.getElementsByTagName('tbody');
    asl_templates[tbl.id] = tbody[0].innerHTML;
    asl_current_sort[tbl.id] = { col: '', ord: '' }

    for(var j=0; j<ths_len; j++) {
        th = ths[j];
        if(!th.getAttribute('col')) { continue; }
        col = th.getAttribute('col').toLowerCase();
        ord = '';
        if(a['column'].toLowerCase() == col) {
            ord = a['order'].toLowerCase();
            if(ord != 'asc' && ord != 'desc') { ord = 'asc'; }
            ord = asl_sorts[ord];
        }
        th.innerHTML += "&nbsp;<span>"+ ord +"</span>";
        asl_current_sort[tbl.id]['col'] = col;
        asl_current_sort[tbl.id]['ord'] = ord;

        th.onclick = function() {
            asl_columnsort(this);
        }
    }
}


function asl_columnsort(obj) {
    //console.log("asl_columnsort")
    var col = obj.getAttribute('col');
    var ord = 'asc';
    var table = obj.parentNode.parentNode.parentNode;
    var cur_ord = '';
    var cur_col = asl_current_sort[table.id]['col'];
    // Switch orders if we are on the same one
    if(col == cur_col) {
        cur_ord = asl_current_sort[table.id]['ord'];
        ord = (cur_ord == 'asc' ? 'desc' : 'asc');
    }
    asl_update_sortcolumn(table,col,ord);

    if(asl[table.id]['type'] == 'pagination' && cur_col != col) { 
        pagination_new_page(table.id,1);
    } else {
        asl_sort(table.id,col,ord);
    }
}

function asl_update_sortcolumn(table,col,ord) {
    var ths = table.getElementsByTagName('th');
    var ths_len = ths.length;
    for(var i=0; i<ths_len; i++) {
        var th = ths[i];
        if(!th.getAttribute('col')) { continue; }
        if(!th.getAttribute('sort')) { th.setAttribute('sort','-'); }
        var span = th.getElementsByTagName('span')[0];
        ord_display = (th.getAttribute('col') == col ? asl_sorts[ord] : '');
        span.innerHTML = ord_display;
    }
    asl_current_sort[table.id]['col'] = col;
    asl_current_sort[table.id]['ord'] = ord;
}

function asl_sort(table_id,col,ord) {
    //console.log("asl_sort")
    if(typeof table_id == 'undefined') { return false; }
    if(typeof col == 'undefined') {
        col = (typeof a['column'] != "undefined" && a['column'] != "" ? a['column'] : "");
    }
    if(typeof ord == 'undefined') {
        ord = (typeof a['order'] != "undefined" && a['order'] != "" ? a['order'] : "");
    }

    a = asl[table_id];

    params = 'table_id='+ table_id +'&col='+ col +'&ord='+ ord;
    params += '&display_count='+ a['display_count'];
    if(a['data'] != '') { params += "&"+ a['data']; }
    if(a['filters'] != '') { params += '&'+ build_post_variables(a['filters']); }
    if(a['type'] == 'pagination') {
        cp = pagination[table_id]['cp'];
        params += '&cp='+ cp;
    }
    params += '&type='+ a['type'];

    last_sort = md5(params);

    // Check for caching
    if(a['cache'] && typeof cached_sort[last_sort] != 'undefined') {
        fill_rows(cached_sort[last_sort]);

        if(typeof pagination[table_id] != 'undefined') {
            pagination_draw_boxes(table_id);
        } else {
            pagination_hide();
        }
    } else {
        ajax({
            url: a['url']
            ,debug: a['debug']
            ,data: params
            ,type: 'json'
            ,callback: fill_rows
            // ,success: function(data) {
            //  alert(JSON.stringify(data['output']))
            // }
        });
    }

}

function fill_rows(results) {
    // console.log(results)
    if(typeof results == "undefined") { return false; }
    // if(typeof results['output'] == "string") {
    //  if(typeof results['params'] != "undefined" && typeof results['params']['table_id'] != "undefined") {
    //      if($id("sortlist_box")) { hide($id("sortlist_box")); }
    //      if($id("asl_error")) { show($id("asl_error")); }
    //      return;
    //  }
    // }

    for(table_id in results['output']) {
        res = results['output'];

        if($id("sortlist_box")) { show($id("sortlist_box")); }
        if($id("asl_error")) { hide($id("asl_error")); }

        var output = '';
        var tmp = asl_templates[table_id];

        // No records returned, show message
        if(res[table_id].length == 0) {
            output = '<tr><td class="center" colspan="'+ tmp.match(/\<td/g).length +'"><b>-- <em>No Results</em> --</b></td></tr>';
    
        // Records returned
        } else {
            for(i in res[table_id]) {
                x = tmp;
                for(j in res[table_id][i]) {
                    var regex = new RegExp("{{"+ j +"}}","g");
                    x = x.replace(regex,res[table_id][i][j]);
                }
                output += x;
            }
        }
        var tbody = $id(table_id).getElementsByTagName('tbody');
        tbody[0].innerHTML = output;
        tbody[0].style.display = '';

        if(typeof last_sort != 'undefined') {
            if(asl[table_id]['cache'] && last_sort != '' && typeof cached_sort[last_sort] == 'undefined') {
                cached_sort[last_sort] = results;
            }           
        }

        if(asl[table_id]['callback'] != '') {
            // asl[table_id]['callback'](results);
            var fn = window[asl[table_id]['callback']];
            if(typeof fn === 'function') {
                fn(results);
            }
        }
    }

    if(typeof results['pagination'] != 'undefined') {
        if(typeof pagination[results['pagination']['table_id']] != 'undefined') {
            r = results['pagination'];
            // Only show pagination if there are records for it
            if(parseInt(r['total_records']) > 0) {
                pagination[r['table_id']]['total_records'] = r['total_records'];
                pagination[r['table_id']]['ipp'] = r['ipp'];
                pagination_draw_boxes(r['table_id']);
            } else {
                pagination_hide();
            }
        }
    }
}


function pagination_setup(table_id,boxes_to_show) {
    if(typeof table_id == 'undefined') { return false; }
    if(typeof boxes_to_show == 'undefined') { boxes_to_show = 5; }
    pagination[table_id] = {};
    pagination[table_id]['boxes_to_show'] = boxes_to_show;
    pagination[table_id]['cp'] = 1;
}

function pagination_new_page(table_id,cp) {
    if(typeof table_id == 'undefined') { return false; }
    if(typeof cp == 'undefined' || typeof cp == 'string' || isNaN(cp)) { return false }
    if(cp < 1 ) { return false; }
    var max_page = Math.ceil(pagination[table_id]['total_records'] / pagination[table_id]['ipp']);
    if(cp > max_page ) { return false; }
    //console.log(pagination)
    //console.log(table_id)
    pagination[table_id]['cp'] = cp;
    col = '';
    ord = '';
    if(typeof asl_current_sort[table_id] != 'undefined') {
        col = asl_current_sort[table_id]['col'];
        ord = asl_current_sort[table_id]['ord'];
    }

    asl_sort(table_id,col,ord);
}

function pagination_draw_boxes(table_id) {
    // console.log(table_id +'_pagination : '+ $id(table_id +'_pagination'))
    var show_pagination = $classes('show_pagination');
    if(show_pagination.length == 0) {
        var span = document.createElement("span");
        span.className = 'show_pagination';
        $id(table_id).parentNode.insertBefore(span, $id(table_id).nextSibling);
    }
    
    var p = pagination[table_id];

    if(typeof p['total_records'] == 'undefined') {
        pagination_hide();
        return;
    }

    var pages = Math.ceil(p['total_records'] / p['ipp']);

    var current = parseInt(p['cp']);
    var buffer = Math.floor(p['boxes_to_show'] / 2);

    var prev = current - 1;
    var next = current + 1;

    //console.log("Current: "+ current +", Prev: "+ prev +", Next: "+ next +", Boxes to show: "+ p['boxes_to_show']);
    if(prev < 1) { prev = 1; }
    if(next > pages) { next = pages; }

    var output = '';
    output = "<div class='pagi_details'>Results <span class='pagi_nums'>"+ (((current - 1) * p['ipp']) + 1) +"</span> - <span class='pagi_nums'>"+ ((p['ipp'] * current > p['total_records']) ? p['total_records'] : p['ipp'] * current) +"</span> of <span class='pagi_nums'>"+ p['total_records'] +"</span></div>";

    // Only one page?  Don't show pagination
    if(pages > 1) {

        output += "<div class='pagi'>";
        output += "<div class='pagi_direction'><a href='javascript:void(0)' title='Previous Page' onclick='pagination_new_page(\""+ table_id +"\","+ prev +")'>Newer</a></div>";
        
        var style = ' style="visibility: hidden;"';
        var range_low = 1;
        var range_high = pages;
        if(p['boxes_to_show'] < pages) {
            range_high = p['boxes_to_show'];
        }
        
        if((current - buffer) > 1 && pages > p['boxes_to_show']) {
            style = '';
            range_low = (current - buffer < 1 ? 1 : current - buffer);
            range_high = (current + buffer > pages ? pages : current + buffer);
        }
        if(range_low > (pages - (buffer*2))) {
            range_low = (pages - (buffer*2));
            if(range_low < 1) { range_low = 1; }
        }
        
        //console.log("Range Low: "+ range_low +", Range High: "+ range_high);

        output += "<span"+ style +"><div class='pagi_page'><a href='javascript:void(0)' title='Go To Page 1' class='two_digit' onclick='pagination_new_page(\""+ table_id +"\",1)'>1</a></div><div class='pagi_filler'>...</div></span>";
        while(range_low <= range_high) {
            if(current == range_low) {
                output += "<div class='pagi_bold_two'>"+ range_low +"</div>";
            } else {
                output += "<div class='pagi_page'><a href='javascript:void(0)' title='Go To Page "+ range_low +"' class='two_digit' onclick='pagination_new_page(\""+ table_id +"\","+ range_low +")'>"+ range_low +"</a></div>";
            }
            range_low++;
        }
        style = ' style="visibility: hidden;"';
        if(range_high < pages) {
            style = '';     
        }
        output += "<span"+ style +"><div class='pagi_filler'>...</div><div class='pagi_page'><a href='javascript:void(0)' title='Go To Page "+ pages +"' class='two_digit' onclick='pagination_new_page(\""+ table_id +"\","+ pages +")'>"+ pages +"</a></div></span>";

        output += "<div class='pagi_direction'><a href='javascript:void(0)' title='Next Page' onclick='pagination_new_page(\""+ table_id +"\","+ next +")'>Older</a></div>";
        output += " &nbsp; Go To Page: <input type='text' name='pagi_gotopage' id='pagi_gotopage' style='width: 30px;' value='"+ current +"' onchange='pagination_new_page(\""+ table_id +"\",parseInt(this.value))'>";
        output += "<div class='pagi_clear'></div>";
        output += "</div>";
    }

    var show_pagination = $classes('show_pagination');
    var len = show_pagination.length;
    var i = 0;
    while(i < len) {
        var pagi = show_pagination[i];
        pagi.innerHTML = output;
        show(pagi);
        i++;        
    }
}

function pagination_hide() {
    var show_pagination = $classes('show_pagination');
    var len = show_pagination.length;
    var i = 0;
    while(i < len) {
        hide(show_pagination[i]);
        i++;        
    }
}


// Local Sorting

function sort_array(array, key) {
    var k = key;
    array.sort(function(a,b) {
        // assuming distance is always a valid integer
        // ;
        if(isNumeric(a[k]) && isNumeric(b[k])) {
            i = parseFloat(a[k]);
            j = parseFloat(b[k]);
            return i - j;
        } else {
            if(a[k].toLowerCase() > b[k].toLowerCase()) return 1;
            if(a[k].toLowerCase() < b[k].toLowerCase()) return -1;
        }
        return 0
    });
    // console.log(JSON.stringify(array))
}
function isNumeric( obj ) {
    return (obj - parseFloat( obj ) + 1) >= 0;
}


function md5cycle(x, k) {
var a = x[0], b = x[1], c = x[2], d = x[3];

a = ff(a, b, c, d, k[0], 7, -680876936);
d = ff(d, a, b, c, k[1], 12, -389564586);
c = ff(c, d, a, b, k[2], 17,  606105819);
b = ff(b, c, d, a, k[3], 22, -1044525330);
a = ff(a, b, c, d, k[4], 7, -176418897);
d = ff(d, a, b, c, k[5], 12,  1200080426);
c = ff(c, d, a, b, k[6], 17, -1473231341);
b = ff(b, c, d, a, k[7], 22, -45705983);
a = ff(a, b, c, d, k[8], 7,  1770035416);
d = ff(d, a, b, c, k[9], 12, -1958414417);
c = ff(c, d, a, b, k[10], 17, -42063);
b = ff(b, c, d, a, k[11], 22, -1990404162);
a = ff(a, b, c, d, k[12], 7,  1804603682);
d = ff(d, a, b, c, k[13], 12, -40341101);
c = ff(c, d, a, b, k[14], 17, -1502002290);
b = ff(b, c, d, a, k[15], 22,  1236535329);

a = gg(a, b, c, d, k[1], 5, -165796510);
d = gg(d, a, b, c, k[6], 9, -1069501632);
c = gg(c, d, a, b, k[11], 14,  643717713);
b = gg(b, c, d, a, k[0], 20, -373897302);
a = gg(a, b, c, d, k[5], 5, -701558691);
d = gg(d, a, b, c, k[10], 9,  38016083);
c = gg(c, d, a, b, k[15], 14, -660478335);
b = gg(b, c, d, a, k[4], 20, -405537848);
a = gg(a, b, c, d, k[9], 5,  568446438);
d = gg(d, a, b, c, k[14], 9, -1019803690);
c = gg(c, d, a, b, k[3], 14, -187363961);
b = gg(b, c, d, a, k[8], 20,  1163531501);
a = gg(a, b, c, d, k[13], 5, -1444681467);
d = gg(d, a, b, c, k[2], 9, -51403784);
c = gg(c, d, a, b, k[7], 14,  1735328473);
b = gg(b, c, d, a, k[12], 20, -1926607734);

a = hh(a, b, c, d, k[5], 4, -378558);
d = hh(d, a, b, c, k[8], 11, -2022574463);
c = hh(c, d, a, b, k[11], 16,  1839030562);
b = hh(b, c, d, a, k[14], 23, -35309556);
a = hh(a, b, c, d, k[1], 4, -1530992060);
d = hh(d, a, b, c, k[4], 11,  1272893353);
c = hh(c, d, a, b, k[7], 16, -155497632);
b = hh(b, c, d, a, k[10], 23, -1094730640);
a = hh(a, b, c, d, k[13], 4,  681279174);
d = hh(d, a, b, c, k[0], 11, -358537222);
c = hh(c, d, a, b, k[3], 16, -722521979);
b = hh(b, c, d, a, k[6], 23,  76029189);
a = hh(a, b, c, d, k[9], 4, -640364487);
d = hh(d, a, b, c, k[12], 11, -421815835);
c = hh(c, d, a, b, k[15], 16,  530742520);
b = hh(b, c, d, a, k[2], 23, -995338651);

a = ii(a, b, c, d, k[0], 6, -198630844);
d = ii(d, a, b, c, k[7], 10,  1126891415);
c = ii(c, d, a, b, k[14], 15, -1416354905);
b = ii(b, c, d, a, k[5], 21, -57434055);
a = ii(a, b, c, d, k[12], 6,  1700485571);
d = ii(d, a, b, c, k[3], 10, -1894986606);
c = ii(c, d, a, b, k[10], 15, -1051523);
b = ii(b, c, d, a, k[1], 21, -2054922799);
a = ii(a, b, c, d, k[8], 6,  1873313359);
d = ii(d, a, b, c, k[15], 10, -30611744);
c = ii(c, d, a, b, k[6], 15, -1560198380);
b = ii(b, c, d, a, k[13], 21,  1309151649);
a = ii(a, b, c, d, k[4], 6, -145523070);
d = ii(d, a, b, c, k[11], 10, -1120210379);
c = ii(c, d, a, b, k[2], 15,  718787259);
b = ii(b, c, d, a, k[9], 21, -343485551);

x[0] = add32(a, x[0]);
x[1] = add32(b, x[1]);
x[2] = add32(c, x[2]);
x[3] = add32(d, x[3]);

}

function cmn(q, a, b, x, s, t) {
a = add32(add32(a, q), add32(x, t));
return add32((a << s) | (a >>> (32 - s)), b);
}

function ff(a, b, c, d, x, s, t) {
return cmn((b & c) | ((~b) & d), a, b, x, s, t);
}

function gg(a, b, c, d, x, s, t) {
return cmn((b & d) | (c & (~d)), a, b, x, s, t);
}

function hh(a, b, c, d, x, s, t) {
return cmn(b ^ c ^ d, a, b, x, s, t);
}

function ii(a, b, c, d, x, s, t) {
return cmn(c ^ (b | (~d)), a, b, x, s, t);
}

function md51(s) {
txt = '';
var n = s.length,
state = [1732584193, -271733879, -1732584194, 271733878], i;
for (i=64; i<=s.length; i+=64) {
md5cycle(state, md5blk(s.substring(i-64, i)));
}
s = s.substring(i-64);
var tail = [0,0,0,0, 0,0,0,0, 0,0,0,0, 0,0,0,0];
for (i=0; i<s.length; i++)
tail[i>>2] |= s.charCodeAt(i) << ((i%4) << 3);
tail[i>>2] |= 0x80 << ((i%4) << 3);
if (i > 55) {
md5cycle(state, tail);
for (i=0; i<16; i++) tail[i] = 0;
}
tail[14] = n*8;
md5cycle(state, tail);
return state;
}

/* there needs to be support for Unicode here,
 * unless we pretend that we can redefine the MD-5
 * algorithm for multi-byte characters (perhaps
 * by adding every four 16-bit characters and
 * shortening the sum to 32 bits). Otherwise
 * I suggest performing MD-5 as if every character
 * was two bytes--e.g., 0040 0025 = @%--but then
 * how will an ordinary MD-5 sum be matched?
 * There is no way to standardize text to something
 * like UTF-8 before transformation; speed cost is
 * utterly prohibitive. The JavaScript standard
 * itself needs to look at this: it should start
 * providing access to strings as preformed UTF-8
 * 8-bit unsigned value arrays.
 */
function md5blk(s) { /* I figured global was faster.   */
var md5blks = [], i; /* Andy King said do it this way. */
for (i=0; i<64; i+=4) {
md5blks[i>>2] = s.charCodeAt(i)
+ (s.charCodeAt(i+1) << 8)
+ (s.charCodeAt(i+2) << 16)
+ (s.charCodeAt(i+3) << 24);
}
return md5blks;
}

var hex_chr = '0123456789abcdef'.split('');

function rhex(n)
{
var s='', j=0;
for(; j<4; j++)
s += hex_chr[(n >> (j * 8 + 4)) & 0x0F]
+ hex_chr[(n >> (j * 8)) & 0x0F];
return s;
}

function hex(x) {
for (var i=0; i<x.length; i++)
x[i] = rhex(x[i]);
return x.join('');
}

function md5(s) {
return hex(md51(s));
}

/* this function is much faster,
so if possible we use it. Some IEs
are the only ones I know of that
need the idiotic second function,
generated by an if clause.  */

function add32(a, b) {
return (a + b) & 0xFFFFFFFF;
}

if (md5('hello') != '5d41402abc4b2a76b9719d911017c592') {
function add32(x, y) {
var lsw = (x & 0xFFFF) + (y & 0xFFFF),
msw = (x >> 16) + (y >> 16) + (lsw >> 16);
return (msw << 16) | (lsw & 0xFFFF);
}
}
