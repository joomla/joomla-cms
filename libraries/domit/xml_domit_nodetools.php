<?php
/**
* nodetools is a class of miscellaneous XML helper methods
* @package domit-xmlparser
* @copyright (C) 2004 John Heinstein. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author John Heinstein <johnkarl@nbnet.nb.ca>
* @link http://www.engageinteractive.com/domit/ DOMIT! Home Page
* DOMIT! is Free Software
**/

/** attribute parse state, just before parsing an attribute */
define('DOMIT_ATTRIBUTEPARSER_STATE_ATTR_NONE', 0);
/** attribute parse state, parsing an attribute key */
define('DOMIT_ATTRIBUTEPARSER_STATE_ATTR_KEY', 1);
/** attribute parse state, parsing an attribute value */
define('DOMIT_ATTRIBUTEPARSER_STATE_ATTR_VALUE', 2);

/**
*@global Array Translation table for predefined XML entities
*/
$GLOBALS['DOMIT_PREDEFINED_ENTITIES'] = array('&' => '&amp;', '<' => '&lt;', '>' => '&gt;',
											'"' => '&quot;', "'" => '&apos;');

											/**
* A class of miscellaneous XML helper methods
*
* @package domit-xmlparser
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class nodetools {
	/**
	* Parses the attributes string into an array of key / value pairs
	* @param string The attribute text
	* @return Array An array of key / value pairs
	*/
	function parseAttributes($attrText, $convertEntities = true, $definedEntities = null) {
		$attrText = trim($attrText);
		$attrArray = array();
		$maybeEntity = false;

		$total = strlen($attrText);
		$keyDump = '';
		$valueDump = '';
		$currentState = DOMIT_ATTRIBUTEPARSER_STATE_ATTR_NONE;
		$quoteType = '';

		if ($definedEntities == null) $defineEntities = array();

		for ($i = 0; $i < $total; $i++) {
			$currentChar = $attrText{$i};

			if ($currentState == DOMIT_ATTRIBUTEPARSER_STATE_ATTR_NONE) {
				if (trim($currentChar != '')) {
					$currentState = DOMIT_ATTRIBUTEPARSER_STATE_ATTR_KEY;
				}
			}

			switch ($currentChar) {
				case "\t":
					if ($currentState == DOMIT_ATTRIBUTEPARSER_STATE_ATTR_VALUE) {
						$valueDump .= $currentChar;
					}
					else {
						$currentChar = '';
					}
					break;

				case "\x0B": //vertical tab
				case "\n":
				case "\r":
					$currentChar = '';
					break;

				case '=':
					if ($currentState == DOMIT_ATTRIBUTEPARSER_STATE_ATTR_VALUE) {
						$valueDump .= $currentChar;
					}
					else {
						$currentState = DOMIT_ATTRIBUTEPARSER_STATE_ATTR_VALUE;
						$quoteType = '';
						$maybeEntity = false;
					}
					break;

				case '"':
					if ($currentState == DOMIT_ATTRIBUTEPARSER_STATE_ATTR_VALUE) {
						if ($quoteType == '') {
							$quoteType = '"';
						}
						else {
							if ($quoteType == $currentChar) {
								if ($convertEntities && $maybeEntity) {
								    $valueDump = strtr($valueDump, DOMIT_PREDEFINED_ENTITIES);
									$valueDump = strtr($valueDump, $definedEntities);
								}

								$attrArray[trim($keyDump)] = $valueDump;
								$keyDump = $valueDump = $quoteType = '';
								$currentState = DOMIT_ATTRIBUTEPARSER_STATE_ATTR_NONE;
							}
							else {
								$valueDump .= $currentChar;
							}
						}
					}
					break;

				case "'":
					if ($currentState == DOMIT_ATTRIBUTEPARSER_STATE_ATTR_VALUE) {
						if ($quoteType == '') {
							$quoteType = "'";
						}
						else {
							if ($quoteType == $currentChar) {
								if ($convertEntities && $maybeEntity) {
								    $valueDump = strtr($valueDump, $predefinedEntities);
									$valueDump = strtr($valueDump, $definedEntities);
								}

								$attrArray[trim($keyDump)] = $valueDump;
								$keyDump = $valueDump = $quoteType = '';
								$currentState = DOMIT_ATTRIBUTEPARSER_STATE_ATTR_NONE;
							}
							else {
								$valueDump .= $currentChar;
							}
						}
					}
					break;

				case '&':
					//might be an entity
					$maybeEntity = true;
					$valueDump .= $currentChar;
					break;

				default:
					if ($currentState == DOMIT_ATTRIBUTEPARSER_STATE_ATTR_KEY) {
						$keyDump .= $currentChar;
					}
					else {
						$valueDump .= $currentChar;
					}
			}
		}

		return $attrArray;
	} //parseAttributes

	/**
	* Move a node to the previous index in the childNodes array
	* @param Object The node to be moved
	*/
	function moveUp(&$node) {
		if (($node->previousSibling != null) && ($node->parentNode != null)) {
			$parent =& $node->parentNode;
			$previous =& $node->previousSibling;

			$node =& $parent->removeChild($node);
			$parent->insertBefore($node, $previous);
		}
	} //moveUp

	/**
	* Move a node to the next index in the childNodes array
	* @param Object The node to be moved
	*/
	function moveDown(&$node) {
		if (($node->nextSibling != null) && ($node->parentNode != null)) {
			$parent =& $node->parentNode;

			if ($node->nextSibling->nextSibling == null) {
				$node =& $parent->removeChild($node);
				$parent->appendChild($node);
			}
			else {
				$insertionPoint =& $node->nextSibling->nextSibling;
				$node =& $parent->removeChild($node);
				$parent->insertBefore($node, $insertionPoint);
			}
		}
	} //moveDown

	/**
	* Checks if a node exists on the given path; if so, returns the node, otherwise false
	* @param Object The calling node
	* @param string The path
	* @return mixed The found node, or false
	*/
	function &nodeExists(&$callingNode, $path) {
		$foundNode =& $callingNode->getElementsByPath($path, 1);

		if ($foundNode == null) return false;
		return $foundNode;
	} //nodeExists

	/**
	* Generates a heirarchy of nodes based on a path expression
	* @param string The path expression
	* @param string The value of a text node to be appended to the last element
	* @return object The generated nodes
	*/
	function &fromPath(&$xmldoc, $path, $text = null) {
		$pathSegments = explode('/', $path);
		$parent = null;
		$lastNode = null;
		$total = count($pathSegments);

		for ($i = 0; $i < $total; $i++) {
			if ($pathSegments[$i] != '') {
				$currNode =& $xmldoc->createElement($pathSegments[$i]);

				if ($parent == null) {
					$parent =& $currNode;
				}
				else {
					$lastNode->appendChild($currNode);
				}

				$lastNode =& $currNode;
			}
		}

		if ($text != null) {
			$currNode =& $xmldoc->createTextNode($text);
			$lastNode->appendChild($currNode);
		}

		return $parent;
	} //nodeExists
} //nodetools

?>