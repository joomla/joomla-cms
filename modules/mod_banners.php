<?php
/**
* @version $Id: mod_banners.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

class modBannersData {

	function &getBanner( &$params ){
		global $database;

		$clientids 			= $params->get( 'banner_cids', '' );
		$moduleclass_sfx 	= $params->get( 'moduleclass_sfx' );

		$where = '';
		if( $clientids != '' ) {
			$where = "\n AND cid in ( $clientids )";
		}

		// number of banners
		$query = "SELECT count(*) AS numrows"
		. "\n FROM #__banner"
		. "\n WHERE showBanner = 1"
		. ( ( $where <> '' ) ? $where : '');
		$database->setQuery( $query );

		$numrows = $database->loadResult();
		if ($numrows === null) {
			mosErrorAlert( $database->stderr() );
		}

		// randomizing code
		if ( $numrows > 1 ) {
			mt_srand( (double) microtime()*1000000 );
			$bannum = mt_rand( 0, --$numrows );
		} else {
			$bannum = 0;
		}

		// main banner query
		$query = "SELECT * FROM #__banner"
		. "\n WHERE showBanner = 1"
		. ( ( $where <> '' ) ? $where : '' )
		;
		$database->setQuery( $query, $bannum, 1 );
		$database->loadObject( $banner );

		if ( $banner ) {
			$query = "UPDATE #__banner"
			. "\n SET impmade = impmade + 1"
			. "\n WHERE bid = '$banner->bid'";
			$database->setQuery( $query );
			if(!$database->query()) {
				mosErrorAlert( $database->stderr() );
			}

			// impression count increase
			$banner->impmade++;

			if ( $numrows > 0 ) {
				// Check if this impression is the last one and print the banner
				if ( $banner->imptotal == $banner->impmade ) {
					$query = "INSERT INTO #__bannerfinish"
					. "\n ( cid, type, name, impressions, clicks, imageurl, datestart, dateend )"
					. "\n VALUES ( '$banner->cid', '$banner->type', '$banner->name', '$banner->impmade', '$banner->clicks', '$banner->imageurl', '$banner->date', now() )"
					;
					$database->setQuery($query);
					if(!$database->query()) {
						mosErrorAlert( $database->stderr(true) );
					}

					$query = "DELETE FROM #__banner"
					. "\n WHERE bid = $banner->bid"
					;
					$database->setQuery( $query );
					if(!$database->query()) {
						mosErrorAlert( $database->stderr() );
					}
				}

				return $banner;
			}
		}
	}
}

/**
 * @package Joomla
 * @subpackage Banner
 */
class modBanners {

	function show ( &$params ) {
		$cache  = mosFactory::getCache( "mod_banners" );

		$cache->setCaching($params->get('cache', 1));
		$cache->setLifeTime($params->get('cache_time', 900));
		$cache->setCacheValidation(true);

		$cache->callId( "modBanners::_display", array( $params ), "mod_banners" );
	}

	function _display( &$params ) {
		global $mosConfig_live_site;
		global $_LANG;

		$banner = modBannersData::getBanner( $params );

		$tmpl =& moduleScreens::createTemplate( 'mod_banners.html' );

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

modBanners::show( $params );
?>