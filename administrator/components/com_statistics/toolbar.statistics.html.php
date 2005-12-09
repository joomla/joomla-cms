<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Statistics
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
* @subpackage Statistics
*/
class TOOLBAR_statistics {
	function _SEARCHES() {
		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Search Engine Text' ), 'searchtext.png' );
		mosMenuBar::custom( 'resetStats', 'delete.png', 'delete_f2.png', JText::_( 'Reset' ), false );
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.stats.searches' );
		mosMenuBar::endTable();
	}

	function _PAGEIMP() {
		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Page Impression Statistics' ), 'impressions.png' );
		mosMenuBar::custom( 'resetStats', 'delete.png', 'delete_f2.png', JText::_( 'Reset' ), false );
		mosMenuBar::endTable();
	}

	function _DEFAULT() {
		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Browser, OS, Domain Statistics' ), 'browser.png' );
		mosMenuBar::custom( 'resetStats', 'delete.png', 'delete_f2.png', JText::_( 'Reset' ), false );
		mosMenuBar::endTable();
	}
}
?>