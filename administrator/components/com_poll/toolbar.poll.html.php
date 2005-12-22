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
		JMenuBar::startTable();
		JMenuBar::title(  JText::_( 'Poll' ).'<small>'.JText::_( 'New' ) .'</small>' );
		JMenuBar::save();
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.polls.edit' );
		JMenuBar::endTable();
	}
	/**
	* Draws the menu for Editing an existing category
	*/
	function _EDIT( $pollid, $cur_template ) {
		global $database, $id;

		JMenuBar::startTable();
		JMenuBar::title(  JText::_( 'Poll' ).'<small>'.JText::_( 'Edit' ) .'</small>' );
		JMenuBar::Preview('index3.php?option=com_poll&pollid='.$pollid);
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
		JMenuBar::help( 'screen.polls.edit' );
		JMenuBar::endTable();
	}
	function _DEFAULT() {
		JMenuBar::startTable();
		JMenuBar::title(  JText::_( 'Poll Manager' ) );
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
		JMenuBar::help( 'screen.polls' );
		JMenuBar::endTable();
	}
}
?>