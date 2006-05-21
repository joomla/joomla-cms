<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
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
* @package Joomla
* @subpackage Menus
*/
class content_typed_menu {

	function edit( &$uid, $menutype, $option ) {
		global $database, $my, $mainframe;

		$menu =& JTable::getInstance('menu', $database );
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
			$menu->type 		= 'content_typed';
			$menu->menutype 	= $menutype;
			$menu->browserNav 	= 0;
			$menu->ordering 	= 9999;
			$menu->parent 		= JRequest::getVar( 'parent', 0, 'post', 'int' );
			$menu->published 	= 1;
		}

		$query = "SELECT a.id AS value, CONCAT( a.title, '(', a.title_alias, ')' ) AS text"
		. "\n FROM #__content AS a"
		. "\n WHERE a.state = 1"
		. "\n AND a.sectionid = 0"
		. "\n AND a.catid = 0"
		. "\n ORDER BY a.title, a.id"
		;
		$database->setQuery( $query );
		$contents = $database->loadObjectList( );

		//	Create a list of links
		$lists['content'] = mosHTML::selectList( $contents, 'content_typed', 'class="inputbox" size="10"', 'value', 'text', $menu->componentid );

		// outputs item name
		$lists['link_content'] = '';
		if ( $uid ) {
			$temp = explode( 'id=', $menu->link );

			$query = "SELECT a.title, a.title_alias, a.id"
			. "\n FROM #__content AS a"
			. "\n WHERE a.id = $temp[1]"
			;
			$database->setQuery( $query );
			$content = $database->loadObjectlist();
			// outputs item name, category & section instead of the select list
			if ( $content[0]->title_alias ) {
				$alias = '  (<i>'. $content[0]->title_alias .'</i>)';
			} else {
				$alias = '';
			}
			$contents 	= '';
			$link 		= 'javascript:submitbutton( \'redirect\' );';

			$lists['link_content'] = '<a href="'. $link .'" title="'. JText::_( 'Edit Static Content Item' ) .'">'. $content[0]->title . $alias .'</a>';
		}

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
		$lists['link'] 			= mosAdminMenus::Link( $menu, $uid );

		// get params definitions
		$params = new JParameter( $menu->params, JApplicationHelper::getPath( 'menu_xml', $menu->type ), 'menu' );

		content_menu_html::edit( $menu, $lists, $params, $option, $contents );
	}

	function redirect( $id ) {
		global $database;

		$menu =& JTable::getInstance('menu', $database );
		$menu->bind( $_POST );
		$menuid = JRequest::getVar( 'menuid', 0, 'post', 'int' );
		if ( $menuid ) {
			$menu->id = $menuid;
		}
		$menu->checkin();

		josRedirect( 'index2.php?option=com_typedcontent&task=edit&id='. $id );
	}
}
?>
