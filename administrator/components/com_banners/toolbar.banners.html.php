<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Banners
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
* @package Joomla
* @subpackage Banners
*/
class TOOLBAR_banners {
	/**
	* Draws the menu for to Edit a banner
	*/
	function _EDIT() {
		global $id;

		if ( !$id ) {
			$id = mosGetParam( $_REQUEST, 'cid', '' );
		}
		$text = ( $id ? JText::_( 'Edit' ) : JText::_( 'New' ) );

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Banner' ) .': <small><small>[ '. $text.' ]</small></small>', 'generic.png' );
		JMenuBar::media_manager( 'banners' );
		JMenuBar::spacer();
		if ($id) {
			JMenuBar::trash('remove', 'Delete', false);
			JMenuBar::spacer();
		}
		JMenuBar::apply();
		JMenuBar::spacer();
		JMenuBar::save();
		JMenuBar::spacer();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			JMenuBar::cancel( 'cancel', JText::_( 'Close' ) );
		} else {
			JMenuBar::cancel();
		}
		JMenuBar::spacer();
		JMenuBar::help( 'screen.banners.edit' );
		JMenuBar::endTable();
	}
	
	function _DEFAULT() {

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Banner Manager' ), 'generic.png' );
		JMenuBar::media_manager( 'banners' );
		JMenuBar::spacer();
		JMenuBar::publishList();
		JMenuBar::spacer();
		JMenuBar::unpublishList();
		JMenuBar::spacer();
		JMenuBar::deleteList();
		JMenuBar::spacer();
		JMenuBar::editListX();
		JMenuBar::spacer();
		JMenuBar::addNewX();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.banners' );
		JMenuBar::spacer();
		JMenuBar::endTable();
	}
}

/**
* @package Joomla
*/
class TOOLBAR_bannerClient {
	/**
	* Draws the menu for to Edit a client
	*/
	function _EDIT() {
		global $id;

		if ( !$id ) {
			$id = mosGetParam( $_REQUEST, 'cid', '' );
		}
		$text = ( $id ? JText::_( 'Edit' ) : JText::_( 'New' ) );

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Banner Client' ) .': <small><small>[ '. $text.' ]</small></small>', 'generic.png' );
		if ($id) {
			JMenuBar::trash('removeclients', 'Delete', false);
			JMenuBar::spacer();
		}
		JMenuBar::apply('applyclient');
		JMenuBar::save( 'saveclient' );
		JMenuBar::spacer();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			JMenuBar::cancel( 'cancelclient', JText::_( 'Close' ) );
		} else {
			JMenuBar::cancel( 'cancelclient' );
		}
		JMenuBar::spacer();
		JMenuBar::help( 'screen.banners.client.edit' );
		JMenuBar::endTable();
	}
	
	/**
	* Draws the default menu
	*/
	function _DEFAULT() {
		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Banner Client Manager' ), 'generic.png' );
		JMenuBar::deleteList( '', 'removeclients' );
		JMenuBar::spacer();
		JMenuBar::editListX( 'editclient' );
		JMenuBar::spacer();
		JMenuBar::addNewX( 'newclient' );
		JMenuBar::spacer();
		JMenuBar::help( 'screen.banners.client' );
		JMenuBar::endTable();
	}
}
?>