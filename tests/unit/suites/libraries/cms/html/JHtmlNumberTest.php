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
				'1 kB',
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
				'1 YB',
				1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
				'auto',
			),
			array(
				'1 YB',
				1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
				'YB',
			),
			array(
				'1024 ZB',
				1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
				'ZB',
			),
			array(
				'1048576 EB',
				1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
				'EB',
			),
			array(
				'1 PB',
				1024 * 1024 * 1024 * 1024 * 1024,
				'PB',
			),
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
				'1099511627776 kB',
				1024 * 1024 * 1024 * 1024 * 1024,
				'kB',
			),
			array(
				'1.1258999068426E+15 b',
				1024 * 1024 * 1024 * 1024 * 1024,
				'b',
			),
			array(
				'1.1258999068426E+15',
				1024 * 1024 * 1024 * 1024 * 1024,
				'',
			),

			// Test precision
			array(
				'1.33 kB',
				1357,
			),
			array(
				'1.3 kB',
				1357,
				null,
				1
			),
			array(
				'1.33 kB',
				1357,
				null,
				2
			),
			array(
				'1.325 kB',
				1357,
				null,
				3
			),
			array(
				'1.3252 kB',
				1357,
				null,
				4
			),

			// Test unit suffixed inputs
			array(
				'1 MB',
				'1024K',
			),
			array(
				'1024 MB',
				'1 GB',
				'MB'
			),
			array(
				'10.5 GB',
				'1.0752E+4 MB',
				'GB'
			),

			// Test IEC aware input
			array(
				'1024000',
				'1024 KB',
				'',
				2,
				true
			),
			array(
				'1048576',
				'1024 KiB',
				'',
				2,
				true
			),

			// Test IEC aware output with automatic unit
			array(
				'1 MB',
				1000 * 1000,
				'auto',
				2,
				true
			),

			// Test automatic binary units output
			array(
				'1 MiB',
				1024 * 1024,
				'binary',
				2,
				true
			),
			array(
				'1 MiB',
				1024 * 1024,
				'binary',
				2,
				false
			),

			// Test IEC aware specific unit output
			array(
				'1000 KiB',
				'1024 KB',
				'KiB',
				2,
				true
			),
			array(
				'1048.58 kB',
				'1024 KiB',
				'kB',
				2,
				true
			),
		);
	}

	/**
	 * Tests the JHtmlNumber::bytes method.
	 *
	 * @param   string   $result     The expected result to match against.
	 * @param   string   $bytes      The number of bytes. Can be either numeric or suffixed format: 32M, 60K, 12G or 812b
	 * @param   string   $unit       The type of unit to return, few special values are:
	 *                               Blank string '' for no unit,
	 *                               'auto' to choose automatically (default)
	 *                               'binary' to choose automatically but use binary unit prefix
	 * @param   integer  $precision  The number of digits to be used after the decimal place.
	 * @param   bool     $iec        Whether to be aware of IEC standards. IEC prefixes are always acceptable in input.
	 *                               When IEC is ON:  KiB = 1024 B, KB = 1000 B
	 *                               When IEC is OFF: KiB = 1024 B, KB = 1024 B
	 *
	 * @return  void
	 *
	 * @since        3.1
	 * @dataProvider dataTestBytes
	 */
	public function testBytes($result, $bytes, $unit = 'auto', $precision = 2, $iec = false)
	{
		$this->assertThat(
			JHtml::_('number.bytes', $bytes, $unit, $precision, $iec),
			$this->equalTo($result)
		);
	}
}
