<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Weblinks
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
* @subpackage Weblinks
*/
class TOOLBAR_weblinks {
	function _EDIT() {
		global $id;

		$text = $id ? JText::_( 'Edit' ) : JText::_( 'New' );

		JMenuBar::startTable();
		JMenuBar::title(   JText::_( 'Weblink' ).': <small>'. $text .'</small>' );
		JMenuBar::save();
		JMenuBar::spacer();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			JMenuBar::cancel( 'cancel', JText::_( 'Close' ) );
		} else {
			JMenuBar::cancel();
		}
		JMenuBar::spacer();
		JMenuBar::help( 'screen.weblink.edit' );
		JMenuBar::endTable();
	}
	function _DEFAULT() {
		JMenuBar::startTable();
		JMenuBar::title(   JText::_( 'Weblink Manager' ) );
		JMenuBar::spacer();
		JMenuBar::publishList();
		JMenuBar::spacer();
		JMenuBar::unpublishList();
		JMenuBar::spacer();
		JMenuBar::editListX();
		JMenuBar::spacer();
		JMenuBar::deleteList();
		JMenuBar::spacer();
		JMenuBar::addNewX();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.weblink' );
		JMenuBar::endTable();
	}
}
?>