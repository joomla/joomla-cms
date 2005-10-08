<?php
/**
* dom_xmlrpc_base64 is a base64 encoding / decoding utility
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
* A base64 encoding / decoding utility
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_base64 {
	/** @var string A base64 encoded string representation of the original binary data */
	var $stringData;

	/**
	* Encodes binary data as a base64 encoded string and stores it in $stringData
	* @param mixed The binary data
	*/
	function fromBinary($binaryData) {
		//input binary data
		$this->stringData = $this->encode($binaryData);
	} //fromBinary

	/**
	* Imports the specified file, encodes it as a base64 encoded string and stores it in $stringData
	* @param string The file path
	*/
	function fromFile($fileName) {
		//input file name
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'php_file_utilities.php');
		$binaryData =& php_file_utilities::getDataFromFile($fileName, 'rb');
		$this->stringData = $this->encode($binaryData);
	} //fromFile

	/**
	* Encodes the specified string as a base64 encoded string and stores it in $stringData
	* @param string The string input
	*/
	function fromString($stringData) {
		//input base64 string
		//Note: this expects a RFC 2045 compliant string!
		//Use convertToRFC2045 function to convert
		$this->stringData = $stringData;
	} //fromString

	/**
	* Returns the specified string, chunked according to the RFC2045 specification (\r\n every 76 chars)
	* @param mixed The string input
	* @return mixed The RFC2045 chunked string
	*/
	function convertToRFC2045($stringData){
		return chunk_split($stringData);
	} //convertToRFC2045

	/**
	* Returns base64 encoded binary data
	* @param mixed The binary input
	* @return mixed The base64 encoded data
	*/
	function &encode($binaryData) {
		//static conversion function
		return chunk_split(base64_encode($binaryData));
	} //encode

	/**
	* Returns decoded base64 binary data
	* @param string The base64 encoded input
	* @return mixed Base64 decoded data
	*/
	function &decode($stringData) {
		//static conversion function
		return base64_decode($stringData);
	} //decode

	/**
	* Returns a base64 decoded representation of $stringData
	* @return mixed A base64 decoded representation of $stringData
	*/
	function &getBinary() {
		return $this->decode($this->stringData);
	} //getBinary

    /**
	* Returns the (base64 encoded) contents of $stringData
	* @return string The (base64 encoded) contents of $stringData
	*/
	function getEncoded() {
		return $this->stringData;
	} //getEncoded
} //dom_xmlrpc_base64
?>