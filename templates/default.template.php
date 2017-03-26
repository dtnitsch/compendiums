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
	echo show_css_code();

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
			<form method="get" action="/search/">
				<input type="text" name="search" placeholder="Search"> <button>Go</button>
			</form>
		</div>
	</div>

	<div id="header_details" class="header_details" style="display: none;">
		details here
	</div>

	<ul class="nav">
		<li><a<?php echo (substr($path,0,7) == "/lists/" ? ' class="active"':""); ?> href="/lists/">Lists</a></li>
		<li><a<?php echo (substr($path,0,13) == "/collections/" ? ' class="active"':""); ?> href="/collections/">Collections</a></li>
		<li><a<?php echo (substr($path,0,13) == "/compendiums/" ? ' class="active"':""); ?> href="/compendiums/">Compendiums</a></li>
	</ul>


<!--Start Body Content-->
<div class="body_content">
	<?php echo ($body); ?>
</div>
<!--End Body Content-->

<!-- DEBUG -->
<?php echo (!empty($GLOBALS["debug_options"]["enabled"]) ? show_debug() : ""); ?>
<div class="clear"></div>

<?php
	echo template_js();
	echo show_js_code();
?>

</body>
</html>