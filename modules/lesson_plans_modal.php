<style>
	#closeprivacy{
		display: none;
	}
	#privacypopup{
		width: 650px;
	}
</style>
<div style="text-align:center; margin-left:10px;">
	<div style="clear:both; height:5px;"></div>
	<a href="javascript:closeprivacy()" style="float:right; margin-top:5px; margin-right:10px;">
		<img src="/images/close_modal.png" alt="Close"/>
	</a>
	<div style="width:600px;">
		<?php
			$worldid="1";
			$level="1";
			if (!empty($_REQUEST["worldid"])) {
				$worldid = $_REQUEST["worldid"];
			}
			if (!empty($_REQUEST["level"])) {
				$level = $_REQUEST["level"];
			}
			if ($level == "1"){
				$level = "k-2";
			}
			if ($level == "2"){
				$level = "3-5";
			}
			if ($level == "3"){
				$level = "6-8";
			}
			if ($worldid == "7"){ //You and Your Insides
				echo '
					<h1>
						You and Your Insides Expeditions (Lesson Plans)
					</h1>
					<div style="clear:both; height:1px;"></div>
					<ul style="margin-right:30px; display:inline-block;">
						<li>
							<a href="/images/file/lessonplans/insides-working together challenge '.$level.'.pdf">
								Working Together Challenge
							</a>
						</li>
						<li>
							<a href="/images/file/lessonplans/insides-bodysystems '.$level.'.pdf">Musical Heartbeat Challenge</a>
						</li>
					</ul>
				';
			} else if ($worldid == "8"){ //You the Athlete
				echo '
					<h1>
						You the Athlete Expeditions (Lesson Plans)
					</h1>
					<div style="clear:both; height:1px;"></div>
					<ul style="margin-right:30px; display:inline-block;">
						<li>
							<a href="/images/file/lessonplans/at-keeping your head '.$level.'.pdf">
								Keeping Your Head Engineering Challenge
							</a>
						</li>
						<li>
							<a href="/images/file/lessonplans/athlete-getfit '.$level.'.pdf">
								Musical Heartbeat Challenge
							</a>
						</li>
					</ul>
				';
			} else if ($worldid == "9"){ //You and Biodiversity
				echo '
					<h1>
						You and Biodiversity Expeditions (Lesson Plans)
					</h1>
					<div style="clear:both; height:1px;"></div>
					<ul style="margin-right:30px; display:inline-block;">
						<li>
							<a href="/images/file/lessonplans/biodiversity_ecosystems_'.$level.'.pdf">
								Neighborhood Ecosystem Problem
							</a>
						</li>
					</ul>
				';
			} else if ($worldid == "10"){ //You and Your Money
				echo '
					<h1>
						You and Your Money Expeditions (Lesson Plans)
					</h1>
					<div style="clear:both; height:1px;"></div>
					<p style="color:red;">
						<b>
							Sorry, there are not currently any STEM Expeditions for You and Your Money.
						</b>
					</p>
					<div style="clear:both; height:1px;"></div>
				';
			} else if ($worldid == "11"){ //You and Our Home Planet
				echo '
					<h1>
						You and Our Home Planet Expeditions<br />(Lesson Plans)
					</h1>
					<div style="clear:both; height:1px;"></div>
					<ul style="margin-right:30px; display:inline-block;">
						<li>
							<a href="/images/file/lessonplans/home planet-concrete problem '.$level.'.pdf">
								Concrete Problem
							</a>
						</li>
						<li>
							<a href="/images/file/lessonplans/hp.populations.'.$level.'.pdf">
								Family Heritage Challenge
							</a>
						</li>
					</ul>
				';
			} else if ($worldid == "12"){ //You and Outer Space
				echo '
					<h1>
						You and Outer Space Expeditions (Lesson Plans)
					</h1>
					<div style="clear:both; height:1px;"></div>
					<ul style="margin-right:30px; display:inline-block;">
						<li>
							<a href="/images/file/lessonplans/Outer Space - Power of the Sun '.$level.'.pdf">
								Power of the Sun Challenge
							</a>
						</li>
					</ul>
				';
			} else if ($worldid == "13"){ //You and How Things Work
				echo '
					<h1>
						You and How Things Work Expeditions<br />(Lesson Plans)
					</h1>
					<div style="clear:both; height:1px;"></div>
					<p style="color:red;">
						<b>
							Sorry, there are not currently any STEM Expeditions for You and How Things Work.
						</b>
					</p>
					<div style="clear:both; height:1px;"></div>
				';
			} else if ($worldid == "14"){ //You the Time Traveler
				echo '
					<h1>
						You the Time Traveler Expeditions<br />(Lesson Plans)
					</h1>
					<div style="clear:both; height:1px;"></div>
					<ul style="margin-right:30px; display:inline-block;">
						<li>
							<a href="/images/file/lessonplans/time traveler_united states '.$level.'.pdf">
								Communication Problem
							</a>
						</li>
						<li>
							<a href="/images/file/lessonplans/time traveler_people '.$level.'.pdf">
								The Orange Mummy Challenge
							</a>
						</li>
					</ul>
				';
			} else if ($worldid == "15"){ //You the Citizen
				echo '
					<h1>
						You the Citizen Expeditions (Lesson Plans)
					</h1>
					<div style="clear:both; height:1px;"></div>
					<ul style="margin-right:30px; display:inline-block;">
						<li>
							<a href="/images/file/lessonplans/Voter-Govt and its Function '.$level.'.pdf">
								Challenge of Passing a Bill
							</a>
						</li>
					</ul>
				';
			} else if ($worldid == "18"){ //Safe Routes to School
				echo '
					<h1>
						Safe Routes to School Expeditions<br>(Lesson Plans)
					</h1>
					<div style="clear:both; height:1px;"></div>
					<ul style="margin-right:30px; display:inline-block;">
						<li>
							<a href="/images/file/lessonplans/at-keeping your head '.$level.'.pdf">
								Keeping Your Head Engineering Challenge
							</a>
						</li>
					</ul>
				';
			} else {
				echo '
					<h1>
						Lesson Plans
					</h1>
					<div style="clear:both; height:1px;"></div>
					<p style="color:red;">
						<b>
							Sorry, there are not currently any STEM Expeditions for this world.
						</b>
					</p>
					<div style="clear:both; height:1px;"></div>
				';
			}
		?>
	</div>
</div>