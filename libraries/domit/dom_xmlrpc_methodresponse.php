<?php
/**
* dom_xmlrpc_methodresponse is a representation of a XML-RPC method response
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
* A representation of an XML-RPC method response
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_methodresponse extends dom_xmlrpc_builder {

	/**
	* Constructor: Instantiates a new method response
	* @param mixed The method response
	*/
	function dom_xmlrpc_methodresponse($response = null) {
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_constants.php');
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_utilities.php');

		$this->methodType = DOM_XMLRPC_TYPE_METHODRESPONSE;

		if ($response != null) {
			$this->add($response);
		}
	} //dom_xmlrpc_methodresponse

	/**
	* Returns a string representation of the method response
	* @return string A string representation of the method response
	*/
	function toString() {
		 $data = <<<METHODRESPONSE
<?xml version='1.0'?>
<methodResponse>
	<params>$this->params
	</params>
</methodResponse>
METHODRESPONSE;

		return $data;
	} //toString

	/**
	* Alias of toString
	* @return string A string representation of the method response
	*/
	function toXML() {
		return $this->toString();
	} //toXML
} //dom_xmlrpc_methodresponse

?>