<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


echo'<?xml version="1.0" encoding="UTF-8"?>';
echo'<cmsData>';
echo'	<Scores>';
echo'		<Child>';
echo'			<Score>';

if (!empty($_REQUEST['child']) && !empty($_REQUEST['act']) && !empty($_REQUEST['omit'])) {
	if ($_REQUEST['act'] == "topscores" && $_REQUEST['omit'] == "additional") {

		$q = "
			SELECT
				a.student_id
				,s.firstname
				,g.title AS grade
				,SUM(calculated_score) AS total
			FROM public.activity_scores AS a
			JOIN system.students AS s ON
				s.id = a.student_id
			LEFT JOIN public.grades AS g ON
				g.id = s.grade_id
			WHERE
				s.id in (".$_REQUEST["child"].") AND
				a.created >= '".date("Y-m-d", strtotime("last Sunday"))." 00:00:00' AND
				a.created <= '".date("Y-m-d", strtotime("next Saturday"))." 23:59:59' AND
				a.is_classroom = 'f'
			GROUP BY
				a.student_id
				,g.title
				,s.firstname";
		$arr = db_fetch($q, "Get Student Weekly Score");

		echo (!empty($arr["total"]) ? $arr["total"] : '--');

	} else {
		echo "error";
	}
} else {
	echo "error";
}

echo'			</Score>';
echo'		</Child>';
echo'	</Scores>';
echo'</cmsData>';
?>