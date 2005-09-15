<?php
/**
* @version $Id: component_item_link.class.php 137 2005-09-12 10:21:17Z eddieajau $
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
* Component item link class
* @package Mambo
* @subpackage Menus
*/
class component_item_link_menu {

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
			$menu->type 		= 'component_item_link';
			mosMenuFactory::setValues( $menu, $menutype );
		}

		if ( $uid ) {
			$temp = explode( '&Itemid=', $menu->link );
			 $query = "SELECT a.name"
			. "\n FROM #__menu AS a"
			. "\n WHERE a.link = '". $temp[0] ."'"
			;
			$database->setQuery( $query );
			$components = $database->loadResult();
			$lists['components'] =  $components;
			$lists['components'] .= '<input type="hidden" name="link" value="'. $menu->link .'" />';
		} else {
			$query = "SELECT CONCAT( a.link, '&amp;Itemid=', a.id ) AS value, a.name AS text"
			. "\n FROM #__menu AS a"
			. "\n WHERE a.published = '1'"
			. "\n AND a.type = 'components'"
			. "\n ORDER BY a.menutype, a.name"
			;
			$database->setQuery( $query );
			$components = $database->loadObjectList( );

			//	Create a list of links
			$lists['components'] = mosHTML::selectList( $components, 'link', 'class="inputbox" size="10"', 'value', 'text', '' );
		}

		// build common lists
		mosMenuFactory::buildLists( $lists, $menu, $uid, 1 );

		// get params definitions
		$params = new mosParameters( $menu->params, $mainframe->getPath( 'menu_xml', $menu->type ), 'menu' );

		component_item_link_menu_html::edit( $menu, $lists, $params, $option );
	}
}
?>