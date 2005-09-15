<?php
/**
* @version $Id: mod_wrapper.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla_4.5.2
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

class modWrapperData {

	function &getParams( &$params ){

		$params->def( 'url', 			'' );
		$params->def( 'scrolling', 		'auto' );
		$params->def( 'height', 		200 );
		$params->def( 'height_auto', 	0 );
		$params->def( 'width', 			'100%' );
		$params->def( 'add', 			1 );
		$params->def( 'name', 			'wrapper' );

		$url = $params->get( 'url' );
		if ( $params->get( 'add' ) ) {
			// adds "http://" if none is set
			if ( !strstr( $url, 'http' ) && !strstr( $url, 'https' ) ) {
				$url = 'http://'. $url;
			}
		}

		// auto height control
		if ( $params->get( 'height_auto' ) ) {
			$load = "window.onload = iFrameHeight;\n";
		} else {
			$load = '';
		}

		$params->set( 'load', $load );
		$params->set( 'url', $url );

		return $params;
	}
}

class modWrapper {

	function show( &$params ){
		$cache = mosFactory::getCache("mod_wrapper");

		$cache->setCaching($params->get('cache', 1));
		$cache->setCacheValidation(false);

		$cache->callId("modWrapper::_display", array( $params ), "mod_wrapper");
	}

	function _display( &$params ) {

		$params = modWrapperData::getParams( $params );

		$tmpl =& moduleScreens::createTemplate( 'mod_wrapper.html' );

		$tmpl->addVar( 'mod_wrapper', 'class', 	$params->get( 'moduleclass_sfx' ) );

		$tmpl->addObject( 'mod_wrapper', $params->toObject() );

		$tmpl->displayParsedTemplate( 'mod_wrapper' );
	}
}

modWrapper::show( $params );
?>