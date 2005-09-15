<?php
/**
* dom_xmlrpc_domxml_document wraps a DOM-XML DOM document in the DOM XML-RPC API
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
* Wraps a DOM-XML DOM document in the DOM XML-RPC API
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_domxml_document {
	/** @var object A reference to the DOM-XML document */
	var $xmldoc = null;

	/**
	* Instantiates a DOM-XML Document
	* @param string The XML text to be parsed
	* @return boolean True if parsing has been successful
	*/
	function parseXML($xmlText) {
		//remove whitespace
		$xmlText = eregi_replace('>' . "[[:space:]]+" . '<' , '><', $xmlText);

		//parse document
		$this->xmldoc = domxml_open_mem($xmlText);

		if (is_object($this->xmldoc)) $success = true;
		else $success = false;

		return $success;
	} //parseXML

	/**
	* Returns a reference to the DOM-XML Document
	* @return object A reference to the DOM-XML Document
	*/
	function getDocument() {
		return $this->xmldoc;
	} //getDocument

	/**
	* Gets the method type
	* @return string The method type
	*/
	function getMethodType() {
		$root = $this->xmldoc->document_element();
		return $root->node_name();
	} //getMethodType

	/**
	* Gets the method name
	* @return string The method name
	*/
	function getMethodName() {
		if ($this->getMethodType() == DOM_XMLRPC_TYPE_METHODCALL) {
			$node = $this->xmldoc->document_element();
			$childNodes = $node->child_nodes();
			$node = $childNodes[0];
			$node = $node->first_child();
			return $node->node_value();
		}
		//else throw exception
	} //getMethodName

	/**
	* Gets a reference to the method params node
	* @return object A reference to the method params node
	*/
	function getParams() {
		switch ($this->getMethodType()) {
			case DOM_XMLRPC_TYPE_METHODCALL:
				$node = $this->xmldoc->document_element();
				$childNodes = $node->child_nodes();
				return $childNodes[1];
				break;

			case DOM_XMLRPC_TYPE_METHODRESPONSE:
				if (!$this->isFault()) {
					$node = $this->xmldoc->document_element();
					return $node->first_child();
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
	function getParam($index) {
		switch ($this->getMethodType()) {
			case DOM_XMLRPC_TYPE_METHODCALL:
				$node = $this->xmldoc->document_element();
				$childNodes = $node->child_nodes();
				$node = $childNodes[1];
				$childNodes = $node->child_nodes();
				return $childNodes[$index];
				break;

			case DOM_XMLRPC_TYPE_METHODRESPONSE:
				if (!$this->isFault()) {
					$node = $this->xmldoc->document_element();
					$node = $node->first_child();
					$childNodes = $node->child_nodes();
					return $childNodes[$index];
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
				$node = $this->xmldoc->document_element();
				$childNodes = $node->child_nodes();
				$node = $childNodes[1];
				$childNodes = $node->child_nodes();
				return count($childNodes);
				break;

			case DOM_XMLRPC_TYPE_METHODRESPONSE:
				if (!$this->isFault()) {
					$node = $this->xmldoc->document_element();
					$node = $node->first_child();
					$childNodes = $node->child_nodes();
					return count($childNodes);
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
		$node = $this->xmldoc->document_element();
		$node = $node->first_child();

		return ($node->node_name() == DOM_XMLRPC_TYPE_FAULT);
	} //isFault

	/**
	* Returns the fault code, if a fault has occurred
	* @return int The fault code, if a fault has occurred
	*/
	function getFaultCode() {
		if ($this->isFault()) {
			$node = $this->xmldoc->document_element();
			$node = $node->first_child();
			$node = $node->first_child();
			$faultStruct = $node->first_child();

			$childNodes = $faultStruct->child_nodes();
			$node = $childNodes[0];
			$childNodes = $node->child_nodes();
			$node = $childNodes[1];
			$node = $node->first_child();
			$node = $node->first_child();

			return ($node->node_value());
		}
	} //getFaultCode

	/**
	* Returns the fault string, if a fault has occurred
	* @return string The fault string, if a fault has occurred
	*/
	function getFaultString() {
		if ($this->isFault()) {
			$node = $this->xmldoc->document_element();
			$node = $node->first_child();
			$node = $node->first_child();
			$faultStruct = $node->first_child();

			$childNodes = $faultStruct->child_nodes();
			$node = $childNodes[1];
			$childNodes = $node->child_nodes();
			$node = $childNodes[1];
			$node = $node->first_child();
			$node = $node->first_child();

			return ($node->nodeValue);
		}
	} //getFaultString

	/**
	* Returns the type of the specified param
	* @param object A reference to the param to be tested for type
	* @return string The type of the param
	*/
	function getParamType($node) {
		switch ($node->node_name()) {
			case DOM_XMLRPC_TYPE_PARAM:
				$node = $node->first_child();
				$node = $node->first_child();
				return $node->node_name();
				break;

			default:
				//throw exception
		}
	} //getParamType

	/**
	* Returns an unformatted string representation of the document
	* @return string An unformatted string representation of the document
	*/
	function toString() {
		if (func_num_args() > 0) {
			$node = func_get_arg(0);
		}
		else {
			$node = $this->xmldoc->document_element();
		}

		$str = '';
		$str = @$this->xmldoc->dump_node($node);

		if ($str == '') {
			$str = @$node->dump_node($node);
		}

		return $str;
	} //toString

	/**
	* Returns a string representation of the document, formatted for readability
	* @return string A string representation of the document, formatted for readability
	*/
	function toNormalizedString() {
		//this doesn't actually normalize the string
		//but is here as a precaution, better
		//that generating an error!
		if (func_num_args() > 0) {
			return $this->toString(func_get_arg(0));
		}
		return $this->toString();
	} //toNormalizedString

} //dom_xmlrpc_domit_document
?>