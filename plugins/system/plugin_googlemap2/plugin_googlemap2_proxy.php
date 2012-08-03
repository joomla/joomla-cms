<?php
/*------------------------------------------------------------------------
# plugin_googlemap2_proxy.php - Google Maps plugin
# ------------------------------------------------------------------------
# author    Mike Reumer
# copyright Copyright (C) 2011 tech.reumer.net. All Rights Reserved.
# @license - http://www.gnu.org/copyleft/gpl.html GNU/GPL
# Websites: http://tech.reumer.net
# Technical Support: http://tech.reumer.net/Contact-Us/Mike-Reumer.html 
# Documentation: http://tech.reumer.net/Google-Maps/Documentation-of-plugin-Googlemap/
--------------------------------------------------------------------------*/

// No protection of Joomla because this php program may be called directly to deliver content
// defined( '_JEXEC' ) or die( 'Restricted access' );

if (!isset($HTTP_RAW_POST_DATA)){
$HTTP_RAW_POST_DATA = file_get_contents('php://input');
}
$post_data = $HTTP_RAW_POST_DATA;
$header[] = "Content-type: text/xml";
$header[] = "Content-length: ".strlen($post_data);

$url = urldecode($_GET['url']);
$url = "http://".$url;
	
$ok = false;

if (ini_get('allow_url_fopen'))
	if (($response = file_get_contents($url)))
		$ok = true;

if (!$ok) {
	$ch = curl_init( $url );

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 80);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_FAILONERROR, 0);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_COOKIEFILE, 1);
	
	if ( strlen($post_data)>0 ){
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	}
	
	$response = curl_exec($ch);    
	if (curl_errno($ch)) {
		print curl_error($ch);
	} else {
		curl_close($ch);
		$ok = true;
	}
}

if (!$ok) {
	$url = urldecode($_GET['url']);

    // Do it the safe mode way for local files
	$pattern = "/(www.)?".$_SERVER["HTTP_HOST"]."/i";
	if (preg_match($pattern, $url)!=0) {
		$url = $_SERVER["DOCUMENT_ROOT"].preg_replace($pattern, "", $url);
	
		if (ini_get('allow_url_fopen'))
			if (($response = file_get_contents($url)))
				$ok = true;
		
		if (!$ok) {
			$ch = curl_init( $url );
		
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 80);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_FAILONERROR, 0);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_COOKIEFILE, 1);
			
			if ( strlen($post_data)>0 ){
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			}
			
			$response = curl_exec($ch);    
			if (curl_errno($ch)) {
				print curl_error($ch);
			} else {
				curl_close($ch);
				$ok = true;
			}
		}
	}
}

if ($ok) {
	while (@ob_end_clean());
	header('content-type:text/xml;');
}

print $response;

?> 