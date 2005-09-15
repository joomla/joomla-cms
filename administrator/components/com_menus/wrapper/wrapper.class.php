<?php
/**
* @version $Id: wrapper.class.php 137 2005-09-12 10:21:17Z eddieajau $
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
* Wrapper class
* @package Mambo
* @subpackage Menus
*/
class wrapper_menu {

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
			$menu->type = 'wrapper';
			mosMenuFactory::setValues( $menu, $menutype );
			$menu->link = 'index.php?option=com_wrapper';
			$menu->url 	= '';
		}

		// build common lists
		mosMenuFactory::buildLists( $lists, $menu, $uid );

		// get params definitions
		// get params definitions
		// common
		$commonParams = new mosParameters( $menu->params, $mainframe->getPath( 'commonmenu_xml' ), 'menu' );
		// menu type specific
		$itemParams = new mosParameters( $menu->params, $mainframe->getPath( 'menu_xml', $menu->type ), 'menu' );
		$params[] = $commonParams;
		$params[] = $itemParams;

		if ( $uid ) {
			$menu->url = $itemParams->def( 'url', '' );
		}

		wrapper_menu_html::edit( $menu, $lists, $params, $option );
	}


	function saveMenu( $option, $task ) {
		global $database;
		global $_LANG;

		$params = mosGetParam( $_POST, 'params', '' );
		$params[url] = mosGetParam( $_POST, 'url', '' );

		if (is_array( $params )) {
		    $txt = array();
		    foreach ($params as $k=>$v) {
			   $txt[] = "$k=$v";
			}
 			$_POST['params'] = mosParameters::textareaHandling( $txt );
		}

		$row = new mosMenu( $database );

		if (!$row->bind( $_POST )) {
			mosErrorAlert( $row->getError() );
		}
		if (!$row->check()) {
			mosErrorAlert( $row->getError() );
		}
		if (!$row->store()) {
			mosErrorAlert( $row->getError() );
		}
		$row->checkin();
		$row->updateOrder( "menutype='$row->menutype' AND parent='$row->parent'" );


		$msg = $_LANG->_( 'Menu item Saved' );
		switch ( $task ) {
			case 'apply':
				mosRedirect( 'index2.php?option='. $option .'&menutype='. $row->menutype .'&task=edit&id='. $row->id, $msg );
				break;

			case 'save':
			default:
				mosRedirect( 'index2.php?option='. $option .'&menutype='. $row->menutype, $msg );
			break;
		}
	}
}
?>