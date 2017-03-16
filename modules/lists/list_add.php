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

##################################################
#	Content
##################################################
?>
	<h2 class='lists'>Add List</h2>
  
  	<div id="messages">
		<?php echo dump_messages(); ?>
	</div>
	<form id="addform" method="post" action="" onsubmit="return v.validate();">

		<div class="float_left" style="width: 49%;">
			<label class="form_label" for="title">List Name <span>*</span></label>
			<div class="form_data">
				<input type="text" name="title" id="title" value="">
			</div>

			<!--label class="form_label">Visibility</label>
			<div class="form_data">
				<label for="public"><input type="radio" name="visibility" id="public" value="public"> Public</label>
				<label for="private"><input type="radio" name="visibility" id="private" value="private"> Private</label>
			</div-->

			<label class="form_label" for="title">Inputs</label>
			<div class="form_data">
				<!--textarea name="inputs" id="inputs" onchange="show_example()" onkeyup="show_example()" style="width: 400px; height: 150px;">Chicken; 30; poor,middle class,rich,lunch,dinner
Beef; 5; middle class,rich, lunch, dinner
Oysters; 5; poor,rich, dinner
Eggs; 10; poor,middle class,rich,breakfast
Soup; 20; poor,middle class, lunch, dinner
Bread; 20; poor,middle class,rich, breakfast, lunch, dinner
Apples; 10; poor,middle class,rich, snack</textarea-->
				<textarea name="inputs" id="inputs" onchange="show_example()" onkeyup="show_example()" style="width: 90%; height: 150px;">
				Name|Color|Thiny
				Orange|Orange|yucky;;orange
				Sky|Blue|Clouds;;blue,orange
				Water|Transparent|Wet;;clear
				Computer|Silver|Apple;;Silver
				Ore|Silver,Black|Nasty;;black,silver
				</textarea>
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
		<div id="example" class="float_left" style="width: 49% padding: 1em;"></div>
		<div class="clear"></div>
		<input type="submit" value="Add List">
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

		var output = "<strong>Example Output</strong>";
		output += '<div id="filter_examples">';
		output += "<br>"+ build_filters();

		output += (is_table ? '<table cellspacing="0" cellpadding="0" class="list_table"><thead>' : '<ol class="mt">');
		percentages = 0;
		// // Limit the demo exmaple?
		// if(len > 10) {
		// 	len = 10;
		// }
		for(var i=0; i<len; i++) {
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

  			output += (is_table ? '<tr'+ filters +'>'+ inner.trim() +'</tr>' : '<li'+ filters +'>'+ inner_pieces[0].trim() +'</li>');
		}


		output += (is_table ? "</tbody></table>" : "</ol>");
		output += '</div>';
		$id('example').innerHTML = output;
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
					tag = tags[j].trim().toLowerCase();
					output[tag] = 1;
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