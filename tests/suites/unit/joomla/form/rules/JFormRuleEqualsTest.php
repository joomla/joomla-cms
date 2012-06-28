<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JForm.
 *
 * @package		Joomla.UnitTest
 * @subpackage  Form
 *
 */
class JFormRuleEqualsTest extends TestCase
{
	/**
	 * set up for testing
	 *
	 * @return void
	 */
	public function setUp()
	{
		require_once JPATH_PLATFORM.'/joomla/form/rules/equals.php';
	}

	/**
	 * Test the JFormRuleEquals::test method.
	 */
	public function testEquals()
	{
		// Initialise variables.

		$rule = new JFormRuleEquals;
		$xml = simplexml_load_string('<form><field name="foo" /></form>');

		// Test fail conditions.

		// Test pass conditions.

		$this->markTestIncomplete();
	}
}
