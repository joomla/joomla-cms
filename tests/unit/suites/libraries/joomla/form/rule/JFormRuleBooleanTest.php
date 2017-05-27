<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFormRuleBoolean
 *
 * @since       11.1
 */
class JFormRuleBooleanTest extends TestCase
{
	/**
	 * Data provider for the failure test case
	 *
	 * @return  array
	 */
	public function casesRuleFailure()
	{
		return array(
			'bogus'                  => array('bogus'),
			'0_anything'             => array('0_anything'),
			'anything_1_anything'    => array('anything_1_anything'),
			'anything_true_anything' => array('anything_true_anything'),
			'anything_false'         => array('anything_false'),
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
			'integer zero' => array(0),
			'string zero'  => array('0'),
			'integer one'  => array(1),
			'string one'   => array('1'),
			'string true'  => array('true'),
			'string false' => array('false'),
		);
	}

	/**
	 * @testdox  The boolean rule fails values that do not represent boolean values
	 *
	 * @param   mixed  $value  The value to test
	 *
	 * @dataProvider  casesRuleFailure
	 */
	public function testRuleFailure($value)
	{
		$rule = new JFormRuleBoolean;

		$this->assertFalse($rule->test(new SimpleXMLElement('<form><field name="foo" /></form>'), $value));
	}

	/**
	 * @testdox  The boolean rule passes values that represent boolean values
	 *
	 * @param   mixed  $value  The value to test
	 *
	 * @dataProvider  casesRuleSuccess
	 */
	public function testRuleSuccess($value)
	{
		$rule = new JFormRuleBoolean;

		$this->assertTrue($rule->test(new SimpleXMLElement('<form><field name="foo" /></form>'), $value));
	}
}
