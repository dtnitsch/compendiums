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
		// add_css('global.css',2);
		add_css('admin.css', 3);
		add_css('glyphicons.css', 4);
		echo template_css();
	?>
	<!-- Javascript -->
	<?php 
		add_js('global.js',2);
	?>
</head>
<body<?=(template_onload().template_onunload())?> id='body'>

<?php
	// Skip check if super admin
	if(!is_superadmin()) {
		// $security_check_list[] = 'security_permission_list';
		// $security_check_list[] = 'security_group_list';
		// $security_check_list[] = 'security_section_list';
		$security_check_list[] = 'security_role_list';
		$security_check_list[] = 'view_dashboard';
		
		$security_list = has_access(implode(",",$security_check_list));	
	}
	$name = $_SESSION['user']['firstname'] .' '. $_SESSION['user']['lastname'];
?>

<table cellspacing="0" cellpadding="0" class='content_box'>
	<tr>
		<td class='left_nav'>
		    <header id='header'>
				<div><img src="/images/logo-cck-admin.png" width="220" height="22" alt=""/></div>
			</header>
			<div id='user_nav'><a href='javascript: void(0)' onclick='show_hide("user_menu")' title='<?php echo $name; ?>'><?php echo $name; ?></a>
				<ul id='user_menu' style='display: none;'>
					<li><a href='/acu/users/edit/?id=<?php echo $_SESSION['user']['id']; ?>' class='my_account'>My Account</a></li>
					<li><a href='?logout=1' class='logout'>Logout</a></li>
				</ul>
			</div>
			<nav>
				<ul>
					<li><h3>General Settings</h3></li>
					<li><a href='/' class='public'>Public Page</a></li>
					<?php if(is_superadmin() || $security_list['view_dashboard']) { ?> <li><a href='/acu/' class='home'>Home</a></li> <?php } ?>
					<li><a href='/contact-us/' class='contact'>Contact Us</a></li>
				</ul>
				<ul>
					<li><h3>Admin Settings</h3></li>
<?php
	// $output = '';
	// if(is_superadmin() || $security_list['security_role_list']) { 
	// 	$output .= '<li><a href="/acu/security-roles/" class="security-roles">Security Roles</a></li>';
	// }
	// if(is_superadmin() || $security_list['security_section_list']) { 
	// 	$output .= '<li><a href="/acu/security-sections/">Security Sections</a></li>';
	// }
	// if(is_superadmin() || $security_list['security_group_list']) { 
	// 	$output .= '<li><a href="/acu/security-groups/">Security Groups</a></li>';
	// }
	// if(is_superadmin() || $security_list['security_permission_list']) { 
	// 	$output .= '<li><a href="/acu/security-permissions/">Security Permissions</a></li>';
	// }
	// echo $output;
?>
					<li><a href='/acu/reports/' class='reports'>Reports</a></li>

					<li><a href='/acu/users/' class='users'>Users</a></li>

					<li><a href='/acu/quiz-questions/' class='quiz-questions'>Quiz Questions</a></li>
					<li><a href='/acu/skill-and-drill/' class='skill-and-drill'>Skill and Drill</a></li>
					<li><a href='/acu/crosswords/' class='crosswords'>Crosswords</a></li>
					<li><a href='/acu/word-search/' class='word-search'>Word Search</a></li>

					<?php #<li><a href='/acu/paths/' class='paths'>Paths</a></li> ?>

					<?php #<li><a href='/acu/dynamic-content-type/' class='dynamic-content-types'>Dynamic Content Types</a></li> ?>

					<li><a href='/acu/dynamic-content/' class='dynamic-content'>Dynamic Content</a></li>

					<li><a href='/acu/worlds/' class='worlds'>Worlds</a></li>

					<li><a href='/acu/themes/' class='themes'>Themes</a></li>
					<li><a href='/acu/students/' class='students'>Students</a></li>
					<li><a href='/acu/schools/' class='schools'>Schools</a></li>
					<li><a href="/acu/security-roles/" class="security-roles">Security Roles</a></li>

				</ul>
			</nav>
		</td>
		<td class='content'>
			<!--Start Body Content-->
			<?=($body)?>
			<!--End Body Content-->
			<footer class='copyright'>
				&copy; <?=(date('Y'))?> <b><?=($GLOBALS['project_info']['name'])?></b>. All Rights Reserved.
			</footer>
		</td>
	</tr>
</table>


<?php
	echo template_js();
	echo show_js_code();
?>

<?php echo ($GLOBALS['debug_options']['enabled'] == 1 ? show_debug() : ''); ?>

</body>