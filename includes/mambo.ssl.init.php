<?php
//backup the live site url;
$mosConfig_unsecure_site = $mosConfig_live_site;

if ( $_SERVER["SERVER_PORT"] == '443' ) {
	if (!isset($mosConfig_secure_site)) {
		$mosConfig_secure_site = str_replace( 'http://', 'https://', $mosConfig_live_site );
	}
	$mosConfig_live_site = $mosConfig_secure_site;
}
?>
