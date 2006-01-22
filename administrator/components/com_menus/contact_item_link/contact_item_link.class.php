<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Contact item link class
* @package Joomla
* @subpackage Menus
*/
class contact_item_link_menu {

	function edit( &$uid, $menutype, $option ) {
		global $database, $my, $mainframe;

		$menu =& JModel::getInstance('menu', $database );
		$menu->load( $uid );

		// fail if checked out not by 'me'
		if ($menu->checked_out && $menu->checked_out <> $my->id) {
        	$alert = sprintf( JText::_( 'DESCBEINGEDITTED' ), JText::_( 'The module' ), $row->title );
			$action = "document.location.href='index2.php?option=$option'";
			mosErrorAlert( $alert, $action );
		}

		if ( $uid ) {
			$menu->checkout( $my->id );
		} else {
			// load values for new entry
			$menu->type 		= 'contact_item_link';
			$menu->menutype 	= $menutype;
			$menu->browserNav 	= 0;
			$menu->ordering 	= 9999;
			$menu->parent 		= intval( mosGetParam( $_POST, 'parent', 0 ) );
			$menu->published 	= 1;
		}
	
		$query = "SELECT a.id AS value, CONCAT( a.name, ' - ',a.con_position ) AS text, a.catid "
		. "\n FROM #__contact_details AS a"
		. "\n INNER JOIN #__categories AS c ON a.catid = c.id"
		. "\n WHERE a.published = 1"
		. "\n ORDER BY a.catid, a.name"
		;
		$database->setQuery( $query );
		$contacts = $database->loadObjectList( );

		//	Create a list of links
		$lists['contact'] = mosHTML::selectList( $contacts, 'contact_item_link', 'class="inputbox" size="10"', 'value', 'text', $menu->componentid );

		// build html select list for target window
		$lists['target'] 		= mosAdminMenus::Target( $menu );

		// build the html select list for ordering
		$lists['ordering'] 		= mosAdminMenus::Ordering( $menu, $uid );
		// build the html select list for the group access
		$lists['access'] 		= mosAdminMenus::Access( $menu );
		// build the html select list for paraent item
		$lists['parent'] 		= mosAdminMenus::Parent( $menu );
		// build published button option
		$lists['published'] 	= mosAdminMenus::Published( $menu );
		// build the url link output
		$lists['link'] 		= mosAdminMenus::Link( $menu, $uid );

		// get params definitions
		$params = new JParameters( $menu->params, JApplicationHelper::getPath( 'menu_xml', $menu->type ), 'menu' );

		contact_item_link_menu_html::edit( $menu, $lists, $params, $option, $contacts );
	}
}
?>
