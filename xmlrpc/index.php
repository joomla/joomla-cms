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
require_once( JPATH_BASE .'/includes/framework.php' );
require_once( JPATH_BASE.'/includes/application.php' );

error_reporting( E_ALL );

// create the mainframe object
$mainframe = new JXMLRPC(3);

// JRequest::clean() disabled for now as it breaks xmlrpcs.php
// TODO: We need to make sure to enable it prior to release!
//JRequest::clean();

// set the configuration
$mainframe->setConfiguration(JPATH_CONFIGURATION.DS.'configuration.php');

// Includes the required class file for the XML-RPC Server
jimport('phpxmlrpc.xmlrpc' );
jimport('phpxmlrpc.xmlrpcs' );

// load all available remote calls
JPluginHelper::importPlugin('xmlrpc');
$allCalls = $mainframe->triggerEvent('onGetWebServices');
$methodsArray = array();

foreach ($allCalls as $calls) {
	$methodsArray = array_merge($methodsArray, $calls);
}

$xmlrpcServer = new xmlrpc_server($methodsArray, false);
// allow casting to be defined by that actual values passed
$xmlrpcServer->functions_parameters_type = 'phpvals';
// debug level
$xmlrpcServer->setDebug(0);
// start the service
$xmlrpcServer->service();
?>
