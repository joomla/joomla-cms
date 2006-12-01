<?php
/**
* @version $Id: toolbar.admin.html.php 5747 2006-11-12 21:49:30Z louis $
* @package Joomla
* @subpackage Admin
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
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
* @subpackage Admin
*/
class TOOLBAR_cpanel
{
	function _DEFAULT() {
		JMenuBar::title( JText::_( 'Control Panel' ), 'cpanel.png' );
		JMenuBar::help( 'screen.cpanel' );
	}
}
?>