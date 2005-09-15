<?php
/**
* @version $Id: content_archive_section.class.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Menus
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
* @subpackage Menus
*/
class content_archive_section_menu {
	/**
	* @param database A database connector object
	* @param integer The unique id of the category to edit (0 if new)
	*/
	function editSection( $uid, $menutype, $option ) {
		global $database, $my, $mainframe;
  		global $_LANG;

		mosFS::load( '@class', 'com_content' );

		$menu = new mosMenu( $database );
		$menu->load( $uid );

		// fail if checked out not by 'me'
		if ($menu->checked_out && $menu->checked_out <> $my->id) {
			mosErrorAlert( $_LANG->_( 'The module' ) .' '. $menu->title .' '. $_LANG->_( 'descBeingEditted' ) );
		}

		if ( $uid ) {
			$menu->checkout( $my->id );
		} else {
			$menu->type 		= 'content_archive_section';
			mosMenuFactory::setValues( $menu, $menutype );
		}

		// build the html select list for section
		$lists['componentid'] 	= mosContentFactory::buildSectionList( $menu, $uid, 1 );
		// build common lists
		mosMenuFactory::buildLists( $lists, $menu, $uid );

		// get params definitions
		// common
		$commonParams = new mosParameters( $menu->params, $mainframe->getPath( 'commonmenu_xml' ), 'menu' );
		// blog type specific
		$blogParams = new mosParameters( $menu->params, $mainframe->getPath( 'blogmenu_xml' ), 'menu' );
		// menu type specific
		$itemParams = new mosParameters( $menu->params, $mainframe->getPath( 'menu_xml', $menu->type ), 'menu' );
		$params = array();
		$params[] = $commonParams;
		$params[] = $blogParams;
		$params[] = $itemParams;

		content_archive_section_menu_html::editSection( $menu, $lists, $params, $option );
	}
}
?>