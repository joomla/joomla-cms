<?php
/**
* @version $Id: mod_rssfeed.php 137 2005-09-12 10:21:17Z eddieajau $
* @pacakge Mambo
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

class modRssfeeData {

	function &getVars( &$params ){
		global $_LANG;

		$moduleclass_sfx 	= $params->get( 'moduleclass_sfx', '' );
		$rss091  			= $params->get( 'rss091', 1 );
		$rss10  			= $params->get( 'rss10', 1 );
		$rss20  			= $params->get( 'rss20', 1 );
		$atom  				= $params->get( 'atom', 1 );
		$opml  				= $params->get( 'opml', 1 );
		$rss091_image		= $params->get( 'rss091_image', '' );
		$rss10_image		= $params->get( 'rss10_image', '' );
		$rss20_image		= $params->get( 'rss20_image', '' );
		$atom_image			= $params->get( 'atom_image', '' );
		$opml_image			= $params->get( 'opml_image', '' );

		$list->text			= $params->get( 'text' );
		$list->show_text	= ( $list->text ? 1 : 0 );

		$i = 0;
		// rss091 link
		if ( $rss091 ) {
			$img = mosAdminMenus::ImageCheck( 'rss091.gif', '/images/M_images/', $rss091_image, '/images/M_images/', $_LANG->_( 'RSS 0.91 Feed' ), 'RSS_0_91' );

			$rows[$i]->image 	= $img;
			$rows[$i]->link 	= 'index2.php?option=com_rss&amp;feed=RSS0.91&amp;no_html=1';
			$i++;
		}

		// rss10 link
		if ( $rss10 ) {
			$img = mosAdminMenus::ImageCheck( 'rss10.gif', '/images/M_images/', $rss10_image, '/images/M_images/', $_LANG->_( 'RSS 1.0 Feed' ), 'RSS_1_0' );

			$rows[$i]->image 	= $img;
			$rows[$i]->link 	= 'index2.php?option=com_rss&amp;feed=RSS1.0&amp;no_html=1';
			$i++;
		}

		// rss20 link
		if ( $rss20 ) {
			$img = mosAdminMenus::ImageCheck( 'rss20.gif', '/images/M_images/', $rss20_image, '/images/M_images/', $_LANG->_( 'RSS 2.0 Feed' ), 'RSS_2_0' );

			$rows[$i]->image 	= $img;
			$rows[$i]->link 	= 'index2.php?option=com_rss&amp;feed=RSS2.0&amp;no_html=1';
			$i++;
		}

		// atom link
		if ( $atom ) {
			$img = mosAdminMenus::ImageCheck( 'atom03.gif', '/images/M_images/', $atom_image, '/images/M_images/', $_LANG->_( 'ATOM 0.3 Feed' ), 'ATOM_0_3' );

			$rows[$i]->image 	= $img;
			$rows[$i]->link 	= 'index2.php?option=com_rss&amp;feed=ATOM0.3&amp;no_html=1';
			$i++;
		}

		// opml link
		if ( $opml ) {
			$img = mosAdminMenus::ImageCheck( 'opml.png', '/images/M_images/', $opml_image, '/images/M_images/', $_LANG->_( 'OPML Feed' ), 'OPML' );

			$rows[$i]->image 	= $img;
			$rows[$i]->link 	= 'index2.php?option=com_rss&amp;feed=OPML&amp;no_html=1';
			$i++;
		}

		return array( $rows, $list );
	}
}

class modRssfeed {

	function show( &$params ) {
		$cache = mosFactory::getCache("mod_rssfeed");

		$cache->setCaching($params->get('cache', 1));
		$cache->setCacheValidation(false);

		$cache->callId("modRssfeed::_display", array( $params ), "mod_rssfeed");
	}

	function _display( &$params ) {

		$vars = modRssfeeData::getVars( $params );
		$rows = $vars[0];
		$list = $vars[1];

		$tmpl =& moduleScreens::createTemplate( 'mod_rssfeed.html' );

		$tmpl->addVar( 'mod_rssfeed', 'class', 	$params->get( 'moduleclass_sfx' ) );

		$tmpl->addObject( 'mod_rssfeed', 	$list );
		$tmpl->addObject( 'body-list-rows', $rows, 'row_' );

		$tmpl->displayParsedTemplate( 'mod_rssfeed' );
	}
}

modRssfeed::show( $params );
?>