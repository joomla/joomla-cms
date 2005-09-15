<?php
/**
* version $Id: newsfeeds.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Newsfeeds
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Joomla
 * @subpackage Newsfeeds
 */
class newsfeedsScreens_front {
	/**
	 * @param string The main template file to include for output
	 * @param array An array of other standard files to include
	 * @return patTemplate A template object
	 */
	function &createTemplate( $bodyHtml='', $files=null ) {
		$tmpl =& mosFactory::getPatTemplate( $files );

		$directory = mosComponentDirectory( $bodyHtml, dirname( __FILE__ ) );
		$tmpl->setRoot( $directory );

		$tmpl->setAttribute( 'body', 'src', $bodyHtml );

		return $tmpl;
	}

	function item( &$items, &$rows, &$params ) {
		$tmpl = newsfeedsScreens_front::createTemplate( 'item.html' );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );

		$tmpl->addVar( 'body', 'show_header',		( $params->get( 'header' ) ? 1 : 0 ) );

		$tmpl->addObject( 'rows', $rows, 'row_' );
		$tmpl->addObject( 'items', $items, 'item_' );

		$tmpl->displayParsedTemplate( 'body' );
	}

	function list_section( &$params, &$current, &$cats ) {
		global $_MAMBOTS;

		// process the new bots
		$current->text = $current->descrip;
		$_MAMBOTS->loadBotGroup( 'content' );
		$results = $_MAMBOTS->trigger( 'onPrepareContent', array( &$current, &$params ), true );

		$tmpl = newsfeedsScreens_front::createTemplate( 'list-section.html' );

		$tmpl->addVar( 'body', 'show_image',			( $current->img ? 1 : 0 ) );

		$tmpl->addObject( 'current', $current, 'cur_' );

		// category list params
		$tmpl->addObject( 'categories', $cats, 'cat_' );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );

		$tmpl->displayParsedTemplate( 'body' );
	}

	function table_category( &$params, &$current, &$cats, &$rows ) {
		global $_MAMBOTS;

		// process the new bots
		$current->text = $current->descrip;
		$_MAMBOTS->loadBotGroup( 'content' );
		$results = $_MAMBOTS->trigger( 'onPrepareContent', array( &$current, &$params ), true );

		$tmpl = newsfeedsScreens_front::createTemplate( 'table-category.html' );

		$tmpl->addVar( 'body', 'show_image',			( $current->img ? 1 : 0 ) );

		$tmpl->addObject( 'current', $current, 'cur_' );

		// table item params
		$tmpl->addObject( 'rows', $rows, 'row_' );

		// category list params
		$tmpl->addObject( 'categories', $cats, 'cat_' );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );

		$tmpl->displayParsedTemplate( 'body' );
	}
}
?>