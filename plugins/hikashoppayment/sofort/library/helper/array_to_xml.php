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
require_once('array_to_xml_exception.php');



class ArrayToXml {
	private $_maxDepth = 4;

	private $_parsedData = null;


	public function __construct(array $input, $maxDepth = 10, $trim = true) {
		if ($maxDepth > 50) {
			throw new ArrayToXmlException('Max depth too high.');
		}

		$this->_maxDepth = $maxDepth;

		if (count($input) == 1) {
			$tagName = key($input);
			$SofortTag = new SofortTag($tagName, $this->_extractAttributesSection($input[$tagName]), $this->_extractDataSection($input[$tagName], $trim));
			$this->_render($input[$tagName], $SofortTag, 1, $trim);
			$this->_parsedData = $SofortTag->render();
		} elseif(!$input) {
			$this->_parsedData = '';
		} else {
			throw new ArrayToXmlException('No valid input.');
		}
	}


	public function toXml($version = '1.0', $encoding = 'UTF-8') {
		return !$version && !$encoding
			? $this->_parsedData
			: "<?xml version=\"{$version}\" encoding=\"{$encoding}\" ?>\n{$this->_parsedData}";
	}


	public static function render(array $input, array $options = array()) {
		$options = array_merge(array(
				'version' => '1.0',
				'encoding' => 'UTF-8',
				'trim' => true,
				'depth' => 10,
			),
			$options
		);

		$Instance = new ArrayToXml($input, $options['depth'], $options['trim']);
		return $Instance->toXml($options['version'], $options['encoding']);
	}


	private function _checkDepth($currentDepth) {
		if ($this->_maxDepth && $currentDepth > $this->_maxDepth) {
			throw new ArrayToXmlException("Max depth ({$this->_maxDepth}) exceeded");
		}
	}


	private function _createNode($name, array $attributes, array $children) {
		return new SofortTag($name, $attributes, $children);
	}


	private function _createTextNode($text, $trim) {
		return new SofortText($text, true, $trim);
	}


	private function _extractAttributesSection(&$node) {
		$attributes = array();

		if (is_array($node) && isset($node['@attributes']) && $node['@attributes']) {
			$attributes = is_array($node['@attributes']) ? $node['@attributes'] : array($node['@attributes']);
			unset($node['@attributes']);
		} elseif (is_array($node) && isset($node['@attributes'])) {
			unset($node['@attributes']);
		}

		return $attributes;
	}


	private function _extractDataSection(&$node, $trim) {
		$children = array();

		if (is_array($node) && isset($node['@data']) && $node['@data']) {
			$children = array($this->_createTextNode($node['@data'], $trim));
			unset($node['@data']);
		} elseif (is_array($node) && isset($node['@data'])) {
			unset($node['@data']);
		}

		return $children;
	}


	private function _render($input, SofortTag $ParentTag, $currentDepth, $trim) {
		$this->_checkDepth($currentDepth);

		if (is_array($input)) {
			foreach ($input as $tagName => $data) {
				$attributes = $this->_extractAttributesSection($data);

				if (is_array($data) && is_int(key($data))) {
					$this->_checkDepth($currentDepth+1);

					foreach ($data as $line) {
						if (is_array($line)) {
							$Tag = $this->_createNode($tagName, $this->_extractAttributesSection($line), $this->_extractDataSection($line, $trim));
							$ParentTag->children[] = $Tag;
							$this->_render($line, $Tag, $currentDepth+1, $trim);
						} else {
							$ParentTag->children[] = $this->_createNode($tagName, $attributes, array($this->_createTextNode($line, $trim)));
						}
					}
				} elseif (is_array($data)) {
					$Tag = $this->_createNode($tagName, $attributes, $this->_extractDataSection($data, $trim));
					$ParentTag->children[] = $Tag;
					$this->_render($data, $Tag, $currentDepth+1, $trim);
				} elseif (is_numeric($tagName)) {
					$ParentTag->children[] = $this->_createTextNode($data, $trim);
				} else {
					$ParentTag->children[] = $this->_createNode($tagName, $attributes, array($this->_createTextNode($data, $trim)));
				}
			}

			return;
		}

		$ParentTag->children[] = $this->_createTextNode($input, $trim);
	}
}

?>
