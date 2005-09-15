<?php
/**
* @version $Id: url.class.php 137 2005-09-12 10:21:17Z eddieajau $
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
class url_menu {

	/**
	* @param database A database connector object
	* @param integer The unique id of the category to edit (0 if new)
	*/
	function edit( &$uid, $menutype, $option ) {
		global $database, $my, $mainframe;
		global $_LANG;

		$menu = new mosMenu( $database );
		$menu->load( $uid );

		// fail if checked out not by 'me'
		if ($menu->checked_out && $menu->checked_out <> $my->id) {
			mosErrorAlert( $_LANG->_( 'The module' ) .' '. $menu->title .' '. $_LANG->_( 'descBeingEditted' ) );
		}

		if ($uid) {
			$menu->checkout( $my->id );
		} else {
			$menu->type 		= 'url';
			mosMenuFactory::setValues( $menu, $menutype );
		}

		// build common lists
		mosMenuFactory::buildLists( $lists, $menu, $uid );

		// get params definitions
		$params = new mosParameters( $menu->params, $mainframe->getPath( 'menu_xml', $menu->type ), 'menu' );

		url_menu_html::edit( $menu, $lists, $params, $option );
	}
}
?>