<?php
/**
 * JDateTest.php -- unit testing file for JDate
 *
 * @package	Joomla.UnitTest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
require_once JPATH_BASE . '/libraries/joomla/utilities/date.php';

/**
 * JDateTest
 *
 * Test class for Jdate.
 *
 * @package	Joomla.UnitTest
 * @subpackage Utilities
 */
class JDateTest extends PHPUnit_Framework_TestCase
{
	protected $object;

	/**
	 *	tearDown
	 *
	 * @return void
	 */
	public function setUp()
	{
		$this->object = new JDate('12/20/2007 11:44:56', 'America/New_York');
	}

	/**
	 *	tearDown
	 *
	 * @return void
	 */
	function tearDown()
	{
	}

	/**
	 *	Test Cases for __construct
	 *
	 * @return array
	 */
	function casesConstruct()
	{
		date_default_timezone_set('UTC');
		return array(
			"basic" => array(
				'12/23/2008 13:45',
				null,
				'Tue 12/23/2008 13:45',
			),
			"unix" => array(
				strtotime('12/26/2008 13:45'),
				null,
				'Fri 12/26/2008 13:45',
			),
			"tz-7" => array(
				'12/27/2008 13:45',
				-6,
				'Sat 12/27/2008 13:45',
			),
			"tzCT" => array(
				'12/23/2008 13:45',
				'US/Central',
				'Tue 12/23/2008 13:45',
			),
		);
	}

	/**
	 *	Testing the Constructor
	 *
	 * @param string What time should be set?
	 * @param mixed  Which time zone? (can be string or numeric
	 * @param string What should the resulting time string look like?
	 *
	 * @return void
	 * @dataProvider casesConstruct
	 */
	public function testConstruct( $date, $tz, $expectedTime )
	{
		$jdate = new JDate($date, $tz);

		$this->assertThat(
			$expectedTime,
			$this->equalTo(date_format($jdate, 'D m/d/Y H:i'))
		);
		$this->assertThat(
			$expectedTime,
			$this->equalTo($jdate->format('D m/d/Y H:i', true))
		);
	}

	/**
	 *	Test Cases for __toString
	 *
	 * @return array
	 */
	function casesToString()
	{
		return array(
			"basic" => array(
				null,
				'2007-12-20 11:44:56',
			),
			"mmmddyy" => array(
				'mdy His',
				'122007 114456',
			),
			"Long" => array(
				'D F j, Y H:i:s',
				'Thu December 20, 2007 11:44:56',
			),
		);
	}

	/**
	 *	Testing toString
	 *
	 * @param string $format		How should the time be formatted?
	 * @param string $expectedTime What should the resulting time string look like?
	 *
	 * @return void
	 * @dataProvider casesToString
	 */
	public function testToString( $format, $expectedTime )
	{
		if( !is_null($format) )
		{
			JDate::$format = $format;
		}

		$this->assertThat(
			$this->object->__toString(),
			$this->equalTo($expectedTime)
		);
	}

	/**
	 *	Test Cases for getOffsetFromGMT
	 *
	 * @return array
	 */
	function casesGetOffsetFromGMT()
	{
		return array(
			"basic" => array(
				null,
				'2007-11-20 11:44:56',
				null,
				0,
			),
			"-1" => array(
				-1,
				'2007-11-20 11:44:56',
				null,
				-3600,
			),
			"-1hours" => array(
				-1,
				'2007-11-20 11:44:56',
				true,
				-1,
			),
			"Atlantic/Azores" => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				null,
				-3600,
			),
			"	/Hours" => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				true,
				-1,
			),
			"8" => array(
				8,
				'2007-05-20 11:44:56',
				null,
				28800,
			),
			"8hours" => array(
				8,
				'2007-05-20 11:44:56',
				true,
				8,
			),
			"Australia/Brisbane" => array(
				'Australia/Brisbane',
				'2007-5-20 11:44:56',
				null,
				36000,
			),
			"Australia/Brisbane/Hours" => array(
				'Australia/Brisbane',
				'2007-5-20 11:44:56',
				true,
				10,
			),
		);
	}

	/**
	 *	Testing getOffsetFromGMT
	 *
	 * @param mixed  $tz		Which time zone? (can be string or numeric
	 * @param string $setTime  What time should be set?
	 * @param bool   $hours	Return offset in hours (true) or seconds?
	 * @param string $expected What should the resulting time string look like?
	 *
	 * @return void
	 * @dataProvider casesGetOffsetFromGMT
	 */
	public function testGetOffsetFromGMT( $tz, $setTime, $hours, $expected )
	{
		if( is_null($tz) )
		{
			$testJDate = new JDate($setTime);
		}
		else
		{
			$testJDate = new JDate($setTime, $tz);
		}

		if( is_null($hours) )
		{
			$offset = $testJDate->getOffsetFromGMT();
		}
		else
		{
			$offset = $testJDate->getOffsetFromGMT($hours);
		}

		$this->assertThat($offset, $this->equalTo($expected));
	}

	/**
	 *	Test Cases for format
	 *
	 * @return array
	 */
	function casesFormat()
	{
		return array(
			"basic" => array(
				'd/m/Y H:i:s',
				true,
				'20/12/2007 11:44:56',
			),
			"mmddyy" => array(
				'mdy His',
				true,
				'122007 114456',
			),
			"mmddyyGMT" => array(
				'mdy His',
				false,
				'122007 164456',
			),
			"Long" => array(
				'D F j, Y H:i:s',
				true,
				'Thu December 20, 2007 11:44:56',
			),
			"LongGMT" => array(
				'D F j, Y H:i:s',
				false,
				'Thu December 20, 2007 16:44:56',
			),
			"Long2" => array(
				'H:i:s D F j, Y',
				false,
				'16:44:56 Thu December 20, 2007',
			),
			"Long3" => array(
				'H:i:s l F j, Y',
				false,
				'16:44:56 Thursday December 20, 2007',
			),
			"Long4" => array(
				'H:i:s l M j, Y',
				false,
				'16:44:56 Thursday Dec 20, 2007',
			),
			"RFC822" => array(
				'r',
				false,
				'Thu, 20 Dec 2007 16:44:56 +0000',
			),
		);
	}

	/**
	 *	Testing format
	 *
	 * @param string $format   How should the time be formatted?
	 * @param bool   $local	Local (true) or GMT?
	 * @param string $expected What should the resulting time string look like?
	 *
	 * @return void
	 * @dataProvider casesFormat
	 */
	public function testFormat( $format, $local, $expected )
	{
		$formatted = $this->object->format($format, $local);

		$this->assertThat($formatted, $this->equalTo($expected));
	}

	/**
	 *	Test Cases for toRFC822
	 *
	 * @return array
	 */
	function casesToRFC822()
	{
		return array(
			"basic" => array(
				null,
				'2007-11-22 11:44:56',
				null,
				'Thu, 22 Nov 2007 11:44:56 +0000',
			),
			"-1GMT" => array(
				-1,
				'2007-11-23 11:44:56',
				false,
				'Fri, 23 Nov 2007 12:44:56 +0000',
			),
			"-1local" => array(
				-1,
				'2007-11-24 11:44:56',
				true,
				'Sat, 24 Nov 2007 11:44:56 -0100',
			),
			"Atlantic/AzoresGMT" => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				null,
				'Tue, 20 Nov 2007 12:44:56 +0000',
			),
			"Atlantic/AzoresLocal" => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				true,
				'Tue, 20 Nov 2007 11:44:56 -0100',
			),
			"8GMT" => array(
				8,
				'2007-05-20 11:44:56',
				null,
				'Sun, 20 May 2007 03:44:56 +0000',
			),
			"8local" => array(
				8,
				'2007-05-21 11:44:56',
				true,
				'Mon, 21 May 2007 11:44:56 +0800',
			),
			"Australia/BrisbaneGMT" => array(
				'Australia/Brisbane',
				'2007-5-22 11:44:56',
				null,
				'Tue, 22 May 2007 01:44:56 +0000',
			),
			"Australia/Brisbane/Local" => array(
				'Australia/Brisbane',
				'2007-5-23 11:44:56',
				true,
				'Wed, 23 May 2007 11:44:56 +1000',
			),
		);
	}

	/**
	 * Testing toRFC822
	 *
	 * @param mixed  $tz		Which time zone? (can be string or numeric
	 * @param string $setTime  What time should be set?
	 * @param bool   $local	Local (true) or GMT?
	 * @param string $expected What should the resulting time string look like?
	 *
	 * @return void
	 * @dataProvider casesToRFC822
	 **/
	public function testToRFC822( $tz, $setTime, $local, $expected )
	{
		$language = JFactory::getLanguage();
		$debug = $language->setDebug(true);
		if( is_null($tz) )
		{
			$testJDate = new JDate($setTime);
		}
		else
		{
			$testJDate = new JDate($setTime, $tz);
		}

		$this->assertThat(
			$testJDate->toRFC822($local),
			$this->equalTo($expected)
		);
		$language->setDebug($debug);
	}

	/**
	 *	Test Cases for toISO8601
	 *
	 * @return array
	 */
	function casesToISO8601()
	{
		return array(
			"basic" => array(
				null,
				'2007-11-20 11:44:56',
				null,
				'2007-11-20T11:44:56+00:00',
			),
			"-1GMT" => array(
				-1,
				'2007-11-20 11:44:56',
				false,
				'2007-11-20T12:44:56+00:00',
			),
			"-1local" => array(
				-1,
				'2007-11-20 11:44:56',
				true,
				'2007-11-20T11:44:56-01:00',
			),
			"Atlantic/AzoresGMT" => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				null,
				'2007-11-20T12:44:56+00:00',
			),
			"Atlantic/AzoresLocal" => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				true,
				'2007-11-20T11:44:56-01:00',
			),
			"8GMT" => array(
				8,
				'2007-05-20 11:44:56',
				null,
				'2007-05-20T03:44:56+00:00',
			),
			"8local" => array(
				8,
				'2007-05-20 11:44:56',
				true,
				'2007-05-20T11:44:56+08:00',
			),
			"Australia/BrisbaneGMT" => array(
				'Australia/Brisbane',
				'2007-5-20 11:44:56',
				null,
				'2007-05-20T01:44:56+00:00',
			),
			"Australia/Brisbane/Local" => array(
				'Australia/Brisbane',
				'2007-5-20 11:44:56',
				true,
				'2007-05-20T11:44:56+10:00',
			),
		);
	}

	/**
	 * Testing toISO8601
	 *
	 * @param mixed  $tz		Which time zone? (can be string or numeric
	 * @param string $setTime  What time should be set?
	 * @param bool   $local	Local (true) or GMT?
	 * @param string $expected What should the resulting time string look like?
	 *
	 * @return void
	 * @dataProvider casesToISO8601
	 **/
	public function testToISO8601( $tz, $setTime, $local, $expected )
	{
		if( is_null($tz) )
		{
			$testJDate = new JDate($setTime);
		}
		else
		{
			$testJDate = new JDate($setTime, $tz);
		}

		$this->assertThat(
			$testJDate->toISO8601($local),
			$this->equalTo($expected)
		);
	}

	/**
	 *	Test Cases for toMySQL
	 *
	 * @return array
	 */
	function casesToMySQL()
	{
		return array(
			"basic" => array(
				null,
				'2007-11-20 11:44:56',
				null,
				'2007-11-20 11:44:56',
			),
			"-1GMT" => array(
				-1,
				'2007-11-20 11:44:56',
				false,
				'2007-11-20 12:44:56',
			),
			"-1local" => array(
				-1,
				'2007-11-20 11:44:56',
				true,
				'2007-11-20 11:44:56',
			),
			"Atlantic/AzoresGMT" => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				null,
				'2007-11-20 12:44:56',
			),
			"Atlantic/AzoresLocal" => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				true,
				'2007-11-20 11:44:56',
			),
			"8GMT" => array(
				8,
				'2007-05-20 11:44:56',
				null,
				'2007-05-20 03:44:56',
			),
			"8local" => array(
				8,
				'2007-05-20 11:44:56',
				true,
				'2007-05-20 11:44:56',
			),
			"Australia/BrisbaneGMT" => array(
				'Australia/Brisbane',
				'2007-5-20 11:44:56',
				null,
				'2007-05-20 01:44:56',
			),
			"Australia/Brisbane/Local" => array(
				'Australia/Brisbane',
				'2007-5-20 11:44:56',
				true,
				'2007-05-20 11:44:56',
			),
		);
	}

	/**
	 * Testing toMySQL
	 *
	 * @param mixed  $tz		Which time zone? (can be string or numeric
	 * @param string $setTime  What time should be set?
	 * @param bool   $local	Local (true) or GMT?
	 * @param string $expected What should the resulting time string look like?
	 *
	 * @return void
	 * @dataProvider casesToMySQL
	 **/
	public function testToMySQL($tz, $setTime, $local, $expected )
	{
		if( is_null($tz) )
		{
			$testJDate = new JDate($setTime);
		}
		else
		{
			$testJDate = new JDate($setTime, $tz);
		}

		$this->assertThat(
			$testJDate->toMySQL($local),
			$this->equalTo($expected)
		);
	}

	/**
	 *	Test Cases for toUnix
	 *
	 * @return array
	 */
	function casesToUnix()
	{
		return array(
			"basic" => array(
				null,
				'2007-11-20 11:44:56',
				1195559096,
			),
			"-1GMT" => array(
				-1,
				'2007-11-20 11:44:56',
				1195562696,
			),
			"-1local" => array(
				-1,
				'2007-11-20 11:44:56',
				1195562696,
			),
			"Atlantic/AzoresGMT" => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				1195562696,
			),
			"Atlantic/AzoresLocal" => array(
				'Atlantic/Azores',
				'2007-11-20 11:44:56',
				1195562696,
			),
			"8GMT" => array(
				8,
				'2007-05-20 11:44:56',
				1179632696,
			),
			"8local" => array(
				8,
				'2007-05-20 11:44:56',
				1179632696,
			),
			"Australia/BrisbaneGMT" => array(
				'Australia/Brisbane',
				'2007-5-20 11:44:56',
				1179625496,
			),
			"Australia/Brisbane/Local" => array(
				'Australia/Brisbane',
				'2007-5-20 11:44:56',
				1179625496,
			),
		);
	}

	/**
	 * Testing toUnix
	 *
	 * @param mixed  $tz		Which time zone? (can be string or numeric
	 * @param string $setTime  What time should be set?
	 * @param string $expected What should the resulting time string look like?
	 *
	 * @return void
	 * @dataProvider casesToUnix
	 **/
	public function testToUnix($tz, $setTime, $expected )
	{
		if( is_null($tz) )
		{
			$testJDate = new JDate($setTime);
		}
		else
		{
			$testJDate = new JDate($setTime, $tz);
		}

		$this->assertThat(
			$testJDate->toUnix(),
			$this->equalTo($expected)
		);
	}

	/**
	 *	Test Cases for setTimezone
	 *
	 * @return array
	 */
	function casesSetTimezone()
	{
		return array(
			"New_York" => array(
				'America/New_York',
				'Thu, 20 Dec 2007 11:44:56 -0500',
			),
			"Chicago" => array(
				'America/Chicago',
				'Thu, 20 Dec 2007 10:44:56 -0600',
			),
			"Los_Angeles" => array(
				'America/Los_Angeles',
				'Thu, 20 Dec 2007 08:44:56 -0800',
			),
			"Isle of Man" => array(
				'Europe/Isle_of_Man',
				'Thu, 20 Dec 2007 16:44:56 +0000',
			),
			"Berlin" => array(
				'Europe/Berlin',
				'Thu, 20 Dec 2007 17:44:56 +0100',
			),
			"Pacific/Port_Moresby" => array(
				'Pacific/Port_Moresby',
				'Fri, 21 Dec 2007 02:44:56 +1000',
			),
		);
	}

	/**
	 * Testing setTimezone
	 *
	 * @param string $tz		Which Time Zone should it be?
	 * @param string $expected What should the resulting time string look like?
	 *
	 * @return void
	 * @dataProvider casesSetTimezone
	 **/
	public function testSetTimezone( $tz, $expected )
	{
		$this->object->setTimezone(new DateTimeZone($tz));
		$this->assertThat(
			$this->object->format('r', true),
			$this->equalTo($expected)
		);
	}
}
