<?php

if (!empty($_GET["state"])) {

	$regions = '"supplements"."regions"';
	$institutions = '"public"."institutions"';

	$q = "
		select
			r.id
			,r.title
		from ".$regions." as r
		join ".$institutions." as i on
			i.region_id = r.id
		where
			lower(r.\"2code\") = '".db_prep_sql(strtolower($_GET["state"]))."'
		group by
			r.id;
	";

	$state = db_fetch($q, "getting state info");

	$letter = (!empty($_GET['first_letter']) ? $_GET['first_letter'] : 'A');

	if ($letter == "09") {
		$sub_q = "
			and (
				i.title ilike '0%'
				or i.title ilike '1%'
				or i.title ilike '2%'
				or i.title ilike '3%'
				or i.title ilike '4%'
				or i.title ilike '5%'
				or i.title ilike '6%'
				or i.title ilike '7%'
				or i.title ilike '8%'
				or i.title ilike '9%'
			)
		";
	} else {
		$sub_q = "
			and i.title ilike '".$letter."%'
		";
	}

	$q = "
		select
			i.id
			,i.title
			,i.city
			,r.title as state
		from public.institutions as i
		join supplements.regions as r on
			r.id = i.region_id
		where
			i.region_id = ".db_prep_sql((int) $state["id"])."
			and i.institution_type_id = 1
			".$sub_q."
		order by
			i.title
	";

	$school_res = db_query($q, "Getting Schools");
	$school_count = db_num_rows($school_res);

}

// show a state select menu
function stateselect() {

	$display = "";

	$display .= '
		<div id="schoolstateselect">
			<h1>Is Your School Registered? Search our Database.</h1>
			<div class="fieldname">Select Your State:</div>
			<div class="fieldvalue">
				<select id="usstates" name="usstates" size="1" onchange="showschools_edit(this.value,'. $_GET["row_id"] .')">
					<option value=""></option>
					<!--option value="non us">Non US</option-->
					<option value="al">Alabama</option>
					<option value="ak">Alaska</option>
					<option value="as">American Samoa</option>
					<option value="az">Arizona</option>
					<option value="ar">Arkansas</option>
					<option value="ca">California</option>
					<option value="co">Colorado</option>
					<option value="ct">Connecticut</option>
					<option value="de">Delaware</option>
					<option value="dc">District Of Columbia</option>
					<option value="fl">Florida</option>
					<option value="ga">Georgia</option>
					<option value="gm">Guam</option>
					<option value="hi">Hawaii</option>
					<option value="id">Idaho</option>
					<option value="il">Illinois</option>
					<option value="in">Indiana</option>
					<option value="ia">Iowa</option>
					<option value="ks">Kansas</option>
					<option value="ky">Kentucky</option>
					<option value="la">Louisiana</option>
					<option value="me">Maine</option>
					<option value="md">Maryland</option>
					<option value="ma">Massachusetts</option>
					<option value="mi">Michigan</option>
					<option value="mn">Minnesota</option>
					<option value="ms">Mississippi</option>
					<option value="mo">Missouri</option>
					<option value="mt">Montana</option>
					<option value="ne">Nebraska</option>
					<option value="nv">Nevada</option>
					<option value="nh">New Hampshire</option>
					<option value="nj">New Jersey</option>
					<option value="nm">New Mexico</option>
					<option value="ny">New York</option>
					<option value="nc">North Carolina</option>
					<option value="nd">North Dakota</option>
					<option value="oh">Ohio</option>
					<option value="ok">Oklahoma</option>
					<option value="or">Oregon</option>
					<option value="pa">Pennsylvania</option>
					<option value="pr">Puerto Rico</option>
					<option value="ri">Rhode Island</option>
					<option value="sc">South Carolina</option>
					<option value="sd">South Dakota</option>
					<option value="tn">Tennessee</option>
					<option value="tx">Texas</option>
					<option value="ut">Utah</option>
					<option value="vt">Vermont</option>
					<option value="va">Virginia</option>
					<option value="wa">Washington</option>
					<option value="wv">West Virginia</option>
					<option value="wi">Wisconsin</option>
					<option value="wy">Wyoming</option>
				</select>
			</div>
			<div class="clear"></div>
		</div>
	';

	return $display;

}

if (empty($_GET["state"])) {
	echo stateselect();
}

if (!empty($_GET["state"])) {

	$display = "";

	$display = '
		<div id="state_schools">

			<div id="dontseeyourschool" class="lg">Can\'t find your school? <a href="/add-school/">Click here</a>.</div>

			<div id="schoolpopup">

				<h1>'.$state['title'].' Public and Private Schools</h1>

				<div class="center">
	';

	$cnt = 64;

	$output = '';

	$output .= "
		<a href='javascript:showstateschoolselect(\"".$_GET["state"]."\",\"09\")'>#</a> | 
	";

	while ($cnt++ < 90) {
		$output .= "<a href='javascript:showstateschoolselect(\"". $_GET['state'] ."\",\"". chr($cnt) ."\")'>". chr($cnt) ."</a> | ";
	}

	$display .= substr($output,0,-2);

	unset($output);

	$display .= '</div>';

	$output = "";

	if ($school_count) {

		$output .= '<table border="0" cellspacing="0" cellpadding="5">';

		while ($row = db_fetch_row($school_res)) {

			$output .= '
				<tr class="school">
					<td><a href="javascript:update_institution_ed('. $row['id'] .','.$_GET['row_id'].')" id="selectschool'.$row['id'].'">'.$row['title'].'</a></td>
					<td>'.$row['city'].'</td>
					<td>'.$row['state'].'</td>
				</tr>
			';

		}

		$output .= '</table>';

	} else {

		$output .= '
			<table border="0" cellspacing="0" cellpadding="5">
				<tr class="school">
					<td>There were no results matching your search.</td>
				</tr>
			</table>
		';

	}

	$display .= $output;

	unset($output);

	$display .= '
				</table>
			</div>
		</div>
	';

	echo $display;

}

?>

<script>

function update_institution(institution_id) {

	// ON SUCCESS CHOOSE INSTITUTION
	$.ajax({
		"type": "POST"
		,"url": "/ajax.php"
		,"data": {
			"apid": "070a27400e439c18a98794784f5fe61e"
			,"update_institution": true
			,"institution_id": institution_id
		}
		,"dataType": "json"
		,"success": function(data) {

			if (typeof ajax_debugger == "function" && typeof data.debug != "undefined") {
				ajax_debugger(data.debug, JSON.stringify(data.debug).length);
				data.debug = null;
			}

			if (data.success) {
				window.location.href = "/myaccount/";
			}

		}
	});

}

</script>