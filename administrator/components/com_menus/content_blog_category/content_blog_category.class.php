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
class content_blog_category {

	/**
	* @param database A database connector object
	* @param integer The unique id of the category to edit (0 if new)
	*/
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

		if ($uid) {
			$menu->checkout( $my->id );
			// get previously selected Categories
			$params = new JParameter( $menu->params );
			$catids = $params->def( 'categoryid', '' );
			if ( $catids ) {
				$query = "SELECT c.id AS `value`, c.section AS `id`, CONCAT_WS( ' / ', s.title, c.title) AS `text`"
				. "\n FROM #__sections AS s"
				. "\n INNER JOIN #__categories AS c ON c.section = s.id"
				. "\n WHERE s.scope = 'content'"
				. "\n AND c.id IN ( $catids )"
				. "\n ORDER BY s.name,c.name"
				;
				$database->setQuery( $query );
				$lookup = $database->loadObjectList();
			} else {
				$lookup 			= '';
			}
		} else {
			$menu->type 			= 'content_blog_category';
			$menu->menutype 		= $menutype;
			$menu->ordering 		= 9999;
			$menu->parent 			= JRequest::getVar( 'parent', 0, 'post', 'int' );
			$menu->published 		= 1;
			$lookup 				= '';
		}

		// build the html select list for category
		$rows[] = mosHTML::makeOption( '', JText::_( 'All Categories' ) );
		$query = "SELECT c.id AS `value`, c.section AS `id`, CONCAT_WS( ' / ', s.title, c.title) AS `text`"
		. "\n FROM #__sections AS s"
		. "\n INNER JOIN #__categories AS c ON c.section = s.id"
		. "\n WHERE s.scope = 'content'"
		. "\n ORDER BY s.name,c.name"
		;
		$database->setQuery( $query );
		$rows = array_merge( $rows, $database->loadObjectList() );
		$category = mosHTML::selectList( $rows, 'catid[]', 'class="inputbox" size="10" multiple="multiple"', 'value', 'text', $lookup );
		$lists['categoryid']	= $category;

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
		$params = new JParameter( $menu->params, JApplicationHelper::getPath( 'menu_xml', $menu->type ), 'menu' );

		/* chipjack: passing $sectCatList (categories) instead of $slist (sections) */
		content_blog_category_html::edit( $menu, $lists, $params, $option );
	}

	function saveMenu( $option, $task )
	{
		global $database;

		$params = JRequest::getVar( 'params', '', 'post' );
		$catids	= JRequest::getVar( 'catid', array(), 'post', 'array' );
		$catid	= implode( ',', $catids );

		$_POST['params']['categoryid']	= $catid;

		$row =& JTable::getInstance('menu', $database );

		if (!$row->bind( $_POST )) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}

		if (count($catids)==1 && $catids[0]!="") {
			$row->link = str_replace("id=0","id=".$catids[0],$row->link);
			$row->componentid = $catids[0];
		}

		if (!$row->check()) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		if (!$row->store()) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		$row->checkin();
		$row->reorder( "menutype = '$row->menutype' AND parent = $row->parent" );

		$msg = JText::_( 'Menu item Saved' );
		switch ( $task ) {
			case 'apply':
				josRedirect( 'index2.php?option='. $option .'&menutype='. $row->menutype .'&task=edit&id='. $row->id, $msg );
				break;

			case 'save':
			default:
				josRedirect( 'index2.php?option='. $option .'&menutype='. $row->menutype, $msg );
			break;
		}
	}
}
?>
