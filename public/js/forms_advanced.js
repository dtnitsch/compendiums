
// Catch all function to handle all the post elements within a given form element set.
// Parses out information much like PHP's post would to help save space in the post



// Check, uncheck, invert checkboxes
function checkbox_selection(container_id,forced_value) {
	forced_value = forced_value.toLowerCase();
	var convert_type = '';
	if(forced_value == 'all') { convert_type = true; }
	else if(forced_value == 'none') { convert_type = false; }
	else if(forced_value == 'invert') { convert_type = 'invert'; }
	
	if(!$id(container_id)) { return; }
	var elems = $id(container_id).getElementsByTagName("input");
	var len = elems.length;
	var i = 0;
	// If the forced value is set to invert, then run this slightly more complex version
	if(convert_type == 'invert') {
		while(i < len) {
			if(elems[i].type == 'checkbox') { elems[i].checked = (elems[i].checked ? false : true); }	
			i++;
		}
	} else {
		// Convery all the checkboxes into their new forced values
		while(i < len) {
			if(elems[i].type == 'checkbox') { elems[i].checked = convert_type; }	
			i++;
		}
	}
	
}
