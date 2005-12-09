<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Polls
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
* @subpackage Polls
*/
class TOOLBAR_poll {
	/**
	* Draws the menu for a New category
	*/
	function _NEW() {
		mosMenuBar::startTable();
		mosMenuBar::title(  JText::_( 'Poll' ).'<small>'.JText::_( 'New' ) .'</small>' );
		mosMenuBar::save();
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.polls.edit' );
		mosMenuBar::endTable();
	}
	/**
	* Draws the menu for Editing an existing category
	*/
	function _EDIT( $pollid, $cur_template ) {
		global $database, $id;

		mosMenuBar::startTable();
		mosMenuBar::title(  JText::_( 'Poll' ).'<small>'.JText::_( 'Edit' ) .'</small>' );
		mosMenuBar::Preview('index3.php?option=com_poll&pollid='.$pollid);
		mosMenuBar::spacer();
		mosMenuBar::save();
		mosMenuBar::spacer();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			mosMenuBar::cancel( 'cancel', JText::_( 'Close' ) );
		} else {
			mosMenuBar::cancel();
		}
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.polls.edit' );
		mosMenuBar::endTable();
	}
	function _DEFAULT() {
		mosMenuBar::startTable();
		mosMenuBar::title(  JText::_( 'Poll Manager' ) );
		mosMenuBar::publishList();
		mosMenuBar::spacer();
		mosMenuBar::unpublishList();
		mosMenuBar::spacer();
		mosMenuBar::deleteList();
		mosMenuBar::spacer();
		mosMenuBar::editListX();
		mosMenuBar::spacer();
		mosMenuBar::addNewX();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.polls' );
		mosMenuBar::endTable();
	}
}
?>