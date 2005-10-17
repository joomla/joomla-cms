<?php
/**
* @version $Id: jossiteurl.php 85 2005-09-15 23:12:03Z akede $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$_MAMBOTS->registerFunction( 'onStart', 'botJoomlaSiteUrl' );

/**
* Converting the site URL to fit to the HTTP request
*
*/
function botJoomlaSiteUrl( ) {
	global $mosConfig_live_site, $mosConfig_unsecure_site, $mosConfig_original_site;
	
	$mosConfig_original_site = $mosConfig_live_site;
	
	// Testing the server information
	if ( isset ( $_SERVER['PHP_SELF'] ) ) {
		$siteProtocol = 'http';
		if ( isset( $_SERVER['HTTPS'] ) && ( strtolower( $_SERVER['HTTPS'] ) != 'off' ) ) {
			$siteProtocol = 'https';
			
		} else if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$url = parse_url( $_SERVER['HTTP_REFERER'] );
			$siteProtocol = $url['scheme'];
		}
	
		# $_SERVER['SERVER_PORT'] is not defined in case of php-cgi.exe
		if ( isset( $_SERVER['SERVER_PORT'] ) ) {
			$sitePort = ':' . $_SERVER['SERVER_PORT'];
			if ( ( $sitePort == ':80' && $siteProtocol == 'http')
			  || ( $sitePort == ':443' && $siteProtocol == 'https' )) {
				$sitePort = '';
			}
			
			if( $sitePort == ':443' && $siteProtocol == 'http' ) {
				$siteProtocol = 'https';
			} 
		} else {
			$sitePort = '';
		}
	
		if ( isset( $_SERVER['HTTP_HOST'] ) ) {
			$siteHost = $_SERVER['HTTP_HOST'];
		} else if ( isset( $_SERVER['SERVER_NAME'] ) ) {
			$siteHost = $_SERVER['SERVER_NAME'] . $sitePort;
		} else if ( isset( $_SERVER['SERVER_ADDR'] ) ) {
			$siteHost = $_SERVER['SERVER_ADDR'] . $sitePort;
		} else {
			$siteHost = $mosConfig_live_site;
		}
	
		$sitePath = dirname( $_SERVER['PHP_SELF'] );
		if ( $sitePath == '/' || $sitePath == '\\' ) {
			$sitePath = '';
		}
		if( eregi( '/administrator', $sitePath ) ) {
			$sitePath = substr( $sitePath, 0, strpos( $sitePath, "/administrator") );
		}
	
		$mosConfig_unsecure_site = 'http://' . $siteHost . $sitePath;
		$mosConfig_live_site = $siteProtocol . '://' . $siteHost . $sitePath;
	}

}
?>