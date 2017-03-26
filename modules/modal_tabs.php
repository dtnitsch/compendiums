<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

##################################################
#	Validation
##################################################

##################################################
#	DB Queries
##################################################

##################################################
#	Pre-Content
##################################################
// $info = (!empty($_POST) ? $_POST : array());
add_css('modal.css');
add_js('modal.js');

##################################################
#	Content
##################################################
?>
<!-- The Modal -->
<div id="modal_tabs" class="modal">
	<!-- Modal content -->
	<div class="modal_outer">
		<div class='modal_header'>
			<span class="close" onclick="hide('modal_tabs')">&times;</span>
			Create Tab
		</div>
		<div class="modal_inner">
			<div id="modal_tabs_details">
				<label class="form_label" for="title">Tab Name</label>
				<div class="form_data">
					<input type="text" name="tab_name" id="tab_name" value="" placeholder="Tab Name">
				</div>

				<label class="form_label" for="title">Tab Color</label>
				<div class="form_data">
					<input type="text" name="tab_color" id="tab_color" value="" placeholder="Tab Color">
				</div>


				<button onclick="build_modal_tab();">Add Tab</button>

			</div>
		</div> <!-- end modal inner -->
	</div>
</div>

<?
##################################################
#	Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">
	modal_init("modal_tabs");

	function build_modal_tab() {
		var info = {
			"name": $id("tab_name").value
			,"color": $id("tab_color").value
		};
		add_compendium_buttons(info);
		modal_clear("modal_tabs");
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