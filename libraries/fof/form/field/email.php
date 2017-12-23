<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

JFormHelper::loadFieldClass('email');

/**
 * Form Field class for the FOF framework
 * Supports a one line text field.
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormFieldEmail extends JFormFieldEMail implements FOFFormField
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

				return $this->repeatable;
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
		$dolink = $this->element['show_link'] == 'true';
		$empty_replacement = '';

		if ($this->element['empty_replacement'])
		{
			$empty_replacement = (string) $this->element['empty_replacement'];
		}

		if (!empty($empty_replacement) && empty($this->value))
		{
			$this->value = JText::_($empty_replacement);
		}

		$innerHtml = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');

		if ($dolink)
		{
			$innerHtml = '<a href="mailto:' . $innerHtml . '">' .
				$innerHtml . '</a>';
		}

		return '<span id="' . $this->id . '" ' . $class . '>' .
			$innerHtml .
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
		$class = '';
		$show_link = false;
		$link_url = '';
		$empty_replacement = '';

		// Get field parameters
		if ($this->element['class'])
		{
			$class = (string) $this->element['class'];
		}

		if ($this->element['show_link'] == 'true')
		{
			$show_link = true;
		}

		if ($this->element['url'])
		{
			$link_url = $this->element['url'];
		}
		else
		{
			$link_url = 'mailto:' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');
		}

		if ($this->element['empty_replacement'])
		{
			$empty_replacement = (string) $this->element['empty_replacement'];
		}

		// Get the (optionally formatted) value
		if (!empty($empty_replacement) && empty($this->value))
		{
			$this->value = JText::_($empty_replacement);
		}

		$value = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');

		// Create the HTML
		$html = '<span class="' . $this->id . ' ' . $class . '">';

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
}
