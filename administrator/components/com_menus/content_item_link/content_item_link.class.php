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
* Content item link class
* @package Joomla
* @subpackage Menus
*/
class content_item_link_menu {

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
			$menu->type 		= 'content_item_link';
			$menu->menutype 	= $menutype;
			$menu->browserNav 	= 0;
			$menu->ordering 	= 9999;
			$menu->parent 		= JRequest::getVar( 'parent', 0, 'post', 'int' );
			$menu->published 	= 1;
		}
		
		$query = "SELECT a.id AS value, a.title AS text, a.sectionid, a.catid "
		. "\n FROM #__content AS a"
		. "\n INNER JOIN #__categories AS c ON a.catid = c.id"
		. "\n INNER JOIN #__sections AS s ON a.sectionid = s.id"
		. "\n WHERE a.state = 1"
		. "\n ORDER BY a.sectionid, a.catid, a.title"
		;
		$database->setQuery( $query );
		$contents = $database->loadObjectList( );

		foreach ( $contents as $content ) {
			$query = "SELECT s.title"
			. "\n FROM #__sections AS s"
			. "\n WHERE s.scope = 'content'"
			. "\n AND s.id = $content->sectionid"
			;
			$database->setQuery( $query );
			$section = $database->loadResult();

			$query = "SELECT c.title"
			. "\n FROM #__categories AS c"
			. "\n WHERE c.id = $content->catid"
			;
			$database->setQuery( $query );
			$category = $database->loadResult();

			$value = $content->value;
			$text = $section ." - ". $category ." / ". $content->text ."&nbsp;&nbsp;&nbsp;&nbsp;";

			$temp[] = mosHTML::makeOption( $value, $text );
			$contents = $temp;
		}

		//	Create a list of links
		$lists['content'] = mosHTML::selectList( $contents, 'content_item_link', 'class="inputbox" size="10"', 'value', 'text', $menu->componentid );

		// outputs item name
		$lists['link_content'] = '';
		if ( $uid ) {
			$link 	= 'javascript:submitbutton( \'redirect\' );';
			
			$temp 	= explode( 'id=', $menu->link );
			$query = "SELECT a.title, c.name AS category, s.name AS section"
			. "\n FROM #__content AS a"
			. "\n LEFT JOIN #__categories AS c ON a.catid = c.id"
			. "\n LEFT JOIN #__sections AS s ON a.sectionid = s.id"
			. "\n WHERE a.id = $temp[1]"
			;
			$database->setQuery( $query );
			$content = $database->loadObjectlist();
			
			$lists['link_content'] = '<a href="'. $link .'" title="'. JText::_( 'Edit Content Item' ) .'">'. $content[0]->title .'</a>';
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

		content_item_link_menu_html::edit( $menu, $lists, $params, $option, $contents );
	}

	function redirect( $id ) {
		global $database;

		$menu =& JModel::getInstance('menu', $database );
		$menu->bind( $_POST );
		$menuid = JRequest::getVar( 'menuid', 0, 'post', 'int' );
		if ( $menuid ) {
			$menu->id = $menuid;
		}
		$menu->checkin();

		mosRedirect( 'index2.php?option=com_content&task=edit&id='. $id );
	}
}
?>
