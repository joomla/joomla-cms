<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

if (!class_exists('JFormFieldCheckbox'))
{
	require_once JPATH_LIBRARIES . '/joomla/form/fields/checkbox.php';
}

/**
 * Form Field class for the FOF framework
 * A single checkbox
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormFieldCheckbox extends JFormFieldCheckbox implements FOFFormField
{
	protected $static;

	protected $repeatable;
	
	/** @var   FOFTable  The item being rendered in a repeatable form field */
	public $item;
	
	/** @var int A monotonically increasing number, denoting the row number in a repeatable view */
	public $rowid;

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
		$value = $this->element['value'] ? (string) $this->element['value'] : '1';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$onclick = $this->element['onclick'] ? ' onclick="' . (string) $this->element['onclick'] . '"' : '';
		$required = $this->required ? ' required="required" aria-required="true"' : '';

		if (empty($this->value))
		{
			$checked = (isset($this->element['checked'])) ? ' checked="checked"' : '';
		}
		else
		{
			$checked = ' checked="checked"';
		}

		return '<span id="' . $this->id . '" ' . $class . '>' .
			'<input type="checkbox" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
			. htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"' . $class . $checked . $disabled . $onclick . $required . ' />' .
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
		$class = $this->element['class'] ? (string) $this->element['class'] : '';
		$value = $this->element['value'] ? (string) $this->element['value'] : '1';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$onclick = $this->element['onclick'] ? ' onclick="' . (string) $this->element['onclick'] . '"' : '';
		$required = $this->required ? ' required="required" aria-required="true"' : '';

		if (empty($this->value))
		{
			$checked = (isset($this->element['checked'])) ? ' checked="checked"' : '';
		}
		else
		{
			$checked = ' checked="checked"';
		}

		return '<span class="' . $this->id . ' ' . $class . '">' .
			'<input type="checkbox" name="' . $this->name . '" class="' . $this->id . ' ' . $class . '"' . ' value="'
			. htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $disabled . $onclick . $required . ' />' .
			'</span>';
	}
}
