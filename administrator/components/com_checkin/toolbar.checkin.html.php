<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Checkin
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Checkin
*/
class TOOLBAR_checkin {
	/**
	* Draws the menu for a New category
	*/
	function _DEFAULT() {
		
		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Global Check-in' ), 'checkin.png', 'index2.php?option=com_checkin' );
		mosMenuBar::help( 'screen.checkin' );
		mosMenuBar::endTable();
	}
}
?>