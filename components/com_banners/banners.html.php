<?php
/**
* @version $Id: banners.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Banners
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Joomla
 * @subpackage Banner
 */
class bannerScreens_front {
	/**
	 * @param string The main template file to include for output
	 * @param array An array of other standard files to include
	 * @return patTemplate A template object
	 */
	function &createTemplate( $bodyHtml='', $files=null ) {
		$tmpl =& mosFactory::getPatTemplate( $files );

		$directory = mosComponentDirectory( $bodyHtml, dirname( __FILE__ ) );
		$tmpl->setRoot( $directory );

		if ( $bodyHtml ) {
			$tmpl->setAttribute( 'body', 'src', $bodyHtml );
		}

		return $tmpl;
	}

	function item( &$banner ) {
		global $mosConfig_live_site;
		global $_LANG;

		$tmpl =& bannerScreens_front::createTemplate( 'item.html' );

		$imageurl 	= $mosConfig_live_site .'/images/banners/'. $banner->imageurl;
		$href 		= sefRelToAbs( 'index.php?option=com_banners&amp;task=click&amp;bid='. $banner->bid );
		if ( !$banner->editor ) {
			$alt = $_LANG->_( 'Advertisement' );
		} else {
			$alt = $banner->editor;
		}

		if ( trim( $banner->custombannercode ) ) {
			$tmpl->addVar( 'mod_banner', 'banner', $banner->custombannercode );

			$tmpl->addVar( 'mod_banner', 'type', 	1 );
		} else {
			$tmpl->addVar( 'mod_banner', 'href', 	$href );
			$tmpl->addVar( 'mod_banner', 'image', $imageurl );
			$tmpl->addVar( 'mod_banner', 'alt', $alt );

			if ( eregi( "(\.bmp|\.gif|\.jpg|\.jpeg|\.png)$", $banner->imageurl ) ) {
				$tmpl->addVar( 'mod_banner', 'type', 	2 );
			} else if ( eregi( "\.swf$", $banner->imageurl ) ) {
				$tmpl->addVar( 'mod_banner', 'type', 	3 );
			}
		}

		$tmpl->displayParsedTemplate( 'mod_banner' );
	}
}
?>