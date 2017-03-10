var validation = (function() {
	var messages_id = "messages";
	var msg = {
		"required": "'{label}' is a required field"
		,"string_length": "'{label}' does not match the string length of {val1}"
		,"string_length_min": "'{label}' does not meet the minimum length of {val1}"
		,"string_length_max": "'{label}' exceeds the maximum length of {val1}"
		,"string_length_between": "'{label}' must be between {val1} and {val2} characters"
		,"integer": "'{label}' is not a valid integer"
		,"int": "'{label}' is not a valid integer"
		,"int_between": "'{label}' must be between {val1} and {val2} "
		,"int_min": "'{label}' must be greater than {val1} "
		,"int_max": "'{label}' must be less than {val1} "
		,"integer_between": "'{label}' must be between {val1} and {val2} "
		,"match": "'{label}' does not match {val1}"
		,"compare": "'{label}' does not match {val1}"
		,"equal": "'{label}' does not correctly equal {dynamic1}"
		,"numeric": "'{label}' is not a valid number"
		,"numeric_between": "'{label}' must be between {val1} and {val2} "
		,"string": "'{label}' is not a valid string"
		,"str": "'{label}' is not a valid string"
		,"alpha": "'{label}' is not a valid alpha-only string"
		,"alphanum": "'{label}' is not a valid alphanumeric only string"
		,"alphanumeric": "'{label}' is not a valid alphanumeric only string"
		,"array": "'{label}' is not a valid array"
		,"bool": "'{label}' is not a valid boolean value"
		,"valid_bool": "'{label}' is not a valid boolean value"
		,"datetime": "'{label}' is not a valid datetime"
		,"email": "'{label}' is not a valid email address"
		,"json": "'{label}' is not valid JSON format"
		,"in": "'{label}' is not within accepted values {valx} "
		,"regex": "'{label}' does not match the regular expression requirements"
		,"regularexpression": "'{label}' does not match the regular expression requirements"
	}

    function validation(id) {
    	this.msg = msg;
    	this.onclicks_attached = false;
    	this.id = id;
    	this.custom_validation = {};
    	this.custom_msg = {};
    }
    validation.prototype.load_json = function(json) {
    	return this.json = json;
    }
    validation.prototype.msg = function(name) {
    	return msg[name];
    }
    validation.prototype.validate = function(key) {
    	var validators, pieces, i, len, res, value, elem, func;
    	this.errors = [];
    	for(key in this.json) {
    		if(this.json[key].hasOwnProperty[key]) {
    			continue;
    		}
    		elem = $id(key);
    		if(!this.onclicks_attached) {
				elem.onchange = new Function(this.id +".validate(this.id)");
    		}
    		removeclass(key,"error");
    		i = 0;
    		len = this.json[key].validators.length;

    		while(i<len) {
    			pieces = this.split_validator(this.json[key].validators[i])
    			value = elem.value;

    			if(typeof this[pieces[0]] == "function") {
    				func = this[pieces[0]];
    			} else if(this.custom_validation[pieces[0]]) {
    				func = this.custom_validation[pieces[0]];
    			}

    			if(!func(value,pieces[1])) {
    				//console.log("FAIL - "+ key +" -- "+ value +" -- "+ pieces[0] +" -- "+ pieces[1]);
    				addclass(elem,"error");
    				this.set_error_message(key,this.json[key],pieces[0],pieces[1]);
    				break;
    			}

    			i++;
    		}
    		
    	}
    	

    	if(this.errors.length) {
			$id(messages_id).innerHTML = '<div class="error_message">'+ this.errors.join('</div><div class="error_message">') +'</div>';
    	} else {
    		$id(messages_id).innerHTML = "";
    	}
    	this.onclicks_attached = true;

    	// return (this.errors.length ? false : true);
    	return false;
    }

	validation.prototype.set_error_message = function(key,json,validator,params) {
		var label = json.label || key;
		var i = params.length;
		var msg = this.msg[validator] || "";
		if(msg == "" && typeof this.custom_msg[validator] != "undefined") {
			msg = this.custom_msg[validator];
		}

		if(msg == "") {
			return false;
		}

		if(msg.indexOf('{valx}') != -1) {
			msg = msg.replace('{valx}',params.join(', '));
		// } else if(msg.indexOf('{dynamic') != -1) {
		// 	console.log("dynamic: "+ params[0])
		// 	msg = msg.replace('{dynamic}',$id(params[0]).value);
		} else if(i) {
			while(i--) {
				msg = msg.replace('{val'+ (i + 1) +'}',params[i])
				msg = msg.replace('{dynamic'+ (i + 1) +'}',$id(params[i]).value)
			}
		}

		msg = msg.replace('{label}',label);
		this.errors.push(msg);
	}

	validation.prototype.validate_check = function(value,validator) {
		var pieces;
		pieces = this.split_validator(validator);
		return this[pieces[0]](value,pieces[1]);
	}

	validation.prototype.split_validator = function(validator) {
		var params = "";
		var pieces;
		if(validator.indexOf(":") !== -1) {
			var pieces = validator.split(":");
			validator = pieces[0];
			params = pieces[1];
		}
		validator = validator.toLowerCase();
		return [validator,params.split(",")];
	}
	validation.prototype.required = function(value) {
		return (value ? true : false);
	}
	validation.prototype.string_length_between = function(value,params) {
		return (typeof value[parseInt(params[0])-1] == "undefined" || typeof value[parseInt(params[1])] == "string" ? false : true);
	}
	validation.prototype.match = function(value,params) {
		return (value == params[0] ? true : false);
	}
	validation.prototype.compare = function(value,params) {
		return (value == params[0] ? true : false);
	}
	validation.prototype.equal = function(value,params) {
		return (value == $id(params).value ? true : false);
	}
	validation.prototype.email = function(value) {
		return (value.match(/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/) ? true : false);
	}

	validation.prototype.custom_validation = function(value,params) {
		return this.custom_validation[name](value,params);
	}

	validation.prototype.custom = function(name,func,msg) {
		msg = msg || msg;
		this.custom_validation[name] = func;
		this.custom_msg[name] = msg || "";
	}

    return validation;
}());