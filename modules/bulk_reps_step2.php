<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
post_queue("bulk_reps_step2");
// if(!HAS_ACCESS('dynamic_web_pages')) { BACK_REDIRECT(); }

if(empty($_SESSION['user']['id'])) {
    safe_redirect("/player-login/");
    die();
} else if(empty($_SESSION['user']['student_id'])) {
    // run_module("choose_student");
    safe_redirect("/choose-player/");
    die();
} else if(empty($_SESSION['user']['pin'])) {
    // run_module("choose_student");
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


$q = "select user_id,institution_id,grade_id from system.students where id = '". db_prep_sql($_POST['siu_student_id']) ."'";
$student_info = db_fetch($q,"Getting student info");
$grade_id = (empty($student_info['grade_id']) ? 8 : $student_info['grade_id']);

// Step it up form data
$q = "
	select
		siu.id
		,siu.title
	    ,am.id as activity_map_id
	from activities.step_it_up as siu
	left join public.activity_map as am on
		am.content_id = siu.id
		and am.grade_id = '". $grade_id ."'
";
$res = db_query($q,"Getting form data");

$titles = array();
while ($row = db_fetch_row($res)) {
	if (empty($row['activity_map_id'])) {
		$row['activity_map_id'] = 0;
	}
	$titles[$row['id']] = $row;
}

$start_date = strtotime('last sunday');
$end_date = strtotime('this sunday');

// Input Data
$q = "
	select
		s.reps
		,s.created
		,am.content_id
		,am.id as activity_map_id
	from public.activity_scores as s
	join public.activity_map am on
		am.id = s.activity_map_id
		and am.grade_id = '". $grade_id ."'
	where
		s.student_id = '". db_prep_sql($_POST['siu_student_id']) ."'
		and s.created >= '". date('Y-m-d 00:00:00',$start_date) ."' and s.created <= '". date('Y-m-d 23:59:59',$end_date) ."'
		and s.reps > 0
";
$input_res = db_query($q,"Getting previous inputs");

$reps = array();
while($row = db_fetch_row($input_res)) {
	$row['created'] = strtotime($row['created']);
	$reps[$row['content_id']][$row['created']][$row['activity_map_id']] = $row['reps'];
}
##################################################
#   Pre-Content
##################################################
$siu_student_name = (!empty($_POST['siu_student_name']) ? $_POST['siu_student_name'] : "none");
$siu_student_id = (!empty($_POST['siu_student_id']) ? $_POST['siu_student_id'] : "0");
$siu_student_grade = (!empty($_POST['siu_student_grade']) ? $_POST['siu_student_grade'] : "0");



$GLOBALS["activity_info"]["title"] = (empty($info["title"]) ? "Clever Crazes" : $info["title"]);
$GLOBALS["activity_info"]["is_guest"] = (empty($_SESSION["is_guest"]) ? "false" : $_SESSION["is_guest"]);
$GLOBALS["activity_info"]["user_id"] = (empty($_SESSION["user"]["id"]) ? "false" : $_SESSION["user"]["id"]);
$GLOBALS["activity_info"]["student_id"] = (empty($_SESSION["student_id"]) ? "false" : $_SESSION["student_id"]);
$GLOBALS["activity_info"]["student_grade"] = (empty($_SESSION["student_grade"]) ? "false" : $_SESSION["student_grade"]);
$GLOBALS["activity_info"]["student_firstname"] = (empty($_SESSION["student_firstname"]) ? "false" : $_SESSION["student_firstname"]);

$GLOBALS['load_sidebar'] = 'myaccount';

##################################################
#   Content
##################################################

?>

<?php echo DUMP_MESSAGES(); ?>

<div class="headline">

	<h1><?php echo (empty($info["title"]) ? "Welcome to CleverCrazes.com" : $info["title"]); ?></h1>

</div>

<?php

if(!empty($GLOBALS['siu_success_message'])) {
	echo "<h3>". $GLOBALS['siu_success_message'] ."</h3>";
	unset($GLOBALS['siu_success_message']);
}

?>

<div class="body">
	<p align="center">
	<b>Child's Name: </b><?php echo $siu_student_name; ?><br />
	<a href="/bulk_reps_step1/">Select another student</a><br />
	</p>
	<form action="" method="POST">
	<input type="hidden" name="siu_student_id" value=<?php echo '"'.$siu_student_id.'"'; ?>></input>
	<input type="hidden" name="siu_student_name" value=<?php echo '"'.$siu_student_name.'"'; ?>></input>
	<input type="hidden" name="siu_student_grade" value=<?php echo '"'.$siu_student_grade.'"'; ?>></input>
	<input type="hidden" name="start_date" value=<?php echo '"'.$start_date.'"'; ?>></input>
	<input type="hidden" name="end_date" value=<?php echo '"'.$end_date.'"'; ?>></input>
	<input type="submit" value="Enter <?php echo $siu_student_name;?>'s reps for this week"></input><br />
	<br />
	<table bgcolor="#ffffff" border="1" cellpadding="2" cellspacing="0" width="100%" style="font-size:12px;">
		<tr bgcolor="cccccc">
			<th width="30%">Excercise name</th>
				<?php

					$tmp = $start_date;
					$dates = array();
					$colcount = 0;
					$output = "";
					while($tmp < $end_date) {
						$day_of_week = "Sunday";
						switch ($colcount) {
							case 1:
								$day_of_week = 'Mon';
								break;
							case 2:
								$day_of_week = 'Tue';
								break;
							case 3:
								$day_of_week = 'Wed';
								break;
							case 4:
								$day_of_week = 'Thurs';
								break;
							case 5:
								$day_of_week = 'Fri';
								break;
							case 6:
								$day_of_week = 'Sat';
								break;
							default:
								$day_of_week = 'Sun';
								break;
						}
						$output .= '<th width="10%" align="center">'.$day_of_week.'<br />'. date('n/d/Y',$tmp) ."</th>";
						$dates[] = array('clean' => date('m/d/Y',$tmp),'numeric' => $tmp);
						$tmp = strtotime(date('Y-m-d',$tmp). " + 1 day");
						$colcount++;
					}
					echo $output .= "</tr>";

					$output = "";
					foreach($titles as $row) {
						$output .= '<tr>';
						$output .= '<td>'. $row['title'] .'</td>';
						foreach($dates as $date) {
							$value = '';
							if(!empty($reps[$row['id']][$date['numeric']][$row['activity_map_id']])) {
								$value = ' value="'. $reps[$row['id']][$date['numeric']][$row['activity_map_id']] .'"';
							}
							$output .= '<td><input type="text" name="siu['. $row['id'] .']['. $date['numeric'] .']['. $row['activity_map_id'] .']"'. $value .' style="width: 50px;"></td>';
						}
						$output .= '</tr>';
					}
					echo $output;
					unset($output);

				?>

	</table>
	<br />
	<input type="submit" value="Enter <?php echo $siu_student_name;?>'s reps for this week"></input>
	</form>
</div>

<?php
##################################################
#   Javascript Functions
##################################################

if (!empty($js)) { ADD_JS_CODE($js); }

##################################################
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################