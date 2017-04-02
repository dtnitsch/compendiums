<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

// if(!logged_in()) { safe_redirect("/login/"); }
// if(!has_access("list_add")) { back_redirect(); }

post_queue($module_name,'modules/lists/post_files/');

##################################################
#	Validation
##################################################

##################################################
#	DB Queries
##################################################

##################################################
#	Pre-Content
##################################################
$info = (!empty($_POST) ? $_POST : array());


library("validation.php");
add_js("validation.js");
add_js("markdown.min.js");

##################################################
#	Content
##################################################
?>
	<form id="addform" method="post" action="">
		<h2 class='lists'>Add List</h2>
  
  		<div id="messages">
			<?php echo dump_messages(); ?>
		</div>

		<div id="compendium_buttons" class="w3-bar w3-black mt">
			<button type="button" class="w3-bar-item w3-button tablink w3-red" onclick="open_tabs(this,'default')">Default</button>
			<button type="button" class="w3-bar-item w3-button tablink" onclick="open_tabs(this,'md')">Markdown</button>
			<div class="float_right">
				<button class="w3-bar-item w3-button tablink" style="background: green;">Save List</button>
			</div>
		</div>
		<div id="compendium_bodies" style='padding: 1em; border: 1px solid #ccc;'>

			<div id="default" class="w3-container w3-border tabs">

				<div class="float_left" style="width: 59%;">
					<label class="form_label" for="title">List Name <span>*</span></label>
					<div class="form_data">
						<input type="text" name="title" id="title" class="xl" value="<?php echo $info['title'] ?? ""; ?>">
					</div>

					<!--label class="form_label">Visibility</label>
					<div class="form_data">
						<label for="public"><input type="radio" name="visibility" id="public" value="public"> Public</label>
						<label for="private"><input type="radio" name="visibility" id="private" value="private"> Private</label>
					</div-->

					<label class="form_label" for="title">Inputs</label>
					<div class="form_data">
						<!--textarea name="inputs" id="inputs" onchange="show_example()" onkeyup="show_example()" style="width: 400px; height: 150px;">Chicken; 30; Poor,Middle Class,Rich,Lunch,Dinner
		Beef; 5; Middle Class,Rich, Lunch, Dinner
		Oysters; 5; Poor,Rich, Dinner
		Eggs; 10; Poor,Middle Class,Rich,breakfast
		Soup; 20; Poor,Middle Class, Lunch, Dinner
		Bread; 20; Poor,Middle Class,Rich, breakfast, Lunch, Dinner
		Apples; 10; Poor,Middle Class,Rich, snack</textarea-->
						<textarea name="inputs" id="inputs" onchange="show_example()" onkeyup="show_example()" style="width: 90%; height: 250px;"><?php echo $info['inputs'] ?? ""; ?></textarea>
						<div style="font-size: 80%;">*Notes: Tab Deliminated List - Name &nbsp; Percentage &nbsp; Tags</div>
					</div>

					<!--label class="form_label" for="title">Input Options</label>
					<div class="form_data">
						<label for="percentages">
							<input type="checkbox" name="options" id="percentages" value="percentages"> Percentages
						</label>
						&nbsp;
						<label for="tags">
							<input type="checkbox" name="options" id="tags" value="tags"> Tags
						</label>
					</div->
					

						<!--input checked type="radio" name="multipart" value="yes"> Individual
						<input type="radio" name="multipart" value="no"> Multi-Part -->

					<!--input type="button" value="Add List" onclick="addform()"-->
					</div>
				<div class="float_left" style="width: 39% padding: 1em;">
					<table cellspacing="0" id="filters_table" class="tbl" style='display: none;'>
						<thead>
							<tr>
								<th>&nbsp;</th>
								<th>Filter label</th>
								<th>Filter Value</th>
								<th>Order</th>
							</tr>
						</thead>
						<tbody id="filters_table_tbody">
						</tbody>
					</table>
					<div id="example"></div>
				</div>


			</div>

<style type="text/css">
	code { border: 1px solid #ccc; padding: 1em; background: #ddd; margin: 1em; }
	.markdown h1, .markdown h2, .markdown h3, .markdown h4, .markdown h5, .markdown h6 {
		margin: 0; padding: 0;
	}
</style>
			<div id="md" class="w3-container w3-border tabs" style="display: none">
				<textarea name="markdown" id="markdown" class="float_left" style="width: 47%; height: 200px;" onkeyup="parse_markdown()">
** Markdown List Preview ** 

Some useful information about setting up previews here...
Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
</textarea>
				<article id="preview" class="markdown-body float_left" style="width: 47%; border: 1px solid #ccc; margin-left: 1em; padding: 1em"></article>
				<div class="clear mt"></div>
			</div>
		</div>

		<div class="clear mt"></div>
		<input type="submit" value="Save List">
	</form>
	
<?php
##################################################
#	Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">
	var j = <?php echo validation_create_json_string(validation_load_file(__DIR__."/validation.json"),"js"); ?>;
	// name of variable should be sent in the validation function
	var v = new validation("v"); 
	v.load_json(j);
	v.custom("percentage",calc_percentages,"Percentages don't add up to 100")

	function show_example() {
		var pieces = $id('inputs').value.trim().split("\n");
		var len = pieces.length;
		var filters, tags, tag, slugs, inner;
		var is_table = false;

		var re = new RegExp("\\|");
		if(re.test(pieces[0])) {
			is_table = true;
		}

		// var output = "<strong>Example Output</strong>";
		percentages = 0;
		// // Limit the demo exmaple?
		// if(len > 10) {
		// 	len = 10;
		// }
		var html = "";
		for(var i=0; i<len; i++) {
			if(pieces[i].trim() == "") {
				continue;
			}
			inner_pieces = pieces[i].split(';');

			if(parseInt(inner_pieces[1])) {
				percentages += parseInt(inner_pieces[1]);
			}

			if(is_table) {
				table_pieces = inner_pieces[0].split('|');
				inner = "";

				if(i == 0) {
					for(var j=0,jlen=table_pieces.length; j<jlen; j++) {
						inner += "<th>"+ table_pieces[j].trim() +"</th>";
					}
					inner += "</thead><tbody>";
				} else {
					for(var j=0,jlen=table_pieces.length; j<jlen; j++) {
						inner += "<td>"+ table_pieces[j].trim() +"</td>";
					}
				}
			}

			filters = "";
			if(typeof inner_pieces[2] != "undefined") {
				tags = inner_pieces[2].trim().split(",");
				slugs = [];
				for(var j=0,jlen=tags.length; j<jlen; j++) {
					tag = tags[j].trim();
					filters += slug(tag,'_') +" ";
				}
				if(filters != "") {
					filters = " data-filters='"+ filters +"'";
				}
  			}

  			html += (is_table ? '<tr'+ filters +'>'+ inner.trim() +'</tr>' : '<li'+ filters +'>'+ inner_pieces[0].trim() +'</li>');
		}

		if(html.trim() != "") {
			var output = '<div id="filter_examples">';
			// output += "<br>"+ build_filters_table();

			output += (is_table ? '<table cellspacing="0" cellpadding="0" class="tbl"><thead>' : '<ol class="mt">');
			output += html;
			output += (is_table ? "</tbody></table>" : "</ol>");
			output += '</div>';
			$id('example').innerHTML = output;
			if(build_filters_table()) {							
				$id('filters_table').style.display = "";
			}
		}
	}
	show_example();

	function calc_percentages() {
		var pieces = $id('inputs').value.trim().split("\n");
		var inner_pieces, perc;
		var percentages = 0;
		for(var i=0,len=pieces.length; i<len; i++) {
			inner_pieces = pieces[i].split(';');
			if(typeof inner_pieces[1] != "undefined") {
				perc = parseInt(inner_pieces[1].trim())
				if(perc) {
					percentages += perc;
				}				
			} else {
				return true;
			}
		}
		return (percentages != 100 ? false : true);
		// return percentages;
	}

	function unique_tags() {
		var pieces = $id('inputs').value.trim().split("\n");
		var inner_pieces, tags, tag;
		var output = {};
		for(var i=0,len=pieces.length; i<len; i++) {
			inner_pieces = pieces[i].split(';');
			if(typeof inner_pieces[2] != "undefined") {
				tags = inner_pieces[2].trim().split(",");
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

	function build_filters() {
		var tags = unique_tags();
		var output = "";
		var cnt = 0;
		for(key in tags) {
			key = key.trim();
			if(!key) { continue; }
			alias = slug(key,'_');
			output += `
				<label for="filter_`+ cnt +`">
					<input type="checkbox" id="filter_`+ cnt +`" name="filters[`+ alias +`]" onclick="filter_list('`+ alias +`')" value="`+ alias +`"> `+ key +`
				</label> &nbsp; 
			`;
			cnt += 1;
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

		// console.log(existing_tag)
		// console.log(rem_tags)
		var cnt = 0;
		for(key in tags) {
			key = key.trim();
			if(!key) { continue; }
			alias = slug(key,'_');
			// console.log(alias)
			if(typeof existing_tag[alias] == "undefined") {
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
					<td><input type="text" name="filter_order[`+ key +`]" value="`+ cnt +`" class="s"></td>
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
			if(typeof trs[i] != "undefined" && typeof tags[trs[i].dataset.key] != "undefined") {
				trs[i].parentNode.removeChild(trs[i]);
			}
		}
	}



	function filter_list(key) {
		// console.log("Filter List: "+ key)
		var filters = $query('input[name^=filter]');
		var checked = []
		for(var i=0,len=filters.length; i<len; i++) {
			if(filters[i].checked) {
				checked[checked.length] = filters[i].value;
			}
		}
		// console.log(checked)
		elems = $query('#filter_examples [data-filters]')
		// var elems = $query('#filter_examples ol > li');

		// console.log(elems);
		var test;
		var checked_length = checked.length;
		for(var i=0,len=elems.length; i<len; i++) {
			test = (checked_length == 0 ? true : false);
			for(j in checked) {
				r = new RegExp('(^|\\s)'+ checked[j] + '(\\s|$)');
				if(r.test(elems[i].dataset.filters)) {
					test = true;
					break;
				}
			}
			if(test) {
				elems[i].style.display = "";
			} else {
				elems[i].style.display = "none";
			}
		}
		
	}

	function parse_markdown() {
		var markdown = document.getElementById('markdown').value;
		var preview = document.getElementById('preview');

		preview.innerHTML = micromarkdown.parse(markdown);
	}
	parse_markdown();

	function open_tabs(evt, tabname) {
		var i, x, tablinks;
		x = document.getElementsByClassName("tabs");
		for (i = 0; i < x.length; i++) {
			x[i].style.display = "none";
		}
		tablinks = document.getElementsByClassName("tablink");
		for (i = 0; i < x.length; i++) {
			tablinks[i].className = tablinks[i].className.replace(" w3-red", ""); 
		}
		document.getElementById(tabname).style.display = "block";
		evt.className += " w3-red";
	}
</script>

<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional ƒHP Functions
##################################################

##################################################
#	EOF
##################################################
?>