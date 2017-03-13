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
		,"equal": "'{label}' does not correctly equal {val1}"
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
    	this.force_run = false;
    	this.id = id;
    	this.custom_validation = {};
    	this.custom_msg = {};
    	this.errors = {}
    	this.equal_check = {}
    }
    validation.prototype.load_json = function(json) {
    	// check for messages
    	var i = json.length; 
    	while(i--) {
			if(typeof json[i].id == "undefined" && typeof json[i].messages != "undefined") {
				this.load_messages(json.splice(i,1)[0].messages);
			}
    	}
    	return this.json = json;
    }
    validation.prototype.load_messages = function(msgs) {
    	for(i in msgs) {
    		if(msgs.hasOwnProperty(i)) {
    			this.msg[i] = msgs[i];	
    		}
    	}
    }
    validation.prototype.msg = function(name) {
    	return msg[name];
    }
    validation.prototype.validate = function(id,name) {
    	var id = id || "";
    	var name = name || "";
    	var keys = [];
    	var validators, pieces, res, value, elem, func;
		var len = this.json.length;
	    var i = 0;

    	if(name) {
    		while(i < len) {
				if(this.json[i].name == name) {
					this.validate_key(this.json[i]);
					break;
				}
	    		i++;
	    	}
    	} else if(id) {
			while(i < len) {
				if(this.json[i].id == id) {
					this.validate_key(this.json[i]);
					break;
				}
	    		i++;
	    	}
    	} else {
	    	while(i < len) {
	    		this.validate_key(this.json[i]);
	    		i++;
	    	}
    	}

    	output = [];
    	for(i in this.errors) {
			output.push('<div class="error_message">'+ this.errors[i] +'</div>');
    	}
    	if($id(messages_id)) {
    		$id(messages_id).innerHTML = output.join("");	
    	} else {
    		console.log("Missing area to output errors");
    		console.log(output);
    	}
    	this.onclicks_attached = true;

	   	return (output.length ? false : true);
    }

	validation.prototype.validate_key = function(info) {
		key = info.id;
		elem = $id(key);
		optional = info.optional || false;
		this.attach_event(info);

		removeclass(key,"error");
		i = 0;
		len = info.validators.length;

		while(i<len) {
			elem = $id(key);
			value = elem.value;
			key = (elem ? elem.id : info.id);

			pieces = this.split_validator(info.validators[i])
			if(pieces[0] == "equal") {
				this.equal_check[pieces[1]] = key;
			}

			if(!this.force_run && optional && !value) {
				if(this.errors[key]) {
					this.delete_error_key(key);	
				}
				i++;
			// 	if(this.errors[key]) {
			// 		console.log(i +": "+elem.name +": "+ value)
			// 		// console.log(arguments)
			// 		this.delete_error_key(key);
			// 		if(typeof this.equal_check[key] != "undefined") {
			// 			console.log(key +" ---- "+ this.equal_check[key])
			// 			this.validate(this.equal_check[key]);
			// 		}
			// 	}
				continue;
			}


			if(info.multiple && typeof this[pieces[0] +"_multiple"] == "function") {
				func = this[pieces[0] +"_multiple"];
			} else if(typeof this[pieces[0]] == "function") {
				func = this[pieces[0]];
			} else if(this.custom_validation[pieces[0]]) {
				func = this.custom_validation[pieces[0]];
			}


			if(!func(value,pieces[1],info)) {
				addclass(elem,"error");
				this.set_error_message(info,pieces[0],pieces[1]);
				break;
			}
			this.delete_error_key(key);

			// Required for anything set to "equal" so we can re-validate that as well

			i++;
		}

		this.force_run = false;

		if(typeof this.equal_check[key] != "undefined") {
			if(value) {
				this.force_run = true;
			}
			this.validate(this.equal_check[key]);
		}

   	}

	validation.prototype.delete_error_key = function(key) {
		if(typeof this.errors[key] != "undefined") {
			this.errors[key] = null;
			delete this.errors[key];
		}
	}

   	validation.prototype.attach_event = function(info) {
		key = info['id'];
		elem = $id(key);
		name = info.name || '';
		if(elem && !info.multiple) {
			if(!this.onclicks_attached) {
				elem.onchange = new Function(this.id +".validate(this.id)");
			}			
		} else if(info.multiple && name && document.getElementsByName(name).length) {
			elems = document.getElementsByName(name);
			var i = elems.length;
			while(i--) {
				elems[i].onchange = new Function(this.id +".validate(this.id,this.name)");
			}
		}
   	}

	validation.prototype.set_error_message = function(json,validator,params) {
		var label = json.label || json.id;
		var i = params.length;
		var msg = this.msg[validator] || "";

		if(typeof json.messages != "undefined" && typeof json.messages[validator] != "undefined") {
			msg = json.messages[validator];
		} else if(msg == "" && typeof this.custom_msg[validator] != "undefined") {
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
		this.errors[json.id] = msg;
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

	validation.prototype.optional = function(id,val) {
	    var len = this.json.length;
	    var i = 0;
    	while(i < len) {
    		if(this.json[i].id == id) {
    			this.json[i].optional = val;
    			return true;

    		}
    		i++;
    	}
    	return false;
	}


	validation.prototype.required = function(value) {
		return (value ? true : false);
	}

	validation.prototype.required_multiple = function(value,param,info) {
		if(info.multiple && info.name) {
			elems = document.getElementsByName(info.name);
			var i = elems.length;
			while(i--) {
				if(elems[i].checked) {
					return true;
				}
			}
		}
		return false;
	}
	validation.prototype.string_length_between = function(value,params) {
		return (typeof value[parseInt(params[0])-1] == "undefined" || typeof value[parseInt(params[1])] == "string" ? false : true);
	}
	validation.prototype.string_length_min = function(value,params) {
		return (value.trim().length >= params[0] ? true : false);
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
