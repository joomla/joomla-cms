<?php
/**
* @package domit-xmlparser
* @version 1.01
* @copyright (C) 2004 John Heinstein. All rights reserved
* @author John Heinstein <johnkarl@nbnet.nb.ca>
* @link http://www.engageinteractive.com/domit/ DOMIT! Home Page
* DOMIT! is Free Software
**/

if (!defined('DOMIT_INCLUDE_PATH')) {
	/* Path to DOMIT! files */
	define('DOMIT_INCLUDE_PATH', (dirname(__FILE__) . "/"));
}

//Nodes
/** DOM Element nodeType */
define('DOMIT_ELEMENT_NODE', 1);
/** DOM Attr nodeType */
define('DOMIT_ATTRIBUTE_NODE', 2);
/** DOM Text nodeType */
define('DOMIT_TEXT_NODE', 3);
/** DOM CDATA Section nodeType */
define('DOMIT_CDATA_SECTION_NODE', 4);
/** DOM Entity Reference nodeType */
define('DOMIT_ENTITY_REFERENCE_NODE', 5);
/** DOM Entity nodeType */
define('DOMIT_ENTITY_NODE', 6);
/** DOM Processing Instruction nodeType */
define('DOMIT_PROCESSING_INSTRUCTION_NODE', 7);
/** DOM Comment nodeType */
define('DOMIT_COMMENT_NODE', 8);
/** DOM Document nodeType */
define('DOMIT_DOCUMENT_NODE', 9);
/** DOM DocType nodeType */
define('DOMIT_DOCUMENT_TYPE_NODE', 10);
/** DOM Document Fragment nodeType */
define('DOMIT_DOCUMENT_FRAGMENT_NODE', 11);
/** DOM Notation nodeType */
define('DOMIT_NOTATION_NODE', 12);

//DOM Level 1 Exceptions
/** DOM error: array index out of bounds  */
define('DOMIT_INDEX_SIZE_ERR', 1);
/** DOM error: text doesn't fit into a DOMString */
define('DOMIT_DOMSTRING_SIZE_ERR', 2);
/** DOM error: node can't be inserted at this location */
define('DOMIT_HIERARCHY_REQUEST_ERR', 3);
/** DOM error: node not a child of target document */
define('DOMIT_WRONG_DOCUMENT_ERR', 4);
/** DOM error: invalid character specified */
define('DOMIT_INVALID_CHARACTER_ERR', 5);
/** DOM error: data can't be added to current node */
define('DOMIT_NO_DATA_ALLOWED_ERR', 6);
/** DOM error: node is read-only */
define('DOMIT_NO_MODIFICATION_ALLOWED_ERR', 7);
/** DOM error: node can't be found in specified context */
define('DOMIT_NOT_FOUND_ERR', 8);
/** DOM error: operation not supported by current implementation */
define('DOMIT_NOT_SUPPORTED_ERR', 9);
/** DOM error: attribute currently in use elsewhere */
define('DOMIT_INUSE_ATTRIBUTE_ERR', 10);

//DOM Level 2 Exceptions
/** DOM error: attempt made to use an object that is no longer usable */
define('DOMIT_INVALID_STATE_ERR', 11);
/** DOM error: invalid or illegal string specified */
define('DOMIT_SYNTAX_ERR', 12);
/** DOM error: can't modify underlying type of node */
define('DOMIT_INVALID_MODIFICATION_ERR', 13);
/** DOM error: attempt to change node in a way incompatible with namespaces */
define('DOMIT_NAMESPACE_ERR', 14);
/** DOM error: operation unsupported by underlying object */
define('DOMIT_INVALID_ACCESS_ERR', 15);

//DOMIT! Exceptions
/** DOM error: attempt to instantiate abstract class */
define('DOMIT_ABSTRACT_CLASS_INSTANTIATION_ERR', 100);
/** DOM error: attempt to call abstract method */
define('DOMIT_ABSTRACT_METHOD_INVOCATION_ERR', 101);
/** DOM error: can't perform this action on or with Document Fragment */
define('DOMIT_DOCUMENT_FRAGMENT_ERR', 102);

//DOMIT! Error Modes
/** continue on error  */
define('DOMIT_ONERROR_CONTINUE', 1);
/** die on error  */
define('DOMIT_ONERROR_DIE', 2);


/**
*@global Object Instance of the UIDGenerator class
*/
$GLOBALS['uidFactory'] = new UIDGenerator();

require_once(DOMIT_INCLUDE_PATH . 'xml_domit_nodemaps.php');

/**
* Generates unique ids for each node
*
* @package domit-xmlparser
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class UIDGenerator {
	/** @var int A seed value for generating uids */
	var $seed;
	/** @var int A tally of the number of uids generated */
	var $counter = 0;

	/**
	* UIDGenerator constructor
	*/
	function UIDGenerator() {
		$this->seed = 'node' . time();
	} //UIDGenerator

	/**
	* Generates a unique id
	* @return uid
	*/
	function generateUID() {
		return ($this->seed . $this->counter++);
	} //generateUID
} //UIDGenerator

/**
* @global object Reference to custom error handler for DOMException class
*/
$GLOBALS['DOMIT_DOMException_errorHandler'] = null;
/**
* @global int Error mode; specifies whether to die on error or simply return
*/
$GLOBALS['DOMIT_DOMException_mode'] = DOMIT_ONERROR_CONTINUE;
/**
* @global string Log file for errors
*/
$GLOBALS['DOMIT_DOMException_log'] = null;

/**
* A DOMIT! exception handling class
*
* @package domit-xmlparser
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class DOMIT_DOMException {
	/**
	* Raises the specified exception
	* @param int The error number
	* @param string A string explanation of the error
	*/
	function raiseException($errorNum, $errorString) {
		if ($GLOBALS['DOMIT_DOMException_errorHandler'] != null) {
			call_user_func($GLOBALS['DOMIT_DOMException_errorHandler'], $errorNum, $errorString);
		}
		else {
			$errorMessageText = $errorNum  . ' ' . $errorString;
			$errorMessage = 'Error: ' . $errorMessageText;

			if ((!isset($GLOBALS['DOMIT_ERROR_FORMATTING_HTML'])) ||
				($GLOBALS['DOMIT_ERROR_FORMATTING_HTML'] == true)) {
					$errorMessage = "<p><pre>" . $errorMessage . "</pre></p>";
			}

			//log error to file
			if ((isset($GLOBALS['DOMIT_DOMException_log'])) &&
				($GLOBALS['DOMIT_DOMException_log'] != null)) {
					require_once(DOMIT_INCLUDE_PATH . 'php_file_utilities.php');
					$logItem = "\n" . date('Y-m-d H:i:s') . 'DOMIT! Error ' . $errorMessageText;
					php_file_utilities::putDataToFile($GLOBALS['DOMIT_DOMException_log'],
										$logItem, 'a');
			}

			switch ($GLOBALS['DOMIT_DOMException_mode']) {
				case DOMIT_ONERROR_CONTINUE:
					return;
					break;

				case DOMIT_ONERROR_DIE:
					die($errorMessage);
					break;
			}
		}
	} //raiseException

	/**
	* custom handler for DOM errors
	* @param object A reference to the custom error handler
	*/
	function setErrorHandler($method) {
		$GLOBALS['DOMIT_DOMException_errorHandler'] =& $method;
	} //setErrorHandler

	/**
	* Set error mode
	* @param int The DOM error mode
	*/
	function setErrorMode($mode) {
		$GLOBALS['DOMIT_DOMException_mode'] = $mode;
	} //setErrorMode

	/**
	* Set error mode
	* @param boolean True if errors should be logged
	* @param string Absolute or relative path to log file
	*/
	function setErrorLog($doLogErrors, $logfile) {
		if ($doLogErrors) {
			$GLOBALS['DOMIT_DOMException_log'] = $logfile;
		}
		else {
			$GLOBALS['DOMIT_DOMException_log'] = null;
		}
	} //setErrorLog
} //DOMIT_DOMException

/**
* A class representing the DOM Implementation node
*
* @package domit-xmlparser
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class DOMIT_DOMImplementation {
	function hasFeature($feature, $version = null) {
		if (strtoupper($feature) == 'XML') {
			if (($version == '1.0') || ($version == '2.0') || ($version == null)) {
				return true;
			}
		}

		return false;
	} //hasFeature

	/**
	* Creates a new DOMIT_Document node and appends a documentElement with the specified info
	* @param string The namespaceURI of the documentElement
	* @param string The $qualifiedName of the documentElement
	* @param Object A document type node
	* @return Object The new document fragment node
	*/
	function &createDocument($namespaceURI, $qualifiedName, &$docType) {
		$xmldoc = new DOMIT_Document();
		$documentElement =& $xmldoc->createElementNS($namespaceURI, $qualifiedName);

		$xmldoc->setDocumentElement($documentElement);

		if ($docType != null) {
			$xmldoc->doctype =& $docType;
		}

		return $xmldoc;
	} //createDocument

	/**
	* Creates a new DOMIT_DocumentType node (not yet implemented!)
	* @param string The $qualifiedName
	* @param string The $publicID
	* @param string The $systemID
	* @return Object The new document type node
	*/
	function &createDocumentType($qualifiedName, $publicID, $systemID) {
		//not yet implemented
		DOMIT_DOMException::raiseException(DOMIT_NOT_SUPPORTED_ERROR,
			('Method createDocumentType is not yet implemented.'));
	} //createDocumentType
} //DOMIT_DOMImplementation

?>