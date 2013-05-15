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
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldList extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'List';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';
		$attr .= $this->required ? ' required="required" aria-required="true"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// Get the field options.
		$options = (array) $this->getOptions();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true')
		{
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
		}
		// Create a regular list.
		else
		{
			$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		}

		return implode($html);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();

		foreach ($this->element->children() as $option)
		{
			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}

			switch ((string) $option['type'])
			{
				case 'sql':
					$result = $this->parseOptionSQL($option);
					if (!empty($result))
					{
						$options += $result;
					}
					break;
				case 'table':
					$result = $this->parseOptionTable($option);
					if (!empty($result))
					{
						$options += $result;
					}
					break;
				case 'standard':
				default:
					$options[] = $this->parseOptionStandard($option);
					break;
			}
		}

		reset($options);

		return $options;
	}

	/**
	 * Converts a standard 'option' xml element from a Joomla config file to an html option.
	 *
	 * @param   SimpleXMLElement  $option  An option element from a Joomla config file.
	 *
	 * @return  string                     HTML option.
	 */
	protected function parseOptionStandard(SimpleXMLElement $option)
	{
		// Create a new option object based on the <option /> element.
		$tmp = JHtml::_(
			'select.option', (string) $option['value'],
			JText::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text',
			((string) $option['disabled'] == 'true')
		);

		// Set some option attributes.
		$tmp->class = (string) $option['class'];

		// Set some JavaScript option attributes.
		$tmp->onclick = (string) $option['onclick'];

		return $tmp;
	}

	/**
	 * Reads an 'sql' type 'option' xml element, executes the query, and converts the results to html options.
	 * This tag should have a 'query' attribute but may also have 'query' tags as children which can be used to specify queries based on db type.
	 *
	 * @param   SimpleXMLElement  $option  An 'option' element of the 'sql' type.
	 *
	 * @return  array                      A list of html options.
	 */
	protected function parseOptionSQL(SimpleXMLElement $option)
	{
		// Initialize variables.
		$options = array();

		// Initialize some field attributes.
		$key = $option['key_field'] ? (string) $option['key_field'] : 'value';
		$value = $option['value_field'] ? (string) $option['value_field'] : (string) $option['name'];
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
				$options[] = JHtml::_('select.option', $item->$key, $translate ? JText::_($item->$value) : $item->$value);
			}
		}

		return $options;
	}

	/**
	 * Reads a 'table' type 'option' xml element, executes a query, and converts the restults to html options.
	 * 'key_field', 'value_field', and 'table' are all required attributes for the 'table' type.
	 *
	 * @param   SimpleXMLElement  $option  An 'option' element of the 'table' type.
	 *
	 * @return  array                      A list of html options.
	 */
	protected function parseOptionTable(SimpleXMLElement $option)
	{
		// Initialize variables.
		$options = array();

		// Initialize some field attributes.
		$key = $option['key_field'] ? (string) $option['key_field'] : null;
		$value = $option['value_field'] ? (string) $option['value_field'] : null;
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

		// Set the query and get the result list.
		$items = $db->setQuery($query)->loadObjectList();

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = JHtml::_('select.option', $item->$key, $translate ? JText::_($item->$value) : $item->$value);
			}
		}

		return $options;
	}
}
