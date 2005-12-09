<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Massmail
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
* @subpackage Massmail
*/
class TOOLBAR_media {
	/**
	* Draws the menu for a New Media
	*/

	function _DEFAULT() {

		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Media Manager' ), 'mediamanager.png');
		mosMenuBar::custom('upload','upload.png','upload_f2.png',JText::_( 'Upload' ),false);
		mosMenuBar::spacer();
		mosMenuBar::custom('newdir','new.png','new_f2.png',JText::_( 'Create' ),false);
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.mediamanager' );
		mosMenuBar::endTable();
	}
}
?>