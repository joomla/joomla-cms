<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Modules
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* @package Joomla
* @subpackage Modules
*/
class TOOLBAR_modules {
	/**
	* Draws the menu for a New module
	*/
	function _NEW($client)	{
		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'New Module' ).' <small><small>['. JText::_( $client ) .']</small></small>', 'module.png' );
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.modules.new' );
		JMenuBar::endTable();
	}

	/**
	* Draws the menu for Editing an existing module
	*/
	function _EDIT( $publish, $module = '', $client ) {
		global $id;

		JMenuBar::startTable();
		JMenuBar::title( JText::_( $client ). JText::_( 'Module' ).' <small><small>[ Edit ]</small></small>', 'module.png' );

		if($module == '') {
			JMenuBar::Preview('index3.php?option=com_modules&client='.$client.'&pollid='.$id);
		}
		JMenuBar::spacer();
		JMenuBar::save();
		JMenuBar::spacer();
		JMenuBar::apply();
		JMenuBar::spacer();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			JMenuBar::cancel( 'cancel', JText::_( 'Close' ) );
		} else {
			JMenuBar::cancel();
		}
		JMenuBar::spacer();
		JMenuBar::help( 'screen.modules.edit' );
		JMenuBar::endTable();
	}
	function _DEFAULT($client) {

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Module Manager' ).' <small><small>[' .JText::_( $client ) .']</small></small>', 'module.png' );
		JMenuBar::publishList();
		JMenuBar::spacer();
		JMenuBar::unpublishList();
		JMenuBar::spacer();
		JMenuBar::custom( 'copy', 'copy.png', 'copy_f2.png', JText::_( 'Copy' ), true );
		JMenuBar::spacer();
		JMenuBar::deleteList();
		JMenuBar::spacer();
		JMenuBar::editListX();
		JMenuBar::spacer();
		JMenuBar::addNewX();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.modules' );
		JMenuBar::endTable();
	}
}
?>