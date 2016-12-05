<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * SQL Option class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
abstract class JFormOptionSQL
{
	/**
	 * Method to get a list of options.
	 *
	 * @param   SimpleXMLElement  $option     <option/> element
	 * @param   string            $fieldname  The name of the field containing this option.
	 *
	 * @return  array  A list of objects representing HTML option elements (such as created by JHtmlSelect::option).
	 *
	 * @since   11.1
	 */
	public static function getOptions(SimpleXMLElement $option, $fieldname = '')
	{
		// Initialize variables.
		$options = array();

		// Initialize some field attributes.
		$key = $option['key_field'] ? (string) $option['key_field'] : 'value';
		$value = $option['value_field'] ? (string) $option['value_field'] : (string) $option['name'];
		$disable = $option['disable_field'] ? (string) $option['disable_field'] : null;
		$translate = $option['translate'] ? (string) $option['translate'] : false;
		$query = (string) $option['query'];

		// Get the database object.
		$db = JFactory::getDBO();
		$driver = $db->name;

		// Check for a query specific to the driver in use.
		foreach ($option->children() as $child)
		{
			if ($child->getName() == 'query'
				&& isset($child['driver'], $child['query'])
				&& (string) $child['driver'] == $driver)
			{
				$query = (string) $child['query'];
				break;
			}
		}

		// Set the query and get the result list.
		$items = $db->setQuery($query)->loadObjectList();

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = (object) array(
					'value' => $item->$key,
					'text' => $translate ? JText::_($item->$value) : $item->$value,
					'disable' => ($disable ? (bool) $item->$disable : false)
				);
			}
		}

		return $options;
	}
}
