<?php
/**
* @version $Id: content_typed.class.php 137 2005-09-12 10:21:17Z eddieajau $
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
class content_typed_menu {

	function edit( &$uid, $menutype, $option ) {
		global $database, $my, $mainframe;
		global $_LANG;

		$menu = new mosMenu( $database );
		$menu->load( $uid );

		// fail if checked out not by 'me'
		if ($menu->checked_out && $menu->checked_out <> $my->id) {
			mosErrorAlert( $_LANG->_( 'The module' ) .' '. $menu->title .' '. $_LANG->_( 'descBeingEditted' ) );
		}

		if ( $uid ) {
			$menu->checkout( $my->id );
		} else {
			// load values for new entry
			$menu->type 		= 'content_typed';
			mosMenuFactory::setValues( $menu, $menutype );
		}

		if ( $uid ) {
			$temp = explode( 'id=', $menu->link );
			 $query = "SELECT a.title, a.title_alias, a.id"
			. "\n FROM #__content AS a"
			. "\n WHERE a.id = '". $temp[1] ."'"
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
			$lists['content'] = '<input type="hidden" name="content_typed" value="'. $temp[1] .'" />';
			$lists['content'] .= '<a href="'. $link .'" title="Edit Static Content Item">'. $content[0]->title . $alias .'</a>';
		} else {
			$query = "SELECT a.id AS value, CONCAT( a.title, '(', a.title_alias, ')' ) AS text"
			. "\n FROM #__content AS a"
			. "\n WHERE a.state = '1'"
			. "\n AND a.sectionid = '0'"
			. "\n AND a.catid = '0'"
			. "\n ORDER BY a.id, a.title"
			;
			$database->setQuery( $query );
			$contents = $database->loadObjectList( );

			//	Create a list of links
			$lists['content'] = mosHTML::selectList( $contents, 'content_typed', 'class="inputbox" size="10"', 'value', 'text', '' );
		}

		// build common lists
		mosMenuFactory::buildLists( $lists, $menu, $uid );

		// get params definitions
		$params = new mosParameters( $menu->params, $mainframe->getPath( 'menu_xml', $menu->type ), 'menu' );

		content_menu_html::edit( $menu, $lists, $params, $option, $contents );
	}

	function redirect( $id ) {
		global $database;

		$menu = new mosMenu( $database );
		$menu->bind( $_POST );
		$menuid = mosGetParam( $_POST, 'menuid', 0 );
		if ( $menuid ) {
			$menu->id = $menuid;
		}
		$menu->checkin();

		mosRedirect( 'index2.php?option=com_typedcontent&task=edit&id='. $id );
	}
}
?>