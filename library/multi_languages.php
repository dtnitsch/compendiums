<?php
####################################################
# Multiple Languages
# 
####################################################

function l($val,$language="en_EN") {
	static $languages;
	if(empty($languages[$language])) { $languages[$language] = include_language_file($language); }
	$lang =& $languages[$language];
	$x = (!empty($lang[$val]) ? $lang[$val] : $val);
    return $x;
}

function include_language_file($language) {
	echo "<b>including: languages/". $language .".inc.php</b>"; 
	if(empty($GLOBALS["root_path"][$language.".inc.php"])) {
		include($GLOBALS["root_path"] ."library/languages/". $language .".inc.php");
		$GLOBALS["root_path"][$language.".inc.php"] = 1;
		$lang_file = "language_array_".$language;
		return $$lang_file;
	}
}