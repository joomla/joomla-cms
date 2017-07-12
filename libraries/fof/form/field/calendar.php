<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  form
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

JFormHelper::loadFieldClass('calendar');

/**
 * Form Field class for the FOF framework
 * Supports a calendar / date field.
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormFieldCalendar extends JFormFieldCalendar implements FOFFormField
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
			// ATTENTION: Redirected getInput() to getStatic()
			case 'input':
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
		return $this->getCalendar('static');
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
		return $this->getCalendar('repeatable');
	}

	/**
	 * Method to get the calendar input markup.
	 *
	 * @param   string  $display  The display to render ('static' or 'repeatable')
	 *
	 * @return  string	The field input markup.
	 *
	 * @since   2.1.rc4
	 */
	protected function getCalendar($display)
	{
		// Initialize some field attributes.
		$format  = $this->element['format'] ? (string) $this->element['format'] : '%Y-%m-%d';
		$class   = $this->element['class'] ? (string) $this->element['class'] : '';
		$default = $this->element['default'] ? (string) $this->element['default'] : '';

		// PHP date doesn't use percentages (%) for the format, but the calendar Javascript
		// DOES use it (@see: calendar-uncompressed.js). Therefore we have to convert it.
		$formatJS  = $format;
		$formatPHP = str_replace(array('%', 'H:M:S', 'B'), array('', 'H:i:s', 'F'), $formatJS);

		// Check for empty date values
		if (empty($this->value) || $this->value == FOFPlatform::getInstance()->getDbo()->getNullDate() || $this->value == '0000-00-00')
		{
			$this->value = $default;
		}

		// Get some system objects.
		$config = FOFPlatform::getInstance()->getConfig();
		$user   = JFactory::getUser();

		// Format date if exists
		if (!empty($this->value))
		{
			$date   = FOFPlatform::getInstance()->getDate($this->value, 'UTC');

			// If a known filter is given use it.
			switch (strtoupper((string) $this->element['filter']))
			{
				case 'SERVER_UTC':
					// Convert a date to UTC based on the server timezone.
					if ((int) $this->value)
					{
						// Get a date object based on the correct timezone.
						$date->setTimezone(new DateTimeZone($config->get('offset')));
					}
					break;

				case 'USER_UTC':
					// Convert a date to UTC based on the user timezone.
					if ((int) $this->value)
					{
						// Get a date object based on the correct timezone.
						$date->setTimezone($user->getTimezone());
					}
					break;

				default:
					break;
			}

			// Transform the date string.
			$this->value = $date->format($formatPHP, true, false);
		}

		if ($display == 'static')
		{
			// Build the attributes array.
			$attributes = array();

			if ($this->element['size'])
			{
				$attributes['size'] = (int) $this->element['size'];
			}

			if ($this->element['maxlength'])
			{
				$attributes['maxlength'] = (int) $this->element['maxlength'];
			}

			if ($this->element['class'])
			{
				$attributes['class'] = (string) $this->element['class'];
			}

			if ((string) $this->element['readonly'] == 'true')
			{
				$attributes['readonly'] = 'readonly';
			}

			if ((string) $this->element['disabled'] == 'true')
			{
				$attributes['disabled'] = 'disabled';
			}

			if ($this->element['onchange'])
			{
				$attributes['onchange'] = (string) $this->element['onchange'];
			}

			if ($this->required)
			{
				$attributes['required'] = 'required';
				$attributes['aria-required'] = 'true';
			}

			return JHtml::_('calendar', $this->value, $this->name, $this->id, $formatJS, $attributes);
		}
		else
		{
			return '<span class="' . $this->id . ' ' . $class . '">' .
			htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') .
			'</span>';
		}
	}
}
