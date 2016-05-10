<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 *
 * Provides a pop up date picker linked to a button.
 * Optionally may be filtered to use user's or server's time zone.
 *
 * @since  11.1
 */
class JFormFieldCalendar extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Calendar';

	/**
	 * The allowable maxlength of calendar field.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $maxlength;

	/**
	 * The format of date and time.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $format;

	/**
	 * The filter.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $filter;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'maxlength':
			case 'format':
			case 'filter':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'maxlength':
				$value = (int) $value;
			case 'format':
			case 'filter':
				$this->$name = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->maxlength = (int) $this->element['maxlength'] ? (int) $this->element['maxlength'] : 45;
			$this->format    = (string) $this->element['format'] ? (string) $this->element['format'] : '%Y-%m-%d';
			$this->filter    = (string) $this->element['filter'] ? (string) $this->element['filter'] : 'USER_UTC';
		}

		return $return;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Translate placeholder text
		$hint = $this->translateHint ? JText::_($this->hint) : $this->hint;

		// Initialize some field attributes.
		$format = $this->format;

		// Build the attributes array.
		$attributes = array();

		empty($this->size)      ? null : $attributes['size'] = $this->size;
		empty($this->maxlength) ? null : $attributes['maxlength'] = $this->maxlength;
		empty($this->class)     ? null : $attributes['class'] = $this->class;
		!$this->readonly        ? null : $attributes['readonly'] = 'readonly';
		!$this->disabled        ? null : $attributes['disabled'] = 'disabled';
		empty($this->onchange)  ? null : $attributes['onchange'] = $this->onchange;
		empty($hint)            ? null : $attributes['placeholder'] = $hint;
		$this->autocomplete     ? null : $attributes['autocomplete'] = 'off';
		!$this->autofocus       ? null : $attributes['autofocus'] = '';

		if ($this->required)
		{
			$attributes['required'] = '';
			$attributes['aria-required'] = 'true';
		}

		// Handle the special case for "now".
		if (strtoupper($this->value) == 'NOW')
		{
			$this->value = JFactory::getDate()->format('Y-m-d H:i:s');
		}

		// Get some system objects.
		$config = JFactory::getConfig();
		$user = JFactory::getUser();

		// If a known filter is given use it.
		switch (strtoupper($this->filter))
		{
			case 'SERVER_UTC':
				// Convert a date to UTC based on the server timezone.
				if ($this->value && $this->value != JFactory::getDbo()->getNullDate())
				{
					// Get a date object based on the correct timezone.
					$date = JFactory::getDate($this->value, 'UTC');
					$date->setTimezone(new DateTimeZone($config->get('offset')));

					// Transform the date string.
					$this->value = $date->format('Y-m-d H:i:s', true, false);
				}

				break;

			case 'USER_UTC':
				// Convert a date to UTC based on the user timezone.
				if ($this->value && $this->value != JFactory::getDbo()->getNullDate())
				{
					// Get a date object based on the correct timezone.
					$date = JFactory::getDate($this->value, 'UTC');

					$date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

					// Transform the date string.
					$this->value = $date->format('Y-m-d H:i:s', true, false);
				}

				break;
		}

		// Including fallback code for HTML5 non supported browsers.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', false, true);

		return JHtml::_('calendar', $this->value, $this->name, $this->id, $format, $attributes);
	}
}
