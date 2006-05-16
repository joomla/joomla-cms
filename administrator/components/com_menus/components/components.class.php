<?php
/**
 * @version  $Id$
 * @package Joomla
 * @subpackage Menus
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * @package Joomla
 * @subpackage Menus
 */
class components_menu {
	/**
	* @param database A database connector object
	* @param integer The unique id of the category to edit (0 if new)
	*/
	function edit( $uid, $menutype, $option ) {
		global $database, $my, $mainframe;

		$menu =& JTable::getInstance('menu', $database );
		$menu->load( $uid );

		$row =& JTable::getInstance('component', $database );
		// load the row from the db table
		$row->load( $menu->componentid );

		// fail if checked out not by 'me'
		if ( $menu->checked_out && $menu->checked_out <> $my->id ) {
        	$alert = sprintf( JText::_( 'DESCBEINGEDITTED' ), JText::_( 'The module' ), $row->title );
			$action = "document.location.href='index2.php?option=$option'";
			mosErrorAlert( $alert, $action );
		}

		if ( $uid ) {
			// do stuff for existing item
			$menu->checkout( $my->id );
		} else {
			// do stuff for new item
			$menu->type 		= 'components';
			$menu->menutype 	= $menutype;
			$menu->browserNav 	= 0;
			$menu->ordering 	= 9999;
			$menu->parent 		= JRequest::getVar( 'parent', 0, 'post', 'int' );
			$menu->published 	= 1;
		}

		$query = "SELECT c.id AS value, c.name AS text, c.link"
		. "\n FROM #__components AS c"
		. "\n WHERE c.link <> ''"
		. "\n ORDER BY c.name"
		;
		$database->setQuery( $query );
		$components = $database->loadObjectList( );

		// build the html select list of components
		$lists['componentid'] 	= mosAdminMenus::Component( $menu, $uid );

		// componentname
		$lists['componentname'] = mosAdminMenus::ComponentName( $menu, $uid );
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

		$helper = JMenuHelper::getInstance( $row->option );
		$params = $helper->getParams( $menu->params, $row->option );
		// get params definitions
		//$params = new JParameter( $menu->params, JApplicationHelper::getPath( 'com_xml', $row->option ), 'component' );
		components_menu_html::edit( $menu, $components, $lists, $params, $helper, $option );
	}
}
?>
