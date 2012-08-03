<?php

// no direct access
defined('_JEXEC') or die('Go Away');

$html = $params->get( 'fwd_html' );
$clean_js = $params->get( 'clean_js' );
$clean_css = $params->get( 'clean_css' );
$clean_all = $params->get( 'clean_all' );

if (!$clean_all) {
	if ($clean_js) {
		preg_match("/<script(.*)>(.*)<\/script>/i", $html, $matches);
		if ($matches) {
			foreach ($matches as $i=>$match) {
				$clean_js = str_replace('<br />', '', $match);
				$html = str_replace($match, $clean_js, $html);
			}
		}
	}
	if ($clean_css) {
		preg_match("/<style(.*)>(.*)<\/style>/i", $html, $matches);
		if ($matches) {
			foreach ($matches as $i=>$match) {
				$clean_js = str_replace('<br />', '', $match);
				$html = str_replace($match, $clean_js, $html);
			}
		}
	}
} else {
	$html = str_replace('<br />', '', $html);
}

echo $html;

 ?>
