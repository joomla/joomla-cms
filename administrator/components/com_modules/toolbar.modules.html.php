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
		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( $client ). JText::_( 'Module' ).' <small><small>[New]</small></small>', 'module.png' );
		mosMenuBar::preview( 'modulewindow' );
		mosMenuBar::spacer();
		mosMenuBar::save();
		mosMenuBar::spacer();
		mosMenuBar::apply();
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.modules.new' );
		mosMenuBar::endTable();
	}

	/**
	* Draws the menu for Editing an existing module
	*/
	function _EDIT( $publish, $module = '', $client ) {
		global $id;
		
		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( $client ). JText::_( 'Module' ).' <small><small>[ Edit ]</small></small>', 'module.png' );
		
		if($module == '') {
			mosMenuBar::Preview('index3.php?option=com_modules&client='.$client.'&pollid='.$id);
		}	
		mosMenuBar::spacer();
		mosMenuBar::save();
		mosMenuBar::spacer();
		mosMenuBar::apply();
		mosMenuBar::spacer();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			mosMenuBar::cancel( 'cancel', JText::_( 'Close' ) );
		} else {
			mosMenuBar::cancel();
		}
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.modules.edit' );
		mosMenuBar::endTable();
	}
	function _DEFAULT($client) {

		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Module Manager' ).' <small><small>[' .JText::_( $client ) .']</small></small>', 'module.png' );
		mosMenuBar::publishList();
		mosMenuBar::spacer();
		mosMenuBar::unpublishList();
		mosMenuBar::spacer();
		mosMenuBar::custom( 'copy', 'copy.png', 'copy_f2.png', JText::_( 'Copy' ), true );
		mosMenuBar::spacer();
		mosMenuBar::deleteList();
		mosMenuBar::spacer();
		mosMenuBar::editListX();
		mosMenuBar::spacer();
		mosMenuBar::addNewX();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.modules' );
		mosMenuBar::endTable();
	}
}
?>