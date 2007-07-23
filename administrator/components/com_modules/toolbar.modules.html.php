<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * @package		Joomla
 * @subpackage	Modules
 */
class TOOLBAR_modules {
	/**
	* Draws the menu for a New module
	*/
	function _NEW($client)
	{
		JToolBarHelper::title( JText::_( 'Module' ) . ': <small><small>[ '. JText::_( 'New' ) .' ]</small></small>', 'module.png' );
		JToolBarHelper::customX( 'edit', 'forward.png', 'forward_f2.png', 'Next', true );
		JToolBarHelper::cancel();
		JToolBarHelper::help( 'screen.modules.new' );
	}

	/**
	* Draws the menu for Editing an existing module
	*/
	function _EDIT( $client )
	{
		$moduleType = JRequest::getCmd( 'module' );
		$cid 		= JRequest::getVar( 'cid', array(0), '', 'array' );
		JArrayHelper::toInteger($cid, array(0));

		JToolBarHelper::title( JText::_( 'Module' ) . ': <small><small>[ '. JText::_( 'Edit' ) .' ]</small></small>', 'module.png' );

		if($moduleType == 'custom') {
			JToolBarHelper::Preview('index.php?option=com_modules&tmpl=component&client='.$client->id.'&pollid='.$cid[0]);
		}

		JToolBarHelper::save();
		JToolBarHelper::apply();
		if ( $cid[0] ) {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		} else {
			JToolBarHelper::cancel();
		}
		JToolBarHelper::help( 'screen.modules.edit' );
	}

	function _DEFAULT($client)
	{
		JToolBarHelper::title( JText::_( 'Module Manager' ), 'module.png' );
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::custom( 'copy', 'copy.png', 'copy_f2.png', 'Copy', true );
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::help( 'screen.modules' );
	}
}
