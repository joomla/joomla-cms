<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHtmlDate.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       3.1
 */
class JHtmlDateTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test data for the testRelative method
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function dataTestRelative()
	{
		$now1 = new JDate('now');
		usleep(1);
		$now2 = new JDate('now');

		return array(
			// Element order: result, date, unit, time
			// result - 1 hour ago
			array(
				'JLIB_HTML_DATE_RELATIVE_HOURS',
				new JDate('2011-10-18 11:00:00'),
				null,
				new JDate('2011-10-18 12:00:00')
			),
			// Result - 10 days ago
			array(
				'JLIB_HTML_DATE_RELATIVE_DAYS',
				new JDate('2011-10-08 12:00:00'),
				'day',
				new JDate('2011-10-18 12:00:00')
			),
			// Result - 3 weeks ago
			array(
				'JLIB_HTML_DATE_RELATIVE_WEEKS',
				new JDate('2011-09-27 12:00:00'),
				'week',
				new JDate('2011-10-18 12:00:00')
			),
			// Result - 10 minutes ago
			array(
				'JLIB_HTML_DATE_RELATIVE_MINUTES',
				new JDate('2011-10-18 11:50:00'),
				'minute',
				new JDate('2011-10-18 12:00:00')
			),
			// Result - Less than a minute ago
			array(
				'JLIB_HTML_DATE_RELATIVE_LESSTHANAMINUTE',
				$now1,
				null,
				$now2
			)
		);
	}

	/**
	 * Tests the JHtmlDate::relative method.
	 *
	 * @param   string  $result  The expected test result
	 * @param   string  $date    The date to convert
	 * @param   string  $unit    The optional unit of measurement to return
	 *                            if the value of the diff is greater than one
	 * @param   string  $time    An optional time to compare to, defaults to now
	 *
	 * @return  void
	 *
	 * @since   3.1
	 * @dataProvider dataTestRelative
	 */
	public function testRelative($result, $date, $unit = null, $time = null)
	{
		$this->assertEquals($result, JHtmlDate::relative($date, $unit, $time));
	}
}
