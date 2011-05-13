<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
require_once JPATH_PLATFORM.'/joomla/html/html/number.php';

/**
 * Test class for JHtmlNumberTest.
 *
 * @since  11.1
 */
class JHtmlNumberTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @return	array
	 *
	 * @since   11.1
	 */
	public function dataTestBytes()
	{
		return array(
			// Element order: result, bytes, unit, precision
			array(
				'1 b',
				1,
			),
			array(
				'1 kb',
				1024,
			),
			array(
				'1 MB',
				1024*1024,
			),
			array(
				'1 GB',
				1024*1024*1024,
			),
			array(
				'1 TB',
				1024*1024*1024*1024,
			),
			array(
				'1 PB',
				1024*1024*1024*1024*1024,
			),

			// Test units.
			array(
				'1024 TB',
				1024*1024*1024*1024*1024,
				'TB',
			),
			array(
				'1048576 GB',
				1024*1024*1024*1024*1024,
				'GB',
			),
			array(
				'1073741824 MB',
				1024*1024*1024*1024*1024,
				'MB',
			),
			array(
				'1099511627776 kb',
				1024*1024*1024*1024*1024,
				'kb',
			),
			array(
				'1.1258999068426E+15 b',
				1024*1024*1024*1024*1024,
				'b',
			),

			// Test precision
			array(
				'1.33 kb',
				1357,
			),
			array(
				'1.3 kb',
				1357,
				null,
				1
			),
			array(
				'1.33 kb',
				1357,
				null,
				2
			),
			array(
				'1.325 kb',
				1357,
				null,
				3
			),
			array(
				'1.3252 kb',
				1357,
				null,
				4
			),
		);
	}

	/**
	 * Tests the JHtmlNumber::bytes method.
	 *
	 * @param	string	$result
	 * @param	int		$btyes
	 * @param	string	$unit
	 * @param	int		$precision
	 *
	 * @dataProvider dataTestBytes
	 */
	public function testBytes($result, $bytes, $unit = 'auto', $precision = 2)
	{
		$this->assertThat(
			JHtmlNumber::bytes($bytes, $unit, $precision),
			$this->equalTo($result)
		);
	}
}