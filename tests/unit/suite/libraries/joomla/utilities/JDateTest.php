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
}
