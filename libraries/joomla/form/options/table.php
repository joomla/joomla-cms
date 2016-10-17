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
		// Initialize some field attributes.
		$text = $option['text_field'] ? (string) $option['text_field'] : null;
		$value = $option['value_field'] ? (string) $option['value_field'] : null;
		$disable = $option['disable_field'] ? (string) $option['disable_field'] : null;
		$negate = $option['negate_disable_field'] ? (string) $option['negate_disable_field'] : false;
		$table = $option['table'] ? (string) $option['table'] : null;
		$translate = $option['translate'] ? (string) $option['translate'] : false;

		// These fields are required. If any are missing, return empty array.
		if (!isset($text, $value, $table))
		{
			return array();
		}

		// Get the database object.
		$db = JFactory::getDBO();

		// Build the query.
		$query = $db->getQuery(true)
			->select(array($db->qn($text, 'text'), $db->qn($value, 'value')))
			->from($db->qn($table));

		if (!is_null($disable))
		{
			$negate = $negate && !in_array(strtolower($negate), array('', '0', 'false'));
			$query->select(($negate ? 'NOT ' : '') . $db->qn($disable, 'disable'));
		}

		// Set the query and get the result list.
		$options = $db->setQuery($query)->loadObjectList();

		return is_array($options) ? $options : array();
	}
}
