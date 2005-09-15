<?php
/**
* @version $Id: content_category.class.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
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
* @package Mambo
* @subpackage Menus
*/
class content_category_menu {

	/**
	* @param database A database connector object
	* @param integer The unique id of the category to edit (0 if new)
	*/
	function editCategory( $uid, $menutype, $option ) {
		global $database, $my, $mainframe;
 		global $_LANG;

		mosFS::load( '@class', 'com_content' );

		$menu = new mosMenu( $database );
		$menu->load( $uid );

		// fail if checked out not by 'me'
		if ($menu->checked_out && $menu->checked_out <> $my->id) {
			mosErrorAlert( $_LANG->_( 'The module' ) .' '. $menu->title .' '. $_LANG->_( 'descBeingEditted' ) );
		}

		if ($uid) {
			$menu->checkout( $my->id );
		} else {
			$menu->type 		= 'content_category';
			mosMenuFactory::setValues( $menu, $menutype );
		}

		// build the html select list for category
		$lists['componentid']	= mosContentFactory::buildCategoryList( $menu, $uid );
		// build common lists
		mosMenuFactory::buildLists( $lists, $menu, $uid );

		// get params definitions
		// common
		$commonParams = new mosParameters( $menu->params, $mainframe->getPath( 'commonmenu_xml' ), 'menu' );
		// blog type specific
		$tableParams = new mosParameters( $menu->params, $mainframe->getPath( 'tablemenu_xml' ), 'menu' );
		// menu type specific
		$itemParams = new mosParameters( $menu->params, $mainframe->getPath( 'menu_xml', $menu->type ), 'menu' );
		$params = array();
		$params[] = $commonParams;
		$params[] = $itemParams;
		$params[] = $tableParams;

		content_category_menu_html::editCategory( $menu, $lists, $params, $option );
	}
}
?>