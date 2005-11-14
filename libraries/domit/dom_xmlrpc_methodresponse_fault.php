<?php
/**
* dom_xmlrpc_methodresponse_fault encapsulates an XML-RPC fault
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
* Encapsulates an XML-RPC fault
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_methodresponse_fault extends dom_xmlrpc_builder {
	/** @var int The fault code */
	var $faultCode;
	/** @var string A string description of the fault code */
	var $faultString;

	/**
	* Constructor: instantiates the fault object
	* @param object A reference to the fault code
	*/
	function dom_xmlrpc_methodresponse_fault($fault = null) {
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_constants.php');
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_utilities.php');

		$this->methodType = DOM_XMLRPC_TYPE_METHODRESPONSE;

		if ($fault != null) {
			$this->setFaultCode($fault->getFaultCode());
			$this->setFaultString($fault->getFaultString());
		}
	} //dom_xmlrpc_methodresponse_fault

	/**
	* Returns the fault code
	* @return int The fault code
	*/
	function getFaultCode() {
		return $this->faultCode;
	} //getFaultCode

	/**
	* Sets the fault code
	* @param int The fault code
	*/
	function setFaultCode($faultCode) {
		$this->faultCode = $faultCode;
	} //setFaultCode

	/**
	* Returns the fault string
	* @return int The fault string
	*/
	function getFaultString() {
		return $this->faultString;
	} //getFaultString

	/**
	* Sets the fault string
	* @param string The fault string
	*/
	function setFaultString($faultString) {
		$this->faultString = $faultString;
	} //setFaultString

	/**
	* Returns a string representation of the fault
	* @return string A string representation of the fault
	*/
	function toString() {
		 $data = <<<METHODRESPONSE_FAULT
<?xml version='1.0'?>
<methodResponse>
	<fault>
		<value>
			<struct>
				<member>
					<name>faultCode</name>
					<value><int>$this->faultCode</int></value>
				</member>
				<member>
					<name>faultString</name>
					<value><string>$this->faultString</string></value>
				</member>
			</struct>
		</value>
	</fault>
</methodResponse>
METHODRESPONSE_FAULT;

		return $data;
	} //toString

	/**
	* Alias of toString
	* @return string A string representation of the fault
	*/
	function toXML() {
		return $this->toString();
	} //toXML
} //dom_xmlrpc_methodresponse_fault

?>