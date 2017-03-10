function sortlist(){
    // Settings: S
    var remote = {}
        ,local = {}
        ,sort_icons = { 'asc': '<span class="sort_asc"></span>', 'desc': '<span class="sort_dsc"></span>' };

    function init(x) {
        x = x || {};

        // Default Column, Order
        x.cname = x.column || '';

        // Rows per page
        x.rpp = x.rpp || 20;

        // Default Column, Order
        x.cname = x.column || '';
        x.cdir = x.direction || 'asc';
        x.filters = x.filters || '';

        // options: string, pagination (Default: string)
        x.p = x.pagination  || true;

        x.cache = x.cache || true;
        x.data = x.data || '';

        x.s = x.success || '';
        x.pre = x.pre_hook || '';
        x.post = x.post_hook || '';
        x.complete = x.oncomplete || '';
        x.cols = x.columns || '';

        x.debug = x.debug || false;

        x.col_last = "";
        x.col_ord = "asc";
        x.sorted = {};

        x.cols = (typeof x.cols == "string" ? [] : x.cols);
        x.sort_types = {}
        x.saved_data = {}

        // Pagination Section

        if(x.p) {
            // Number of display blocks.  Ex: 5 =  1 ... 10,11,12,13,14 ... 80
            x.p_boxes = x.p_nums || 5;
            x.p_details = x.p_details || true; // Additional Details
            x.p_cp = x.cp || 1; // Current Page

            x.p_class = ['show_p'];
            if(typeof x.p_class != 'undefined') {
                if(typeof x.p_class == 'string') { x.p_class = [x.p_class]; }
                if(typeof x.p_class == 'object') { x.p_class = x.p_class; }
            }

            //local_sortlist_pagination_setup(x.id,info.pagination_nums);
        }

        return x;
    }


    remote.init = function(url,x) {
        x = x || {};
        x.id = x.id || 'sortlist_ajax';
        x.url = x.url || '/ajax.php';
        x.debug = x.debug || true;
        x.data = x.data || '';

        z = init(x);
        for(i in z) { remote[i] = z[i]; }

        // setup the table
        var cols = document.getElementById(remote.id).getElementsByTagName("th");
        // console.log(cols)
        for(var i=0, len=cols.length; i<len; i++) {
            if(!cols[i].getAttribute("data-col")) { continue; }
            remote.cols.push(cols[i].getAttribute("data-col"))
            // console.log(cols[i])
            cols[i].onclick = new Function(remote.id+".sort(this)");
        }
        // console.log(cols)

        remote.template = document.getElementById(remote.id).getElementsByTagName("tbody")[0].innerHTML;
    }


    remote.setup_onclicks = function() {

        // setup the table
        var cols = document.getElementById(remote.id).getElementsByTagName("th");
        // console.log(cols)
        for(var i=0, len=cols.length; i<len; i++) {
            if(!cols[i].getAttribute("data-col")) { continue; }
            remote.cols.push(cols[i].getAttribute("data-col"))
            // console.log(cols[i])
            cols[i].onclick = new Function(remote.id+".sort(this)");
        }
        // console.log(cols)

    }

    local.init = function(list,x) {
        x = x || {};
        x.id = x.id || 'sortlist_local';

        // Local List
        x.list = list;

        z = init(x);
        for(i in z) { local[i] = z[i]; }
        if(local.p) {
            local.pagination = {};
            local.pagination.total_records = local.list.length;
        }

        // Add any new custom sort types
        local.set_sort_types();

        // setup the table
        var cols = document.getElementById(local.id).getElementsByTagName("th");
        for(var i=0, len=cols.length; i<len; i++) {
            if(typeof cols[i].dataset["col"] == "undefined") { continue; }
            local.cols.push(cols[i].dataset["col"])
            // console.log(s.id+".sort(local)")
            cols[i].onclick = new Function(local.id+".sort(this)");
        }
        // Check the official list to make sure we have the columns needed
        local.verify_columns();
        local.template = document.getElementById(local.id).getElementsByTagName("tbody")[0].innerHTML;
    }

    local.verify_columns = function(){
        output = "";
        for(i in local.cols) {
            if(typeof local.list[0][local.cols[i]] == "undefined") {
                output += "'"+ local.cols[i] +"' is not a valid column";
            }
        }
        output = "";
    }

    local.sort = function(obj,custom){

        custom = custom || "";
        if(custom == "") {
            local.col_type = obj.dataset["colType"] || "string";
            local.cname = obj.dataset["col"];
        } else {
            obj = local;
        }

        // local.col_type = obj.dataset["colType"] || "string";
        // local.cname = obj.dataset["col"];
        local.reverse = false;
        if(local.cname == local.col_last) {
            if(!(local.p && local.p_change)) {
                local.cdir = (local.cdir == "asc" ? "desc" : "asc");
            }
            if(custom == "") {
                local.reverse = true;
            }
        } else {
            local.cdir = "asc";
            if(local.p) {
                local.p_cp = 1;
            }
        }

        if(local.p) {
            local.p_change = false;
        }
        local.col_last = local.cname;

        clear_headers(local.id);
        obj.innerHTML += " <span>"+ sort_icons[local.cdir] +"</span>";

        // build new array and sort that with original context?
        local.build_table();
    }

    local.sort_columns = function() {
       if(typeof local.sorted[local.cname] != "undefined") {
           if(local.reverse) {
                local.sorted[local.cname].reverse();
                return;
           }
           return;
       }

       return local.create_sorted_list();
    }
    local.create_sorted_list = function() {
       var sortable = [];
       for (var key in local.list) {
            sortable.push([key, (typeof local.list[key][local.cname] != "undefined" ? String(local.list[key][local.cname]) : "")]);
       }
       sortable = sortable.sort(local.sort_types[local.col_type]);

       results = [];
       for(var i=0, len = sortable.length; i<len; i++) {
           results.push(sortable[i][0])
       }

       local.sorted[local.cname] = (local.cdir == "asc" ? results : results.reverse());
    }

    local.build_table = function() {
        var tbody = document.getElementById(local.id).getElementsByTagName("tbody")[0];
        var val = "", tmp = "", output = "", row = 0;
        var i,find,re;

        if(local.pre_hook) { local.pre_hook(); }

        local.sort_columns();

        if(local.post_hook) { local.post_hook(); }

        cols = local.sorted[local.cname];

        // pagination needed here
        if(local.p) {
            row = (parseInt(local.p_cp,10) - 1) * parseInt(local.rpp,10);
        }
        if(isNaN(row)) { row = 0; }

        output = "";
        rpp = 0;
        while(rpp++ < local.rpp) {
            tmp = local.template;
            if(typeof local.list[cols[row + (rpp - 1)]] == "undefined") {
                continue;
            }
            for(i=0, len=local.cols.length; i<len; i++) {
                val = local.list[cols[row + (rpp - 1)]][local.cols[i]]
                find = "{{"+ local.cols[i] +"}}";
                re = new RegExp(find, 'g');
                tmp = tmp.replace(re,val);
            }
            output += tmp;
        }

        if(local.cache == false) {
            local.sorted[local.cname] = null;
            local.sorted = {};
        }

        tbody.innerHTML = output;
        tbody.style.display = "";
        if(local.p) {
            pagination_render(local);
        }
        output = "";
        if(local.oncomplete) { local.oncomplete(); }
        if(local.callback) { local.callback(); }
    }

    local.set_sort_types = function() {
        local.sort_types = {
            "string": function(a, b) { return ([a[1],a[0]] > [b[1],b[0]] ? 1 : -1); }
            ,"numeric": function(a, b) { return (parseFloat(a[1]) > parseFloat(b[1]) ? 1 : -1); }
        }
    }
    local.add_sort_type = function(name,func) {
        local.sort_types[name] = func;
    }




    remote.sort = function(obj,custom){
        if(typeof custom == "undefined") {
            //remote.col_type = obj.dataset["colType"] || "string";
            remote.cname = obj.getAttribute("data-col");
        } else {
            obj = remote;
        }

        if(remote.cname == remote.col_last) {
            if(!(remote.p && remote.p_change)) {
                remote.cdir = (remote.cdir == "asc" ? "desc" : "asc");
            }
        } else {
            remote.cdir = "asc";
            if(remote.p) {
                remote.p_cp = 1;
            }
        }

        if(remote.p) {
            remote.p_change = false;
        }
        remote.col_last = remote.cname;

        clear_headers(remote.id);
        obj.innerHTML += " <span>"+ sort_icons[remote.cdir] +"</span>";

        // build new array and sort that with original context?
        remote.get_data();
    }

    clear_headers = function(id) {
        var cols = document.getElementById(id).getElementsByTagName("th");
        for(var i=0, len=cols.length; i<len; i++) {
            if(typeof cols[i].dataset == "undefined" || typeof cols[i].dataset["col"] == "undefined") { continue; }
            cols[i].innerHTML = cols[i].innerHTML.replace(/\<span.*?\<\/span\>/,'');     
        }

    }

    remote.get_data = function() {
        if(remote.pre_hook) { remote.pre_hook(); }
        remote.sort_columns();
    }
  
    remote.sort_columns = function() {
        remote.sort_data = remote.build_sortlist_params();

        // If cached, used remote first
       if(typeof remote.sorted[remote.sort_data] != "undefined") {
           remote.build_table();
           return;
       }

       remote.create_sorted_list();
    }

    remote.create_sorted_list = function() {
        var obj = remote;
        ajax({
            url: remote.url
            ,debug: remote.debug
            ,data: obj.sort_data
            ,type: 'json'
            ,success: function(data) {
                if(obj.p && typeof data['pagination'] != 'undefined') {
                    obj.pagination = data['pagination'];
                }
                obj.sorted[obj.sort_data] = data['output'][obj.id];
                remote.build_table();

                if(remote.callback) { remote.callback(data); }
                if(remote.oncomplete) { remote.oncomplete(data); }
            }
        });
    }

    remote.build_table = function() {
        if(remote.post_hook) { remote.post_hook(); }

        // console.log(remote.template)

        var tbody = document.getElementById(remote.id).getElementsByTagName("tbody")[0];
        var val = "", tmp = "", output = "";
        var find,re;

        cols = remote.sorted[remote.sort_data];

// #var y = tbody.childNodes.length;
// while(y--) {
// 	tbody.removeChild(tbody.lastChild);
// }

        for(var row=0; row<remote.rpp; row++) {
            var tmp = remote.template;
            if(typeof cols[row] == "undefined") {
                continue;
            }
            for(i in cols[row]) {
                find = "{{"+ i +"}}";
                re = new RegExp(find, 'g');
                val = cols[row][i];
                tmp = tmp.replace(re,val);
            }
            output += tmp;
//          console.log
//	    tr = document.createElement('tr');
//	    tr.innerHTML = tmp;
//	    tbody.appendChild(tr);
        }

        if(remote.cache == false) {
            remote.sorted[remote.sort_data] = null;
            remote.sorted = {};
        }
        
	// tbody.innerHTML = output
var el = document.getElementById(remote.id);
var outer = el.outerHTML;

//console.log(outer);
//var re = /<tbody(.*)tbody>/;

outer = outer.replace(/(\r\n|\n|\r)/gm,"");
outer = outer.replace(/\s\s+/gm," ");


pieces = outer.match(/<tbody(.*?)>(.*)<\/tbody>/)
//console.log(pieces)

outer = outer.replace(pieces[pieces.length - 1],output);
//console.log(outer);
//el.outerHTML=el.outerHTML.replace(el.innerHTML, outer);	
el.outerHTML = outer;

tbody = document.getElementById(remote.id).getElementsByTagName("tbody")[0];
        tbody.style.display = "";

remote.setup_onclicks();

        if(remote.p) {
            pagination_render(remote);
        }
        output = "";
    }

    remote.pagination_sort = function(cp) {
        pagi_sort(remote,cp);
    }
    local.pagination_sort = function(cp) {
        pagi_sort(local,cp);
    }

    pagi_sort = function(obj,cp) {
        var cp = cp || 0;
        var max_page;

        if(cp < 1) { return false; }
        if(typeof obj.pagination == "undefined") { return false; }
        max_page = Math.ceil(obj.pagination.total_records / obj.rpp);
        if(cp > max_page ) {
            obj.pagination_sort(max_page);
            return false;
        }

        obj.p_cp = cp;
        obj.p_change = true;

        obj.sort(obj,true);
    }


    remote.build_sortlist_params = function() {
        var params = remote.data;
        params += "&col="+ remote.cname;
        params += "&ord="+ remote.cdir;
        params += "&table_id="+ remote.id;
        params += "&display_count="+ remote.rpp;
        params += "&type="+ remote.type;
        if(remote.p) {
            params += "&cp="+ remote.p_cp;
        }
        if(remote.filters) {
            params += "&"+ build_post_variables(remote.filters);
        }
        return params;
    }

    pagination_render = function(obj) {
        var show_pagination = $classes('.show_pagination.'+obj.id);
        var pages,current,prev,next,buffer,output,i,attr;

        // i = show_pagination.length;

        // while(i--) {
        //     attr = show_pagination[i].getAttribute("data-sortid");
        //     if(!attr || attr != obj.id) {
        //         // console.log(typeof show_pagination)
        //         // console.log(show_pagination[i])
        //         // show_pagination[i] = undefined;
        //         delete show_pagination[i];
        //         console.log(i)
        //         console.log(show_pagination[i])
        //         console.log(show_pagination)
        //     }
        // }


        // Auto add pagination elements if they doesn't exist
        if(show_pagination.length == 0) {
            var span = document.createElement("span");
            span.className = 'show_pagination '+obj.id;
            // span.setAttribute('sortid', obj.id)
            $id(obj.id).parentNode.insertBefore(span, $id(obj.id).nextSibling);
        }

        if(typeof obj.pagination.total_records == 'undefined') {
            pagination_hide();
            return;
        }

        pages = Math.ceil(obj.pagination.total_records / obj.rpp);

        current = obj.p_cp;
        buffer = Math.floor(obj.p_boxes / 2);

        prev = current - 1;
        next = current + 1;

        //console.log("Current: "+ current +", Prev: "+ prev +", Next: "+ next +", Boxes to show: "+ obj.p_boxes);
        if(prev < 1) { prev = 1; }
        if(next > pages) { next = pages; }


        output = '';
        output = "<div class='pagi_details'>Results <span class='pagi_nums'>"+ (((current - 1) * obj.rpp) + 1) +"</span> - <span class='pagi_nums'>"+ ((obj.rpp * current > obj.pagination.total_records) ? obj.pagination.total_records : obj.rpp * current) +"</span> of <span class='pagi_nums'>"+ obj.pagination.total_records +"</span></div>";

        // Only one page?  Don't show pagination
        if(pages > 1) {
          
            output += "<div class='pagi'>";
            output += "<div class='pagi_direction'><a href='javascript:void(0)' title='Previous Page' onclick='"+ obj.id +".pagination_sort("+ prev +")' class='prev'>Prev</a></div>";
            
            var style = ' style="visibility: hidden;"';
            var range_low = 1;
            var range_high = pages;
            if(obj.p_boxes < pages) {
                range_high = obj.p_boxes;
            }
            
            if((current - buffer) > 1 && pages > obj.p_boxes) {
                style = '';
                range_low = (current - buffer < 1 ? 1 : current - buffer);
                range_high = (current + buffer > pages ? pages : current + buffer);
            }
            if(range_low > (pages - (buffer*2))) {
                range_low = (pages - (buffer*2));
                if(range_low < 1) { range_low = 1; }
            }
            
            //console.log("Range Low: "+ range_low +", Range High: "+ range_high);

            output += "<span"+ style +"><div class='pagi_page'><a href='javascript:void(0)' title='Go To Page 1' class='digit' onclick='"+ obj.id +".pagination_sort(1)'>1</a></div><div class='pagi_filler'>...</div></span>";
            while(range_low <= range_high) {
                if(current == range_low) {
                    output += "<div class='pagi_bold'>"+ range_low +"</div>";
                } else {
                    output += "<div class='pagi_page'><a href='javascript:void(0)' title='Go To Page "+ range_low +"' class='digit' onclick='"+ obj.id +".pagination_sort("+ range_low +")'>"+ range_low +"</a></div>";
                }
                range_low++;
            }
            style = ' style="visibility: hidden;"';
            if(range_high < pages) {
                style = '';     
            }
            output += "<span"+ style +"><div class='pagi_filler'>...</div><div class='pagi_page'><a href='javascript:void(0)' title='Go To Page "+ pages +"' class='digit' onclick='"+ obj.id +".pagination_sort("+ pages +")'>"+ pages +"</a></div></span>";

            output += "<div class='pagi_direction'><a href='javascript:void(0)' title='Next Page' onclick='"+ obj.id +".pagination_sort("+ next +")' class='next'>Next</a></div>";
            output += "</div>";
            
            output += "<div class='pagi_gotopage'>Go To Page: <input type='text' name='pagi_gotopage' title='Go To Page' id='pagi_gotopage' value='"+ current +"' onchange='"+ obj.id +".pagination_sort(parseInt(this.value,10))'>";
            var sel = "<select name='change_counts' onchange='"+ obj.id +".update(\"rpp\",parseInt(this.value,10))'><option value='10'>10</option><option value='20'>20</option><option value='50'>50</option><option value='100'>100</option></select>";
            output += sel.replace("'"+ obj.rpp +"'","'"+ obj.rpp +"' selected");
            output += "</div>";
        }

        var show_pagination = $classes('.show_pagination.'+obj.id);
        var len = show_pagination.length;
        var i = 0;
        while(i < len) {
            var pagi = show_pagination[i];
            pagi.innerHTML = output;
            show(pagi);
            i++;        
        }
    }


    remote.pagination_hide = function() {
        var show_pagination = $classes('.show_pagination.'+remote.id);
        var i = show_pagination.length;
        while(i--) {
            hide(show_pagination[i]);       
        }
    }


    function update_value(obj,key,value) {
        obj[key] = value;
    }

    remote.update = function(key,value) {
        update_value(remote,key,value);
        remote.reload();
    }

    local.update = function(key,value) {
        update_value(local,key,value);
        local.reload();
    }

    remote.reload = function() {
        remote.sort_columns();
    }

    local.reload = function() {
        local.build_table();
    }

    return {local:local,remote:remote};
};
