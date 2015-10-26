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

class XmlToArrayNode {

	private $_attributes = array();
	private $_children = array();
	private $_data = '';
	private $_name = '';
	private $_open = true;
	private $_ParentXmlToArrayNode = null;


	public function __construct($name, $attributes) {
		$this->_name = $name;
		$this->_attributes = $attributes;
	}


	public function addChild(XmlToArrayNode $XmlToArrayNode) {
		$this->_children[] = $XmlToArrayNode;
	}


	public function getData() {
		return $this->_data;
	}


	public function getName() {
		return $this->_name;
	}


	public function getParentXmlToArrayNode() {
		return $this->_ParentXmlToArrayNode;
	}


	public function hasChildren() {
		return count($this->_children);
	}


	public function hasParentXmlToArrayNode() {
		return $this->_ParentXmlToArrayNode instanceof XmlToArrayNode;
	}


	public function isOpen() {
		return $this->_open;
	}


	public function render($simpleStructure) {
		$array = array();
		$multiples = array();

		foreach ($this->_children as $Child) {
			$multiples[$Child->getName()] = isset($multiples[$Child->getName()]) ? $multiples[$Child->getName()] + 1 : 0;
		}

		foreach ($this->_children as $Child) {
			if ($multiples[$Child->getName()]) {
				if ($simpleStructure && !$Child->hasChildren()) {
					$array[$Child->getName()][] = $Child->getData();
				} else {
					$array[$Child->getName()][] = $Child->render($simpleStructure);
				}
			} elseif ($simpleStructure && !$Child->hasChildren()) {
				$array[$Child->getName()] = $Child->getData();
			} else {
				$array[$Child->getName()] = $Child->render($simpleStructure);
			}
		}

		if (!$simpleStructure) {
			$array['@data'] = $this->_data;
			$array['@attributes'] = $this->_attributes;
		}

		return $this->_ParentXmlToArrayNode instanceof XmlToArrayNode
			? $array
			: array($this->_name => $simpleStructure && !$this->hasChildren() ? $this->getData() : $array);
	}


	public function setClosed() {
		$this->_open = false;
	}


	public function setData($data) {
		$this->_data .= $data;
	}


	public function setParentXmlToArrayNode(XmlToArrayNode $XmlToArrayNode) {
		$this->_ParentXmlToArrayNode = $XmlToArrayNode;
	}
}
?>
