<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/TestHelpers/JHtmlSelect-helper-dataset.php';

/**
 * Test class for JHtmlSelect.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       3.1
 */
class JHtmlSelectTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getOptionsData()
	{
		return JHtmlSelectTest_DataSet::$optionsTest;
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getOptionData()
	{
		return JHtmlSelectTest_DataSet::$optionTest;
	}

	/**
	 * Test...
	 *
	 * @todo Implement testBooleanlist().
	 *
	 * @return void
	 */
	public function testBooleanlist()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGenericlist().
	 *
	 * @return void
	 */
	public function testGenericlist()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGroupedlist().
	 *
	 * @return void
	 */
	public function testGroupedlist()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testIntegerlist().
	 *
	 * @return void
	 */
	public function testIntegerlist()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testOptgroup().
	 *
	 * @return void
	 */
	public function testOptgroup()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @param   object  $expected  @todo
	 * @param   string  $value     @todo
	 * @param   string  $text      @todo
	 * @param   array   $optKey    @todo
	 * @param   string  $optText   @todo
	 * @param   bool    $disable   @todo
	 *
	 * @return void
	 *
	 * @dataProvider  getOptionData
	 */
	public function testOption($expected, $value, $text = '', $optKey = 'value', $optText = 'text', $disable = false)
	{
		$this->assertEquals(
			(object) $expected,
			JHtml::_('select.option', $value, $text, $optKey, $optText, $disable)
		);
	}

	/**
	 * Test...
	 *
	 * @param   string  $expected   @todo
	 * @param   array   $arr        @todo
	 * @param   string  $optKey     @todo
	 * @param   string  $optText    @todo
	 * @param   null    $selected   @todo
	 * @param   bool    $translate  @todo
	 *
	 * @return  void
	 *
	 * @since         3.1
	 * @dataProvider  getOptionsData
	 */
	public function testOptions($expected, $arr, $optKey = 'value', $optText = 'text', $selected = null, $translate = false)
	{
		$this->assertEquals(
			$expected,
			JHtml::_('select.options', $arr, $optKey, $optText, $selected, $translate)
		);

		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been completely implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testRadiolist().
	 *
	 * @return void
	 */
	public function testRadiolist()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
