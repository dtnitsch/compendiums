function toggle_permissions(obj) {
	var is_checked = (obj.checked ? true : false);
	if(!is_checked) { 
		disable_all(obj);
		return;
	}
	var inputs = $id(obj.id + '_body').getElementsByTagName('input');
	var first = obj.id[0];
	var len = inputs.length;
	var i = 0;
	while(i < len) {
		if(first == 's' && inputs[i].id[0] == 'g') {
			inputs[i].disabled = !inputs[i].disabled;
			if(!inputs[i].disabled && inputs[i].checked) {
				toggle_permissions(inputs[i]);
			}
		} else if (first == 'g' && inputs[i].id[0] == 'p') {
			inputs[i].disabled = !inputs[i].disabled;
		}
		i++;
	}

}

function disable_all(obj) {
	var inputs = $id(obj.id + '_body').getElementsByTagName('input');
	var first = obj.id[0];
	var len = inputs.length;
	var i = 0;
	while(i < len) {
		inputs[i].disabled = true;
		i++;
	}
}


function check_all(obj) {
	var inputs = $id(obj.id + '_body').getElementsByTagName('input');

	var first = obj.id[0];
	var current_checked = obj.checked || false;

	var len = inputs.length;
	var i = 0;
	while(i < len) {
		inputs[i].checked = (current_checked ? true : false);
		i++;
	}
	if(!current_checked && first == "g") {
		uncheck_parent(obj)
	}
}

function uncheck_parent(obj) {

	var first = obj.id[0];
	var section_id = "s"+ obj.dataset["section"];
	var group_id = "g"+ (obj.dataset["group"] || "");

	if(obj.checked == false) {
		$id(section_id).checked = false;
		if(first == "p") {
			$id(group_id).checked = false;
		}
	}


}