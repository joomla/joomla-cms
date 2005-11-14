<?php
/**
* dom_xmlrpc_utilities are a set of static utilities for handling XML-RPC
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

/**
* A set of static utilities for handling XML-RPC
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_utilities {

	/**
	* Strips the HTTP headers from the method response
	* @param string The method response, including headers
	* @return string The method response with headers stripped
	*/
	function stripHeader($myResponse) {
		$body = '';
		$total = strlen($myResponse);

		for ($i = 0; $i < $total; $i++) {
			if ($myResponse{$i} == '<') {
				$body = substr($myResponse, $i);
				break;
			}
		}

		return $body;
	} //stripHeader

	/**
	* Determines the type of a scalar value
	* @param mixed The scalar value
	* @return string The type of the scalar value
	*/
	function getScalarTypeFromValue(&$value) {
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_constants.php');

		if (is_string($value)) {
			return DOM_XMLRPC_TYPE_STRING;
		}
		else if (is_int($value)) {
			return DOM_XMLRPC_TYPE_INT;
		}
		else if (is_float($value)) {
			return DOM_XMLRPC_TYPE_DOUBLE;
		}
		else if (is_bool($value)) {
			return DOM_XMLRPC_TYPE_BOOLEAN;
		}
		else if (is_object($value)) {
			require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_datetime_iso8601.php');
			require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_base64.php');

			if (get_class($value) == 'dom_xmlrpc_datetime_iso8601') {
				return DOM_XMLRPC_TYPE_DATETIME;
			}
			else if (get_class($value) == 'dom_xmlrpc_base64') {
				return DOM_XMLRPC_TYPE_BASE64;
			}
		}

		return '';
	} //getScalarTypeFromValue

	/**
	* Determines the type of any XML-RPC value
	* @param mixed The XML-RPC value
	* @return string The type of the XML-RPC value
	*/
	function getTypeFromValue(&$value) {
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_constants.php');
		$scalarType = dom_xmlrpc_utilities::getScalarTypeFromValue($value);

		if ($scalarType == '') {
			require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_struct.php');

			if (is_array($value)) {
				if (dom_xmlrpc_utilities::isAssociativeArray($value)) {
					return DOM_XMLRPC_TYPE_STRUCT;
				}
				else {
					return DOM_XMLRPC_TYPE_ARRAY;
				}
			}
			else if (get_class($value) == 'dom_xmlrpc_struct') {
				return DOM_XMLRPC_TYPE_STRUCT;
			}
			else if(is_object($value)) {
				return DOM_XMLRPC_TYPE_STRUCT;
			}
		}
		else {
			return $scalarType;
		}
	} //getTypeFromValue

	/**
	* Converts the given scalar value to its XML-RPC equivalent
	* @param mixed The scalar value
	* @param string The type of the scalar value
	* @return string The XML-RPC equivalent of the scalar value
	*/
	function getScalarValue(&$value, $type) {
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_constants.php');

		switch($type) {
			case DOM_XMLRPC_TYPE_BOOLEAN:
				return (($value == true) ? '1' : '0');
				break;

			case DOM_XMLRPC_TYPE_DATETIME:
				require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_datetime_iso8601.php');

				if (is_object($value) && (get_class($value) == 'dom_xmlrpc_datetime_iso8601')) {
					return ($value->getDateTime_iso());
				}
				break;

			case DOM_XMLRPC_TYPE_BASE64:
				require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_base64.php');

				if (is_object($value) && (get_class($value) == 'dom_xmlrpc_base64')) {
					return ($value->getEncoded());
				}
				break;

			default:
				return ('' . $value);
		}

		return ('' . $value);
	} //getScalarValue

	/**
	* Determines whether an array is associative or not
	*
	* Note: PHP converts string keys that look like integers
	* to actual integer keys. This function will thus NOT trap
	* $myArray = array('1'=>'blah');
	* DON'T use integer string keys,
	* UNLESS you wrap them in a dom_xmlrpc_struct object
	* The following IS considered associative, however:
	* $myArray = array('1.2'=>'blah');
	* @param array The array to be tested
	* @return boolean True if the array is associative
	*/
	function isAssociativeArray(&$myArray) {
		reset($myArray);
		$myKey = key($myArray);

		if (is_string($myKey)) {
			return true;
		}

		return false;
	} //isAssociativeArray

	/**
	* Flips the html translation table
	*
	* @return array The flipped html translation table
	*/
	function getInverseTranslationTable() {
		$trans = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
		$trans = array_flip($trans);
		$trans['&amp;'] = "'";
		return $trans;
	} //getInverseTranslationTable
} //dom_xmlrpc_utilities

?>