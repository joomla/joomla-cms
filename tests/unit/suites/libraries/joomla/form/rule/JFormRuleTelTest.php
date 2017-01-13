<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFormRuleTel
 *
 * @since       11.1
 */
class JFormRuleTelTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Data provider for the failure test case on the NANP format
	 *
	 * @return  array
	 */
	public function casesRuleFailureNanp()
	{
		return array(
			'bogus'               => array('bogus'),
			'123451234512'        => array('123451234512'),
			'anything_5555555555' => array('anything_5555555555'),
			'5555555555_anything' => array('5555555555_anything'),
		);
	}

	/**
	 * Data provider for the failure test case on the ITU-T format
	 *
	 * @return  array
	 */
	public function casesRuleFailureItuT()
	{
		return array(
			'bogus'                => array('bogus'),
			'123451234512'         => array('123451234512'),
			'anything_5555555555'  => array('anything_5555555555'),
			'5555555555_anything'  => array('5555555555_anything'),
			'1 2 3 4 5 6 '         => array('1 2 3 4 5 6 '),
			'5552345678'           => array('5552345678'),
			'anything_555.5555555' => array('anything_555.5555555'),
			'555.5555555_anything' => array('555.5555555_anything'),
		);
	}

	/**
	 * Data provider for the failure test case on the EPP format
	 *
	 * @return  array
	 */
	public function casesRuleFailureEpp()
	{
		return array(
			'bogus'                => array('bogus'),
			'12345123451234512345' => array('12345123451234512345'),
			'123.1234'             => array('123.1234'),
			'23.1234'              => array('23.1234'),
			'3.1234'               => array('3.1234'),
		);
	}

	/**
	 * Data provider for the failure test case for no specified plan
	 *
	 * @return  array
	 */
	public function casesRuleFailureNoPlan()
	{
		return array(
			'bogus'                    => array('bogus'),
			'anything_555.5555555'     => array('anything_555.5555555'),
			'555.5555555x555_anything' => array('555.5555555x555_anything'),
			'555.'                     => array('555.'),
			'1 2 3 4 5 6 '             => array('1 2 3 4 5 6 '),
		);
	}

	/**
	 * Data provider for the success test case on the NANP format
	 *
	 * @return  array
	 */
	public function casesRuleSuccessNanp()
	{
		return array(
			'(555) 234-5678'  => array('(555) 234-5678'),
			'1-555-234-5678'  => array('1-555-234-5678'),
			'+1-555-234-5678' => array('+1-555-234-5678'),
			'555-234-5678'    => array('555-234-5678'),
			'1 555 234 5678'  => array('1 555 234 5678'),
		);
	}

	/**
	 * Data provider for the success test case on the ITU-T format
	 *
	 * @return  array
	 */
	public function casesRuleSuccessItuT()
	{
		return array(
			'+555 234 5678'     => array('+555 234 5678'),
			'+123 555 234 5678' => array('+123 555 234 5678'),
			'+2 52 34 55'       => array('+2 52 34 55'),
			'+5552345678'       => array('+5552345678'),
		);
	}

	/**
	 * Data provider for the success test case on the EPP format
	 *
	 * @return  array
	 */
	public function casesRuleSuccessEpp()
	{
		return array(
			'+123.1234'   => array('+123.1234'),
			'+23.1234'    => array('+23.1234'),
			'+3.1234'     => array('+3.1234'),
			'+3.1234x555' => array('+3.1234x555'),
		);
	}

	/**
	 * Data provider for the success test case for no specified plan
	 *
	 * @return  array
	 */
	public function casesRuleSuccessNoPlan()
	{
		return array(
			'555 234 5678'      => array('555 234 5678'),
			'+123 555 234 5678' => array('+123 555 234 5678'),
			'+2 52 34 55'       => array('+2 52 34 55'),
			'5552345678'        => array('5552345678'),
			'+5552345678'       => array('+5552345678'),
			'1 2 3 4 5 6 7'     => array('1 2 3 4 5 6 7'),
			'123451234512'      => array('123451234512'),
		);
	}

	/**
	 * @testdox  The tel rule fails values that do not pass NANP guidelines
	 *
	 * @param   mixed  $value  The value to test
	 *
	 * @dataProvider  casesRuleFailureNanp
	 */
	public function testRuleFailureNanp($value)
	{
		$rule = new JFormRuleTel;

		$this->assertFalse($rule->test(new SimpleXMLElement('<field name="tel" plan="NANP" />'), $value));
	}

	/**
	 * @testdox  The tel rule fails values that do not pass ITU-T guidelines
	 *
	 * @param   mixed  $value  The value to test
	 *
	 * @dataProvider  casesRuleFailureItuT
	 */
	public function testRuleFailureItuT($value)
	{
		$rule = new JFormRuleTel;

		$this->assertFalse($rule->test(new SimpleXMLElement('<field name="tel" plan="ITU-T" />'), $value));
	}

	/**
	 * @testdox  The tel rule fails values that do not pass EPP guidelines
	 *
	 * @param   mixed  $value  The value to test
	 *
	 * @dataProvider  casesRuleFailureEpp
	 */
	public function testRuleFailureEpp($value)
	{
		$rule = new JFormRuleTel;

		$this->assertFalse($rule->test(new SimpleXMLElement('<field name="tel" plan="EPP" />'), $value));
	}

	/**
	 * @testdox  The tel rule fails values that do not pass when no plan is specified
	 *
	 * @param   mixed  $value  The value to test
	 *
	 * @dataProvider  casesRuleFailureNoPlan
	 */
	public function testRuleFailureNoPlan($value)
	{
		$rule = new JFormRuleTel;

		$this->assertFalse($rule->test(new SimpleXMLElement('<field name="tel" />'), $value));
	}

	/**
	 * @testdox  The tel rule passes values that meet NANP guidelines
	 *
	 * @param   mixed  $value  The value to test
	 *
	 * @dataProvider  casesRuleSuccessNanp
	 */
	public function testRuleSuccessNanp($value)
	{
		$rule = new JFormRuleTel;

		$this->assertTrue($rule->test(new SimpleXMLElement('<field name="tel" plan="NANP" />'), $value));
	}

	/**
	 * @testdox  The tel rule passes values that meet ITU-T guidelines
	 *
	 * @param   mixed  $value  The value to test
	 *
	 * @dataProvider  casesRuleSuccessItuT
	 */
	public function testRuleSuccessItuT($value)
	{
		$rule = new JFormRuleTel;

		$this->assertTrue($rule->test(new SimpleXMLElement('<field name="tel" plan="ITU-T" />'), $value));
	}

	/**
	 * @testdox  The tel rule passes values that meet EPP guidelines
	 *
	 * @param   mixed  $value  The value to test
	 *
	 * @dataProvider  casesRuleSuccessEpp
	 */
	public function testRuleSuccessEpp($value)
	{
		$rule = new JFormRuleTel;

		$this->assertTrue($rule->test(new SimpleXMLElement('<field name="tel" plan="EPP" />'), $value));
	}

	/**
	 * @testdox  The tel rule passes values that pass when no plan is specified
	 *
	 * @param   mixed  $value  The value to test
	 *
	 * @dataProvider  casesRuleSuccessNoPlan
	 */
	public function testRuleSuccessNoPlan($value)
	{
		$rule = new JFormRuleTel;

		$this->assertTrue($rule->test(new SimpleXMLElement('<field name="tel" />'), $value));
	}
}
