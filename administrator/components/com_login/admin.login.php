<?php
/**
* @version $Id: login.php 3222 2006-04-24 01:49:01Z webImagery $
* @package Joomla
* @subpackage Joomla.Extensions
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

/*
 * Get the task variable from the page request variables
 */
$task = strtolower(JRequest::getVar('task'));

switch ($task) {

	case 'login' :
		$mainframe->login();
		break;
	case 'logout' :
		$mainframe->logout();
		break;
	default : break;
}

josRedirect( 'index.php' );
?>