<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/html/html/date.php';

/**
 * Test class for JHtmlDate.
 *
 * @since  11.3
 */
class JHtmlDateTest extends JoomlaTestCase
{
	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function setUp()
	{
		parent::setUp();

		// We are only coupled to Document and Language in JFactory.
		$this->saveFactoryState();

		JFactory::$language = $this->getMockLanguage();
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   11.3
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * @return	array
	 *
	 * @since   11.3
	 */
	public function dataTestRelative()
	{
		return array(
			// Element order: result, date, unit, time
			array(
				'1 hour ago',
				JFactory::getDate('2011-10-18 12:00:00'),
				null,
				JFactory::getDate('2011-10-18 11:00:00')
			),
			array(
				'10 days ago',
				JFactory::getDate('2011-10-18 12:00:00'),
				'day',
				JFactory::getDate('2011-10-08 12:00:00')
			),
			array(
				'Less than a minute ago',
				JFactory::getDate('now'),
			)
		);
	}

	/**
	 * Tests the JHtmlDate::relative method.
	 *
	 * @param	string  $result  The expected test result
	 * @param   string  $date    The date to convert
	 * @param   string  $unit    The optional unit of measurement to return
	 *                           if the value of the diff is greater than one
	 * @param   string  $time    An optional time to compare to, defaults to now
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @dataProvider dataTestRelative
	 */
	public function testRelative($result, $date, $unit = null, $time = null)
	{
		$this->assertThat(
			JHtmlDate::relative($date, $unit, $time),
			$this->equalTo($result)
		);
	}
}