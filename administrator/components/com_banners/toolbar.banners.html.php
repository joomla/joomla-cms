<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Banners
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
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
class TOOLBAR_banners
{
	/**
	* Draws the menu for to Edit a banner
	*/
	function _EDIT()
	{
		$cid = JRequest::getVar( 'cid', array(0));

		$text = ( $cid[0] ? JText::_( 'Edit' ) : JText::_( 'New' ) );

		JMenuBar::title( JText::_( 'Banner' ) .': <small><small>[ '. $text.' ]</small></small>', 'generic.png' );
		JMenuBar::save();
		JMenuBar::apply();
		if ($cid[0]) {
			// for existing items the button is renamed `close`
			JMenuBar::cancel( 'cancel', 'Close' );
		} else {
			JMenuBar::cancel();
		}
		JMenuBar::help( 'screen.banners.edit' );
	}

	function _DEFAULT()
	{
		JMenuBar::title( JText::_( 'Banner Manager' ), 'generic.png' );
		JMenuBar::publishList();
		JMenuBar::unpublishList();
		JMenuBar::customX( 'copy', 'copy.png', 'copy_f2.png', 'Copy' );
		JMenuBar::deleteList();
		JMenuBar::editListX();
		JMenuBar::addNewX();

		// Get the toolbar object instance
		$bar = & JToolBar::getInstance('JComponent');
		// Add a popup configuration button
		JMenuBar::configuration('com_banners', '500');
		JMenuBar::help( 'screen.banners' );
	}
}

/**
* @package Joomla
*/
class TOOLBAR_bannerClient
{
	/**
	* Draws the menu for to Edit a client
	*/
	function _EDIT()
	{
		$cid = JRequest::getVar( 'cid', array(0));

		$text = ( $cid[0] ? JText::_( 'Edit' ) : JText::_( 'New' ) );

		JMenuBar::title( JText::_( 'Banner Client' ) .': <small><small>[ '. $text.' ]</small></small>', 'generic.png' );
		JMenuBar::save( 'saveclient' );
		JMenuBar::apply('applyclient');
		if ($cid[0]) {
			// for existing items the button is renamed `close`
			JMenuBar::cancel( 'cancelclient', 'Close' );
		} else {
			JMenuBar::cancel( 'cancelclient' );
		}
		JMenuBar::help( 'screen.banners.client.edit' );
	}

	/**
	* Draws the default menu
	*/
	function _DEFAULT()
	{
		JMenuBar::title( JText::_( 'Banner Client Manager' ), 'generic.png' );
		JMenuBar::deleteList( '', 'removeclients' );
		JMenuBar::editListX( 'editclient' );
		JMenuBar::addNewX( 'newclient' );
		JMenuBar::help( 'screen.banners.client' );
	}
}
?>