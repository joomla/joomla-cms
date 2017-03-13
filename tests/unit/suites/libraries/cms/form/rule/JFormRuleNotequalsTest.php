<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFormRuleNotequals.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       3.4
 */
class JFormRuleNotequalsTest extends TestCase
{
	/**
	 * Test the JFormRuleNotequals::test method.
	 *
	 * @return void
	 *
	 * @covers  JFormRuleNotequals::test
	 * @since   3.4
	 */
	public function testNotequals()
	{
		$rule = new JFormRuleNotequals;
		$xml = simplexml_load_string('<form><field name="foo" field="notequalsfield" /></form>');
		$input = new Joomla\Registry\Registry;
		$input->set('notequalsfield', 'testvalue');

		$this->assertTrue($rule->test($xml->field, 'test', null, $input));

		$this->assertFalse($rule->test($xml->field, 'testvalue', null, $input));
	}

	/**
	 * Test the JFormRuleNotequals::test method with UnexpectedValueException
	 *
	 * @return void
	 *
	 * @covers  JFormRuleNotequals::test
	 * @expectedException  UnexpectedValueException
	 * @since   3.4
	 */
	public function testNotequalsUnexpectedValueException()
	{
		$rule = new JFormRuleNotequals;
		$xml = simplexml_load_string('<form><field name="foo" field="notequalsfield" /></form>');
		$input = new Joomla\Registry\Registry;
		$input->set('notequalsfield', 'testvalue');

		$rule->test($xml, 'test', null, $input);
	}

	/**
	 * Test the JFormRuleNotequals::test method with InvalidArgumentException
	 *
	 * @return void
	 *
	 * @covers  JFormRuleNotequals::test
	 * @expectedException  InvalidArgumentException
	 * @since   3.4
	 */
	public function testNotequalsInvalidArgumentException()
	{
		$rule = new JFormRuleNotequals;
		$xml = simplexml_load_string('<form><field name="foo" field="notequalsfield" /></form>');
		$input = new Joomla\Registry\Registry;
		$input->set('notequalsfield', 'testvalue');

		$rule->test($xml->field, 'test');
	}
}
