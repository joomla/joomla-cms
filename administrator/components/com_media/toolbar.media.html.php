<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Media
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
* @subpackage	Massmail
*/
class TOOLBAR_media {

	/**
	* Draws the menu for a New Media
	*/
	function _upload() {
		JToolBarHelper::title( JText::_( 'Media Manager' ) . '- <span>' . JText::_( 'Upload' ) . '</span' , 'mediamanager.png');
		JToolBarHelper::cancel('cancel', 'Close' );
		JToolBarHelper::help( 'screen.mediamanager' );
	}

	/**
	* Draws the menu for a New Media
	*/
	function _DEFAULT() {

		// Get the toolbar object instance
		$bar = & JToolBar::getInstance('JComponent');

		// Set the titlebar text
		JToolBarHelper::title( JText::_( 'Media Manager' ), 'mediamanager.png');

		// Add a delete button
		$bar = & JToolBar::getInstance('JComponent');
		$dhtml = "<a href=\"#\" onclick=\"document.mediamanager.submit('delete')\" class=\"toolbar\">
					<span class=\"icon-32-delete\" title=\"Delete\" type=\"Custom\"></span>
					Delete
				</a>";
		$bar->appendButton( 'Custom', $dhtml, 'delete' );

		// Add a popup configuration button
		JToolBarHelper::help( 'screen.mediamanager' );
	}
}
?>