<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
post_queue($module_name);

if(empty($_SESSION['user']['id'])) {
    safe_redirect("/player-login/");
    die();
} else if(empty($_SESSION['user']['pin'])) {
    $_SESSION['pin_redirect'] = $_SERVER['SCRIPT_URI'];
    safe_redirect("/choose-pin/");
    die();
}

##################################################
#   Validation
##################################################

##################################################
#   DB Queries
##################################################
$q = "
	select
		students.id
		,students.firstname
		,students.gender_id
		,students.grade_id
		,students.institution_id
		,grades.alias
		,institutions.title as institution
	from system.students
	join public.grades on
		grades.id = students.grade_id
	join public.institutions on
		institutions.id = students.institution_id
	where
		students.user_id = '". $_SESSION['user']['id'] ."'
";
$students_res = db_query($q,"Getting Students");

$student_count = db_num_rows($students_res);



/**************************************************/
// START - Selecting first created player on account if none chosen already
// (This should only happen when this page is shown right after registration)
if (empty($_SESSION['user']['student_id'])){
	$new_selected_student_id = '';
	$new_selected_student_grade = '';
	$new_selected_student_firstname = '';
	$students_res2 = db_query($q,"Getting Students Again");
	while($row = db_fetch_row($students_res2)) {
		$new_selected_student_id = $row['id'];
		$new_selected_student_grade = $row['alias'];
		$new_selected_student_firstname = $row['firstname'];
		if(
			strtolower($row['alias']) == 'other'
			|| strtolower($row['alias']) == 'adult'
			){
				$new_selected_student_grade = '4';
				break; // If we found an adult, we want that to be the chosen player. If there isnt one, we just need a player.
		}
		else if(
			strtolower($row['alias']) == 'k'
			|| strtolower($row['alias']) == 'prek'
			|| strtolower($row['alias']) == 'preschool'
			){
				$new_selected_student_grade = '1';
		}
		else if(
			strtolower($row['alias']) == 'n'
			){
				$new_selected_student_grade = '8';
		}
		else if(
			intval($row['alias']) < 1
			|| intval($row['alias']) > 8
			){
				$new_selected_student_grade = '8';
		}
	}
	if ($new_selected_student_id != ''){
		$_SESSION['user']['student_id'] = $new_selected_student_id;
		$_SESSION['student_id'] = $new_selected_student_id;
		$_SESSION['user']['student_grade'] = $new_selected_student_grade;
		$_SESSION['student_grade'] = $new_selected_student_grade;
		$_SESSION['user']['student_firstname'] = $new_selected_student_firstname;
		$_SESSION['student_firstname'] = $new_selected_student_firstname;
		$_SESSION['user']['is_guest'] = $_SESSION['is_guest'];
	}
}
// END - Selecting first created player on account if none chosen already
/**************************************************/



$q = "
SELECT
		institutions.id
		,institutions.title AS institution
	FROM system.students
	JOIN public.institutions ON
		institutions.id = students.institution_id
	WHERE
		students.id = '". $_SESSION['user']['student_id'] ."'
";
$school = db_fetch($q,"Getting Default School");

##################################################
#   Pre-Content
##################################################
add_js('cck2.js');
//add_js('cck2_wip.js');

$GLOBALS['load_sidebar'] = 'myaccount';

##################################################
#   Content
##################################################
?>

<h1>Add Kids</h1>

<div id="accountchildformadd" name="childform" class="childform">
	<div class="accountsectionheader">
	Add a new kid/player
	</div>

<?php
	if(!empty($_SESSION['update_messages'])) {
		foreach($_SESSION['update_messages'] as $k => $v) {
			echo '<div class="alert">'. $v .'</div>';
		}
		unset($_SESSION['update_messages']);
	}
?>

	<form action="" method="post" name="childaddform" onsubmit="return checkaddform();">
	<input name="act" type="hidden" value="addchildren">
	<input type="hidden" name="id" value="34">
	<input type="hidden" name="sec" value="17">
	<input type="hidden" name="returnval" value="childreninfo">
	<input name="i" id="i" type="hidden" value="1">

	<div class="even">
		<div>
			<div style="border:none; padding:0px; margin:0px;">
				<label style="line-height:14px; margin-left:40px;">Kid's First Name&nbsp; <br> and Last Name Initial:</label>
			</div>
			<div style="float:left; border:none; padding:0px; margin:0px; margin-top:-25px; margin-left:165px;">
				<input autocomplete="off" name="childfirstname0" id="childfirstname0" value="" type="text" size="20">
				<input style="margin-top:-5px; padding-bottom:10px;" name="childid0" id="childid0" type="hidden" value="">
				<select id="childgrade0" name="childgrade0" size="1" class="childgrade">
					<option value="">Grade</option>
					<option value="15">Pre-Kindergarten</option>
					<option value="16">Kindergarten</option>
					<option value="1">1st Grade</option>
					<option value="2">2nd Grade</option>
					<option value="3">3rd Grade</option>
					<option value="4">4th Grade</option>
					<option value="5">5th Grade</option>
					<option value="6">6th Grade</option>
					<option value="7">7th Grade</option>
					<option value="8">8th Grade</option>
					<option value="18">Other</option>
				</select>
				<select class="childgender" id="childgender0" name="childgender0" size="1">
					<option value="">Gender (Optional)</option>
					<option value="boy">Boy</option>
					<option value="girl">Girl</option>
				</select>
			</div></div>
		<div>&nbsp;Child's School:
			<span class="schoolname"><a href="javascript:showstates_edit(0,0,0,0)" id="schoolname0"><?php echo $school['institution']; ?></a></span>
			<input id="childschoolid0" name="childschoolid0" type="hidden" value="<?php echo $school['id']; ?>">
		</div>
		</div>
		<div class="center submit">
			<input type="submit" value="Add a new kid/player">
			<input type="hidden" name="type" value="new">
			<input type="hidden" name="user_id" value="<?php echo $_SESSION['user']['id']; ?>">
		</div>
	</form>
	</div>





		<div id="accountchildrenform" name="childform" class="childform">
			<div class="accountsectionheader">
			Update your registered kids/players (<?php echo $student_count; ?>)
			</div>

			<form action="" method="post">
			<input type="hidden" name="act" value="updatechildren">
			<input type="hidden" name="id" value="34">
			<input type="hidden" name="sec" value="17">
			<input type="hidden" name="returnval" value="childreninfo">
			<input type="hidden" name="type" value="update">

			<div id="existingchildren">
				<div class="accountsectionnextprev">
					<div class="nextprev">1 thru <?php echo $student_count; ?> out of <?php echo $student_count; ?></div>
				</div>
<?php
$grades = '
							<select id="childgradeXXX" name="childgradeXXX" size="1" class="childgrade">
								<option value="K">Kindergarten</option>
								<option value="1">1st Grade</option>
								<option value="2">2nd Grade</option>
								<option value="3">3rd Grade</option>
								<option value="4">4th Grade</option>
								<option value="5">5th Grade</option>
								<option value="6">6th Grade</option>
								<option value="7">7th Grade</option>
								<option value="8">8th Grade</option>
								<option value="Other">Other</option>
							</select>
';

$gender = '
						<select class="childgender" id="childgenderXXX" name="childgenderXXX" size="1">
								<option value="">Gender (Optional)</option>
								<option value="girl">Girl</option>
								<option value="boy">Boy</option>
							</select>
';

	$count = 1;
	while($row = db_fetch_row($students_res)) {
		$grade_id = $row['alias'];
		if(!is_numeric($grade_id)) {
			$grade_id = ucfirst($grade_id);
		}

		 $gender_id = '';
		 if($row['gender_id'] == 1) {
		 	$gender_id = 'boy';
		 } else if($row['gender_id'] == 2) {
		 	$gender_id = 'girl';
		 }

		 $grade_str = str_replace('XXX',$count,$grades);
		 $gender_str = str_replace('XXX',$count,$gender);

?>
				<div class="even">
					<div>
						<div style="border:none; padding:0px; margin:0px;">
							<label style="line-height:14px; margin-left:40px;">Kid's First Name&nbsp; <br> and Last Name Initial:</label>
						</div>
						<div style="float:left; border:none; padding:0px; margin:0px; margin-top:-25px; margin-left:165px;">
							<input autocomplete="off" name="childfirstname<?php echo $count; ?>" id="childfirstname<?php echo $count; ?>" value="<?php echo $row['firstname']; ?>" type="text" size="20">
							<input style="margin-top:-5px; padding-bottom:10px;" name="childid<?php echo $count; ?>" id="childid<?php echo $count; ?>" type="hidden" value="<?php echo $row['id']; ?>">
							<?php echo str_replace('value="'. $grade_id .'"','value="'. $grade_id .'" selected',$grade_str); ?>
							<?php echo str_replace('value="'. $gender_id .'"','value="'. $gender_id .'" selected',$gender_str); ?>

						</div></div>
					<div>&nbsp;Child's School:
						<span class="schoolname"><a href="javascript:showstates_edit(<?php echo $count; ?>,<?php echo $row['institution_id']; ?>,<?php echo $row['id']; ?>,<?php echo $count; ?>)" id="schoolname<?php echo $count; ?>"><?php echo $row['institution']; ?></a></span>
						<input id="childschoolid<?php echo $count; ?>" name="childschoolid<?php echo $count; ?>" type="hidden" value="<?php echo $row['institution_id']; ?>">
					</div>
				</div>
<?php
		$count++;
	}
?>

				</div><div class="accountsectionnextprev"><div class="nextprev">1 thru <?php echo $student_count; ?> out of <?php echo $student_count; ?>
</div>
</div></div>
			<div class="center submit"><input type="submit" value="Update your registered kids/players"></div>
			<input name="totalchildren" id="totalchildren" type="hidden" value="3">
			</form>
			</div>

<?php
##################################################
#   Javascript Functions
##################################################
ob_start();
?>
	<script type="text/javascript">

	var chosen_state = '';
	var loopcount_id = '';
	var student_id = '';
	var row_id=0;

	function show_state_schools(state) {
		chosen_state = state;
		hide('state_options');
		show('state_schools');
	}

	function update_school_information(school_id) {
		// console.log(loopcount_id);
		// console.log(school_id);
		// console.log(student_id);

		var data = 'apid=5a50ce49941d5de2f7f7295f17790750&school_id='+ school_id +'&student_id='+ student_id;

		ajax({
			url: '/ajax.php'
			,debug: false
			,data: data
			,callback: update_school_display
		});
	}

	function update_school_display(data) {
		data = JSON.parse(data);
		output = JSON.parse(data.output);
		$id('schoolname'+loopcount_id).innerHTML = output.title;
		$id('childschoolid'+loopcount_id).value = output.school_id;

		closeprivacy();
	}


	function checkaddform() {
		var strMsg = "";
		if(document.childaddform.childfirstname0.value == "") {
			strMsg = "Child name cannot be blank";
			alert(strMsg);
			document.childaddform.childfirstname0.focus();
			return false;
		}

		if(document.childaddform.childgrade0.value == "") {
			strMsg = "Child grade cannot be blank";
			alert(strMsg);
			document.childaddform.childgrade0.focus();
			return false;
		}

		return true;
	}

	function update_institution_ed(institution_id,rr_id) {

		var institution_name_id = "schoolname"+rr_id;
		var institution_id_id = "childschoolid"+rr_id;
		var institution_id_outerHTML = '<input id="'+institution_id_id+'" name="'+institution_id_id+'" type="hidden" value="'+institution_id+'">';
		document.getElementById(institution_name_id).innerHTML = document.getElementById("selectschool"+institution_id).innerHTML;
		document.getElementById(institution_id_id).outerHTML = institution_id_outerHTML;
		closeprivacy();
	}
	</script>
<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################