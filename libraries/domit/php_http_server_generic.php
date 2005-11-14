<?php
//*******************************************************************
//php_http_server_generic represents a basic http server
//*******************************************************************
//by John Heinstein
//johnkarl@nbnet.nb.ca
//*******************************************************************
//Version 0.1
//copyright 2004 Engage Interactive
//http://www.engageinteractive.com/dom_xmlrpc/
//All rights reserved
//*******************************************************************
//Licensed under the GNU General Public License (GPL)
//http://www.gnu.org/copyleft/gpl.html
//*******************************************************************

if (!defined('PHP_HTTP_TOOLS_INCLUDE_PATH')) {
	define('PHP_HTTP_TOOLS_INCLUDE_PATH', (dirname(__FILE__) . "/"));
}

define ('CRLF', "\r\n"); //end-of-line char as defined in HTTP spec
define ('CR', "\r");
define ('LF', "\n");

class php_http_server_generic {
	var $httpStatusCodes;

	var $protocol = 'HTTP';
	var $protocolVersion = '1.0';
	var $statusCode = 200;

	var $events = array('onRequest' => null, 'onResponse' => null,
						'onGet' => null, 'onHead' => null,
						'onPost' => null, 'onPut' => null);

	function php_http_server_generic() {
		//require_once(PHP_HTTP_TOOLS_INCLUDE_PATH . 'php_http_status_codes.php');
		//$this->httpStatusCodes =&new php_http_status_codes();
	} //php_http_server_generic

	function &getHeaders() {
		$headers = headers_list();
		$response = '';

		if (count($headers) > 0) {
			foreach ($headers as $key => $value) {
				$response .= $value . CRLF;
			}
		}

		return $response;
	} //getHeaders

	function setProtocolVersion($version) {
		if (($version == '1.0') || ($version == '1.1')) {
			$$this->protocolVersion = $version;
			return true;
		}

		return false;
	} //setProtocolVersion

	function setHeader($name, $value) {
		header($name . ': ' . $value);
	} //setHeader

	function setHeaders() {
		//you will want to override this method
		$this->setHeader('Content-Type', 'text/html');
		$this->setHeader('Server', 'PHP HTTP Server (Generic)/0.1');
	} //setHeaders

	function fireEvent($target, $data) {
		if ($this->events[$target] != null) {
			call_user_func($this->events[$target], $data);
		}
	} //fireEvent

	function fireHTTPEvent($target, $data = null) {
		if ($this->events[$target] == null) {
			//if no handler is assigned,
			//delegate the event to the default handler
			$this->setHTTPEvent($target);
		}

		call_user_func($this->events[$target], $data);
	} //fireHTTPEvent

	function setHTTPEvent($option, $customHandler = null) {
		if ($customHandler != null) {
			$handler =& $customHandler;
		}
		else {
			$handler = array(&$this, 'defaultHTTPEventHandler');
		}

		switch($option) {
			case 'onGet':
			case 'onHead':
			case 'onPost':
			case 'onPut':
				$this->events[$option] =& $handler;
				break;
		}
	} //setHTTPServerEvent

	function defaultHTTPHandler() {
		//will add functionality for this later
		//work with subclasses for the time being
	} //defaultHTTPHandler

	function setDebug($option, $truthVal, $customHandler = null) {
		if ($customHandler != null) {
			$handler =& $customHandler;
		}
		else {
			$handler = array(&$this, 'defaultDebugHandler');
		}

		switch($option) {
			case 'onRequest':
			case 'onResponse':
				$truthVal ? ($this->events[$option] =& $handler) :
							($this->events[$option] = null);
				break;
		}
	} //setDebug

	function getDebug($option) {
		switch($option) {
			case 'onRequest':
			case 'onResponse':
				return ($this->events[$option] != null);
				break;
		}
	} //getDebug

	function defaultDebugHandler($data) {
		//just write to a log file, since can't display in a browser
		$this->writeDebug($data);
	} //defaultDebugHandler

	function writeDebug($data) {
		$filename = 'debug_' . time() . '.txt';
		$fileHandle = fopen($fileName, 'a');
		fwrite($fileHandle, $data);
		fclose($fileHandle);
	} //writeDebug

	function receive() {
		global $HTTP_SERVER_VARS;
		$requestMethod = strToUpper($HTTP_SERVER_VARS['REQUEST_METHOD']);

		switch ($requestMethod) {
			case 'GET':
				$this->fireHTTPEvent('onGet');
				break;

			case 'HEAD':
				$this->fireHTTPEvent('onHead');
				break;

			case 'POST':
				$this->fireHTTPEvent('onPost');
				break;

			case 'PUT':
				$this->fireHTTPEvent('onPut');
				break;
		}
	} //receive

	function respond($response) {
		//build header info
		//$response = $this->protocol . '/' . $this->protocolVersion . ' ' .
					//$this->statusCode . ' ' . $this->httpStatusCodes->getCodeString($this->statusCode) . CRLF;

		if (!headers_sent()) { //avoid generating an error when in debug mode
			$this->setHeader('Date', "date('r')");
			$this->setHeader('Content-Length', strlen($response));
			$this->setHeader('Connection', 'Close');
		}

		echo $response;
	} //respond
} //php_http_server_generic


//To invoke the server, do:
//$httpServer = new php_http_server_generic(); //or instance of a subclass
//$httpServer->receive();

?>