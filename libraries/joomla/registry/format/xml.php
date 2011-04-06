<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Registry
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * XML format handler for JRegistry.
 *
 * @package		Joomla.Platform
 * @subpackage	Registry
 * @since		11.1
 */
class JRegistryFormatXml extends JRegistryFormat
{
	/**
	 * Converts an object into an XML formatted string.
	 *	-	If more than two levels of nested groups are necessary, since INI is not
	 *		useful, XML or another format should be used.
	 *
	 * @param	object	Data source object.
	 * @param	array	Options used by the formatter.
	 * @return	string	XML formatted string.
	 * @since	11.1
	 */
	public function objectToString($object, $options = array())
	{
		// Initialise variables.
		$rootName = (isset($options['name'])) ? $options['name'] : 'registry';
		$nodeName = (isset($options['nodeName'])) ? $options['nodeName'] : 'node';

		// Create the root node.
		$root = simplexml_load_string('<'.$rootName.' />');

		// Iterate over the object members.
		foreach ((array) $object as $k => $v)
		{
			if (is_scalar($v)) {
				$n = $root->addChild($nodeName, $v);
				$n->addAttribute('name', $k);
				$n->addAttribute('type', gettype($v));
			} else {
				$n = $root->addChild($nodeName);
				$n->addAttribute('name', $k);
				$n->addAttribute('type', gettype($v));

				$this->_getXmlChildren($n, $v, $nodeName);
			}
		}

		return $root->asXML();
	}

	/**
	 * Parse a XML formatted string and convert it into an object.
	 *
	 * @param	string	XML formatted string to convert.
	 * @param	array	Options used by the formatter.
	 * @return	object	Data object.
	 * @since	11.1
	 */
	public function stringToObject($data, $options = array())
	{
		// Initialize variables.
		$obj = new stdClass;

		// Parse the XML string.
		$xml = simplexml_load_string($data);

		foreach ($xml->children() as $node) {
			$obj->$node['name'] = $this->_getValueFromNode($node);
		}

		return $obj;
	}

	/**
	 * Method to get a PHP native value for a SimpleXMLElement object. -- called recursively
	 *
	 * @param	object	SimpleXMLElement object for which to get the native value.
	 * @return	mixed	Native value of the SimpleXMLElement object.
	 * @since	2.0
	 */
	protected function _getValueFromNode($node)
	{
		switch ($node['type']) {
			case 'integer':
				$value = (string) $node;
				return (int) $value;
				break;
			case 'string':
				return (string) $node;
				break;
			case 'boolean':
				$value = (string) $node;
				return (bool) $value;
				break;
			case 'double':
				$value = (string) $node;
				return (float) $value;
				break;
			case 'array':
				$value = array();
				foreach ($node->children() as $child) {
					$value[(string) $child['name']] = $this->_getValueFromNode($child);
				}
				break;
			default:
				$value = new stdClass;
				foreach ($node->children() as $child) {
					$value->$child['name'] = $this->_getValueFromNode($child);
				}
				break;
		}

		return $value;
	}

	/**
	 * Method to build a level of the XML string -- called recursively
	 *
	 * @param	object	SimpleXMLElement object to attach children.
	 * @param	object	Object that represents a node of the xml document.
	 * @param	string	The name to use for node elements.
	 * @return	void
	 * @since	2.0
	 */
	protected function _getXmlChildren(& $node, $var, $nodeName)
	{
		// Iterate over the object members.
		foreach ((array) $var as $k => $v)
		{
			if (is_scalar($v)) {
				$n = $node->addChild($nodeName, $v);
				$n->addAttribute('name', $k);
				$n->addAttribute('type', gettype($v));
			} else {
				$n = $node->addChild($nodeName);
				$n->addAttribute('name', $k);
				$n->addAttribute('type', gettype($v));

				$this->_getXmlChildren($n, $v, $nodeName);
			}
		}
	}
}
