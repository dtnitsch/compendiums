<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__));   # Debugger
// Required for Posting
post_queue($module_name);

if(empty($_SESSION['user']['id'])) {
	safe_redirect("/player-login/");
	die();
} else if(empty($_SESSION['user']['student_id'])) {
    if(!empty($_SESSION['user']['id'])) {
	    if(!choose_adult_by_user_id($_SESSION['user']['id'])) {
	    	echo 'SELECTION OF ADULT FAILED';
	    	safe_redirect("/choose-player/");
	    	die();
	    }
	} else {
		safe_redirect("/player-login/");
	    die();
	}
} else if(!empty($_SESSION['user']['pin'])) {
    // run_module("choose_student");
    safe_redirect("/myaccount/");
    die();
}

##################################################
#   Validation
##################################################

##################################################
#   DB Queries
##################################################

##################################################
#   Pre-Content
##################################################
$GLOBALS['load_sidebar'] = 'myaccount';

##################################################
#   Content
##################################################
?>



	<div class="headline" id="headline155"><h1>PIN Secured Content</h1></div>
	<div class="yellowbox">
		<h2>Enter your PIN to access this account information.</h2>
		<form>
			<input name="pin" id="pin" type="password" autocomplete="off">
			<input type="submit" id="submit_pin" value="Enter your PIN">
		</form>
	</div>

	<div id="forgotpinalert" class="alert">&nbsp;</div>
	<div id="contactloginform" class="pinforgotpasswordform">
	<form  method="post" action="javascript:ajaxrecoverpin()" name="forgotpin">
		<input type="hidden" name="required" value="registeredemail">
		<input type="hidden" name="req" value="forgotpin">

		<div id="forgotpassdesc"><h2>Forgot your PIN?</h2>Simply enter your registered email and we will send it to you:</div>
		<div class="row"></div>
		<div id="forgotpassname" class="fieldname">Registered Email:</div>
		<div id="forgotpass"><input type="text" name="registeredemail" size="15" value=""></div>
		<div class="row"></div>
		<div class="fieldname">&nbsp;</div>
		<div id="submitbutton" class="fieldvalue"><button type="button" onclick="ajaxrecoverpin()">Retrieve PIN</button></div>
		<div class="row"></div>
	</form>
	</div>

<?php
##################################################
#   Javascript Functions
##################################################
ob_start();
?>
<script>

var cnt = 0,
	pin_redirect = "<?php echo (!empty($_SESSION["pin_redirect"]) ? $_SESSION["pin_redirect"] : "/myaccount/"); ?>";

$("input#submit_pin").on("click", false, function() {

	if ($("input#pin").val().length > 0) {
		check_pin();
	} else {
		alert("PIN cannot be empty.");
	}

	return false;

});

function check_pin() {

	// ON SUCCESS CHOOSE INSTITUTION
	$.ajax({
		"type": "POST"
		,"url": "/ajax.php"
		,"data": {
			"apid": "9819e1d00a4d5ca15ea6a852f2231c56"
			,"check_pin": true
			,"pin": $("input#pin").val()
		}
		,"dataType": "json"
		,"success": function(rtn) {

			if (typeof ajax_debugger == "function" && typeof rtn.debug != "undefined") {
				ajax_debugger(rtn.debug, JSON.stringify(rtn.debug).length);
				rtn.debug = null;
			}

			if (rtn.success) {
				window.location.href = pin_redirect;
			} else {

				cnt++;

				if (cnt == 3) {
					window.location.href = "/";
				} else {
					alert("PIN incorrect.");
				}

			}

		}
	});

	return false;

}

</script>
<?php
$js = trim(ob_get_clean());
if (!empty($js)) { ADD_JS_CODE($js); }

##################################################
#   Additional PHP Functions
##################################################

function choose_adult_by_user_id($userid) {

	// First, try to select the adult on the account that was created the longest ago
	$q = "
		select
			s.id
			,s.gender_id
			,s.grade_id
			,s.firstname
			,s.lastname
			,s.institution_id
			,s.ethicspledge
			,s.created
			,g.title
			,g.alias
		from system.students as s
		left join public.grades as g on
			g.id = s.grade_id
		where
			s.user_id = ".db_prep_sql((int)$userid)."
			and (s.grade_id = 19 OR s.grade_id = 18 OR s.grade_id = 17)
			and s.active = true
		order by
			s.created
	";

	$res = db_query($q, __FUNCTION__."()");

	// If no adult could be selected, select the player created longest ago
	if (!db_num_rows($res)) {
		$q = "
		select
			s.id
			,s.gender_id
			,s.grade_id
			,s.firstname
			,s.lastname
			,s.institution_id
			,s.ethicspledge
			,s.created
			,g.title
			,g.alias
		from system.students as s
		left join public.grades as g on
			g.id = s.grade_id
		where
			s.user_id = ".db_prep_sql((int)$userid)."
			and s.active = true
		order by
			s.created
	";

	$res = db_query($q, __FUNCTION__."()");
	}

	if (db_num_rows($res)) {

		$arr = array();
		while ($row = db_fetch_row($res)) {
			$arr[0] = $row;
		}

		if (set_session_student_id($arr[0]['id'], $arr[0]['title'], $arr[0]['firstname'])){
			return true;
		} else {
			return false;
		}
		return $arr[0]['id'];
	}

	return false;

}

function set_session_student_id($sent_student_id, $sent_student_grade, $sent_student_firstname) {

	if ($sent_student_grade == 'k' || $sent_student_grade == 'K'){
		$sent_student_grade = '1';
	}

	if (!in_array($sent_student_grade, array('1', '2', '3', '4', '5', '6', '7', '8'))){
		$sent_student_grade = '4';
	}

	$_SESSION["student_id"] = (int) $sent_student_id;
	$_SESSION["is_guest"] = false;
	$_SESSION["student_grade"] = (int) $sent_student_grade;
	$_SESSION["student_firstname"] = $sent_student_firstname;
	$_SESSION["user"]["student_id"] = (int) $sent_student_id;
	$_SESSION["user"]["is_guest"] = false;
	$_SESSION["user"]["student_grade"] = (int) $sent_student_grade;
	$_SESSION["user"]["student_firstname"] = $sent_student_firstname;

	if (
			$_SESSION["user"]["student_id"] == (int) $sent_student_id &&
			$_SESSION["user"]["student_grade"] == (int) $sent_student_grade &&
			$_SESSION["user"]["is_guest"] == false

	) {
		return true;
	} else {
		return false;
	}
}

##################################################
#   EOF
##################################################