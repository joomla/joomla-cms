<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Installation
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Set flag that this is a parent file
define( '_JEXEC', 1 );
define( 'JPATH_BASE', dirname(__FILE__) );
define( 'DS', DIRECTORY_SEPARATOR );

require_once JPATH_BASE.DS.'includes'.DS.'defines.php';
require_once JPATH_BASE.DS.'includes'.DS.'framework.php';

// We want to echo the errors so that the xmlrpc client has a chance to capture them in the payload
JError::setErrorHandling( E_ERROR,	 'die' );
JError::setErrorHandling( E_WARNING, 'echo' );
JError::setErrorHandling( E_NOTICE,	 'echo' );

// create the mainframe object
$mainframe =& JFactory::getApplication('xmlrpc');

// Ensure that this application is enabled
if (!$mainframe->getCfg('xmlrpc_server')) {
	JError::raiseError(403, 'XML-RPC Server not enabled.');
}

// Includes the required class file for the XML-RPC Server
jimport('phpxmlrpc.xmlrpc');
jimport('phpxmlrpc.xmlrpcs');

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
// define UTF-8 as the internal encoding for the XML-RPC server
$xmlrpcServer->xml_header($mainframe->getEncoding());
$xmlrpc_internalencoding = $mainframe->getEncoding();
// debug level
$xmlrpcServer->setDebug(0);
// start the service
$xmlrpcServer->service();
?>
