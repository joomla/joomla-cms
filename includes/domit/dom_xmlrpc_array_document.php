<?php
/**
* dom_xmlrpc_array_document wraps a PHP array with the DOM XML-RPC API
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

require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_constants.php');

/**
* An array of XML-RPC data wrapped in the DOM XML-RPC API
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_array_document {
	/** @var string The type of method - response or fault */
	var $methodType;
	/** @var string The name of the requested method */
	var $methodName = "";
	/** @var string An array containing the method response params */
	var $params = array();
	/** @var int The fault code, if one exists */
	var $faultCode = null;
	/** @var string A description of the fault code, if one exists */
	var $faultString = null;

	/**
	* dom_xmlrpc_array_document constructor
	*/
	function dom_xmlrpc_array_document() {
		//do nothing
	} //dom_xmlrpc_array_document

	/**
	* Returns the method type
	* @return string The method type
	*/
	function getMethodType() {
		return $this->methodType;
	} //getMethodType

	/**
	* Returns the method name
	* @return string The method name
	*/
	function getMethodName() {
		return $this->methodName;
		//if name is "", will be picked up by methodNotFoundHandler
	} //getMethodName

	/**
	* Returns a reference to the params array
	* @return array A reference to the params array
	*/
	function &getParams() {
		return $this->params;
	} //getParams

	/**
	* Returns a reference to the specified param
	* @param int The param index in the params array
	* @return mixed A reference to the param
	*/
	function &getParam($index) {
		return $this->params[$index];
	} //getParam

	/**
	* Returns the number of params in the params array
	* @return int The number of params in the params array
	*/
	function getParamCount() {
		return count($this->params);
	} //getParamCount

	/**
	* Determines whether the method response is a fault
	* @return boolean True if the method response is a fault
	*/
	function isFault() {
		return ($this->methodType == DOM_XMLRPC_TYPE_FAULT);
	} //isFault

	/**
	* Returns the current fault code
	* @return int The current fault code
	*/
	function getFaultCode() {
		return $this->faultCode;
	} //getFaultCode

	/**
	* Returns the current fault string
	* @return string The current fault string
	*/
	function getFaultString() {
		return $this->faultString;
	} //getFaultString

	/**
	* Returns the type of the specified param
	* @param mixed The param to be tested
	* @return string The type of the specified param
	*/
	function getParamType($param) {
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_utilities.php');
		return (dom_xmlrpc_utilities::getTypeFromValue($param));
	} //getParamType

	/**
	* Returns a string representation of the parameters array
	* @return string A string representation of the parameters array
	*/
	function toString() {
		ob_start();
		print_r($this->params);

		$ob_contents = ob_get_contents();
	    ob_end_clean();

	    return $ob_contents;
	} //toString

	/**
	* Returns a formatted string representation of the parameters array
	* @return string A formatted string representation of the parameters array
	*/
	function toNormalizedString() {
		return $this->toString();
	} //toNormalizedString
} //dom_xmlrpc_array_document

?>