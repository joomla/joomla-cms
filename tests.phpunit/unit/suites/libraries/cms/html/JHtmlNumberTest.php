<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHtmlNumberTest.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       3.1
 */
class JHtmlNumberTest extends TestCase
{
	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   3.1
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
				1024 * 1024,
			),
			array(
				'1 GB',
				1024 * 1024 * 1024,
			),
			array(
				'1 TB',
				1024 * 1024 * 1024 * 1024,
			),
			array(
				'1 PB',
				1024 * 1024 * 1024 * 1024 * 1024,
			),
			array(
				'0',
				0,
			),

			// Test units.
			array(
				'1024 TB',
				1024 * 1024 * 1024 * 1024 * 1024,
				'TB',
			),
			array(
				'1048576 GB',
				1024 * 1024 * 1024 * 1024 * 1024,
				'GB',
			),
			array(
				'1073741824 MB',
				1024 * 1024 * 1024 * 1024 * 1024,
				'MB',
			),
			array(
				'1099511627776 kb',
				1024 * 1024 * 1024 * 1024 * 1024,
				'kb',
			),
			array(
				'1.1258999068426E+15 b',
				1024 * 1024 * 1024 * 1024 * 1024,
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
	 * @param   string   $result     @todo
	 * @param   integer  $bytes      @todo
	 * @param   string   $unit       @todo
	 * @param   integer  $precision  @todo
	 *
	 * @return  void
	 *
	 * @since        3.1
	 * @dataProvider dataTestBytes
	 */
	public function testBytes($result, $bytes, $unit = 'auto', $precision = 2)
	{
		$this->assertThat(
			JHtml::_('number.bytes', $bytes, $unit, $precision),
			$this->equalTo($result)
		);
	}
}
