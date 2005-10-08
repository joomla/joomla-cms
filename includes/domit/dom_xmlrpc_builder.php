<?php
/**
* dom_xmlrpc_builder is a utility for building string representations of XML-RPC documents
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
* A utility for building string representations of XML-RPC documents
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_builder {
	/** @var string The method type of the XML-RPC data, e.g., DOM_XMLRPC_TYPE_METHODRESPONSE */
	var $methodType;
	/** @var string The XML string representing the method params */
	var $params = "";
	/** @var string The type of object marshalling to be used, if this option is enabled */
	var $objectMarshalling = DOM_XMLRPC_OBJECT_MARSHALLING_ANONYMOUS;

	/**
	* Invokes the ability to pass PHP objects in addition to basic native types such as int and string
	* @param string The type of object marshalling to be invoked: either anonymous, named, or serialized
	*/
	function setObjectMarshalling($type) {
		$type = strtolower($type);

		switch ($type) {
			case DOM_XMLRPC_OBJECT_MARSHALLING_ANONYMOUS: //default
			case DOM_XMLRPC_OBJECT_MARSHALLING_NAMED:
			case DOM_XMLRPC_OBJECT_MARSHALLING_SERIALIZED:
				$this->objectMarshalling = $type;
				break;
			default:
				XMLRPC_Client_Exception::raiseException(XMLRPC_CLIENT_RESPONSE_TYPE_ERR,
									('Invalid object marshalling type: ' . $type));
		}
	} //setObjectMarshalling

	/**
	* Creates an XML-RPC representation of a scalar value (e.g. string, double, boolean)
	* @param mixed The value to be converted to XML-RPC notation
	* @param string The type of the value (will be autodetected if omitted)
	* @return string An XML-RPC representation of the value
	*/
	function createScalar($value, $type = '') {
		if ($type == '') {
			$type =  dom_xmlrpc_utilities::getScalarTypeFromValue($value);
		}

		switch ($type) {
			case DOM_XMLRPC_TYPE_STRING:
				if ($this->methodType == DOM_XMLRPC_TYPE_METHODRESPONSE) {
					//htmlencode the string, because
					//DOM XML-RPC server has decoded it
					$value = htmlentities($value, ENT_QUOTES);
				}
				break;
			case DOM_XMLRPC_TYPE_DOUBLE:
				//ensure trailing .0 if it looks like an int
				$value = '' . $value;
				if (strpos($value, '.') === false) {
					$value .= '.0';
				}
				break;
			case DOM_XMLRPC_TYPE_BOOLEAN:
				if (is_bool($value)) {
					$value = ($value ? '1' : '0');
				}
				break;
			case DOM_XMLRPC_TYPE_BASE64:
				if (is_object($value)) {
					$value = $value->getEncoded();
				}
				break;
			case DOM_XMLRPC_TYPE_DATETIME:
				if (is_object($value)) {
					$value = $value->getDateTime_iso();
				}
				break;
		}

		return ("<value><$type>$value</$type></value>");
	} //createScalar

	/**
	* Generates an XML-RPC param from the scalar and adds it to the method params
	* @param mixed The value to be converted to XML-RPC notation
	* @param string The type of the value (will be autodetected if omitted)
	*/
	function addScalar($value, $type = '') {
		$this->params .= "\n\t\t<param>" . $this->createScalar($value, $type)  . '</param>';
	} //addScalar

	/**
	* Creates an XML-RPC representation of a PHP array
	* @param array The array to be converted to XML-RPC notation
	* @return string An XML-RPC representation of the array
	*/
	function createArray(&$myArray) {
		$data = '<value><array><data>';

		foreach ($myArray as $key => $value) {
			$currDataItem =& $myArray[$key];
			$currType = dom_xmlrpc_utilities::getTypeFromValue($currDataItem);

			$data .= $this->create($currDataItem, $currType);
		}

		$data .= '</data></array></value>';

		return $data;
	} //createArray

	/**
	* Generates an XML-RPC param from the array and adds it to the method params
	* @param array The array to be converted to XML-RPC notation
	*/
	function addArray($myArray) {
		$this->addArrayByRef($myArray);
	} //addArray

	/**
	* Generates an XML-RPC param from the array reference and adds it to the method params
	* @param array The array reference to be converted to XML-RPC notation
	*/
	function addArrayByRef(&$myArray) { //can call with reference if deep copy is required
		$this->params .= "\n\t\t<param>" . $this->createArray($myArray)  . '</param>';
	} //addArrayByRef

	/**
	* Creates an XML-RPC representation of a PHP object
	* @param object The object to be converted to XML-RPC notation
	* @return string An XML-RPC representation of the object
	*/
	function createObject(&$myObject) {
		require_once('dom_xmlrpc_object.php');

		if (get_class($myObject) == 'dom_xmlrpc_object') { //wrapper for xmlrpc object
		    $myObject =& $myObject->getObject(); //grab embedded object
		}

		switch($this->objectMarshalling){
			case DOM_XMLRPC_OBJECT_MARSHALLING_ANONYMOUS:
				//generic struct, one member for each
				//object property, object type is discarded
				$data = '<value><struct>';

				foreach ($myObject as $key => $value) {
					$currValue =& $myObject->$key;
					$currType = dom_xmlrpc_utilities::getTypeFromValue($currValue);

					$data .= $this->createMember($key, $currValue, $currType);
				}

				$data .= '</struct></value>';
				break;

			case DOM_XMLRPC_OBJECT_MARSHALLING_NAMED:
				//struct with one member for each
				//object property, object type is defined
				//by an additional member (first in list) named
				//"__phpobject__" whose value is a string
				//containing the class type of the object
				$data = '<value><struct>';
				$data .= $this->createMember(DOM_XMLRPC_PHPOBJECT, get_class($myObject), DOM_XMLRPC_TYPE_STRING);

				foreach ($myObject as $key => $value) {
					$currValue =& $myObject->$key;
					$currType = dom_xmlrpc_utilities::getTypeFromValue($currValue);

					$data .= $this->createMember($key, $currValue, $currType);
				}

				$data .= '</struct></value>';
				break;

			case DOM_XMLRPC_OBJECT_MARSHALLING_SERIALIZED:
				//serialized object, one member of type base64
				//which is the serialized object, base64 encoded
				require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_base64.php');
				$data = '<value><struct>';
				$data .= $this->createMember(DOM_XMLRPC_PHPOBJECT, get_class($myObject), DOM_XMLRPC_TYPE_STRING);

				$serialized =& serialize($myObject);
				$currValue =& $this->createBase64($serialized);
				$data .= $this->createMember(DOM_XMLRPC_SERIALIZED, $currValue, DOM_XMLRPC_TYPE_BASE64);

				$data .= '</struct></value>';
				break;
		} // switch

		return $data;
	} //createObject

	/**
	* Generates an XML-RPC param from the object and adds it to the method params
	* @param object The object to be converted to XML-RPC notation
	*/
	function addObject($myObject) {
		$this->addObjectByRef($myObject);
	}//addObject

	/**
	* Generates an XML-RPC param from the object reference and adds it to the method params
	* @param object The object reference to be converted to XML-RPC notation
	*/
	function addObjectByRef(&$myObject) { //can call with reference if deep copy is required
		$this->params .= "\n\t\t<param>" . $this->createObject($myObject)  . '</param>';
	}//addObjectByRef

	/**
	* Creates an XML-RPC representation of a struct
	* @param mixed The struct to be converted to XML-RPC notation
	* @return string An XML-RPC representation of the struct
	*/
	function createStruct(&$myStruct) {
		require_once('dom_xmlrpc_object.php');
		$className = get_class($myStruct);

		//if struct is explicitly cast as an xmlrpc object
		if ($className == 'dom_xmlrpc_object') {
		    $myObject =& $myStruct->getObject(); //grab embedded object
			return $this->createObject($myObject);
		}
		else {
			//wrapper for numeric indexed structs
			//that are meant to be string indexed
			if ($className == 'dom_xmlrpc_struct') {
				$myStruct =& $myStruct->getStruct(); //grab embedded array
			}

			$isArrayNotObj = is_array($myStruct);

			//if struct is an object and isn't anonymous
			if ($isArrayNotObj && ($this->objectMarshalling != DOM_XMLRPC_OBJECT_MARSHALLING_ANONYMOUS)) {
			    return $this->createObject($myStruct);
			}
			else {
				$data = '<value><struct>';

	 			foreach ($myStruct as $key => $value) {
	 				$isArrayNotObj ? ($currValue =& $myStruct[$key]) : ($currValue =& $value);
	 				$currType = dom_xmlrpc_utilities::getTypeFromValue($currValue);

	 				$data .= $this->createMember($key, $currValue, $currType);
	 			}

	 			$data .= '</struct></value>';
	 			return $data;
			}
		}
	} //createStruct

	/**
	* Generates an XML-RPC param from the struct and adds it to the method params
	* @param mixed The struct to be converted to XML-RPC notation
	*/
	function addStruct($myStruct) {
		$this->addStructByRef($myStruct);
	} //addStruct

	/**
	* Generates an XML-RPC param from the struct reference and adds it to the method params
	* @param mixed The struct reference to be converted to XML-RPC notation
	*/
	function addStructByRef(&$myStruct) { //can call with reference if deep copy is required
		$this->params .= "\n\t\t<param>" . $this->createStruct($myStruct)  . '</param>';
	} //addStructRef

	/**
	* Creates an XML-RPC representation of a member
	* @param mixed The member to be converted to XML-RPC notation
	* @return string An XML-RPC representation of the member
	*/
	function createMember($name, &$value, $type) {
		$data = '<member><name>' . $name . '</name>';

		if ($type == '') {
			$type =  dom_xmlrpc_utilities::getScalarTypeFromValue($value);
		}

		$data .= $this->create($value, $type) . '</member>';

		return $data;
	} //createMember

	/**
	* Generates an XML-RPC param from the value and adds it to the method params
	* @param mixed The value to be converted to XML-RPC notation
	*/
	function create(&$value, $type = '') {
		$data = '';

		if ($type == '') {
			require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_utilities.php');
			$type = dom_xmlrpc_utilities::getTypeFromValue($value);
		}

		switch ($type) {
			case DOM_XMLRPC_TYPE_STRING:
			case DOM_XMLRPC_TYPE_INT:
			case DOM_XMLRPC_TYPE_I4:
			case DOM_XMLRPC_TYPE_DOUBLE:
			case DOM_XMLRPC_TYPE_BOOLEAN:
			case DOM_XMLRPC_TYPE_BASE64:
			case DOM_XMLRPC_TYPE_DATETIME:
				$data .= $this->createScalar($value, $type);
				break;
			case DOM_XMLRPC_TYPE_STRUCT:
				$data .= $this->createStruct($value, $type);
				break;
			case DOM_XMLRPC_TYPE_ARRAY:
				$data .= $this->createArray($value, $type);
				break;
		}

		return $data;
	} //create

	/**
	* Generates an XML-RPC param from the unspecified value
	* @param mixed The value to be converted to XML-RPC notation
	*/
	function add($value, $type = '') {
		$this->addByRef($value, $type);
	} //add

	/**
	* Generates an XML-RPC param from the unspecified reference
	* @param mixed The reference to be converted to XML-RPC notation
	*/
	function addByRef(&$value, $type = '') { //can call with reference if deep copy is required
		$this->params .= "\n\t\t<param>" . $this->create($value, $type)  . '</param>';
	} //addByRef

	/**
	* Generates an XML-RPC param from list of values
	*/
	function addList() {
		$total = func_num_args();

		for ($i = 0; $i < $total; $i++) {
			$this->add(func_get_arg($i));
		}
	} //addList

	/**
	* Creates a dom_xmlrpc_base64 object from the binary data
	* @param mixed The binary data
	* @return object The base64 encoded data
	*/
	function &createBase64($binaryData) {
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_base64.php');

		$base64 = new dom_xmlrpc_base64();
		$base64->fromBinary($binaryData);
		return $base64;
	} //createBase64

	/**
	* Creates a dom_xmlrpc_base64 object from the file
	* @param string The file path
	* @return object The base64 encoded data
	*/
	function &createBase64FromFile($fileName) {
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_base64.php');

		$base64 = new dom_xmlrpc_base64();
		$base64->fromFile($fileName);
		return $base64;
	} //createBase64FromFile

	/**
	* Creates a dom_xmlrpc_datetime_iso8601 object from the PHP time
	* @param string The time
	* @return object The dom_xmlrpc_datetime_iso8601 object
	*/
	function &createDateTimeISO($time) {
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_datetime_iso8601.php');

		$isoDateTime = new dom_xmlrpc_datetime_iso8601($time);

		return $isoDateTime;
	} //createDateTimeISO

} //dom_xmlrpc_builder
?>