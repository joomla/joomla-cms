<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFormRuleColor.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.1
 */
class JFormRuleColorTest extends TestCase
{
	/**
	 * Test the JFormRuleColor::test method.
	 *
	 * @return void
	 */
	public function testColor()
	{
		$rule = new JFormRuleColor;
		$xml = simplexml_load_string('<form><field name="color" /></form>');

		// Test fail conditions.
		$this->assertThat(
			$rule->test($xml->field[0], 'bogus'),
			$this->isFalse(),
			'Line:' . __LINE__ . ' The rule should fail and return false.'
		);

		// Test pass conditions.
		$this->assertThat(
			$rule->test($xml->field[0], '#000000'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The basic rule should pass and return true.'
		);
	}

	/**
	 * Test...
	 *
	 * @return array
	 */
	public function colorData()
	{
		return array(
			array('#000000', true),
			array('#', false),
			array('#000', true),
			array('#FFFFFF', true),
			array('#EEE', true),
			array('#A0A0A0', true),
			array('#GGGGGG', false),
			array('FFFFFF', false),
			array('#GGG', false),
			array('', false)
		);
	}

	/**
	 * Test...
	 *
	 * @param   string  $color           @todo
	 * @param   string  $expectedResult  @todo
	 *
	 * @dataProvider colorData
	 *
	 * @return void
	 */
	public function testColorData($color, $expectedResult)
	{
		$rule = new JFormRuleColor;
		$xml = simplexml_load_string('<form><field name="color1" /></form>');
		$this->assertThat(
			$rule->test($xml->field[0], $color),
			$this->equalTo($expectedResult),
			$color . ' should have returned ' . ($expectedResult ? 'true' : 'false') . ' but did not'
		);
	}
}
