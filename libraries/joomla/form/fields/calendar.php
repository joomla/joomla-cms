<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
	 * The minimum year number to subtract/add from the current year
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	protected $minyear;

	/**
	 * The maximum year number to subtract/add from the current year
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	protected $maxyear;

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $layout = 'joomla.form.field.calendar';

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
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
			case 'todaybutton':
			case 'singleheader':
			case 'weeknumbers':
			case 'showtime':
			case 'filltable':
			case 'minyear':
			case 'maxyear':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
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
				$this->$name = (int) $value;
				break;
			case 'todaybutton':
			case 'singleheader':
			case 'weeknumbers':
			case 'showtime':
			case 'filltable':
			case 'format':
			case 'filter':
			case 'minyear':
			case 'maxyear':
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
	 * @param   string            $group    The field name group control value. This acts as an array container for the field.
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
			$this->todaybutton  = (string) $this->element['todaybutton'] ? (string) $this->element['todaybutton'] : 'true';
			$this->weeknumbers  = (string) $this->element['weeknumbers'] ? (string) $this->element['weeknumbers'] : 'true';
			$this->showtime     = (string) $this->element['showtime'] ? (string) $this->element['showtime'] : 'false';
			$this->filltable    = (string) $this->element['filltable'] ? (string) $this->element['filltable'] : 'true';
			$this->timeformat   = (int) $this->element['timeformat'] ? (int) $this->element['timeformat'] : 24;
			$this->singleheader = (string) $this->element['singleheader'] ? (string) $this->element['singleheader'] : 'false';
			$this->minyear      = (string) $this->element['minyear'] ? (string) $this->element['minyear'] : null;
			$this->maxyear      = (string) $this->element['maxyear'] ? (string) $this->element['maxyear'] : null;

			if ($this->maxyear < 0 || $this->minyear > 0)
			{
				$this->todaybutton = 'false';
			}
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
		$config    = JFactory::getConfig();
		$user      = JFactory::getUser();

		// Translate the format if requested
		$translateFormat = (string) $this->element['translateformat'];

		if ($translateFormat && $translateFormat != 'false')
		{
			$showTime = (string) $this->element['showtime'];

			if ($showTime && $showTime != 'false')
			{
				$this->format = JText::_('DATE_FORMAT_CALENDAR_DATETIME');
			}
			else
			{
				$this->format = JText::_('DATE_FORMAT_CALENDAR_DATE');
			}
		}

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
					$date->setTimezone($user->getTimezone());

					// Transform the date string.
					$this->value = $date->format('Y-m-d H:i:s', true, false);
				}
				break;
		}

		// Format value when not nulldate ('0000-00-00 00:00:00'), otherwise blank it as it would result in 1970-01-01.
		if ($this->value && $this->value != JFactory::getDbo()->getNullDate() && strtotime($this->value) !== false)
		{
			$tz = date_default_timezone_get();
			date_default_timezone_set('UTC');
			$this->value = strftime($this->format, strtotime($this->value));
			date_default_timezone_set($tz);
		}
		else
		{
			$this->value = '';
		}

		return $this->getRenderer($this->layout)->render($this->getLayoutData());
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since  3.7.0
	 */
	protected function getLayoutData()
	{
		$data      = parent::getLayoutData();
		$tag       = JFactory::getLanguage()->getTag();
		$calendar  = JFactory::getLanguage()->getCalendar();
		$direction = strtolower(JFactory::getDocument()->getDirection());

		// Get the appropriate file for the current language date helper
		$helperPath = 'system/fields/calendar-locales/date/gregorian/date-helper.min.js';

		if (!empty($calendar) && is_dir(JPATH_ROOT . '/media/system/js/fields/calendar-locales/date/' . strtolower($calendar)))
		{
			$helperPath = 'system/fields/calendar-locales/date/' . strtolower($calendar) . '/date-helper.min.js';
		}

		// Get the appropriate locale file for the current language
		$localesPath = 'system/fields/calendar-locales/en.js';

		if (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . strtolower($tag) . '.js'))
		{
			$localesPath = 'system/fields/calendar-locales/' . strtolower($tag) . '.js';
		}
		elseif (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . strtolower(substr($tag, 0, -3)) . '.js'))
		{
			$localesPath = 'system/fields/calendar-locales/' . strtolower(substr($tag, 0, -3)) . '.js';
		}

		$extraData = array(
			'value'        => $this->value,
			'maxLength'    => $this->maxlength,
			'format'       => $this->format,
			'filter'       => $this->filter,
			'todaybutton'  => ($this->todaybutton === 'true') ? 1 : 0,
			'weeknumbers'  => ($this->weeknumbers === 'true') ? 1 : 0,
			'showtime'     => ($this->showtime === 'true') ? 1 : 0,
			'filltable'    => ($this->filltable === 'true') ? 1 : 0,
			'timeformat'   => $this->timeformat,
			'singleheader' => ($this->singleheader === 'true') ? 1 : 0,
			'helperPath'   => $helperPath,
			'localesPath'  => $localesPath,
			'minYear'      => $this->minyear,
			'maxYear'      => $this->maxyear,
			'direction'    => $direction,
		);

		return array_merge($data, $extraData);
	}
}
