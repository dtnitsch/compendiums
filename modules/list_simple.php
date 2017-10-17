<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

##################################################
#   Validation
##################################################

##################################################
#   DB Queries
##################################################

##################################################
#   Pre-Content
##################################################

##################################################
#   Content
##################################################
?>

<div id="list_simple_details" class="clearfix" style="display:none;">
	<div class="title">List: {{title}}</div>

	<div class="filters" onclick="show_hide('filter_details')">
		Filters (<span class="filter_count" id="filter_count">0 applied</span>) <span class="small">(<span class="fakeref">show/hide</span>)</span>
	</div>
	<div class="filter_details" id="filter_details" style="display: none;">

		<form id="form_filters" method="" action="" onsubmit="return false;">
			<label for="limit_{{key}}">
				Limit Display: <input type="input" name="limit" id="limit_{{key}}" value="20" class='xs'> 
			</label>

			<label for="randomize_{{key}}">
				<input type="checkbox" name="options" id="randomize_{{key}}" value="randomize"> Randomize
			</label>

			<div id="filters_dynamic" class="mtb"></div>
		</form>

		<div class="mt">
			<input type="button" value="Update List" onclick="show_build_display('listcounter',false)">
		</div>
	</div>

	<div class='listcounter mt' id="listcounter"></div>
</div>

<?php
##################################################
#   Javascript Functions
##################################################

##################################################
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################