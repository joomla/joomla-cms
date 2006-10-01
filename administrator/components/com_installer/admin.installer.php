<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installer
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Make sure the user is authorized to view this page
 */
$user = & JFactory::getUser();
if (!$user->authorize('com_installer', 'installer')) {
	$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

define( 'COM_EXTENSIONMANAGER', dirname( __FILE__ ) . DS );
require_once( COM_EXTENSIONMANAGER . 'controller.php' );
//require_once( COM_EXTENSIONMANAGER . 'helper.php' );

$controller = new ExtensionManagerController( 'installform' );
$controller->setModelPath( COM_EXTENSIONMANAGER.'models' );
$controller->setViewPath( COM_EXTENSIONMANAGER.'views' );
$controller->execute( JRequest::getVar('task') );
$controller->redirect();
?>