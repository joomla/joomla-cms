<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('text');

/**
 * Form Field class for the FOF framework
 * Supports a button input.
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormFieldButton extends FOFFormFieldText implements FOFFormField
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
	public function getInput()
	{
		$this->label = '';

		$text = $this->element['text'];
		$class = $this->element['class'] ? (string) $this->element['class'] : '';
		$icon = $this->element['icon'] ? (string) $this->element['icon'] : '';
		$onclick = $this->element['onclick'] ? 'onclick="' . (string) $this->element['onclick'] . '"' : '';

		$this->value = JText::_($text);

		if ($icon)
		{
			$icon = '<span class="icon ' . $icon . '"></span>';
		}

		return '<button id="' . $this->id . '" class="btn ' . $class . '" ' .
			$onclick . '>' .
			$icon .
			htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') .
			'</button>';
	}

	/**
	 * Method to get the field title.
	 *
	 * @return  string  The field title.
	 */
	protected function getTitle()
	{
		return null;
	}
}
