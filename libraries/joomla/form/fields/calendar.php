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
class JFormFieldCalendar extends JFormField implements JFormDomfieldinterface
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
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.7
	 */
	protected $layout = 'joomla.form.field.calendar';

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
			case 'timeformat':
			case 'minyear':
			case 'maxyear':
			case 'todaybutton':
			case 'singleheader':
			case 'weeknumbers':
			case 'showtime':
			case 'filltable':
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
			case 'timeformat':
			case 'minyear':
			case 'maxyear':
				$this->$name = (int) $value;
				break;
			case 'todaybutton':
			case 'singleheader':
			case 'weeknumbers':
			case 'showtime':
			case 'filltable':
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
			$this->maxlength    = (int) $this->element['maxlength'] ? (int) $this->element['maxlength'] : 45;
			$this->format       = (string) $this->element['format'] ? (string) $this->element['format'] : '%Y-%m-%d';
			$this->filter       = (string) $this->element['filter'] ? (string) $this->element['filter'] : 'USER_UTC';
			$this->todaybutton  = (string) $this->element['todaybutton'] ? (string) $this->element['todaybutton'] : "true";
			$this->weeknumbers  = (string) $this->element['weeknumbers'] ? (string) $this->element['weeknumbers'] : "false";
			$this->showtime     = (string) $this->element['showtime'] ? (string) $this->element['showtime'] : "false";
			$this->filltable    = (string) $this->element['filltable'] ? (string) $this->element['filltable'] : "true";
			$this->timeformat   = (int) $this->element['timeformat'] ? (int) $this->element['timeformat'] : 24;
			$this->minyear      = (int) $this->element['minyear'] ? (int) $this->element['minyear'] : JText::_('JLIB_HTML_BEHAVIOR_CALENDAR_MIN_YEAR');
			$this->maxyear      = (int) $this->element['maxyear'] ? (int) $this->element['maxyear'] : JText::_('JLIB_HTML_BEHAVIOR_CALENDAR_MAX_YEAR');
			$this->singleheader = (string) $this->element['singleheader'] ? (string) $this->element['singleheader'] : "false";
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
		$translateFormat = (string) $this->element['translateformat'];

		if ($translateFormat && $translateFormat != 'false')
		{
			$showTime = (string) $this->element['showtime'];

			if ($showTime && $showTime != 'false')
			{
				$format = JText::_('DATE_FORMAT_CALENDAR_DATETIME');
			}
			else
			{
				$format = JText::_('DATE_FORMAT_CALENDAR_DATE');
			}
		}
		else
		{
			$format = $this->format;
		}

		// Build the attributes array.
		$attributes = array();

		empty($this->size)      ? null : $attributes['size'] = $this->size;
		empty($this->maxlength) ? null : $attributes['maxlength'] = $this->maxlength;
		empty($this->class)     ? null : $attributes['class'] = $this->class;
		!$this->readonly        ? null : $attributes['readonly'] = 'readonly';
		!$this->disabled        ? null : $attributes['disabled'] = 'disabled';
		empty($this->onchange)  ? null : $attributes['onchange'] = $this->onchange;
		!strlen($hint)          ? null : $attributes['placeholder'] = $hint;
		$this->autocomplete     ? null : $attributes['autocomplete'] = 'off';
		!$this->autofocus       ? null : $attributes['autofocus'] = '';

		if ($this->required)
		{
			throw new UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
		}

		return $this->getRenderer($this->layout)->render($this->getLayoutData());
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since 3.5
	 */
	protected function getLayoutData()
	{
		$data   = parent::getLayoutData();
		$user   = JFactory::getUser();
		$config = JFactory::getConfig();
		$tag    = JFactory::getLanguage()->getTag();

		// Format value when not nulldate ('0000-00-00 00:00:00'), otherwise blank it as it would result in 1970-01-01.
		if ($this->value && $this->value != JFactory::getDbo()->getNullDate() && strtotime($this->value) !== false)
		{
			$tz = date_default_timezone_get();
			date_default_timezone_set('UTC');
			$data['value'] = strftime($this->format, strtotime($this->value));
			date_default_timezone_set($tz);
		}


		// If a known filter is given use it.
		switch (strtoupper($this->filter))
		{
			case 'SERVER_UTC':
				// Convert a date to UTC based on the server timezone.
				if ($this->value && $this->value != JFactory::getDbo()->getNullDate())
				{
					// Get a date object based on the correct timezone.
					$this->date = JFactory::getDate($this->value, 'UTC');
					$this->date->setTimezone(new DateTimeZone($config->get('offset')));

					// Transform the date string.
					$this->value = $this->date->format('Y-m-d H:i:s', true, false);
				}

				break;

			case 'USER_UTC':
				// Convert a date to UTC based on the user timezone.
				if ($this->value && $this->value != JFactory::getDbo()->getNullDate())
				{
					// Get a date object based on the correct timezone.
					$this->date = JFactory::getDate($this->value, 'UTC');

					$this->date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

					// Transform the date string.
					$this->value = $this->date->format('Y-m-d H:i:s', true, false);
				}

				break;
		}

		// Get the appropriate for the current language date helper
		$path = 'system/calendar-locales/date/date-helper.min.js';

		if (is_dir(JPATH_ROOT . '/media/system/js/calendar-locales/date/' . strtolower($tag) . '/'))
		{
			$path = 'system/calendar-locales/date/' . strtolower($tag) . '/date-helper.min.js';
		}

		$extraData = array(
			'maxLength'    => $this->maxlength,
			'format'       => $this->format,
			'filter'       => $this->filter,
			'todaybutton'  => ($this->todaybutton === "true") ? 1 : 0,
			'weeknumbers'  => ($this->weeknumbers === "true") ? 1 : 0,
			'showtime'     => ($this->showtime === "true") ? 1 : 0,
			'filltable'    => ($this->filltable === "true") ? 1 : 0,
			'timeformat'   => $this->timeformat,
			'minyear'      => $this->minyear,
			'maxyear'      => $this->maxyear,
			'weekenddays'  => $this->weekenddays,
			'singleheader' => ($this->singleheader === "true") ? 1 : 0,
			'tag'          => $tag,
			'datePath'     => $path,
		);

		return array_merge($data, $extraData);
	}
}
