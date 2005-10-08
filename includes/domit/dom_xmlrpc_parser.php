<?php
/**
* dom_xmlrpc_parser is the base parsing class
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
* The base parsing class
*
* @package dom-xmlrpc
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class dom_xmlrpc_parser {
	/** @var object A wrapper for methodCall and methodResponse arrays */
	var $arrayDocument = null;
	/** @var string A temporary container for parsed string data */
	var $charContainer = '';
	/** @var array A stack holding an hierarchical list of arraytypes (i.e., 'array' or 'struct' or '__phpobject__') */
	var $lastArrayType = array();
	/** @var array A stack holding an hierarchical sequence of array references */
	var $lastArray = array();
	/** @var array A stack holding an hierarchical sequence of struct names */
	var $lastStructName = array();

	/**
	* Parses the supplied XML text
	* @param string The XML text
	* @param boolean True if SAXY is to be used
	* @return boolean True if the parsing was successful
	*/
	function parseXML($xmlText, $useSAXY = true) {
		$xmlText = trim($xmlText);

		require_once(DOM_XMLRPC_INCLUDE_PATH . 'dom_xmlrpc_array_document.php');
		$this->arrayDocument = new dom_xmlrpc_array_document();

		if ($xmlText != '') {
			if ($useSAXY || (!function_exists('xml_parser_create'))) {
				//use SAXY parser to generate array
				return $this->parseSAXY($xmlText);
			}
			else {
				//use Expat parser to generate array
				return $this->parse($xmlText);
			}
		}

		return false;
	} //parseXML

	/**
	* Invokes Expat to parse the XML text
	* @param string The XML text
	* @return boolean True if the parsing was successful
	*/
	function parse($xmlText) {
		//create instance of expat parser (should be included in php distro)
		$parser = xml_parser_create();

		//set handlers for SAX events
		xml_set_object($parser, $this);
		xml_set_element_handler($parser, 'startElement', 'endElement');
		xml_set_character_data_handler($parser, array(&$this, 'dataElement'));
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);

		//parse out whitespace -  (XML_OPTION_SKIP_WHITE = 1 does not
		//seem to work consistently across versions of PHP and Expat
		$xmlText = eregi_replace('>' . "[[:space:]]+" . '<' , '><', $xmlText);

		$success = xml_parse($parser, $xmlText);
		xml_parser_free($parser);

		return $success;
	} //parse

	/**
	* Invokes SAXY to parse the XML text
	* @param string The XML text
	* @return boolean True if the parsing was successful
	*/
	function parseSAXY($xmlText) {
		//create instance of SAXY parser
		require_once(DOM_XMLRPC_INCLUDE_PATH . 'xml_saxy_parser.php');
		$parser = new SAXY_Lite_Parser();

		$parser->xml_set_element_handler(array(&$this, 'startElement'), array(&$this, 'endElement'));
		$parser->xml_set_character_data_handler(array(&$this, 'dataElement'));

		$success =  $parser->parse($xmlText);

		return $success;
	} //parseSAXY

	/**
	* Returns a reference to the array document
	* @return array A reference to the array document
	*/
	function &getArrayDocument() {
		return $this->arrayDocument;
	} //getArrayDocument

	/**
	* Abstract method for handling start element events
	* @param object A reference to the SAX parser
	* @param string The name of the start element tag
	* @param array An array of attributes (never used by XML-RPC spec)
	*/
	function startElement($parser, $name, $attrs) {
		//must override
	} //startElement

	/**
	* Abstract method for handling end element events
	* @param object A reference to the SAX parser
	* @param string The name of the end element tag
	*/
	function endElement($parser, $name) {
		//must override
	} //endElement

	/**
	* Abstract method for adding an XML-RPC value to the results array
	* @param mixed The value
	*/
	function addValue($value) {
		//must override
	} //addValue

	/**
	* Abstract method for handling character data events
	* @param object A reference to the SAX parser
	* @param string The character data
	*/
	function dataElement($parser, $data) {
		$this->charContainer .= $data;
	} //dataElement
} //dom_xmlrpc_parser

?>