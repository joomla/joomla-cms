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
 * Table Option class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
abstract class JFormOptionTable
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
		$key = $option['key_field'] ? (string) $option['key_field'] : null;
		$value = $option['value_field'] ? (string) $option['value_field'] : null;
		$disable = $option['disable_field'] ? (string) $option['disable_field'] : null;
		$table = $option['table'] ? (string) $option['table'] : null;
		$translate = $option['translate'] ? (string) $option['translate'] : false;

		// These fields are required. If any are missing, return empty array.
		if (!isset($key, $value, $table))
		{
			return $options;
		}

		// Get the database object.
		$db = JFactory::getDBO();

		// Build the query.
		$query = $db->getQuery(true);
		$query->select(array($db->qn($key), $db->qn($value)))->from($db->qn($table));

		if ($disable)
		{
			$query->select($db->qn($disable));
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
