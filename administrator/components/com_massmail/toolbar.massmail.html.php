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
class TOOLBAR_massmail {
	/**
	* Draws the menu for a New Contact
	*/
	function _DEFAULT() {

//		JMenuBar::startTable();
//		JMenuBar::title( JText::_( 'Mass Mail' ), 'massemail.png' );
//		JMenuBar::custom('send','publish.png','publish_f2.png',JText::_( 'Send Mail' ),false);
//		JMenuBar::spacer();
//		JMenuBar::cancel();
//		JMenuBar::spacer();
//		JMenuBar::help( 'screen.users.massmail' );
//		JMenuBar::endTable();


		jimport('joomla.utilities.presentation.toolbar.toolbar');
		$bar = & JToolBar::getInstance('main');
		$bar->appendButton( 'Standard', 'send', 'Send Mail', 'send', false );
		$bar->appendButton( 'Cancel' );
		$bar->appendButton( 'Help', 'screen.users.massmail' );
		echo $bar->render('main');

		echo mosHTML::Header( JText::_('Mass Mail'), 'massemail' );
	}
}
?>