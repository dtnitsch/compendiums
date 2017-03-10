<?php

function security_permissions_navigation($id,$section) {
	$section = trim($section);
	$section = strtolower($section);
	$paths = [
		"edit" => [
			"label" => "Edit"
			,"url" => "/acu/security-permissions/edit/?id=". $id
		]
		,"audit" => [
			"label" => "Audit"
			,"url" => "/acu/security-permissions/audit/?id=". $id
		]
		,"delete" => [
			"label" => "Delete"
			,"url" => "/acu/security-permissions/delete/?id=". $id
		]
	];

	$output = '
<style type="text/css">

#navlist
{
border-bottom: 1px solid #ccc;
margin: 0 0 10px 0;
padding-bottom: 19px;
padding-left: 10px;
}

#navlist ul, #navlist li
{
display: inline;
list-style-type: none;
margin: 0;
padding: 0;
}

#navlist a:link, #navlist a:visited
{
background: #E8EBF0;
border: 1px solid #ccc;
color: #666;
float: left;
font-size: small;
font-weight: normal;
line-height: 14px;
margin-right: 8px;
padding: 2px 10px 2px 10px;
text-decoration: none;
}

#navlist a:link#current, #navlist a:visited#current
{
background: #fff;
border-bottom: 1px solid #fff;
color: #000;
}

#navlist a:hover { color: #f00; }

body.section-1 #navlist li#nav-1 a, 
body.section-2 #navlist li#nav-2 a,
body.section-3 #navlist li#nav-3 a,
body.section-4 #navlist li#nav-4 a
{
background: #fff;
border-bottom: 1px solid #fff;
color: #000;
}

#navlist #subnav-1,
#navlist #subnav-2,
#navlist #subnav-3,
#navlist #subnav-4
{
display: none;
width: 90%;
}

body.section-1 #navlist ul#subnav-1, 
body.section-2 #navlist ul#subnav-2,
body.section-3 #navlist ul#subnav-3,
body.section-4 #navlist ul#subnav-4
{
display: inline;
left: 10px;
position: absolute;
top: 95px;
}

body.section-1 #navlist ul#subnav-1 a, 
body.section-2 #navlist ul#subnav-2 a,
body.section-3 #navlist ul#subnav-3 a,
body.section-4 #navlist ul#subnav-4 a
{
background: #fff;
border: none;
border-left: 1px solid #ccc;
color: #999;
font-size: smaller;
font-weight: bold;
line-height: 10px;
margin-right: 4px;
padding: 2px 10px 2px 10px;
text-decoration: none;
}

#navlist ul a:hover { color: #f00 !important; }

</style>


	<div id="navcontainer">
		<ul id="navlist">
	';
	foreach($paths as $section_name => $row) {
		if($section_name == $section) {
			$output .= '<li id="active"><a href="#" id="current">'. $row["label"] .'</a></li>';
		} else {
			$output .= '<li><a href="'. $row["url"] .'" title="'. $row["label"] .'">'. $row["label"] .'</a></li>';
		}
	}
	$output .= '
		</ul>
	</div>';

	return $output;
}