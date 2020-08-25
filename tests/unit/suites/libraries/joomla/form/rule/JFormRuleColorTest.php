<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JFormRuleColor
 *
 * @since       1.7.0
 */
class JFormRuleColorTest extends TestCase
{
	/**
	 * Data provider for the failure test case
	 *
	 * @return  array
	 */
	public function casesRuleFailure()
	{
		return array(
			'bogus'        => array('bogus'),
			'#GGGGGG'      => array('#GGGGGG'),
			'FFFFFF'       => array('FFFFFF'),
			'#GGG'         => array('#GGG'),
		);
	}

	/**
	 * Data provider for the success test case
	 *
	 * @return  array
	 */
	public function casesRuleSuccess()
	{
		return array(
			'#000000' => array('#000000'),
			'#000'    => array('#000'),
			'#FFFFFF' => array('#FFFFFF'),
			'#EEE'    => array('#EEE'),
			'#A0A0A0' => array('#A0A0A0'),
			'empty string' => array(''),
		);
	}

	/**
	 * @testdox  The color rule fails values that are not valid hexadecimal color codes
	 *
	 * @param   mixed  $value  The value to test
	 *
	 * @dataProvider  casesRuleFailure
	 */
	public function testRuleFailure($value)
	{
		$rule = new JFormRuleColor;

		$this->assertFalse($rule->test(new SimpleXMLElement('<form><field name="color" /></form>'), $value));
	}

	/**
	 * @testdox  The color rule passes values that are valid hexadecimal color codes
	 *
	 * @param   mixed  $value  The value to test
	 *
	 * @dataProvider  casesRuleSuccess
	 */
	public function testRuleSuccess($value)
	{
		$rule = new JFormRuleColor;

		$this->assertTrue($rule->test(new SimpleXMLElement('<form><field name="color" /></form>'), $value));
	}
}
