<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Newsfeeds
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
* @subpackage Newsfeeds
*/
class TOOLBAR_newsfeeds  {
	function _DEFAULT() {
		JMenuBar::title(  JText::_( 'Newsfeed Manager' ) );
		JMenuBar::publishList();
		JMenuBar::unpublishList();
		JMenuBar::deleteList();
		JMenuBar::editListX();
		JMenuBar::addNewX();
		JMenuBar::help( 'screen.newsfeeds' );
	}

	function _EDIT() {
		global $id;

		$text = ( $id ? JText::_( 'Edit' ) : JText::_( 'New' ) );
		
		JMenuBar::title(  JText::_( 'Newsfeed' ).': <small><small>[ '. $text.' ]</small></small>' );
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
		JMenuBar::help( 'screen.newsfeeds.edit' );
	}
}
?>