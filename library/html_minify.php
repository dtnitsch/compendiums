<?php
function bottomComment($raw, $compressed) {
	$raw = strlen($raw);
	$compressed = strlen($compressed);
	$savings = ($raw-$compressed) / $raw * 100;
	$savings = round($savings, 2);
	return '<!--HTML compressed, size saved '.$savings.'%. From '.$raw.' bytes, now '.$compressed.' bytes-->';
}
function minifyHTML($html) {
	$pattern = '/<(?<script>script).*?<\/script\s*>|<(?<style>style).*?<\/style\s*>|<!(?<comment>--).*?-->|<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si';
	preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

	// echo "<pre>";
	// print_r($matches);
	// echo "</pre>";
	// die();

	$overriding = true;
	$raw_tag = true;
	// Variable reused for output
	$html = '';
	foreach ($matches as $token) {
		// echo "<pre>";
		// print_r($token);
		// echo "</pre>";
		$tag = (isset($token['tag'])) ? strtolower($token['tag']) : null;
		if (is_null($tag)) {
			if ( !empty($token['script']) ) {
				$strip = true; #html_minify_get_ignore_js();
			}
			else if ( !empty($token['style']) ) {
				$strip = true; #html_minify_get_ignore_css();
			}
			else { #if ( html_minify_get_ignore_comments() ) {
				if (!$overriding && $raw_tag != 'textarea') {
					// Remove any HTML comments, except MSIE conditional comments
					$content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);
				}
			}
		}
		else {
			if ($tag == 'pre' || $tag == 'textarea') {
				$raw_tag = $tag;
			}
			else if ($tag == '/pre' || $tag == '/textarea') {
				$raw_tag = false;
			}
			else {
				if ($raw_tag || $overriding) {
					$strip = false;
				}
				else {
					$strip = true;
					// Remove any empty attributes, except:
					// action, alt, content, src
					$content = preg_replace('/(\s+)(\w++(?<!\baction|\balt|\bcontent|\bsrc)="")/', '$1', $content);
					// Remove any space before the end of self-closing XHTML tags
					// JavaScript excluded
					$content = str_replace(' />', '/>', $content);
				}
			}
		}
		if ($strip) {
			$content = removeWhiteSpace($content);
		}
		$html .= $content;
	}
	return $html;
}
function parseHTML($html) {
	$html = minifyHTML($html);
	$html .= "\n" . bottomComment($html, $html);
	return $html;
}
function removeWhiteSpace($str) {
	$str = str_replace("\t", ' ', $str);
	$str = str_replace("\n",  '', $str);
	$str = str_replace("\r",  '', $str);
	while (stristr($str, '  ')) {
		$str = str_replace('  ', ' ', $str);
	}
	return $str;
}
