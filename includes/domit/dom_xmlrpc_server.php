<?php
/**
* dom_xmlrpc_server provides basic XML-RPC server functionality
* @package dom-xmlrpc
* @copyright (C) 2004 John Heinstein. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author John Heinstein <johnkarl@nbnet.nb.ca>
* @link http://www.engageinteractive.com/dom_xmlrpc/ DOM XML-RPC Home Page
* DOM XML-RPC is Free Software
**/

if (!defined('DOM_XMLRPC_INCLUDE_PATH')) {
	define('DOM_XMLRPC_INCLUDE_PATH', (dirname(__FILE__) . "/"));
}

//XML-RPC Exceptions
//From Specification for Fault Code Interoperability
/** @var int XML not well formed error */
define('DOM_XMLRPC_PARSE_ERROR_NOT_WELL_FORMED', -32700);
/** @var int unsupported encoding error */
define('DOM_XMLRPC_PARSE_ERROR_UNSUPPORTED_ENCODING', -32701);
/** @var int invalid encoding error */
define('DOM_XMLRPC_PARSE_ERROR_INVALID_CHARACTER_ENCODING', -32702);
/** @var int nonconformat XML-RPC error */
define('DOM_XMLRPC_SERVER_ERROR_INVALID_XMLRPC_NONCONFORMANT', -32600);
/** @var int requested method not found error */
define('DOM_XMLRPC_SERVER_ERROR_REQUESTED_METHOD_NOT_FOUND', -32601);
/** @var int invalid method parameters error */
define('DOM_XMLRPC_SERVER_ERROR_INVALID_METHOD_PARAMETERS', -32602);
/** @var int internal server error */
define('DOM_XMLRPC_SERVER_ERROR_INTERNAL_XMLRPC', -32603);
/** @var int application error */
define('DOM_XMLRPC_APPLICATION_ERROR', -32500);
/** @var int system error */
define('DOM_XMLRPC_SYSTEM_ERROR', -32400);
/** @var int http transport error */
define('DOM_XMLRPC_TRANSPORT_ERROR', -32300);

require_once(DOM_XMLRPC_INCLUDE_PATH . 'php_http_server_generic.php');
require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_constants.php');

/**
* Provides basic XML-RPC server functionality
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_server extends php_http_server_generic {
	/** @var object A reference to a method mapping class */
	var $methodmapper;
	/** @var boolean True if params array is to be tokenized */
	var $tokenizeParamsArray = false;
	/** @var int Server error code */
	var $serverError = -1;
	/** @var string Server error string */
	var $serverErrorString = '';
	/** @var object A reference to the handler for missing methods  */
	var $methodNotFoundHandler = null;
	/** @var boolean True if object awareness is enabled */
	var $objectAwareness = false;
	/** @var object A reference to the handler for PHP object values */
	var $objectDefinitionHandler = null;
	/** @var array Repository for multiple method responses */
	var $multiresponse = array();

	/**
	* Constructor
	* @param object A reference to a mapping of custom method handlers
	* @param boolean True if onPost is to be immediately called
	*/
	function dom_xmlrpc_server($customMethods = null, $postData = null) {
		$this->php_http_server_generic();
		$this->setHTTPEvents();
		$this->setHeaders();

		$this->methodmapper = new dom_xmlrpc_methodmapper();
		$this->addSystemMethods();

		if ($customMethods != null) $this->addCustomMethods($customMethods);
		if ($postData != null) $this->fireHTTPEvent('onPost'); //don't have to call receive() method in this case
	} //dom_xmlrpc_server

	/**
	* Sets the headers for the client connection
	*/
	function setHeaders() {
		$this->setHeader('Content-Type', 'text/xml');
		$this->setHeader('Server', 'DOM XML-RPC Server/0.1');
	} //setHeaders

	/**
	* Sets the default HTTP event handling
	*/
	function setHTTPEvents() {
		$this->setHTTPEvent('onPost', array(&$this, 'onPost'));

		$defaultHandler = array(&$this, 'onWrongRequestMethod');
		$this->setHTTPEvent('onGet', $defaultHandler);
		$this->setHTTPEvent('onHead', $defaultHandler);
		$this->setHTTPEvent('onPut', $defaultHandler);
	} //setHTTPEvents

	/**
	* Adds the default XML-RPC system methods to the method map
	*/
	function addSystemMethods() {
		$this->methodmapper->addMethod(new dom_xmlrpc_method(array(
									'name' => 'system.listMethods',
									'method' => array(&$this, 'listMethods'),
									'help' => 'Lists available server methods.',
									'signature' => array(DOM_XMLRPC_TYPE_ARRAY))));

		$this->methodmapper->addMethod(new dom_xmlrpc_method(array(
									'name' => 'system.methodSignature',
									'method' => array(&$this, 'methodSignature'),
									'help' => 'Returns signature of specified method.',
									'signature' => array(DOM_XMLRPC_TYPE_ARRAY,DOM_XMLRPC_TYPE_STRING))));

		$this->methodmapper->addMethod(new dom_xmlrpc_method(array(
									'name' => 'system.methodHelp',
									'method' => array(&$this, 'methodHelp'),
									'help' => 'Returns help for the specified method.',
									'signature' => array(DOM_XMLRPC_TYPE_STRING,DOM_XMLRPC_TYPE_STRING))));

		$this->methodmapper->addMethod(new dom_xmlrpc_method(array(
									'name' => 'system.getCapabilities',
									'method' => array(&$this, 'getCapabilities'),
									'help' => 'Returns an array of supported server specifications.',
									'signature' => array(DOM_XMLRPC_TYPE_ARRAY))));

		$this->methodmapper->addMethod(new dom_xmlrpc_method(array(
									'name' => 'system.multicall',
									'method' => array(&$this, 'multicall'),
									'help' => 'Handles multiple, asynchronous XML-RPC calls bundled into a single request.',
									'signature' => array(DOM_XMLRPC_TYPE_ARRAY,DOM_XMLRPC_TYPE_ARRAY))));
	} //addSystemMethods

	/**
	* Adds user defined methods to the method map
	* @param object A map of custom methods
	*/
	function addCustomMethods($customMethods) {
		foreach ($customMethods as $key => $value) {
			$this->methodmapper->addMethod($customMethods[$key]);
		}
	} //addCustomMethods

	/**
	* Adds user defined methods to the method map
	* @param array An array of custom methods
	*/
	function addMethods($methodsArray) {
		foreach ($methodsArray as $key => $value) {
			$this->methodmapper->addMethod($methodsArray[$key]);
		}
	} //addMethods

	/**
	* Adds a single user defined method to the method map
	* @param array An array of custom methods
	*/
	function addMethod($method) {
		//expects an associative array
		$this->methodmapper->addMethod($method);
	} //addMethod

	/**
	* Adds a map of user defined method to the method map
	* @param object A map of custom methods
	* @param array A list of method names
	*/
	function addMappedMethods(&$methodmap, $methodNameList) {
		$this->methodmapper->addMappedMethods($methodmap, $methodNameList);
	} //addMethodMap

	/**
	* Specifies whether params should be tokenized
	* @param boolean True if params should be tokenized
	*/
	function tokenizeParams($truthVal) {
		//if true, params array will be split
		//into individual parameters
		$this->tokenizeParamsArray = $truthVal;
	} //tokenizeParams


	//************************************************************************
	//****************************system methods******************************

	/**
	* XML-RPC defined method: lists all available methods
	* @return array A list of method names
	*/
	function listMethods() {
		return $this->methodmapper->getMethodNames();
	} //listMethods

	/**
	* XML-RPC defined method: returns the signature of the specified method
	* @param string The method name
	* @return array The method signature
	*/
	function methodSignature($name) {
		$myMethod =& $this->methodmapper->getMethod($name);
		return $myMethod->signature;
	} //methodSignature

	/**
	* XML-RPC defined method: returns help on the specified method
	* @param string The method name
	* @return string Help on the method
	*/
	function methodHelp($name) {
		$myMethod =& $this->methodmapper->getMethod($name);
		return $myMethod->help;
	} //methodHelp

	/**
	* XML-RPC defined method: delineates what XML-RPC services are provided by the server
	* @return array The XML-RPC services provided by the server
	*/
	function getCapabilities() {
		$capabilities = array('xmlrpc' => array(
									'specUrl' => 'http://www.xmlrpc.com/spec',
									'specVersion' => 1),
							 'introspect' => array(
									'specUrl' => 'http://xmlrpc.usefulinc.com/doc/reserved.html',
									'specVersion' => 1),
							 'system.multicall' => array(
									'specUrl' => 'http://www.xmlrpc.com/discuss/msgReader$1208',
									'specVersion' => 1),
							 'faults_interop' => array(
									'specUrl' => 'http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php',
									'specVersion' => 3));
		return $capabilities;
	} //getCapabilities

	/**
	* Handles multiple method calls in a single request
	* @param array An array of method calls
	* @return array An array of method responses
	*/
	function &multicall(&$myArray) {
		//call each method and store each result
		//in $this->multiresponse
		foreach ($myArray as $key => $value) {
			$currCall =& $myArray[$key];
			$methodName = $currCall[DOM_XMLRPC_TYPE_METHODNAME];
			$method =& $this->methodmapper->getMethod($methodName);
			$params = $currCall[DOM_XMLRPC_TYPE_PARAMS];

			if (!($method == null)) {
				if ($this->tokenizeParamsArray) {
					$this->multiresponse[] =& call_user_func_array($method->method, $params); //should I worry about < PHP 4.04?
				}
				else {
					if (count($params) == 1) {
						//if only one param, send $params[0]
						//rather than than $params
						$this->multiresponse[] =& call_user_func($method->method, $params[0]);
					}
					else {
						//send the entire array
						$this->multiresponse[] =& call_user_func($method->method, $params);
					}
				}
			}
			else { //method doesn't exist
				////return $this->handleMethodNotFound($methodName, $params);
			}
		}

		return $this->multiresponse;
	} //multicall

	//****************************system methods******************************
	//************************************************************************

	/**
	* Handles the POSTing of data to the server
	* @param string The POST data
	*/
	function onPost($postData) {
		global $HTTP_RAW_POST_DATA;

		if ($postData == null) $postData = $HTTP_RAW_POST_DATA;
		$this->respond($this->invokeMethod($postData));
	} //onPost

	/**
	* Invokes the method(s) in the XML request
	* @param string The XML text of the request
	* @return mixed The method response
	*/
	function &invokeMethod($xmlText) {
		$xmlrpcdoc = $this->parseRequest($xmlText);

		if (!$this->isError()) {
			$methodName = $xmlrpcdoc->getMethodName();
			$method =& $this->methodmapper->getMethod($methodName);
			$params =& $xmlrpcdoc->getParams();

			if (!($method == null)) {
				if ($this->tokenizeParamsArray) {
					$response =& call_user_func_array($method->method, $params); //should I worry about < PHP 4.04?
				}
				else {
					if (count($params) == 1) {
						//if only one param, send $params[0]
						//rather than than $params
						$response =& call_user_func($method->method, $params[0]);
					}
					else {
						//send the entire array
						$response =& call_user_func($method->method, $params);
					}
				}

				return $this->buildResponse($response);
			}
			else { //method doesn't exist
				return $this->handleMethodNotFound($methodName, $params);
			}
		}
	} //invokeMethod

	/**
	* Default handler for a missing method
	* @param string The method name
	* @param mixed The method params
	* @return mixed A fault or user defined data
	*/
	function &handleMethodNotFound($methodName, &$params) {
		if ($this->methodNotFoundHandler == null) {
		    //raise exception, method doesn't exist
			$this->serverError = DOM_XMLRPC_SERVER_ERROR_REQUESTED_METHOD_NOT_FOUND;
			$this->serverErrorString = 'DOM XML-RPC Server Error - Requested method not found.';
			return $this->raiseFault();
		}
		else {
			//fire the custom event; pass in a reference to the server
			//so that a fault can be generated as with default handler above
			return call_user_func($this->methodNotFoundHandler, $this, $methodName, $params);
		}
	} //handleMethodNotFound

	/**
	* Sets the handler for a missing method
	* @param object A reference to the method not found handler
	*/
	function setMethodNotFoundHandler($method) {
		$this->methodNotFoundHandler =& $method;
	} //setMethodNotFoundHandler

	/**
	* Builds a method response or fault
	* @param object A reference to the method response data
	* @return mixed The method response or fault
	*/
	function &buildResponse(&$response) {
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_fault.php');

		if (is_object($response) && (get_class($response) == 'dom_xmlrpc_fault')) {
			return $this->buildFault($response);
		}
		else {
			return $this->buildMethodResponse($response);
		}
	} //buildResponse

	/**
	* Builds a method response
	* @param object A reference to the method response data
	* @return mixed The method response
	*/
	function buildMethodResponse($response) {
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_methodresponse.php');
		$methodResponse = new dom_xmlrpc_methodresponse($response);

		return $methodResponse->toXML();
	} //buildMethodResponse

	/**
	* Determines whether an error has occurred
	* @return boolean True if an error has occurred
	*/
	function isError() {
		return ($this->serverError != -1);
	} //isError

	/**
	* Raises an XML-RPC fault
	* @return object An XML-RPC fault
	*/
	function &raiseFault() {
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_fault.php');

		$fault = new dom_xmlrpc_fault($this->serverError, $this->serverErrorString);
		return $this->buildFault($fault);
	} //raiseFault

	/**
	* Builds an XML-RPC fault
	* @param object A reference to the method response data
	* @return mixed The XML-RPC fault
	*/
	function buildFault($response) {
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_methodresponse_fault.php');
		$fault = new dom_xmlrpc_methodresponse_fault($response);

		return $fault->toXML();
	} //buildFault

	/**
	* Sets object awareness to the specified value
	* @param boolean True if object awareness is to be enabled
	*/
	function setObjectAwareness($truthVal) {
		$this->objectAwareness = $truthVal;
	} //setObjectAwareness

	/**
	* Sets the handler for object handling
	* @param object The handler for object handling
	*/
	function setObjectDefinitionHandler($handler) {
		$this->objectDefinitionHandler =& $handler;
	} //setObjectDefinitionHandler

	/**
	* Parses the method request
	* @param string The text of the method request
	* @return mixed The method response document
	*/
	function &parseRequest($xmlText) {
		if ($this->objectAwareness) {
		    require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_object_parser.php');
			$parser = new dom_xmlrpc_object_parser($this->objectDefinitionHandler);
		}
		else {
			require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_array_parser.php');
			$parser = new dom_xmlrpc_array_parser();
		}

		if ($parser->parseXML($xmlText, false)) {
			return $parser->getArrayDocument();
		}
		else {
			//raise exception, parsing error
			$this->serverError = DOM_XMLRPC_PARSE_ERROR_NOT_WELL_FORMED;
			$this->serverErrorString = 'DOM XML-RPC Parse Error - XML document not well formed.';
			return null;
		}
	} //parseRequest

	/**
	* Raises fault if POST is not used for method request
	*/
	function onWrongRequestMethod() {
		//raise exception, POST method not used
		$this->serverError = DOM_XMLRPC_SERVER_ERROR_INTERNAL_XMLRPC;
		$this->serverErrorString = 'DOM XML-RPC Server Error - ' .
				'Only POST method is allowed by the XML-RPC specification.';

		$this->respond($this->raiseFault());
	} //onWrongRequestMethod
} //dom_xmlrpc_server


/**
* Represents an XML-RPC server method
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_methods {
	/** @var array A list of methods */
	var $methods = array();

	/**
	* Adds a method to the method array
	* @param object The method to be added
	*/
	function addMethod(&$method) {
		$this->methods[$method->name] =& $method;
	} //addMethod

	/**
	* Retrieves a method from the method array
	* @param string The name of the method
	* @return object A reference to the requested method
	*/
	function &getMethod($name) {
		if (isset($this->methods[$name])) {
			return $this->methods[$name];
		}

		return null;
	} //getMethod

	/**
	* Retrieves a list of methods in the method array
	* @return array A list of methods in the method array
	*/
	function getMethodNames() {
		return array_keys($this->methods);
	} //getMethodNames
} //dom_xmlrpc_methods

/**
* Represents a map of methods available to the server
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_methodmapper {
	/** @var array The method map */
	var $mappedmethods = array();
	/** @var array A dom_xmlrpc_methods instance */
	var $unmappedmethods;

	/**
	* Constructor: Instantiates a new methodmapper
	*/
	function dom_xmlrpc_methodmapper() {
		$this->unmappedmethods = new dom_xmlrpc_methods();
	} //dom_xmlrpc_methodmapper

	/**
	* Adds a method to the method map
	* @param object The method to be added
	*/
	function addMethod(&$method) {
		$this->unmappedmethods->addMethod($method);
		$this->mappedmethods[$method->name] =& $this->unmappedmethods;
	} //addMethod

	/**
	* Adds a method map to the method map
	* @param object The method map to be added
	*/
	function addMappedMethods(&$methodmap, $methodNameList) {
		$total = count($methodNameList);

		for ($i = 0; $i < $total; $i++) {
			$this->mappedmethods[$methodNameList[$i]] =& $methodmap;
		}
	} //addMappedMethods

	/**
	* Retrieves a method from the method map
	* @param string The name of the method
	* @return object A reference to the requested method
	*/
	function &getMethod($name) {
		if (isset($this->mappedmethods[$name])) {
			$methodmap =& $this->mappedmethods[$name];
			return $methodmap->getMethod($name);
		}

		return null;
	} //getMethod

	/**
	* Retrieves a list of methods in the method array
	* @return array A list of methods in the method array
	*/
	function getMethodNames() {
		return array_keys($this->mappedmethods);
	} //getMethodNames
} //dom_xmlrpc_methodmapper

/**
* Represents a server method
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_method {
	/** @var string The method name */
	var $name;
	/** @var object A reference to the metho */
	var $method;
	/** @var string Help for the method */
	var $help = '';
	/** @var array The method signature */
	var $signature = '';

	/**
	* Constructor: Instantiates a custom method
	* @param array A method name, reference, signature, and help
	*/
	function dom_xmlrpc_method($paramArray) {
		$this->name = $paramArray['name'];
		$this->method =& $paramArray['method'];

		if (isset($paramArray['help'])) {
			$this->help = $paramArray['help'];
		}

		if (isset($paramArray['signature'])) {
			$this->signature = $paramArray['signature'];
		}
	} //dom_xmlrpc_method
} //dom_xmlrpc_method

/**
* Abstract class for a method map
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_methodmap {
	/**
	* Retrieves a method from the method array
	* @param string The name of the method
	* @return object A reference to the requested method
	*/
	function &getMethod($methodName) {
		//override this method
	} //getMethod
} //dom_xmlrpc_methodmap


/*
//To invoke the server, do:
$httpServer = new dom_xmlrpc_server();
$httpServer->addMethod($someMethod);
$httpServer->receive(); //will grab $HTTP_RAW_POST_DATA

//abbreviated format, will use $postData instead of $HTTP_RAW_POST_DATA
$httpServer = new dom_xmlrpc_server($methods, $postData);
*/

?>