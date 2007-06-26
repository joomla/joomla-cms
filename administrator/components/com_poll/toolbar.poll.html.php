<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Polls
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package		Joomla
* @subpackage	Polls
*/
class TOOLBAR_poll {
	/**
	* Draws the menu for Editing an existing category
	*/
	function _EDIT( $pollid )
	{
		$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
		JArrayHelper::toInteger($cid, array(0));

		$text = ( $cid[0] ? JText::_( 'Edit' ) : JText::_( 'New' ) );

		JToolBarHelper::title(  JText::_( 'Poll' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::Preview('index.php?option=com_poll&tmpl=component&pollid='.$pollid);
		JToolBarHelper::save();
		JToolBarHelper::apply();
		if ($cid[0]) {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		} else {
			JToolBarHelper::cancel();
		}
		JToolBarHelper::help( 'screen.polls.edit' );
	}

	function _DEFAULT() {
		JToolBarHelper::title(  JText::_( 'Poll Manager' ) );
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::help( 'screen.polls' );
	}
}
?>