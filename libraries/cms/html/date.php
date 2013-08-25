<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Extended Utility class for handling date display.
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       2.5
 */
abstract class JHtmlDate
{
	/**
	 * Function to convert a static time into a relative measurement
	 *
	 * @param   string  $date  The date to convert
	 * @param   string  $unit  The optional unit of measurement to return
	 *                         if the value of the diff is greater than one
	 * @param   string  $time  An optional time to compare to, defaults to now
	 *
	 * @return  string  The converted time string
	 *
	 * @since   2.5
	 */
	public static function relative($date, $unit = null, $time = null)
	{
		if (is_null($time))
		{
			// Get now
			$time = JFactory::getDate('now');
		}

		// Get the difference in seconds between now and the time
		$diff = strtotime($time) - strtotime($date);

		// Less than a minute
		if ($diff < 60)
		{
			return JText::_('JLIB_HTML_DATE_RELATIVE_LESSTHANAMINUTE');
		}

		// Round to minutes
		$diff = round($diff / 60);

		// 1 to 59 minutes
		if ($diff < 60 || $unit == 'minute')
		{
			return JText::plural('JLIB_HTML_DATE_RELATIVE_MINUTES', $diff);
		}

		// Round to hours
		$diff = round($diff / 60);

		// 1 to 23 hours
		if ($diff < 24 || $unit == 'hour')
		{
			return JText::plural('JLIB_HTML_DATE_RELATIVE_HOURS', $diff);
		}

		// Round to days
		$diff = round($diff / 24);

		// 1 to 6 days
		if ($diff < 7 || $unit == 'day')
		{
			return JText::plural('JLIB_HTML_DATE_RELATIVE_DAYS', $diff);
		}

		// Round to weeks
		$diff = round($diff / 7);

		// 1 to 4 weeks
		if ($diff <= 4 || $unit == 'week')
		{
			return JText::plural('JLIB_HTML_DATE_RELATIVE_WEEKS', $diff);
		}

		// Over a month, return the absolute time
		return JHtml::_('date', $date);
	}
}
