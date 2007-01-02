<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Statistics
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
* @subpackage	Statistics
*/
class TOOLBAR_statistics
{
	function _DEFAULT()
	{
		JMenuBar::title( JText::_( 'Search Engine Text' ), 'searchtext.png' );
		JMenuBar::custom( 'resetStats', 'delete.png', 'delete_f2.png', 'Reset', false );
		JMenuBar::help( 'screen.stats.searches' );
	}
}
?>