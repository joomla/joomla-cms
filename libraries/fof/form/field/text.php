<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('text');

/**
 * Form Field class for the FOF framework
 * Supports a one line text field.
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormFieldText extends JFormFieldText implements FOFFormField
{
	protected $static;

	protected $repeatable;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   2.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'static':
				if (empty($this->static))
				{
					$this->static = $this->getStatic();
				}

				return $this->static;
				break;

			case 'repeatable':
				if (empty($this->repeatable))
				{
					$this->repeatable = $this->getRepeatable();
				}

				return $this->static;
				break;

			default:
				return parent::__get($name);
		}
	}

	/**
	 * Get the rendering of this field type for static display, e.g. in a single
	 * item view (typically a "read" task).
	 *
	 * @since 2.0
	 *
	 * @return  string  The field HTML
	 */
	public function getStatic()
	{
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$empty_replacement = '';

		if ($this->element['empty_replacement'])
		{
			$empty_replacement = (string) $this->element['empty_replacement'];
		}

		if (!empty($empty_replacement) && empty($this->value))
		{
			$this->value = JText::_($empty_replacement);
		}

		return '<span id="' . $this->id . '" ' . $class . '>' .
			htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') .
			'</span>';
	}

	/**
	 * Get the rendering of this field type for a repeatable (grid) display,
	 * e.g. in a view listing many item (typically a "browse" task)
	 *
	 * @since 2.0
	 *
	 * @return  string  The field HTML
	 */
	public function getRepeatable()
	{
		// Initialise
		$class					= $this->id;
		$format_string			= '';
		$format_if_not_empty	= false;
		$parse_value			= false;
		$show_link				= false;
		$link_url				= '';
		$empty_replacement		= '';

		// Get field parameters
		if ($this->element['class'])
		{
			$class = (string) $this->element['class'];
		}

		if ($this->element['format'])
		{
			$format_string = (string) $this->element['format'];
		}

		if ($this->element['show_link'] == 'true')
		{
			$show_link = true;
		}

		if ($this->element['format_if_not_empty'] == 'true')
		{
			$format_if_not_empty = true;
		}

		if ($this->element['parse_value'] == 'true')
		{
			$parse_value = true;
		}

		if ($this->element['url'])
		{
			$link_url = $this->element['url'];
		}
		else
		{
			$show_link = false;
		}

		if ($show_link && ($this->item instanceof FOFTable))
		{
			$link_url = $this->parseFieldTags($link_url);
		}
		else
		{
			$show_link = false;
		}

		if ($this->element['empty_replacement'])
		{
			$empty_replacement = (string) $this->element['empty_replacement'];
		}

		// Get the (optionally formatted) value
		$value = $this->value;

		if (!empty($empty_replacement) && empty($this->value))
		{
			$value = JText::_($empty_replacement);
		}

		if ($parse_value)
		{
			$value = $this->parseFieldTags($value);
		}

		if (!empty($format_string) && (!$format_if_not_empty || ($format_if_not_empty && !empty($this->value))))
		{
			$format_string = $this->parseFieldTags($format_string);
			$value = sprintf($format_string, $value);
		}
		else
		{
			$value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		}

		// Create the HTML
		$html = '<span class="' . $class . '">';

		if ($show_link)
		{
			$html .= '<a href="' . $link_url . '">';
		}

		$html .= $value;

		if ($show_link)
		{
			$html .= '</a>';
		}

		$html .= '</span>';

		return $html;
	}

	/**
	 * Replace string with tags that reference fields
	 *
	 * @param   string  $text  Text to process
	 *
	 * @return  string         Text with tags replace
	 */
	protected function parseFieldTags($text)
	{
		$ret = $text;

		// Replace [ITEM:ID] in the URL with the item's key value (usually:
		// the auto-incrementing numeric ID)
		$keyfield = $this->item->getKeyName();
		$replace  = $this->item->$keyfield;
		$ret = str_replace('[ITEM:ID]', $replace, $ret);

		// Replace other field variables in the URL
		$fields = $this->item->getFields();

		foreach ($fields as $fielddata)
		{
			$fieldname = $fielddata->Field;

			if (empty($fieldname))
			{
				$fieldname = $fielddata->column_name;
			}

			$search    = '[ITEM:' . strtoupper($fieldname) . ']';
			$replace   = $this->item->$fieldname;
			$ret  = str_replace($search, $replace, $ret);
		}

		return $ret;
	}
}
