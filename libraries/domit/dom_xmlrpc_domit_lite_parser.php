<?php
/**
* dom_xmlrpc_domit_lite_document wraps a DOMIT! Lite DOM document in the DOM XML-RPC API
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

require_once(DOM_XMLRPC_INCLUDE_PATH . 'xml_domit_lite_parser.php');
require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_constants.php');

/**
* Wraps a DOMIT_Lite DOM document in the DOM XML-RPC API
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_domit_lite_document extends DOMIT_Lite_Document {

	/**
	* Constructor: instantiates the DOMIT! Lite superclass
	*/
	function dom_xmlrpc_domit_lite_document() {
		$this->DOMIT_Lite_Document();
	} //dom_xmlrpc_domit_lite_document

	/**
	* Gets the method type
	* @return string The method type
	*/
	function getMethodType() {
		return $this->documentElement->nodeName;
	} //getMethodType

	/**
	* Gets the method name
	* @return string The method name
	*/
	function getMethodName() {
		if ($this->getMethodType() == DOM_XMLRPC_TYPE_METHODCALL) {
			return $this->documentElement->childNodes[0]->firstChild->nodeValue;
		}
		//else throw exception
	} //getMethodName

	/**
	* Gets a reference to the method params node
	* @return object A reference to the method params node
	*/
	function &getParams() {
		switch ($this->getMethodType()) {
			case DOM_XMLRPC_TYPE_METHODCALL:
				return $this->documentElement->childNodes[1];
				break;

			case DOM_XMLRPC_TYPE_METHODRESPONSE:
				if (!$this->isFault()) {
					return $this->documentElement->firstChild;
				}
				break;
		}
		//else throw exception
	} //getParams

	/**
	* Gets a reference to the specified param
	* @param int The index of the requested param
	* @return object A reference to the specified param
	*/
	function &getParam($index) {
		switch ($this->getMethodType()) {
			case DOM_XMLRPC_TYPE_METHODCALL:
				return $this->documentElement->childNodes[1]->childNodes[$index];
				break;

			case DOM_XMLRPC_TYPE_METHODRESPONSE:
				if (!$this->isFault()) {
					return $this->documentElement->firstChild->childNodes[$index];
				}
				break;
		}
		//else throw exception
	} //getParam

	/**
	* Gets the number of existing params
	* @return int The number of existing params
	*/
	function getParamCount() {
		switch ($this->getMethodType()) {
			case DOM_XMLRPC_TYPE_METHODCALL:
				return $this->documentElement->childNodes[1]->childCount;
				break;

			case DOM_XMLRPC_TYPE_METHODRESPONSE:
				if (!$this->isFault()) {
					return $this->documentElement->firstChild->childCount; //either 0 or 1
				}
				break;
		}
		return -1; //maybe throw an exception?
	} //getParamCount

	/**
	* Determines whether the method response is a fault
	* @return boolean True if the method response is a fault
	*/
	function isFault() {
		return ($this->documentElement->firstChild->nodeName == DOM_XMLRPC_TYPE_FAULT);
	} //isFault

	/**
	* Returns the fault code, if a fault has occurred
	* @return int The fault code, if a fault has occurred
	*/
	function getFaultCode() {
		if ($this->isFault()) {
			$faultStruct =& $this->documentElement->firstChild->firstChild->firstChild;
			return ($faultStruct->childNodes[0]->childNodes[1]->firstChild->firstChild->nodeValue);
		}
	} //getFaultCode

	/**
	* Returns the fault string, if a fault has occurred
	* @return string The fault string, if a fault has occurred
	*/
	function getFaultString() {
		if ($this->isFault()) {
			$faultStruct =& $this->documentElement->firstChild->firstChild->firstChild;
			return ($faultStruct->childNodes[1]->childNodes[1]->firstChild->firstChild->nodeValue);
		}
	} //getFaultString

	/**
	* Returns the type of the specified param
	* @param object A reference to the param to be tested for type
	* @return string The type of the param
	*/
	function getParamType(&$node) {
		switch ($node->nodeName) {
			case DOM_XMLRPC_TYPE_PARAM:
				return $node->firstChild->firstChild->nodeName;
				break;

			default:
				//throw exception
		}
	} //getParamType
} //dom_xmlrpc_domit_lite_document
?>