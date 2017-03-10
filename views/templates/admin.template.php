<!DOCTYPE html> 
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<meta name="robots" content="noindex,nofollow" />
	<link rel="shortcut icon" href="/favicon.ico">
	<!--<link rel="apple-touch-icon" href="apple-touch-icon.png">-->

	<title><?=(!empty($GLOBALS['project_info']['title']) ? $GLOBALS['project_info']['title'] .' - ' : '')?><?=($GLOBALS['project_info']['name'])?></title>
	<!-- CSS -->
	<?php 
		add_css('/global.css',2);
		// add_css('/admin.css',3);
		echo template_css();
	?>
	<!-- Javascript -->
	<?php 
		add_js('/global.js',2);
	?>
</head>
<body<?=(template_onload().template_onunload())?> id='body'>


<style type="text/css">
#header {
	background: #333333; /* Old browsers */
	background: -moz-linear-gradient(top,  #333333 0%, #000000 90%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#333333), color-stop(90%,#000000)); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top,  #333333 0%,#000000 90%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top,  #333333 0%,#000000 90%); /* Opera 11.10+ */
	background: -ms-linear-gradient(top,  #333333 0%,#000000 90%); /* IE10+ */
	background: linear-gradient(to bottom,  #333333 0%,#000000 90%); /* W3C */
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#333333', endColorstr='#000000',GradientType=0 ); /* IE6-9 */

	height: 40px;
	border-bottom: 2px solid #666666;
	color: #ccc;
	padding: 5px 10px;
}

.left_nav { min-width: 180px; width: 180px; padding: 10px; margin-right: 5px; vertical-align: top;  }
.left_nav ul { margin: 0 0 10px; padding: 0; }
.left_nav ul li { list-style-type: none; }


.content {  vertical-align: top; padding: 5px 10px; border-left: 1px solid #ccc; background: #fff; }
.content_box { width: 100%; }

</style>

<header id="header">
	<div>Admin</div>
</header>

<table cellspacing="0" cellpadding="0" class='content_box'>
	<tr>
		<td class='left_nav'>
			<nav>
				<ul>
					<li><b>Basics</b></li>
					<li><a href='/acu/'>Home</a></li>
					<li><a href='/contact-us/'>Contact Us</a></li>
				</ul>
				<ul>
					<li><b>Admin Settings</b></li>
					<li><a href='/acu/security-roles/'>Security Roles</a></li>
					<li><a href='/acu/security-sections/'>Security Sections</a></li>
					<li><a href='/acu/security-groups/'>Security Groups</a></li>
					<li><a href='/acu/security-permissions/'>Security Permissions</a></li>
					
					<li class='mt'><a href='/acu/paths/'>Paths</a></li>

					<li class='mt'><a href='/acu/dynamic-content-type/'>Dynamic Content Types</a></li>
					<li><a href='/acu/dynamic-content/'>Dynamic Content</a></li>

					<li class='mt'><a href='/acu/users/'>Users</a></li>
				</ul>
			</nav>
		</td>
		<td class='content'>
			<!--Start Body Content-->
			<?=($body)?>
			<!--End Body Content-->
		</td>
	</tr>
</table>

<footer class='copyright'>
	Copyright <?=($GLOBALS['project_info']['name'])?> - &copy; <?=(date('Y'))?>.	<?=($GLOBALS['project_info']['company_name'])?>, LLC. All Rights Reserved.
</footer>

<?php
	echo template_js();
	echo show_js_code();
?>

<?php echo ($GLOBALS['debug_options']['enabled'] == 1 ? show_debug() : ''); ?>

</body>
</html>