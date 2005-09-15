<?php
/**
* @package domit-xmlparser
* @subpackage domit-xmlparser-main
* @copyright (C) 2004 John Heinstein. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author John Heinstein <johnkarl@nbnet.nb.ca>
* @link http://www.engageinteractive.com/domit/ DOMIT! Home Page
* DOMIT! is Free Software
**/

if (!defined('DOMIT_INCLUDE_PATH')) {
	define('DOMIT_INCLUDE_PATH', (dirname(__FILE__) . "/"));
}

/** Separator for absolute path */
define('DOMIT_XPATH_SEPARATOR_ABSOLUTE', '/');
/** Separator for relative path */
define('DOMIT_XPATH_SEPARATOR_RELATIVE', '//');
/** OR separator for multiple patterns  */
define('DOMIT_XPATH_SEPARATOR_OR', '|');

/** Constant for an absolute path search (starting at the document root) */
define('DOMIT_XPATH_SEARCH_ABSOLUTE', 0);
/** Constant for a relative path search (starting at the level of the calling node) */
define('DOMIT_XPATH_SEARCH_RELATIVE', 1);
/** Constant for a variable path search (finds all matches, regardless of place in the hierarchy) */
define('DOMIT_XPATH_SEARCH_VARIABLE', 2);


/**
* DOMIT! XPath is an XPath parser.
*/
class DOMIT_XPath {
    /** @var Object The node from which the search is called */
	var $callingNode;
	/** @var Object The node that is the current parent of the search */
	var $searchType;
	/** @var array An array containing a series of path segments for which to search */
	var $arPathSegments = array();
	/** @var Object A DOMIT_NodeList of matching nodes */
	var $nodeList;
	/** @var string A temporary string container */
	var $charContainer;
	/** @var string The current character of the current pattern segment being parsed */
	var $currChar;
	/** @var string The current pattern segment being parsed */
	var $currentSegment;
	/** @var array A temporary node container for caching node references at the pattern level*/
	var $globalNodeContainer;
	/** @var array A temporary node container for caching node references at the pattern segment level */
	var $localNodeContainer;
	/** @var array Normalization table for XPath syntax */
	var $normalizationTable = array('child::' => '', 'self::' => '.',
							'attribute::' => '@', 'descendant::' => '*//',
							"\t" => ' ', "\x0B" => ' ');
	/** @var array A second-pass normalization table for XPath syntax */
	var $normalizationTable2 = array(' =' => '=', '= ' => '=', ' <' => '<',
							' >' => '>', '< ' => '<', '> ' => '>',
							' !' => '!', '( ' => '(',
							' )' => ')', ' ]' => ']', '] ' => ']',
							' [' => '[', '[ ' => '[', ' /' => '/',
							'/ ' => '/', '"' => "'");
	/** @var array A third-pass normalization table for XPath syntax */
	var $normalizationTable3 = array('position()=' => '',
							'/descendant-or-self::node()/' => "//", 'self::node()' => '.',
							'parent::node()' => '..');

	/**
	* Constructor - creates an empty DOMIT_NodeList to store matching nodes
	*/
	function DOMIT_XPath() {
		require_once(DOMIT_INCLUDE_PATH . 'xml_domit_nodemaps.php');
		$this->nodeList = new DOMIT_NodeList();
	} //DOMIT_XPath

	/**
	* Parses the supplied "path"-based pattern
	* @param Object The node from which the search is called
	* @param string The pattern
	* @return Object The NodeList containing matching nodes
	*/
	function &parsePattern(&$node, $pattern, $nodeIndex = 0) {
		$this->callingNode =& $node;
		$pattern = $this->normalize(trim($pattern));

		$this->splitPattern($pattern);

		$total = count($this->arPathSegments);

		//whole pattern level
		for ($i = 0; $i < $total; $i++) {
			$outerArray =& $this->arPathSegments[$i];
			$this->initSearch($outerArray);

			$outerTotal = count($outerArray);
			$isInitialMatchAttempt = true;

			//variable path segment level
			for ($j = 0; $j < $outerTotal; $j++) {
				$innerArray =& $outerArray[$j];
				$innerTotal = count($innerArray);

				if (!$isInitialMatchAttempt) {
					$this->searchType = DOMIT_XPATH_SEARCH_VARIABLE;
				}

				//pattern segment level
				for ($k = 0; $k < $innerTotal; $k++) {
					$currentPattern = $innerArray[$k];

					if (($k == 0) && ($currentPattern == null)) {
						if ($innerTotal == 1) {
							$isInitialMatchAttempt = false;
						}
						//else just skip current step and don't alter searchType
					}
					else {
						if (!$isInitialMatchAttempt && ($k > 0)) {
							$this->searchType = DOMIT_XPATH_SEARCH_RELATIVE;
						}

						$this->currentSegment = $currentPattern;
						$this->processPatternSegment();
						$isInitialMatchAttempt = false;
					}
				}
			}
		}

		if ($nodeIndex > 0) {
			if ($nodeIndex <= count($this->globalNodeContainer)) {
				return $this->globalNodeContainer[($nodeIndex - 1)];
			}
			else {
				$null = null;
				return $null;
			}
		}

		if (count($this->globalNodeContainer) != 0) {
			foreach ($this->globalNodeContainer as $key =>$value) {
				$currNode =& $this->globalNodeContainer[$key];
				$this->nodeList->appendNode($currNode);
			}
		}

		return $this->nodeList;
	} //parsePattern

	/**
	* Generates a new globalNodeContainer of matches
	*/
	function processPatternSegment() {
		$total = strlen($this->currentSegment);
		$this->charContainer = '';
		$this->localNodeContainer = array();

		for ($i = 0; $i < $total; $i++) {
			$this->currChar = $this->currentSegment{$i};

			switch ($this->currChar) {
				case '@':
					$this->selectAttribute(substr($this->currentSegment, ($this->currChar + 1)));
					$this->updateNodeContainers();
					return;
					//break;

				case '*':
					if ($i == ($total - 1)) {
						$this->selectNamedChild('*');
					}
					else {
						$this->charContainer .= $this->currChar;
					}
					break;

				case '.':
					$this->charContainer .= $this->currChar;

					if ($i == ($total - 1)) {
						if ($this->charContainer == '..') {
							$this->selectParent();
						}
						else {
							return;
						}
					}
					break;

				case ')':
					$this->charContainer .= $this->currChar;
					$this->selectNodesByFunction();
					break;

				case '[':
					$this->parsePredicate($this->charContainer,
									substr($this->currentSegment, ($i + 1)));
					return;
					//break;

				default:
					$this->charContainer .= $this->currChar;

			}
		}

		if ($this->charContainer != '') {
			$this->selectNamedChild($this->charContainer);
		}

		$this->updateNodeContainers();
	} //processPatternSegment

	/**
	* Replaces the global node container with the local node container
	*/
	function updateNodeContainers() {
		$this->globalNodeContainer =& $this->localNodeContainer;
		unset($this->localNodeContainer);
	} //updateNodeContainers


	/**
	* Parses a predicate expression [...]
	* @param string The pattern segment containing the node expression
	* @param string The pattern segment containing the predicate expression
	*/
	function parsePredicate($nodeName, $patternSegment) {
		$arPredicates =& explode('][', $patternSegment);

		$total = count($arPredicates);

		$lastIndex = $total - 1;
		$arPredicates[$lastIndex] = substr($arPredicates[$lastIndex],
										0, (strlen($arPredicates[$lastIndex]) - 1));

		for ($i = 0; $i < $total; $i++) {
			$isRecursive = ($this->searchType == DOMIT_XPATH_SEARCH_VARIABLE) ? true : false;
			$currPredicate = $arPredicates[$i];

			if (is_numeric($currPredicate)) {
				if ($i == 0) {
					$this->filterByIndex($nodeName, intval($currPredicate), $isRecursive);
				}
				else {
					$this->refilterByIndex(intval($currPredicate));
				}
			}
			else {
				if ($i == 0) {
					$this->selectNamedChild($nodeName);
					$this->updateNodeContainers();
				}

				$phpExpression = $this->predicateToPHP($currPredicate);
				$this->filterByPHPExpression($phpExpression);
			}

			$this->updateNodeContainers();
		}

		$this->charContainer = '';
	} //parsePredicate

	/**
	* Converts the predicate into PHP evaluable code
	* @param string The predicate
	* @return string The converted PHP expression
	*/
	function predicateToPHP($predicate) {
		$phpExpression = $predicate;
		$currChar = '';
		$charContainer = '';
		$totalChars = strlen($predicate);

		for ($i = 0; $i < $totalChars; $i++) {
			$currChar = substr($predicate, $i, 1);

			switch ($currChar) {
				case '(':
				case ')':
				case ' ':
					if ($charContainer != '') {
						$convertedPredicate = $this->expressionToPHP($charContainer);
						$phpExpression = str_replace($charContainer, $convertedPredicate, $phpExpression);
						$charContainer = '';
					}
					break;

				default:
					$charContainer .= $currChar;

			}
		}

		if ($charContainer != '') {
			$convertedPredicate = $this->expressionToPHP($charContainer);
			$phpExpression = str_replace($charContainer, $convertedPredicate, $phpExpression);
		}

		return $phpExpression;
	} //predicateToPHP


	/**
	* Converts the predicate expression into a PHP expression
	* @param string The predicate expression
	* @return string The converted PHP expression
	*/
	function expressionToPHP($expression) {
		if ($expression == 'and') {
			$expression = '&&';
		}
		else if ($expression == 'or') {
			$expression = '||';
		}
		else if ($expression == 'not') {
			$expression = '!';
		}
		else {
			$expression = trim($expression);

			if (strpos($expression, '@') !== false) {
				if (strpos($expression, '>=') !== false) {
					$expression = str_replace('@', ('floatval($' . "contextNode->getAttribute('"), $expression);
					$expression = str_replace('>=', "')) >= floatval(", $expression);
					if (!is_numeric($expression)) $expression = str_replace('floatval', '', $expression);
					$expression .= ')';
				}
				else if (strpos($expression, '<=') !== false) {
					$expression = str_replace('@', ('floatval($' . "contextNode->getAttribute('"), $expression);
					$expression = str_replace('<=', "')) <= floatval(", $expression);
					if (!is_numeric($expression)) $expression = str_replace('floatval', '', $expression);
					$expression .= ')';
				}
				else if (strpos($expression, '!=') !== false) {
					$expression = str_replace('@', ('$' . "contextNode->getAttribute('"), $expression);
					$expression = str_replace('!=', "') != ", $expression);
				}
				else if (strpos($expression, '=') !== false) {
					$expression = str_replace('@', ('$' . "contextNode->getAttribute('"), $expression);
					$expression = str_replace('=', "') == ", $expression);
				}
				else if (strpos($expression, '>') !== false) {
					$expression = str_replace('>', "')) > floatval(", $expression); //reverse so > doesn't get replaced
					$expression = str_replace('@', ('floatval($' . "contextNode->getAttribute('"), $expression);
					if (!is_numeric($expression)) $expression = str_replace('floatval', '', $expression);
					$expression .= ')';
				}
				else if (strpos($expression, '<') !== false) {
					$expression = str_replace('@', ('floatval($' . "contextNode->getAttribute('"), $expression);
					$expression = str_replace('<', "')) < floatval(", $expression);
					if (!is_numeric($expression)) $expression = str_replace('floatval', '', $expression);
					$expression .= ')';
				}
				else {
					$expression = str_replace('@', ('$' . "contextNode->hasAttribute('"), $expression);
					$expression.= "')";
				}
			}
			else {
				if (strpos($expression, '>=') !== false) {
					$signPos = strpos($expression, '>=');
					$elementName = trim(substr($expression, 0, $signPos));
					$elementValue = trim(substr($expression, ($signPos + 2)));

					$expression = '$' . "this->hasNamedChildElementGreaterThanOrEqualToValue(" .
									'$' . "contextNode, '" . $elementName . "', " .
									$elementValue . ')';
				}
				else if (strpos($expression, '<=') !== false) {
					$signPos = strpos($expression, '>=');
					$elementName = trim(substr($expression, 0, $signPos));
					$elementValue = trim(substr($expression, ($signPos + 2)));

					$expression = '$' . "this->hasNamedChildElementLessThanOrEqualToValue(" .
									'$' . "contextNode, '" . $elementName . "', " .
									$elementValue . ')';
				}
				else if (strpos($expression, '!=') !== false) {
					$signPos = strpos($expression, '>=');
					$elementName = trim(substr($expression, 0, $signPos));
					$elementValue = trim(substr($expression, ($signPos + 2)));

					$expression = '$' . "this->hasNamedChildElementNotEqualToValue(" .
									'$' . "contextNode, '" . $elementName . "', " .
									$elementValue . ')';
				}
				else if (strpos($expression, '=') !== false) {
					$signPos = strpos($expression, '=');
					$elementName = trim(substr($expression, 0, $signPos));
					$elementValue = trim(substr($expression, ($signPos + 1)));

					$expression = '$' . "this->hasNamedChildElementEqualToValue(" .
									'$' . "contextNode, '" . $elementName . "', " .
									$elementValue . ')';

				}
				else if (strpos($expression, '>') !== false) {
					$signPos = strpos($expression, '=');
					$elementName = trim(substr($expression, 0, $signPos));
					$elementValue = trim(substr($expression, ($signPos + 1)));

					$expression = '$' . "this->hasNamedChildElementGreaterThanValue(" .
									'$' . "contextNode, '" . $elementName . "', " .
									$elementValue . ')';
				}
				else if (strpos($expression, '<') !== false) {
					$signPos = strpos($expression, '=');
					$elementName = trim(substr($expression, 0, $signPos));
					$elementValue = trim(substr($expression, ($signPos + 1)));

					$expression = '$' . "this->hasNamedChildElementLessThanValue(" .
									'$' . "contextNode, '" . $elementName . "', " .
									$elementValue . ')';
				}
				else {
					$expression = '$' . "this->hasNamedChildElement(" .
									'$' . "contextNode, '" . $expression . "')";
				}
			}
		}

		return $expression;
	} //expressionToPHP

	/**
	* Selects nodes that match the predicate expression
	* @param string The predicate expression, formatted as a PHP expression
	*/
	function filterByPHPExpression($expression) {
		if (count($this->globalNodeContainer) != 0) {
			foreach ($this->globalNodeContainer as $key =>$value) {
				$contextNode =& $this->globalNodeContainer[$key];

				if ($contextNode->nodeType == DOMIT_ELEMENT_NODE) {
					$evaluatedExpression = 'if (' . $expression . ") $" .
									'this->localNodeContainer[] =& $' . 'contextNode;';
					eval($evaluatedExpression);
				}
			}
		}
	} //filterByPHPExpression

	/**
	* Selects nodes with child elements that match the specified name
	* @param object The parent node of the child elements to match
	* @param string The tag name to match on
	* @return boolean True if a matching child element exists
	*/
	function hasNamedChildElement(&$parentNode, $nodeName) {
		$total = $parentNode->childCount;

		for ($i = 0; $i < $total; $i++) {
			$currNode =& $parentNode->childNodes[$i];

			if (($currNode->nodeType == DOMIT_ELEMENT_NODE) && ($currNode->nodeName == $nodeName)) {
				return true;
			}
		}

		return false;
	} //hasNamedChildElement

	/**
	* Selects nodes with child elements that match the specified name and text value
	* @param object The parent node of the child elements to match
	* @param string The tag name to match on
	* @param string The text string to match on
	* @return boolean True if a matching child element exists
	*/
	function hasNamedChildElementEqualToValue(&$parentNode, $nodeName, $nodeValue) {
		$total = $parentNode->childCount;

		for ($i = 0; $i < $total; $i++) {
			$currNode =& $parentNode->childNodes[$i];

			if (($currNode->nodeType == DOMIT_ELEMENT_NODE) &&
				($currNode->nodeName == $nodeName) && ($currNode->getText() == $nodeValue)) {
				return true;
			}
		}

		return false;
	} //hasNamedChildElementEqualToValue

	/**
	* Selects nodes with child elements that are greater than or equal to the specified name and value
	* @param object The parent node of the child elements to match
	* @param string The tag name to match on
	* @param string The text string to match on
	* @return boolean True if a matching child element exists
	*/
	function hasNamedChildElementGreaterThanOrEqualToValue(&$parentNode, $nodeName, $nodeValue) {
		$isNumeric = false;

		if (is_numeric($nodeValue)) {
			$isNumeric = true;
			$nodeValue = floatval($nodeValue);
		}

		$total = $parentNode->childCount;

		for ($i = 0; $i < $total; $i++) {
			$currNode =& $parentNode->childNodes[$i];

			if (($currNode->nodeType == DOMIT_ELEMENT_NODE) && ($currNode->nodeName == $nodeName)) {
				if ($isNumeric) {$compareVal = floatval($currNode->getText());}
				else {$compareVal = $currNode->getText();}
				if ($compareVal >= $nodeValue) return true;
			}
		}

		return false;
	} //hasNamedChildElementGreaterThanOrEqualToValue


	/**
	* Selects nodes with child elements that are less than or equal to the specified name and value
	* @param object The parent node of the child elements to match
	* @param string The tag name to match on
	* @param string The text string to match on
	* @return boolean True if a matching child element exists
	*/
	function hasNamedChildElementLessThanOrEqualToValue(&$parentNode, $nodeName, $nodeValue) {
		$isNumeric = false;

		if (is_numeric($nodeValue)) {
			$isNumeric = true;
			$nodeValue = floatval($nodeValue);
		}

		$total = $parentNode->childCount;

		for ($i = 0; $i < $total; $i++) {
			$currNode =& $parentNode->childNodes[$i];

			if (($currNode->nodeType == DOMIT_ELEMENT_NODE) && ($currNode->nodeName == $nodeName)) {
				if ($isNumeric) {$compareVal = floatval($currNode->getText());}
				else {$compareVal = $currNode->getText();}
				if ($compareVal <= $nodeValue) return true;
			}
		}

		return false;
	} //hasNamedChildElementLessThanOrEqualToValue

	/**
	* Selects nodes with child elements that are not equal to the specified name and value
	* @param object The parent node of the child elements to match
	* @param string The tag name to match on
	* @param string The text string to match on
	* @return boolean True if a matching child element exists
	*/
	function hasNamedChildElementNotEqualToValue(&$parentNode, $nodeName, $nodeValue) {
		$isNumeric = false;

		if (is_numeric($nodeValue)) {
			$isNumeric = true;
			$nodeValue = floatval($nodeValue);
		}

		$total = $parentNode->childCount;

		for ($i = 0; $i < $total; $i++) {
			$currNode =& $parentNode->childNodes[$i];

			if (($currNode->nodeType == DOMIT_ELEMENT_NODE) && ($currNode->nodeName == $nodeName)) {
				if ($isNumeric) {$compareVal = floatval($currNode->getText());}
				else {$compareVal = $currNode->getText();}
				if ($compareVal != $nodeValue) return true;
			}
		}

		return false;
	} //hasNamedChildElementNotEqualToValue

		/**
	* Selects nodes with child elements that are greater than the specified name and value
	* @param object The parent node of the child elements to match
	* @param string The tag name to match on
	* @param string The text string to match on
	* @return boolean True if a matching child element exists
	*/
	function hasNamedChildElementGreaterThanValue(&$parentNode, $nodeName, $nodeValue) {
		$isNumeric = false;

		if (is_numeric($nodeValue)) {
			$isNumeric = true;
			$nodeValue = floatval($nodeValue);
		}

		$total = $parentNode->childCount;

		for ($i = 0; $i < $total; $i++) {
			$currNode =& $parentNode->childNodes[$i];

			if (($currNode->nodeType == DOMIT_ELEMENT_NODE) && ($currNode->nodeName == $nodeName)) {
				if ($isNumeric) {$compareVal = floatval($currNode->getText());}
				else {$compareVal = $currNode->getText();}
				if ($compareVal > $nodeValue) return true;
			}
		}

		return false;
	} //hasNamedChildElementGreaterThanValue


	/**
	* Selects nodes with child elements that are less than the specified name and value
	* @param object The parent node of the child elements to match
	* @param string The tag name to match on
	* @param string The text string to match on
	* @return boolean True if a matching child element exists
	*/
	function hasNamedChildElementLessThanValue(&$parentNode, $nodeName, $nodeValue) {
		$isNumeric = false;

		if (is_numeric($nodeValue)) {
			$isNumeric = true;
			$nodeValue = floatval($nodeValue);
		}

		$total = $parentNode->childCount;

		for ($i = 0; $i < $total; $i++) {
			$currNode =& $parentNode->childNodes[$i];

			if (($currNode->nodeType == DOMIT_ELEMENT_NODE) && ($currNode->nodeName == $nodeName)) {
				if ($isNumeric) {$compareVal = floatval($currNode->getText());}
				else {$compareVal = $currNode->getText();}
				if ($compareVal < $nodeValue) return true;
			}
		}

		return false;
	} //hasNamedChildElementLessThanValue

	/**
	* Selects named elements of the specified index
	* @param string The pattern segment containing the node expression
	* @param int The index (base 1) of the matching node
	* @param boolean True if the selection is to be performed recursively
	*/
	function refilterByIndex($index) {
		if ($index > 1) {
			if (count($this->globalNodeContainer) != 0) {
				$counter = 0;
				$lastParentID = null;

				foreach ($this->globalNodeContainer as $key =>$value) {
					$currNode =& $this->globalNodeContainer[$key];

					if (($lastParentID != null) && ($currNode->parentNode->uid != $lastParentID)) {
						$counter = 0;
					}

					$counter++;

					if (($counter == $index) && ($currNode->parentNode->uid == $lastParentID)) {
						$this->localNodeContainer[] =& $currNode;
					}

					$lastParentID = $currNode->parentNode->uid;
				}
			}
		}
		else {
			$this->localNodeContainer =& $this->globalNodeContainer;
		}
	} //refilterByIndex


	/**
	* Selects named elements of the specified index
	* @param string The pattern segment containing the node expression
	* @param int The index (base 1) of the matching node
	* @param boolean True if the selection is to be performed recursively
	*/
	function filterByIndex($nodeName, $index, $deep) {
		if (count($this->globalNodeContainer) != 0) {
			foreach ($this->globalNodeContainer as $key =>$value) {
				$currNode =& $this->globalNodeContainer[$key];
				$this->_filterByIndex($currNode, $nodeName, $index, $deep);
			}
		}
	} //filterByIndex

	/**
	* Selects named elements of the specified index
	* @param object The context node
	* @param string The pattern segment containing the node expression
	* @param int The index (base 1) of the matching node
	* @param boolean True if the selection is to be performed recursively
	*/
	function _filterByIndex(&$contextNode, $nodeName, $index, $deep) {
		if (($contextNode->nodeType == DOMIT_ELEMENT_NODE) ||
			($contextNode->nodeType == DOMIT_DOCUMENT_NODE)) {
			$total = $contextNode->childCount;
			$nodeCounter = 0;

			for ($i = 0; $i < $total; $i++) {
				$currChildNode =& $contextNode->childNodes[$i];

				if ($currChildNode->nodeName == $nodeName) {
					$nodeCounter++;

					if ($nodeCounter == $index) {
						$this->localNodeContainer[] =& $currChildNode;
					}
				}

				if ($deep) {
					$this->_filterByIndex($currChildNode, $nodeName, $index, $deep);
				}
			}
		}
	} //_filterByIndex

	/**
	* Selects named elements with the specified named child
	* @param string The pattern segment containing the node expression
	* @param string The tag name of the matching child
	* @param boolean True if the selection is to be performed recursively
	*/
	function filterByChildName($nodeName, $childName, $deep) {
		if (count($this->globalNodeContainer) != 0) {
			foreach ($this->globalNodeContainer as $key =>$value) {
				$currNode =& $this->globalNodeContainer[$key];
				$this->_filterByChildName($currNode, $nodeName, $childName, $deep);
			}
		}
	} //filterByChildName

	/**
	* Selects named elements with the specified named child
	* @param object The context node
	* @param string The pattern segment containing the node expression
	* @param string The tag name of the matching child
	* @param boolean True if the selection is to be performed recursively
	*/
	function _filterByChildName(&$contextNode, $nodeName, $childName, $deep) {
		if (($contextNode->nodeType == DOMIT_ELEMENT_NODE) ||
			($contextNode->nodeType == DOMIT_DOCUMENT_NODE)) {
			$total = $contextNode->childCount;

			for ($i = 0; $i < $total; $i++) {
				$currChildNode =& $contextNode->childNodes[$i];

				if (($currChildNode->nodeName == $nodeName) &&
					($currChildNode->nodeType == DOMIT_ELEMENT_NODE)) {
					$total2 = $currChildNode->childCount;

					for ($j = 0; $j < $total2; $j++) {
						$currChildChildNode =& $currChildNode->childNodes[$j];

						if ($currChildChildNode->nodeName == $childName) {
							$this->localNodeContainer[] =& $currChildNode;
						}
					}
				}

				if ($deep) {
					$this->_filterByChildName($currChildNode, $nodeName, $childName, $deep);
				}
			}
		}
	} //_filterByChildName

	/**
	* Selects named attributes of the current context nodes
	* @param string The attribute name, or * to match all attributes
	*/
	function selectAttribute($attrName) {
		if (count($this->globalNodeContainer) != 0) {
			foreach ($this->globalNodeContainer as $key =>$value) {
				$currNode =& $this->globalNodeContainer[$key];

				$isRecursive = ($this->searchType == DOMIT_XPATH_SEARCH_VARIABLE) ? true : false;
				$this->_selectAttribute($currNode, $attrName, $isRecursive);
			}
		}

		$this->charContainer = '';
	} //selectAttribute

	/**
	* Selects all attributes of the context nodes
	* @param object The context node
	* @param string The attribute name, or * to match all attributes
	* @param boolean True if the selection is to be performed recursively
	*/
	function _selectAttribute(&$contextNode, $attrName, $deep) {
		if (($contextNode->nodeType == DOMIT_ELEMENT_NODE) ||
				($contextNode->nodeType == DOMIT_DOCUMENT_NODE)) {
			$total = $contextNode->childCount;

			for ($i = 0; $i < $total; $i++) {
				$currNode =& $contextNode->childNodes[$i];

				if ($currNode->nodeType == DOMIT_ELEMENT_NODE) {
					if ($attrName == '*') {
						$total2 = $currNode->attributes->getLength();

						for ($j = 0; $j < $total2; $j++) {
							$this->localNodeContainer[] =& $currNode->attributes->item($j);
						}
					}
					else {
						if ($currNode->hasAttribute($attrName)) {
							$this->localNodeContainer[] =& $currNode->getAttributeNode($attrName);
						}
					}
				}

				if ($deep) {
					$this->_selectAttribute($currNode, $attrName, $deep);
				}
			}
		}
	} //_selectAttribute


	/**
	* Selects all child nodes of the current context nodes
	* @param string The element name
	*/
	function selectNamedChild($tagName) {
		if (count($this->globalNodeContainer) != 0) {
			foreach ($this->globalNodeContainer as $key =>$value) {
				$currNode =& $this->globalNodeContainer[$key];

				$isRecursive = ($this->searchType == DOMIT_XPATH_SEARCH_VARIABLE) ? true : false;
				$this->_selectNamedChild($currNode, $tagName, $isRecursive);
			}
		}

		$this->charContainer = '';
	} //selectNamedChild

	/**
	* Selects all child nodes of the context node
	* @param object The context node
	* @param string The element name
	* @param boolean True if the selection is to be performed recursively
	*/
	function _selectNamedChild(&$contextNode, $tagName, $deep = false) {
		if (($contextNode->nodeType == DOMIT_ELEMENT_NODE) ||
			($contextNode->nodeType == DOMIT_DOCUMENT_NODE)) {
			$total = $contextNode->childCount;

			for ($i = 0; $i < $total; $i++) {
				$currChildNode =& $contextNode->childNodes[$i];

				if (($currChildNode->nodeType == DOMIT_ELEMENT_NODE) ||
					($currChildNode->nodeType == DOMIT_DOCUMENT_NODE)) {
					if (($tagName == '*') || ($tagName == $currChildNode->nodeName)) {
						$this->localNodeContainer[] =& $currChildNode;
					}

					if ($deep) {
						$this->_selectNamedChild($currChildNode, $tagName, $deep);
					}
				}
			}
		}
	} //_selectNamedChild

	/**
	* Selects parent node of the current context nodes
	*/
	function selectParent() {
		if (count($this->globalNodeContainer) != 0) {
			foreach ($this->globalNodeContainer as $key =>$value) {
				$currNode =& $this->globalNodeContainer[$key];

				$isRecursive = ($this->searchType == DOMIT_XPATH_SEARCH_VARIABLE) ? true : false;
				$this->_selectParent($currNode, $isRecursive);
			}
		}

		$this->charContainer = '';
	} //selectParent

	/**
	* Selects parent node of the current context nodes
	* @param object The context node
	* @param boolean True if the selection is to be performed recursively
	*/
	function _selectParent(&$contextNode, $deep = false) {
		if ($contextNode->nodeType == DOMIT_ELEMENT_NODE) {
			if ($contextNode->parentNode != null) {
				$this->localNodeContainer[] =& $contextNode->parentNode;
			}
		}

		if ($deep) {
			if (($contextNode->nodeType == DOMIT_ELEMENT_NODE) ||
				($contextNode->nodeType == DOMIT_DOCUMENT_NODE)) {
				$total = $contextNode->childCount;

				for ($i = 0; $i < $total; $i++) {
					$currNode =& $contextNode->childNodes[$i];

					if ($currNode->nodeType == DOMIT_ELEMENT_NODE) {
						$this->_selectParent($contextNode, $deep);
					}
				}
			}
		}
	} //_selectParent

	/**
	* Selects any nodes of the current context nodes which match the given function
	*/
	function selectNodesByFunction() {
		$doProcess = false;
		$targetNodeType = -1;

		switch (strtolower(trim($this->charContainer))) {
			case 'last()':
				if (count($this->globalNodeContainer) != 0) {
					foreach ($this->globalNodeContainer as $key =>$value) {
						$currNode =& $this->globalNodeContainer[$key];

						if ($currNode->nodeType == DOMIT_ELEMENT_NODE) {
							if ($currNode->lastChild != null) {
								$this->localNodeContainer[] =& $currNode->lastChild;
							}
						}
					}
				}
				break;

			case 'text()':
				$doProcess = true;
				$targetNodeType = DOMIT_TEXT_NODE;
				break;

			case 'comment()':
				$doProcess = true;
				$targetNodeType = DOMIT_COMMENT_NODE;
				break;

			case 'processing-instruction()':
				$doProcess = true;
				$targetNodeType = DOMIT_PROCESSING_INSTRUCTION_NODE;
				break;
		}

		if ($doProcess) {
			if (count($this->globalNodeContainer) != 0) {
				foreach ($this->globalNodeContainer as $key =>$value) {
					$currNode =& $this->globalNodeContainer[$key];

					if ($currNode->nodeType == DOMIT_ELEMENT_NODE) {
						$total = $currNode->childCount;

						for ($j = 0; $j < $total; $j++) {
							if ($currNode->childNodes[$j]->nodeType == $targetNodeType) {
								$this->localNodeContainer[] =& $currNode->childNodes[$j];
							}
						}
					}
				}
			}
		}

		$this->charContainer = '';
	} //selectNodesByFunction

	/**
	* Splits the supplied pattern into searchable segments
	* @param string The pattern
	*/
	function splitPattern($pattern) {
		//split multiple patterns if they exist (e.g. pattern1 | pattern2 | pattern3)
		$this->arPathSegments =& explode(DOMIT_XPATH_SEPARATOR_OR, $pattern);

		//split each pattern by relative path dividers (i.e., '//')
		$total = count($this->arPathSegments);

		for ($i = 0; $i < $total; $i++) {
			$this->arPathSegments[$i] =& explode(DOMIT_XPATH_SEPARATOR_RELATIVE, trim($this->arPathSegments[$i]));

			$currArray =& $this->arPathSegments[$i];
			$total2 = count($currArray);

			for ($j = 0; $j < $total2; $j++) {
				$currArray[$j] =& explode(DOMIT_XPATH_SEPARATOR_ABSOLUTE, $currArray[$j]);
			}
		}
	} //splitPattern

	/**
	* Converts long XPath syntax into abbreviated XPath syntax
	* @param string The pattern
	* @return string The normalized pattern
	*/
	function normalize($pattern) {
		$pattern = strtr($pattern, $this->normalizationTable);

		while (strpos($pattern, '  ') !== false) {
			$pattern = str_replace('  ', ' ', $pattern);
		}

		$pattern = strtr($pattern, $this->normalizationTable2);
		$pattern = strtr($pattern, $this->normalizationTable3);

		return $pattern;
	} //normalize

	/**
	* Initializes the contextNode and searchType
	* @param array The current array of path segments
	* @return int The index of the first array item to begin the search at
	*/
	function initSearch(&$currArPathSegments) {
		$this->globalNodeContainer = array();

		if (is_null($currArPathSegments[0])) {
			if (count($currArPathSegments) == 1) {
				//variable path
				$this->searchType = DOMIT_XPATH_SEARCH_VARIABLE;
				$this->globalNodeContainer[] =& $this->callingNode->ownerDocument;
			}
			else {
				//absolute path
				$this->searchType = DOMIT_XPATH_SEARCH_ABSOLUTE;
				$this->globalNodeContainer[] =& $this->callingNode->ownerDocument;
			}
		}
		else {
			//relative path
			$this->searchType = DOMIT_XPATH_SEARCH_RELATIVE;

			if ($this->callingNode->uid != $this->callingNode->ownerDocument->uid) {
				$this->globalNodeContainer[] =& $this->callingNode;
			}
			else {
				$this->globalNodeContainer[] =& $this->callingNode->ownerDocument;
			}
		}
	} //initSearch
} //DOMIT_XPath
?>