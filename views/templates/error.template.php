<!DOCTYPE html> 
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title><?php echo (!empty($GLOBALS["project_info"]["title"]) ? $GLOBALS["project_info"]["title"] ." | " : ""); ?><?php echo ($GLOBALS["project_info"]["name"]); ?></title>

	<link rel="shortcut icon" href="/favicon.ico">
	<!--<link rel="apple-touch-icon" href="apple-touch-icon.png">-->

<?php
	//ADD_CSS("reset.css",2);
	add_css("global.css",2);
	echo template_css();

	add_js("global.js",1);
?>
</head>
<body<?php echo (TEMPLATE_ONLOAD().TEMPLATE_ONUNLOAD()); ?> id="body">
<header>
  <div style="border: 1px solid #ccc; background: #eee; padding: 10px;">Header</div>
</header>

<h3>An error has occured!</h3>
<!--Start Body Content-->
<?php echo ($body); ?>
<!--End Body Content-->

<footer>
  <div style="border: 1px solid #ccc; background: #eee; padding: 10px; margin-top: 10px;">Footer</div>
</footer>
<?php
	echo template_js();
	echo show_js_code();
?>
<!--[[DEBUG]]-->
<?php echo (!empty($GLOBALS["debug_options"]["enabled"]) ? show_debug() : ""); ?>
</body>
</html>