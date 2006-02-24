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

		JMenuBar::title( JText::_( 'Banner' ) .': <small><small>[ '. $text.' ]</small></small>', 'generic.png' );
		JMenuBar::media_manager( 'banners' );
		if ($id) {
			JMenuBar::trash('remove', 'Delete', false);
		}
		JMenuBar::apply();
		JMenuBar::save();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			JMenuBar::cancel( 'cancel', JText::_( 'Close' ) );
		} else {
			JMenuBar::cancel();
		}
		JMenuBar::help( 'screen.banners.edit' );
	}
	
	function _DEFAULT() {

		JMenuBar::title( JText::_( 'Banner Manager' ), 'generic.png' );
		JMenuBar::media_manager( 'banners' );
		JMenuBar::publishList();
		JMenuBar::unpublishList();
		JMenuBar::deleteList();
		JMenuBar::editListX();
		JMenuBar::addNewX();
		JMenuBar::help( 'screen.banners' );
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

		JMenuBar::title( JText::_( 'Banner Client' ) .': <small><small>[ '. $text.' ]</small></small>', 'generic.png' );
		if ($id) {
			JMenuBar::trash('removeclients', 'Delete', false);
		}
		JMenuBar::apply('applyclient');
		JMenuBar::save( 'saveclient' );
		if ( $id ) {
			// for existing content items the button is renamed `close`
			JMenuBar::cancel( 'cancelclient', JText::_( 'Close' ) );
		} else {
			JMenuBar::cancel( 'cancelclient' );
		}
		JMenuBar::help( 'screen.banners.client.edit' );
	}
	
	/**
	* Draws the default menu
	*/
	function _DEFAULT() {
		JMenuBar::title( JText::_( 'Banner Client Manager' ), 'generic.png' );
		JMenuBar::deleteList( '', 'removeclients' );
		JMenuBar::editListX( 'editclient' );
		JMenuBar::addNewX( 'newclient' );
		JMenuBar::help( 'screen.banners.client' );
	}
}
?>