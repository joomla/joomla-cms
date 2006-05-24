<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Massmail
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
* @subpackage Massmail
*/
class TOOLBAR_media {

	/**
	* Draws the menu for a New Media
	*/
	function _upload() {
		JMenuBar::title( JText::_( 'Media Manager' ) . '- <span>' . JText::_( 'Upload' ) . '</span' , 'mediamanager.png');
		JMenuBar::cancel('cancel', JText::_( 'Close' ));
		JMenuBar::help( 'screen.mediamanager' );
	}

	/**
	* Draws the menu for a New Media
	*/
	function _DEFAULT() {
		JMenuBar::title( JText::_( 'Media Manager' ), 'mediamanager.png');
		$bar = & JToolBar::getInstance('JComponent');

		// Add a popup configuration button
		$bar->appendButton( 'Popup', 'config', 'Configuration', 'index3.php?option=com_config&c=component&component=com_media', '700', '500' );
		JMenuBar::cancel('cancel', 'Close');
		JMenuBar::help( 'screen.mediamanager' );
	}
}
?>