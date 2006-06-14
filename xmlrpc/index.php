<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installation
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

define( '_JEXEC', 1 );

define( 'JPATH_BASE', dirname( __FILE__ ) );

require_once( JPATH_BASE.'/includes/defines.php'     );
require_once( JPATH_BASE.'/includes/application.php' );

// create the mainframe object
$mainframe = new JXMLRPC(3);

// set the configuration
$mainframe->setConfiguration(JPATH_CONFIGURATION.DS.'configuration.php');

/** get the information about the current user from the sessions table */
$user	= & $mainframe->getUser();
$my		= $user->_table;

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