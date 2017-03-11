<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo (!empty($GLOBALS["project_info"]["title"]) ? $GLOBALS["project_info"]["title"] ." - " : ""); ?><?php echo ($GLOBALS["project_info"]["name"]); ?></title>

	<link rel="shortcut icon" href="/favicon.ico">

<?php
	add_css("styles.css",2);
	add_css("gm_tools.css",3);
	echo template_css();

	add_js("scripts.js", 1);
	add_js("gm_tools.js", 2);
?>

</head>
<body>

	<div class="header">
		<div class="logo">
			<a href="/">GM Tools</a>
		</div>
		
		<div class="user">
			Welcome, <a href="javascript:void(0);" onclick="show_hide('header_details');">Name</a>
		</div>

		<div class="search">
			<input type="text" placeholder="Search"> <button>Go</button>
		</div>
	</div>

	<div id="header_details" class="header_details" style="display: none;">
		details here
	</div>
	<ul class="nav">
		<li><a class="active" href="/lists/">Lists</a></li>
		<li><a href="/collections/">Collections</a></li>
		<li><a href="/compendiums/">Compendiums</a></li>
	</ul>

	<div class="body_content">
		<h1>404 Not Found</h1>
	</div>

	
<!-- DEBUG -->
<?php echo (!empty($GLOBALS["debug_options"]["enabled"]) ? show_debug() : ""); ?>
<div class="clear"></div>

<?php
	echo template_js();
?>

</body>
</html>