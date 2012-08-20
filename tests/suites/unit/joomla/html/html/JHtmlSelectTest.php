<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/html/select.php';
require_once 'TestHelpers/JHtmlSelect-helper-dataset.php';

/**
 * Test class for JHtmlSelect.
 *
 * @since  11.1
 */
class JHtmlSelectTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getOptionsData()
	{
		return JHtmlSelectTest_DataSet::$optionsTest;
	}

	/**
	 * @todo Implement testBooleanlist().
	 */
	public function testBooleanlist()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testGenericlist().
	 */
	public function testGenericlist()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testGroupedlist().
	 */
	public function testGroupedlist()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testIntegerlist().
	 */
	public function testIntegerlist()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testOptgroup().
	 */
	public function testOptgroup()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testOption().
	 */
	public function testOption()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @return  void
	 *
	 * @dataProvider  getOptionsData
	 * @since   11.3
	 */
	public function testOptions($expected, $arr, $optKey = 'value', $optText = 'text', $selected = null, $translate = false)
	{
		$this->assertEquals(
			$expected,
			JHtmlSelect::options($arr, $optKey, $optText, $selected, $translate)
		);

		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been completely implemented yet.'
		);
	}

	/**
	 * @todo Implement testRadiolist().
	 */
	public function testRadiolist()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}
}
