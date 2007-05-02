<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Installation
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

define( '_JEXEC', 1 );
define( 'JPATH_BASE', dirname( __FILE__ ) );
define( 'DS', DIRECTORY_SEPARATOR );

require_once( JPATH_BASE.DS.'includes'.DS.'defines.php' );
require_once( JPATH_BASE.DS.'includes'.DS.'framework.php' );
require_once( JPATH_BASE.DS.'includes'.DS.'application.php' );

error_reporting( E_ALL );

// We want to echo the errors so that the xmlrpc client has a chance to capture them in the payload
JError::setErrorHandling( E_ERROR,	 'echo' );
JError::setErrorHandling( E_WARNING, 'echo' );
JError::setErrorHandling( E_NOTICE,	 'echo' );

// create the mainframe object
$mainframe = new JXMLRPC(3);

// JRequest::clean() disabled for now as it breaks xmlrpcs.php
// TODO: We need to make sure to enable it prior to release!
//JRequest::clean();

// load the configuration
$mainframe->loadConfiguration( JPATH_CONFIGURATION.DS.'configuration.php' );

// Includes the required class file for the XML-RPC Server
jimport( 'phpxmlrpc.xmlrpc' );
jimport( 'phpxmlrpc.xmlrpcs' );

// define UTF-8 as the internal encoding for the XML-RPC server
$xmlrpc_internalencoding = $mainframe->getEncoding();

// load all available remote calls
JPluginHelper::importPlugin( 'xmlrpc' );
$allCalls = $mainframe->triggerEvent( 'onGetWebServices' );
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
