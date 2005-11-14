<?php
/**
* dom_xmlrpc_methodcall is a representation of an XML-RPC method call
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

require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_builder.php');

/**
* A representation of an XML-RPC method call
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_methodcall extends dom_xmlrpc_builder {
	/** @var string The method name */
	var $methodName;
	/** @var boolean True if multiple method calls are to be made in a single request */
	var $multicall = array();

	/**
	* Constructor: Instantiates a new method call
	* @param string The method name
	*/
	function dom_xmlrpc_methodcall($methodName = '') {
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_constants.php');
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_utilities.php');

		$this->methodType = DOM_XMLRPC_TYPE_METHODCALL;
		$this->methodName = $methodName;

		$total = func_num_args();

		for ($i = 1; $i < $total; $i++) {
			$this->add(func_get_arg($i));
		}
	} //dom_xmlrpc_methodcall

	/**
	* Sets the method name
	* @param string The method name
	*/
	function setMethodName($name = '') {
		$this->methodName = $name;
	} //setMethodName

	/**
	* Appends multiple method calls to the request
	* @param string The method name
	*/
	function addMultiCall($methodName) {
		$total = func_num_args();
		$paramsArray = array();

		for ($i = 1; $i < $total; $i++) {
			$paramsArray[] = func_get_arg($i);
		}

		$this->addMultiCallByRef($methodName, $paramsArray);
	} //addMultiCall

	/**
	* Appends multiple method calls to the request
	* @param string The method name
	* @param array A reference to an array of method parameters
	*/
	function addMultiCallByRef($methodName, &$paramsArray) {
		$myCall = array(DOM_XMLRPC_TYPE_METHODNAME => $methodName,
						DOM_XMLRPC_TYPE_PARAMS => $paramsArray);
		$this->multicall[] =& $myCall;
	} //addMultiCallByRef

	/**
	* Returns a string representation of the method call
	* @return string A string representation of the method call
	*/
	function toString() {
		if ($this->methodName == "system.multicall") {
			$this->addArrayByRef($this->multicall);
		}

		$data = <<<METHODCALL
<?xml version='1.0'?>
<methodCall>
	<methodName>$this->methodName</methodName>
	<params>$this->params
	</params>
</methodCall>
METHODCALL;

		return $data;
	} //toString

	/**
	* Alias of toString
	* @return string A string representation of the method call
	*/
	function toXML() {
		return $this->toString();
	} //toXML
} //dom_xmlrpc_methodcall
?>