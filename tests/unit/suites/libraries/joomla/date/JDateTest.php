<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Date
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Tests for JDate class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Date
 * @since       11.3
 */
class JDateTest extends TestCase
{
	/**
	 * Backup of the date format in use by JDate
	 *
	 * @var  string
	 */
	private $format;

	/**
	 * Object under test
	 *
	 * @var    JDate
	 * @since  11.3
	 */
	protected $object;

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->format = JDate::$format;

		$this->object = new JDate('12/20/2007 11:44:56', 'America/New_York');
	}

	/**
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->object);

		JDate::$format = $this->format;

		parent::tearDown();
	}

	/**
	 * Test Cases for __construct
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function cases__construct()
	{
		$cases = array(
			'basic' => array(
				'12/23/2008 13:45',
				null,
				'Tue 12/23/2008 13:45',
			),
			'tzCT' => array(
				'12/23/2008 13:45',
				'US/Central',
				'Tue 12/23/2008 13:45',
			),
			'DateTime tzCT' => array(
				'12/23/2008 13:45',
				new DateTimeZone('US/Central'),
				'Tue 12/23/2008 13:45',
			),
		);

		// Backup the default timezone before continuing - Using the system timezone apparently causes test failures
		$timezone = new DateTimeZone(date_default_timezone_get());

		date_default_timezone_set('UTC');

		$cases['unix'] = array(
			strtotime('12/26/2008 13:45'),
			null,
			'Fri 12/26/2008 13:45',
		);

		// Restore the timezone
		date_default_timezone_set($timezone->getName());

		return $cases;
	}

	/**
	 * Test Cases for __get
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function cases__get()
	{
		return array(
			'daysinmonth' => array(
				'2000-01-02 03:04:05',
				'daysinmonth',
				31,
			),
			'dayofweek' => array(
				'2000-01-02 03:04:05',
				'dayofweek',
				7,
			),
			'dayofyear' => array(
				'2000-01-02 03:04:05',
				'dayofyear',
				1,
			),
			'isleapyear' => array(
				'2000-01-02 03:04:05',
				'isleapyear',
				true,
			),
			'day' => array(
				'2000-01-02 03:04:05',
				'day',
				2,
			),
			'hour' => array(
				'2000-01-02 03:04:05',
				'hour',
				3,
			),
			'minute' => array(
				'2000-01-02 03:04:05',
				'minute',
				4,
			),
			'second' => array(
				'2000-01-02 03:04:05',
				'second',
				5,
			),
			'month' => array(
				'2000-01-02 03:04:05',
				'month',
				1,
			),
			'ordinal' => array(
				'2000-01-02 03:04:05',
				'ordinal',
				'nd',
			),
			'week' => array(
				'2000-01-02 03:04:05',
				'week',
				52,
			),
			'year' => array(
				'2000-01-02 03:04:05',
				'year',
				2000,
			),
		);
	}

	/**
	 * Test Cases for format
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function casesFormat()
	{
		return array(
			'basic' => array(
				'd/m/Y H:i:s',
				true,
				'20/12/2007 11:44:56',
			),
			'mmddyy' => array(
				'mdy His',
				true,
				'122007 114456',
			),
			'mmddyyGMT' => array(
				'mdy His',
				false,
				'122007 164456',
			),
			'Long' => array(
				'D F j, Y H:i:s',
				true,
				'Thu December 20, 2007 11:44:56',
			),
			'LongGMT' => array(
				'D F j, Y H:i:s',
				false,
				'Thu December 20, 2007 16:44:56',
			),
			'Long2' => array(
				'H:i:s D F j, Y',
				false,
				'16:44:56 Thu December 20, 2007',
			),
			'Long3' => array(
				'H:i:s l F j, Y',
				false,
				'16:44:56 Thursday December 20, 2007',
			),
			'Long4' => array(
				'H:i:s l M j, Y',
				false,
				'16:44:56 Thursday Dec 20, 2007',
			),
			'RFC822' => array(
				'r',
				false,
				'Thu, 20 Dec 2007 16:44:56 +0000',
			),
		);
	}

	/**
	 * Test Cases for getOffsetFromGMT
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function casesGetOffsetFromGmt()
	{
		return array(
			'basic' => array(
				null,
				'2007-11-20 11:44:56',
				null,
				0,
			),
			'Atlantic/Azores' => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				null,
				-3600,
			),
			'	/Hours' => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				true,
				-1,
			),
			'Australia/Brisbane' => array(
				'Australia/Brisbane',
				'2007-5-20 11:44:56',
				null,
				36000,
			),
			'Australia/Brisbane/Hours' => array(
				'Australia/Brisbane',
				'2007-5-20 11:44:56',
				true,
				10,
			),
		);
	}

	/**
	 * Test Cases for setTimezone
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function casesSetTimezone()
	{
		return array(
			'New_York' => array(
				'America/New_York',
				'Thu, 20 Dec 2007 11:44:56 -0500',
			),
			'Chicago' => array(
				'America/Chicago',
				'Thu, 20 Dec 2007 10:44:56 -0600',
			),
			'Los_Angeles' => array(
				'America/Los_Angeles',
				'Thu, 20 Dec 2007 08:44:56 -0800',
			),
			'Isle of Man' => array(
				'Europe/Isle_of_Man',
				'Thu, 20 Dec 2007 16:44:56 +0000',
			),
			'Berlin' => array(
				'Europe/Berlin',
				'Thu, 20 Dec 2007 17:44:56 +0100',
			),
			'Pacific/Port_Moresby' => array(
				'Pacific/Port_Moresby',
				'Fri, 21 Dec 2007 02:44:56 +1000',
			),
		);
	}

	/**
	 * Test Cases for toISO8601
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function casesToISO8601()
	{
		return array(
			'basic' => array(
				null,
				'2007-11-20 11:44:56',
				null,
				'2007-11-20T11:44:56+00:00',
			),
			'Atlantic/AzoresGMT' => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				null,
				'2007-11-20T12:44:56+00:00',
			),
			'Atlantic/AzoresLocal' => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				true,
				'2007-11-20T11:44:56-01:00',
			),
			'Australia/BrisbaneGMT' => array(
				'Australia/Brisbane',
				'2007-5-20 11:44:56',
				null,
				'2007-05-20T01:44:56+00:00',
			),
			'Australia/Brisbane/Local' => array(
				'Australia/Brisbane',
				'2007-5-20 11:44:56',
				true,
				'2007-05-20T11:44:56+10:00',
			),
		);
	}

	/**
	 * Test Cases for toSQL
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function casesToSql()
	{
		return array(
			'basic' => array(
				null,
				'2007-11-20 11:44:56',
				null,
				'2007-11-20 11:44:56',
			),
			'Atlantic/AzoresGMT' => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				null,
				'2007-11-20 12:44:56',
			),
			'Atlantic/AzoresLocal' => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				true,
				'2007-11-20 11:44:56',
			),
			'Australia/BrisbaneGMT' => array(
				'Australia/Brisbane',
				'2007-5-20 11:44:56',
				null,
				'2007-05-20 01:44:56',
			),
			'Australia/Brisbane/Local' => array(
				'Australia/Brisbane',
				'2007-5-20 11:44:56',
				true,
				'2007-05-20 11:44:56',
			),
		);
	}

	/**
	 * Test Cases for toRFC822
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function casesToRFC822()
	{
		return array(
			'basic' => array(
				null,
				'2007-11-22 11:44:56',
				null,
				'Thu, 22 Nov 2007 11:44:56 +0000',
			),
			'Atlantic/AzoresGMT' => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				null,
				'Tue, 20 Nov 2007 12:44:56 +0000',
			),
			'Atlantic/AzoresLocal' => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				true,
				'Tue, 20 Nov 2007 11:44:56 -0100',
			),
			'Australia/BrisbaneGMT' => array(
				'Australia/Brisbane',
				'2007-5-22 11:44:56',
				null,
				'Tue, 22 May 2007 01:44:56 +0000',
			),
			'Australia/Brisbane/Local' => array(
				'Australia/Brisbane',
				'2007-5-23 11:44:56',
				true,
				'Wed, 23 May 2007 11:44:56 +1000',
			),
		);
	}

	/**
	 * Test Cases for __toString
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function casesToString()
	{
		return array(
			'basic' => array(
				null,
				'2007-12-20 11:44:56',
			),
			'mmmddyy' => array(
				'mdy His',
				'122007 114456',
			),
			'Long' => array(
				'D F j, Y H:i:s',
				'Thu December 20, 2007 11:44:56',
			),
		);
	}

	/**
	 * Test Cases for toUnix
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function casesToUnix()
	{
		return array(
			'basic' => array(
				null,
				'2007-11-20 11:44:56',
				1195559096,
			),
			'Atlantic/AzoresGMT' => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				1195562696,
			),
			'Atlantic/AzoresLocal' => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				1195562696,
			),
			'Australia/BrisbaneGMT' => array(
				'Australia/Brisbane',
				'2007-5-20 11:44:56',
				1179625496,
			),
			'Australia/Brisbane/Local' => array(
				'Australia/Brisbane',
				'2007-5-20 11:44:56',
				1179625496,
			),
		);
	}

	/**
	 * Testing the Constructor
	 *
	 * @param   string  $date          What time should be set?
	 * @param   mixed   $tz            Which time zone? (can be string or numeric
	 * @param   string  $expectedTime  What should the resulting time string look like?
	 *
	 * @return  void
	 *
	 * @dataProvider  cases__construct
	 * @since   11.3
	 * @covers  JDate::__construct
	 */
	public function test__construct($date, $tz, $expectedTime)
	{
		$jdate = new JDate($date, $tz);

		$this->assertThat(
			date_format($jdate, 'D m/d/Y H:i'),
			$this->equalTo($expectedTime)
		);

		$this->assertThat(
			$jdate->format('D m/d/Y H:i', true, false),
			$this->equalTo($expectedTime)
		);
	}

	/**
	 * Testing the Constructor
	 *
	 * @param   string  $date      The date.
	 * @param   string  $property  The property to test.
	 * @param   string  $expected  The expected value.
	 *
	 * @return  void
	 *
	 * @dataProvider  cases__get
	 * @since   11.3
	 * @covers  JDate::__get
	 */
	public function test__get($date, $property, $expected)
	{
		$jdate = new JDate($date);

		$this->assertThat(
			$jdate->$property,
			$this->equalTo($expected)
		);
	}

	/**
	 * Tests the magic __toString method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @covers  JDate::__toString
	 */
	public function test__toString()
	{
		$jdate = new JDate('2000-01-01 00:00:00');

		$this->assertThat(
			(string) $jdate,
			$this->equalTo('2000-01-01 00:00:00')
		);
	}

	/**
	 * Testing toString
	 *
	 * @param   string  $format        How should the time be formatted?
	 * @param   string  $expectedTime  What should the resulting time string look like?
	 *
	 * @return  void
	 *
	 * @dataProvider casesToString
	 * @since   11.3
	 * @covers  JDate::__toString
	 */
	public function testToString($format, $expectedTime)
	{
		if (!is_null($format))
		{
			JDate::$format = $format;
		}

		$this->assertEquals($expectedTime, (string) $this->object);
	}

	/**
	 * Tests the getInstance method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @covers  JDate::getInstance
	 */
	public function testGetInstance()
	{
		$this->assertInstanceOf('JDate', JDate::getInstance());

		$this->assertEquals('2000-01-01 00:00:00', (string) JDate::getInstance('2000-01-01 00:00:00'));
	}

	/**
	 * Testing getOffsetFromGMT
	 *
	 * @param   mixed    $tz        Which time zone? (can be string or numeric
	 * @param   string   $setTime   What time should be set?
	 * @param   boolean  $hours     Return offset in hours (true) or seconds?
	 * @param   string   $expected  What should the resulting time string look like?
	 *
	 * @return  void
	 *
	 * @dataProvider casesGetOffsetFromGmt
	 * @since   11.3
	 * @covers  JDate::getOffsetFromGMT
	 */
	public function testGetOffsetFromGmt($tz, $setTime, $hours, $expected)
	{
		$testJDate = new JDate($setTime, $tz);
		$offset    = $testJDate->getOffsetFromGmt($hours);

		$this->assertEquals($expected, $offset);
	}

	/**
	 * Testing format
	 *
	 * @param   string   $format    How should the time be formatted?
	 * @param   boolean  $local     Local (true) or GMT?
	 * @param   string   $expected  What should the resulting time string look like?
	 *
	 * @return  void
	 *
	 * @dataProvider casesFormat
	 * @since   11.3
	 * @covers  JDate::format
	 */
	public function testFormat($format, $local, $expected)
	{
		$this->assertEquals($expected, $this->object->format($format, $local, false));
	}

	/**
	 * Testing toRFC822
	 *
	 * @param   mixed   $tz        Which time zone? (can be string or numeric
	 * @param   string  $setTime   What time should be set?
	 * @param   bool    $local     Local (true) or GMT?
	 * @param   string  $expected  What should the resulting time string look like?
	 *
	 * @return  void
	 *
	 * @dataProvider casesToRFC822
	 * @since   11.3
	 * @covers  JDate::toRFC822
	 */
	public function testToRFC822($tz, $setTime, $local, $expected)
	{
		$testJDate = new JDate($setTime, $tz);

		$this->assertEquals($expected, $testJDate->toRFC822($local));
	}

	/**
	 * Testing toISO8601
	 *
	 * @param   mixed    $tz        Which time zone? (can be string or numeric
	 * @param   string   $setTime   What time should be set?
	 * @param   boolean  $local     Local (true) or GMT?
	 * @param   string   $expected  What should the resulting time string look like?
	 *
	 * @return  void
	 *
	 * @dataProvider casesToISO8601
	 * @since   11.3
	 * @covers  JDate::toISO8601
	 */
	public function testToISO8601($tz, $setTime, $local, $expected)
	{
		$testJDate = new JDate($setTime, $tz);

		$this->assertEquals($expected, $testJDate->toISO8601($local));
	}

	/**
	 * Testing toSql
	 *
	 * @param   mixed    $tz        Which time zone? (can be string or numeric
	 * @param   string   $setTime   What time should be set?
	 * @param   boolean  $local     Local (true) or GMT?
	 * @param   string   $expected  What should the resulting time string look like?
	 *
	 * @return  void
	 *
	 * @dataProvider casesToSQL
	 * @since   11.3
	 * @covers  JDate::toSql
	 */
	public function testToSql($tz, $setTime, $local, $expected)
	{
		$testJDate = new JDate($setTime, $tz);

		$this->assertEquals($expected, $testJDate->toSql($local, $this->getMockDatabase()));
	}

	/**
	 * Testing toUnix
	 *
	 * @param   mixed   $tz        Which time zone? (can be string or numeric
	 * @param   string  $setTime   What time should be set?
	 * @param   string  $expected  What should the resulting time string look like?
	 *
	 * @return  void
	 *
	 * @dataProvider casesToUnix
	 * @since   11.3
	 * @covers  JDate::toUnix
	 */
	public function testToUnix($tz, $setTime, $expected)
	{
		$testJDate = new JDate($setTime, $tz);

		$this->assertEquals($expected, $testJDate->toUnix());
	}

	/**
	 * Testing setTimezone
	 *
	 * @param   string  $tz        Which Time Zone should it be?
	 * @param   string  $expected  What should the resulting time string look like?
	 *
	 * @return  void
	 *
	 * @dataProvider casesSetTimezone
	 * @since   11.3
	 * @covers  JDate::setTimezone
	 */
	public function testSetTimezone($tz, $expected)
	{
		$this->object->setTimezone(new DateTimeZone($tz));

		$this->assertEquals($expected, $this->object->format('r', true));
	}
}
