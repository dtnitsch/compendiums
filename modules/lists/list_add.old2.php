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

add_js("markdown.min.js");
add_js("list_functions.js");

##################################################
#	Content
##################################################
?>
	<form id="addform" method="post" action="" onsubmit="return validate_list();">
		<h2 class='lists'>Add List</h2>

  		<a href="#messages"></a>
  		<div id="messages">
			<?php echo dump_messages(); ?>
		</div>

		<div id="list_buttons" class="tabbar">
			<button type="button" class="tablink active" onclick="open_tabs(this,'default','list')">Default</button>
			<button type="button" class="tablink" onclick="open_tabs(this,'md','list')">Information</button>
			<div class="float_right">
				<button class="tablink save">Save List</button>
			</div>
		</div>
		<div id="list_bodies" class="tabbody">
			<div id="default" class="tabs">

				<div class="float_left" style="width: 59%;">

					<label class="form_label" for="title">List Name <span>*</span></label>
					<div class="form_data">
						<input type="text" name="title" id="title" class="xl" value="<?php echo (!empty($info['title']) ? $info['title'] : ""); ?>">
					</div>
<!--
one;odd
two;even
three;odd
four;even
five;odd
six;even

Word|Number
one|1|Lorem;odd
two|2|Lorem;even
three|3|Lorem;odd
four|4|Lorem;even
five|5|Lorem;odd
six|6|Lorem;even
-->
					<label class="form_label" for="title">Inputs</label>
					<div class="form_data">
						<textarea name="inputs" id="inputs" onchange="example(event,true)" onkeyup="example(event)" style="width: 90%; height: 250px;"><?php echo (!empty($info['inputs']) ? $info['inputs'] : ""); ?></textarea>
						<div style="font-size: 80%;">*Notes: Semicolon ";" Deliminated List - Name; Optional Percentage; Optional Filters</div>
					</div>

				</div>
				<div class="float_left" style="width: 39% padding: 1em;">

					<table cellspacing="0" id="filters_table" class="tbl" style='display: none; margin-bottom: 1em;'>
						<thead>
							<tr>
								<th>&nbsp;</th>
								<th>Label</th>
								<th>Slug</th>
								<th>Order</th>
							</tr>
						</thead>
						<tbody id="filters_table_tbody"></tbody>
					</table>
					<div id="example"></div>

				</div>


			</div>
			<div id="md" class="tabs" style="display: none">

				<textarea name="markdown" id="markdown" class="markdown" onkeyup="parse_markdown()"><?php echo (!empty($info['markdown']) ? $info['markdown'] : ''); ?></textarea>
				<article id="preview" class="markdown_body"></article>

				<div class="clear mt"></div>
				<button type="button" onclick="example_markdown()">Generate Example Markdown</button>

			</div>

			<div class="clear"></div>
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
	function example_markdown() {
		$id('markdown').value = `#Header

Paragraphs are separated by a blank line.

2nd paragraph. *Italic*, **bold**, and \`monospace\`. Itemized lists
look like:

* this one
* that one
* the other one

##Header 2

Here's a numbered list:

1. first item
2. second item
3. third item

`;
		parse_markdown();
	}

	function validate_list() {
		return validate({'title':'List Name','inputs':'Inputs'});
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
