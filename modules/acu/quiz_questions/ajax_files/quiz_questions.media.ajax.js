// apid = 60a69b5db32e2cd77ecb285959f81df8

var media_type_id = 0,
	folder = "",
	filename = "",
	quiz_question_id = 0,
	upload_id = 0;

function add_quiz_question_media() {

	$.ajax({
		"type": "POST"
		,"url": "/ajax.php"
		,"data": {
			"apid": "60a69b5db32e2cd77ecb285959f81df8"
			,"add_quiz_question_media": true
			,"quiz_question_id": 0 // optional
			,"media_type_id": media_type_id
			,"folder": folder
			,"filename": filename
		}
		,"dataType": "json"
		,"success": function(data) {

			if (typeof ajax_debugger == "function" && typeof data.debug != "undefined") {
				ajax_debugger(data.debug, JSON.stringify(data.debug).length);
				data.debug = null;
			}

			if (data.success) {
				// returns data.upload_id
				// write success behavior
			}

		}
	});

}

function update_quiz_question_media() {

	$.ajax({
		"type": "POST"
		,"url": "/ajax.php"
		,"data": {
			"apid": "60a69b5db32e2cd77ecb285959f81df8"
			,"update_quiz_question_media": true
			,"quiz_question_id": quiz_question_id
			,"media_type_id": media_type_id // optional
			,"upload_id": upload_id
		}
		,"dataType": "json"
		,"success": function(data) {

			if (typeof ajax_debugger == "function" && typeof data.debug != "undefined") {
				ajax_debugger(data.debug, JSON.stringify(data.debug).length);
				data.debug = null;
			}

			if (data.success) {
				// write success behavior
			}

		}
	});

}