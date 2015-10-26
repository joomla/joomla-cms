<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

require_once('xml_to_array_exception.php');
require_once('xml_to_array_node.php');

class XmlToArray {

	private $_tagStack = array();

	private $_CurrentXmlToArrayNode = null;

	private $_Object = null;

	private $_maxDepth = 0;

	private static $_htmlEntityExceptions = array(
		'&euro;' => '€',
	);

	public function __construct($input, $maxDepth = 20) {
		if (!is_string($input)) throw new XmlToArrayException('No valid input.');
		$this->_maxDepth = $maxDepth;

		$XMLParser = xml_parser_create();
		xml_parser_set_option($XMLParser, XML_OPTION_SKIP_WHITE, false);
		xml_parser_set_option($XMLParser, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option($XMLParser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		xml_set_character_data_handler($XMLParser, array($this, '_contents'));
		xml_set_default_handler($XMLParser, array($this, '_default'));
		xml_set_element_handler($XMLParser, array($this, '_start'), array($this, '_end'));
		xml_set_external_entity_ref_handler($XMLParser, array($this, '_externalEntity'));
		xml_set_notation_decl_handler($XMLParser, array($this, '_notationDecl'));
		xml_set_processing_instruction_handler($XMLParser, array($this, '_processingInstruction'));
		xml_set_unparsed_entity_decl_handler($XMLParser, array($this, '_unparsedEntityDecl'));

		if (!xml_parse($XMLParser, $input, true)) {
			$errorCode = xml_get_error_code($XMLParser);
			$message = sprintf('%s. line: %d, char: %d'.($this->_tagStack ? ', tag: %s' : ''),
				xml_error_string($errorCode),
				xml_get_current_line_number($XMLParser),
				xml_get_current_column_number($XMLParser)+1,
				implode('->', $this->_tagStack));
			xml_parser_free($XMLParser);
			throw new XmlToArrayException($message, $errorCode);
		}

		xml_parser_free($XMLParser);
	}


	public function log($msg, $type = 2) {
		if (class_exists('Object')) {
			!($this->_Object instanceof Object) && $this->_Object = new Object();
			return $this->_Object->log($msg, $type);
		}

		return false;
	}


	public function toArray($simpleStructure = false) {
		return $this->_CurrentXmlToArrayNode->render($simpleStructure);
	}


	public static function render($input, $simpleStructure = false, $maxDepth = 20) {
		$Instance = new XmlToArray($input, $maxDepth);
		return $Instance->toArray($simpleStructure);
	}


	private function _contents($parser, $data) {
		if (trim($data) !== '' && $this->_CurrentXmlToArrayNode instanceof XmlToArrayNode) $this->_CurrentXmlToArrayNode->setData($data);
	}


	private function _default($parser, $data) {
		$data = trim($data);

		if (in_array($data, get_html_translation_table(HTML_ENTITIES))) {
			$this->_CurrentXmlToArrayNode instanceof XmlToArrayNode && $this->_CurrentXmlToArrayNode->setData(html_entity_decode($data));
		} elseif ($data && isset(self::$_htmlEntityExceptions[$data])) {
			$this->_CurrentXmlToArrayNode instanceof XmlToArrayNode && $this->_CurrentXmlToArrayNode->setData(self::$_htmlEntityExceptions[$data]);
		} elseif ($data && is_string($data) && strpos($data, '<!--') === false && strpos($data, '<?xml') === false) {
			if (getenv('sofortDebug') == 'true') {
				trigger_error('Default data handler used. The data passed was: '.$data, E_USER_WARNING);
				throw new XmlToArrayException('Unknown error occurred');
			}
		}
	}


	private function _end($parser, $name) {
		array_pop($this->_tagStack);

		if ($this->_CurrentXmlToArrayNode instanceof XmlToArrayNode) {
			$this->_CurrentXmlToArrayNode->setClosed();

			$breaker = 0;

			if ($this->_CurrentXmlToArrayNode->getName() != $name) {
				do {
					$this->_CurrentXmlToArrayNode = $this->_CurrentXmlToArrayNode->getParentXmlToArrayNode();
					$this->_CurrentXmlToArrayNode->setClosed();

					if ($breaker > 100) {
						trigger_error('Had to break out from endless loop.', E_USER_WARNING);
						break;
					}

					++$breaker;
				} while($this->_CurrentXmlToArrayNode->getName() != $name);
			} elseif ($this->_CurrentXmlToArrayNode->hasParentXmlToArrayNode()) {
				$this->_CurrentXmlToArrayNode = $this->_CurrentXmlToArrayNode->getParentXmlToArrayNode();
			}
		}
	}


	private function _externalEntity($parser , $openEntityNames , $base , $systemId , $publicId) {
	}


	private function _notationDecl($parser , $notationName , $base , $systemId , $publicId) {
	}


	private function _processingInstruction($parser, $target, $data) {
	}


	private function _unparsedEntityDecl($parser , $entityName , $base , $systemId , $publicd , $notationName) {
	}


	private function _start($parser, $name, $attributes) {
		$this->_tagStack[] = $name;

		if ($this->_maxDepth && count($this->_tagStack) > $this->_maxDepth) {
			throw new XmlToArrayException('Parse Error: max depth exceeded.', '7005');
		}

		$XmlToArrayNode = new XmlToArrayNode($name, $attributes);

		if ($this->_CurrentXmlToArrayNode instanceof XmlToArrayNode && $this->_CurrentXmlToArrayNode->isOpen()) {
			$this->_CurrentXmlToArrayNode->addChild($XmlToArrayNode);
			$XmlToArrayNode->setParentXmlToArrayNode($this->_CurrentXmlToArrayNode);
		}

		$this->_CurrentXmlToArrayNode = $XmlToArrayNode;
	}
}

?>
