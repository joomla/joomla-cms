<?php
/**
* dom_xmlrpc_array_parser handles SAX events to convert a DOM XML-RPC XML string into a PHP array
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

require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_parser.php');

/**
* Handles SAX events to convert a DOM XML-RPC XML string into a PHP array
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_array_parser extends dom_xmlrpc_parser {

	/**
	* Handles start element events
	* @param object A reference to the SAX parser
	* @param string The name of the start element tag
	* @param array An array of attributes (never used by XML-RPC spec)
	*/
	function startElement($parser, $name, $attrs) {
		switch($name) {
			case DOM_XMLRPC_TYPE_METHODCALL:
			case DOM_XMLRPC_TYPE_METHODRESPONSE:
			case DOM_XMLRPC_TYPE_FAULT:
				$this->arrayDocument->methodType = $name; //register methodType
				break;
			case DOM_XMLRPC_TYPE_ARRAY:
			case DOM_XMLRPC_TYPE_STRUCT:
				$this->lastArrayType[] = $name;
				$this->lastArray[] = array();
				break;
		}
	} //startElement

	/**
	* Handles end element events
	* @param object A reference to the SAX parser
	* @param string The name of the end element tag
	*/
	function endElement($parser, $name) {
		switch($name) {
			case DOM_XMLRPC_TYPE_STRING:
				//$this->addValue(html_entity_decode($this->charContainer, ENT_QUOTES));
				$this->addValue($this->charContainer);
				break;
			case DOM_XMLRPC_TYPE_I4:
			case DOM_XMLRPC_TYPE_INT:
				$this->addValue((int)($this->charContainer));
				break;
			case DOM_XMLRPC_TYPE_DOUBLE:
				$this->addValue(floatval($this->charContainer));
				break;
			case DOM_XMLRPC_TYPE_BOOLEAN:
				$this->addValue((bool)(trim($this->charContainer)));
				break;
			case DOM_XMLRPC_TYPE_BASE64:
				require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_base64.php');
				$base64 = new dom_xmlrpc_base64();
				$base64->fromString($this->charContainer);
				$this->addValue($base64); //should I add object or string?
				break;
			case DOM_XMLRPC_TYPE_DATETIME:
				require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_datetime_iso8601.php');
				$dateTime = new dom_xmlrpc_datetime_iso8601($this->charContainer);
				$this->addValue($dateTime); //should I add object or string?
				break;
			case DOM_XMLRPC_TYPE_VALUE:
				//if charContainer has anything in it,
				//then there mustn't be a subnode, therefore a <string>
				$myValue = trim($this->charContainer);
				//if ($myValue != '') $this->addValue(html_entity_decode($myValue, ENT_QUOTES));
				if ($myValue != '') $this->addValue($myValue);
				break;
			case DOM_XMLRPC_TYPE_ARRAY:
			case DOM_XMLRPC_TYPE_STRUCT:
				$value =& array_pop($this->lastArray);
				$this->addValue($value);
				array_pop($this->lastArrayType);
				break;
			case DOM_XMLRPC_TYPE_MEMBER:
				array_pop($this->lastStructName);
				break;
			case DOM_XMLRPC_TYPE_NAME:
				$this->lastStructName[] = trim($this->charContainer);
				$this->charContainer = '';
				break;
			case DOM_XMLRPC_TYPE_METHODNAME:
				$this->arrayDocument->methodName = trim($this->charContainer);
				$this->charContainer = '';
				break;
		}
	}    //endElement

	/**
	* Adds an XML-RPC value to the results array
	* @param mixed The value
	*/
	function addValue($value) {
		$upper = count($this->lastArray) - 1;

		if ($upper > -1) {
			if ($this->lastArrayType[$upper] == DOM_XMLRPC_TYPE_STRUCT) {
				$currentName = $this->lastStructName[(count($this->lastStructName) - 1)];

				switch ($currentName) {
					case DOM_XMLRPC_NODEVALUE_FAULTCODE:
						$this->arrayDocument->faultCode = $value;
						break;

					case DOM_XMLRPC_NODEVALUE_FAULTSTRING:
						$this->arrayDocument->faultString = $value;
						break;

					default:
						//associative array item
						$this->lastArray[$upper][$currentName] = $value;
						break;
				}
			}
			else {
				//indexed array item
				$this->lastArray[$upper][] = $value;
			}
		}
		else {
			array_push($this->arrayDocument->params, $value);
		}

		$this->charContainer = '';
	} //addValue
} //dom_xmlrpc_array_parser

?>