<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger
if(!logged_in()) { safe_redirect("/login/"); } 
if(empty(has_access('reports_list'))) { back_redirect(); }

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

//echo md5('reports.games.ajax.php');

?>

<style type="text/css">
	.reports-list td { padding: 5px; }
	.reports-list .desc { padding-left: 10px; }
</style>


<h2 class='reports'>Report List</h2>
<div class='content_container'> <?php echo dump_messages(); ?>
	<table cellpadding="0" cellspacing="0" border="0" class="reports-list">
		<!--tr>
			<td><a href="/acu/reports/scores/" title="Scores Report">Scores Report</a></td>
			<td class="desc">Aggregated scores over a given date range</td>
		</tr-->
		<tr>
			<td><a href="/acu/schools/" title="Schools List">Schools List</a></td>
			<td class="desc">Done - needs description written.</td>
		</tr>
		<tr>
			<td><a href="/acu/reports/schools/" title="Schools Report">Schools Report</a></td>
			<td class="desc">Done - needs description written.</td>
		</tr>
		<tr>
			<td><a href="/acu/reports/clubs/" title="Club Report">Club Report</a></td>
			<td class="desc">Done - needs description written.</td>
		</tr>
		<tr>
			<td><a href="/acu/reports/classrooms/" title="Classrooms">Classrooms</a></td>
			<td class="desc">Done - needs description written.</td>
		</tr>
		<tr>
			<td><a href="/acu/reports/students/" title="Student Report">Student Report</a></td>
			<td class="desc">Done - needs description written.</td>
		</tr>
		<tr>
			<td><a href="/acu/reports/step_it_up/" title="Step It Up">Step It Up</a></td>
			<td class="desc">Done - needs description written.</td>
		</tr>
		<tr>
			<td><a href="/acu/reports/signups/" title="Signups">Signups</a></td>
			<td class="desc">Done - needs description written.</td>
		</tr>
		<tr>
			<td><a href="/acu/reports/heard_about/" title="Heard About Report">Heard About Report</a></td>
			<td class="desc">Done - needs description written.</td>
		</tr>
		<tr>
			<td><a href="/acu/reports/games/" title="Games Report">Games Report</a></td>
			<td class="desc">Not done.</td>
		</tr>
		<tr>
			<td><a href="/acu/reports/participating/" title="Participating">Participating</a></td>
			<td class="desc">Done - needs description written.</td>
		</tr>
		<!--tr>
			<td><a href="/acu/reports/curriculum/" title="Curriculum">Curriculum</a></td>
			<td class="desc">Not done.</td>
		</tr-->
		<tr>
			<td><a href="/acu/reports/home_school/" title="Home-School Report">Home-School Report</a></td>
			<td class="desc">Done - needs description written.</td>
		</tr>
		<tr>
			<td><a href="/acu/reports/kinf/" title="KINF">KINF</a></td>
			<td class="desc">Not done.</td>
		</tr>
		<tr>
			<td><a href="/acu/reports/summer/" title="Combined Scores">Combined Scores</a></td>
			<td class="desc">Combines Student and Step it Up reports</td>
		</tr>
	</table>


</div>

<?php

	//echo run_module("dashboard");
	//echo run_module("user", "modules/acu/users");
##################################################
#   Javascript Functions
##################################################

##################################################
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################
