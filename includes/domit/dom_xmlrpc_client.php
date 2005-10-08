<?php
/**
* dom_xmlrpc_client provides basic XML-RPC client functionality
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

//must change these to match the XML-RPC error spec
/** invalid method response type error */
define('XMLRPC_CLIENT_RESPONSE_TYPE_ERR', 1);
/** malformed XML response error */
define('XMLRPC_CLIENT_MALFORMED_XML_ERR', 2);

require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_methodcall.php');
require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_constants.php');
require_once(DOM_XMLRPC_INCLUDE_PATH . 'php_http_client_generic.php');

/**
* Provides basic XML-RPC client functionality
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_client extends php_http_client_generic {
	/** @var string The method response type requested by the client */
	var $responseType = DOM_XMLRPC_RESPONSE_TYPE_XML_DOMIT;
	/** @var boolean True if multiple method calls are to be made in a single request */
	var $isMultiCall = false;

	/**
	* XML-RPC Client constructor
	* @param string The client connection host name, with or without its protocol prefix
	* @param string The client connection path, not including the host name
	* @param int The port to establish the client connection on
	* @param string A proxy connection host name, with or without its protocol prefix
	* @param int The timeout value for the client connection
	*/
	function dom_xmlrpc_client ($host = '', $path = '/', $port = 80, $proxy = '', $timeout = 0) {
		if ($proxy != '') {
			$host = $proxy;
		}

		$this->php_http_client_generic($host, $path, $port, $timeout);
		$this->setHeaders();
	} //dom_xmlrpc_client

	/**
	* Sets the headers for the client connection
	*/
	function setHeaders() {
		$this->setHeader('Content-Type', 'text/xml');
		$this->setHeader('Host', $this->connection->host);
		$this->setHeader('User-Agent', 'DOM XML-RPC Client/0.1');
		$this->setHeader('Connection', 'close');
	} //setHeaders

	/**
	* Determines whether message is multicall
	* @param mixed The message
	* @return mixed The evaluated message
	*/
	function evaluateMessage(&$message) {
		if (!is_string($message)) {
			if ($message->methodName == 'system.multicall') $this->isMultiCall = true;
			return $message->toXML();
		}
		else {
			if (strpos($message, 'system.multicall') !== false) $this->isMultiCall = true;
		}

		return $message;
	} //evaluateMessage

	/**
	* Sends data through the client connection
	* @param string The message to be sent
	* @return string The http response
	*/
	function &send(&$message) {
		if (!$this->isConnected()) {
			$this->connect();
		}

		$message = $this->evaluateMessage($message);
		$this->setHeader('Content-Length', strlen($message));

		$response =& parent::send($message);

		return $this->formatResponse($response->getResponse());
	} //send

	/**
	* Sends data through the client connection and disconnects
	* @param string The message to be sent
	* @return string The http response
	*/
	function &sendAndDisconnect($message) {
		$response =& $this->send($message);
		$this->disconnect();
		return $response;
	} //send

	/**
	* Returns the message response, formatted according to the specified response type
	* @param string The unformatted response
	* @return string The formatted response
	*/
	function &formatResponse($response) {
		switch ($this->responseType) {
			case DOM_XMLRPC_RESPONSE_TYPE_XML_DOMIT:
			case DOM_XMLRPC_RESPONSE_TYPE_XML_DOMIT_LITE:
			case DOM_XMLRPC_RESPONSE_TYPE_XML_DOMXML:
				return $this->returnAsXML($response);
				break;

			case DOM_XMLRPC_RESPONSE_TYPE_ARRAY:
				return $this->returnAsArray($response);
				break;

			case DOM_XMLRPC_RESPONSE_TYPE_STRING:
				return $response;
				break;
		}
	} //formatResponse

	/**
	* Returns the message response as an XML document of the specified type
	* @param string The unformatted response
	* @return string An XML document representing the method response
	*/
	function &returnAsXML($response) {
		switch ($this->responseType) {
			case DOM_XMLRPC_RESPONSE_TYPE_XML_DOMIT:
				require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_domit_parser.php' );
				$xmlrpcDoc = new dom_xmlrpc_domit_document();
				$success = $xmlrpcDoc->parseXML($response, false);
				break;

			case DOM_XMLRPC_RESPONSE_TYPE_XML_DOMIT_LITE:
				require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_domit_lite_parser.php' );
				$xmlrpcDoc = new dom_xmlrpc_domit_lite_document();
				$success = $xmlrpcDoc->parseXML($response, false);
				break;

			case DOM_XMLRPC_RESPONSE_TYPE_XML_XML_DOMXML:
				require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_domxml_parser.php' );
				$xmlrpcDoc = new dom_xmlrpc_domxml_document();
				$success = $xmlrpcDoc->parseXML($response);
				break;
		}

		if ($success) {
			return $xmlrpcDoc;
		}

		XMLRPC_Client_Exception::raiseException(XMLRPC_CLIENT_MALFORMED_XML_ERR,
										("Malformed xml returned: \n $response"));
	} //returnAsXML

	/**
	* Returns the message response as a PHP array
	* @param string The unformatted response
	* @return string A PHP array representation of the method response
	*/
	function &returnAsArray($response) {
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_array_parser.php');

		$arrayParser = new dom_xmlrpc_array_parser();

		if ($arrayParser->parseXML($response, false)) {
			return $arrayParser->getArrayDocument();
		}
		else {
			XMLRPC_Client_Exception::raiseException(XMLRPC_CLIENT_MALFORMED_XML_ERR,
										("Malformed xml returned:  \n $response"));
		}
	} //returnAsArray

	/**
	* Converts PHP arrays to stdclass objects
	* @param array The PHP array
	* @return object A stdclass object
	*/
	function &arraysToObjects(&$myArray) {
		foreach ($myArray as $key => $value) {
			$currItem =& $myArray[$key];

			if (is_array($currItem)) {
				$currItem =& $this->arraysToObjects($currItem);
			}
		}

		if (dom_xmlrpc_utilities::isAssociativeArray($myArray)) {
			$obj = new stdclass();

			foreach ($myArray as $key => $value) {
				$obj->$key =& $myArray[$value];
			}

			return $obj;
		}
		else {
			return $myArray;
		}
	} //arraysToObjects

	/**
	* Sets the method response type to the specified type
	* @param array The requested method response type
	*/
	function setResponseType($type) {
		$type = strtolower($type);

		switch ($type) {
			case DOM_XMLRPC_RESPONSE_TYPE_ARRAY:
			case DOM_XMLRPC_RESPONSE_TYPE_XML_DOMIT:
			case DOM_XMLRPC_RESPONSE_TYPE_XML_DOMIT_LITE:
			case DOM_XMLRPC_RESPONSE_TYPE_XML_DOMXML:
			case DOM_XMLRPC_RESPONSE_TYPE_STRING:
				$this->responseType = $type;
				break;
			default:
				XMLRPC_Client_Exception::raiseException(XMLRPC_CLIENT_RESPONSE_TYPE_ERR,
								('Invalid response type: ' . $type));
		}
	} //setResponseType

	/**
	* Returns the current method response type
	* @return string The current method response type
	*/
	function getResponseType() {
		return $this->responseType;
	} //getResponseType
} //dom_xmlrpc_client


/**
* An XML-RPC Client excpetion class
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class XMLRPC_Client_Exception {

	/**
	* Raises the specified exception
	* @param int The error number
	* @param int The error string
	*/
	function raiseException($errorNum, $errorString) {
		$errorMessage = $errorNum  .  "\n " . $errorString;

		if ((!isset($GLOBALS['DOMIT_XMLRPC_ERROR_FORMATTING_HTML'])) ||
			($GLOBALS['DOMIT_XMLRPC_ERROR_FORMATTING_HTML'] == true)) {
		        $errorMessage = "<p><pre>" . $errorMessage . "</pre></p>";
		}

		die($errorMessage);
	} //raiseException
} //XMLRPC_Client_Exception


?>