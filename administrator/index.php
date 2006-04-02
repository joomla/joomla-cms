<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) );

require_once ( JPATH_BASE .'/includes/application.php' );
require_once ( JPATH_BASE .'/includes/template.php'    );

// create the mainframe object
$mainframe = new JAdministrator();

// set the configuration
$mainframe->setConfiguration(JPATH_CONFIGURATION . DS . 'configuration.php');

//get the database object
$database =& $mainframe->getDBO();

// load system plugin group
JPluginHelper::importPlugin( 'system' );

// trigger the onStart events
$mainframe->triggerEvent( 'onBeforeStart' );

// Get the global option variable and create the pathway
$option = strtolower( JRequest::getVar( 'option' ) );
$mainframe->_createPathWay( );	

//get the acl object (for backwards compatibility)
$acl =& JFactory::getACL();

//create the session
$mainframe->setSession( $mainframe->getCfg('live_site').$mainframe->getClientId() );

// trigger the onAfterStart events
$mainframe->triggerEvent( 'onAfterStart' );

// login the user
if ($option == 'login') {
	$mainframe->login();
}

$cur_template = JRequest::getVar( 'template', $mainframe->getTemplate(), 'default', 'string' );

$document =& $mainframe->getDocument();
$document->parse($cur_template, 'login.php');

initDocument($document, 'login.php'); //initialise the document

$document->display( 'login.php', $mainframe->getCfg('gzip') );
?>