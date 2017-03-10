<?php

if (strpos($GLOBALS["project_info"]["dns"], "api") !== false) {
	include_once($_SERVER["DOCUMENT_ROOT"]."functions/functions.php");
} else {
	include_once($_SERVER["DOCUMENT_ROOT"]."/../api/functions/functions.php");
}

function get_students($values) {

	$options = array();
	$arr = array();
	$xml = "";
	$continue = true;

	if(!empty($_SESSION["user"]["id"])) {
		$options = $_SESSION["user"];
	}
	if(!empty($_REQUEST['user_id'])) {
		$options['id'] = $_REQUEST['user_id'];
	}

	ob_start();

	if(!empty($_REQUEST['username']) && !empty($_REQUEST['password'])) {
		$res = authenticate_by_username($_REQUEST['username'],$_REQUEST['password']);
		if(!$res) {
			$xml = get_authentication_error();
			$continue = false;
		}
		$options['id'] = $res;
	}

	if($continue) {
		$arr = get_students_by_user_id($options);
	}

	if($continue) {
		$xml = '<User><Id>' . $options["id"] . '</Id></User>';
		$xml .= students_xml($arr);
	}

	echo $xml;

	$output = ob_get_clean();

	$output = str_replace("\n","",$output);
	$output = preg_replace("/\s\s+/"," ",$output);
	$output = str_replace("> <","><",$output);

	header('Content-type: text/xml');
	echo "<CMSData>". $output ."</CMSData>";

	die();
}

function get_students_by_user_id($options) {

	$q = "
		select
			s.id
			,s.gender_id
			,s.firstname
			,s.lastname
			,s.institution_id
			,s.ethicspledge
			,g.title
			,g.alias
		from system.students as s
		left join public.grades as g on
			g.id = s.grade_id
		where
			s.user_id = ".db_prep_sql((int)$options["id"])."
			and s.active = true
	";
	$res = db_query($q, __FUNCTION__."()");

	if (db_num_rows($res)) {

		$arr = array();
		while ($row = db_fetch_row($res)) {
			$arr[] = $row;
		}

		return $arr;
	}

	return false;

}

function students_xml($arr) {

	if (!empty($arr)) {
		
		$output = "<Children>";

		foreach ($arr as $student) {

			if($student["gender_id"] == 1) {
				$student["gender_id"] = "boy";
			} else if($student["gender_id"] == 2) {
				$student["gender_id"] = "girl";
			}

			$output .= "
				<Child>
					".(empty($student["id"]) ? "<Id></Id>" : "<Id>".$student["id"]."</Id>")."
					".(empty($student["gender_id"]) ? "<Gender></Gender>" : "<Gender>".$student["gender_id"]."</Gender>")."
					".(empty($student["firstname"]) ? "<FirstName></FirstName>" : "<FirstName>".$student["firstname"]."</FirstName>")."
					".(empty($student["lastname"]) ? "<LastName></LastName>" : "<LastName>".$student["lastname"]."</LastName>")."
					".(empty($student["institution_id"]) ? "<SchoolId></SchoolId>" : "<SchoolId>".$student["institution_id"]."</SchoolId>")."
					".(empty($student["alias"]) ? "<Grade></Grade>" : "<Grade>".ucfirst($student["alias"])."</Grade>")."
					<Pledged>".$student["ethicspledge"]."</Pledged>
				</Child>
			";

		}

		$output .= "</Children>";

		return $output;

	}

	return false;

}

function authenticate_by_username($username,$password) {
	library("membership.php");

	$q = "select * from system.users where active and lower(username) = '".db_prep_sql(strtolower($username))."'";
	$info = db_fetch($q, "Authorization Check - Verifying Login.");

	if(!empty($info["id"])) {
		if(user_compare_passwords($password,$info["password"],$info["password_salt"])) {
			return $info["id"];
		}
	}

	return false;
}

function get_authentication_error() {
	return "
	<Error>
		<Message>Unable to authenticate user.</Message>
		<Details>
			Information in full or part was not found in the database.
		</Details>
	</Error>
";
}