<?php
/**
* @version $Id: toolbar.plugin.html.php 1541 2005-12-22 21:22:26Z Jinx $
* @package Joomla
* @subpackage Plugins
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
* @subpackage Plugins
*/
class TOOLBAR_modules {
	/**
	* Draws the menu for Editing an existing module
	*/
	function _EDIT() {
		global $id;

		$text = $id ? JText::_('Edit') : JText::_('New');

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Site Plugin' ) .': <small><small>[' .$text. ']</small></small>', 'module.png' );
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
		JMenuBar::help( 'screen.plugins.edit' );
		JMenuBar::endTable();
	}

	function _DEFAULT() {
		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Plugin Manager' ) .': <small><small>['. JText::_( 'Site' ) .']</small></small>', 'module.png' );
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
		JMenuBar::help( 'screen.plugins' );
		JMenuBar::endTable();
	}
}
?>