<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JForm.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @since       11.1
 */
class JFormRuleOptionsTest extends TestCase
{
	/**
	 * Set up for testing
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();
	}

	/**
	 * Tear down test
	 *
	 * @return void
	 *
	 * @since   11.1
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
	}

	/**
	 * Test the JFormRuleEmail::test method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testEmail()
	{
		$rule = new JFormRuleOptions;
		$xml = simplexml_load_string(
			'<form><field name="field1"><option value="value1">Value1</option><option value="value2">Value2</option></field></form>'
		);

		// Test fail conditions.

		$this->assertThat(
			$rule->test($xml->field[0], 'bogus'),
			$this->isFalse(),
			'Line:' . __LINE__ . ' The rule should fail and return false.'
		);

		// Test pass conditions.

		$this->assertThat(
			$rule->test($xml->field[0], 'value1'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' value1 should pass and return true.'
		);

		$this->assertThat(
			$rule->test($xml->field[0], 'value2'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' value2 should pass and return true.'
		);
	}
}
