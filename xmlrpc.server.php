<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** Set flag that this is a parent file */
define( "_JEXEC", 1 );

define('JPATH_BASE', dirname(__FILE__) );

require_once ( JPATH_BASE .DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'application.php' );

//if (!$mainframe->getCfg('xmlrpc_server')) {
//	die( 'XML-RPC server not enabled.' );
//}

// create the mainframe object
$mainframe = new JSite();

// set the configuration
$mainframe->setConfiguration(JPATH_CONFIGURATION . DS . 'configuration.php');

/** get the information about the current user from the sessions table */
$user	= & $mainframe->getUser();
$my		= $user->_table;

/**
* CUSTOM HANDLER FOR METHOD NOT FOUND
*/

// Includes the required class file for the XML-RPC Server
jimport('phpxmlrpc.xmlrpc' );
jimport('phpxmlrpc.xmlrpcs' );

// load all available remote calls
JPluginHelper::importPlugin( 'xmlrpc' );

$allCalls = $mainframe->triggerEvent( 'onGetWebServices' );
$methodsArray = array();

foreach($allCalls as $calls) {
	$methodsArray = array_merge($methodsArray, $calls);
}

$xmlrpcServer = new xmlrpc_server($methodsArray, false);
$xmlrpcServer->setDebug(3);
$xmlrpcServer->service();

?>