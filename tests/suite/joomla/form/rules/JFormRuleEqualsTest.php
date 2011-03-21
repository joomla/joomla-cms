<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Test class for JForm.
 *
 * @package		Joomla.UnitTest
 * @subpackage	Form
 *
 */
class JFormRuleEqualsTest extends JoomlaTestCase
{
	/**
	 * set up for testing
	 *
	 * @return void
	 */
	public function setUp()
	{
		jimport('joomla.form.formrule');
		jimport('joomla.utilities.xmlelement');
		require_once JPATH_PLATFORM.'/joomla/form/rules/equals.php';
	}

	/**
	 * Test the JFormRuleEquals::test method.
	 */
	public function testEquals()
	{
		// Initialise variables.

		$rule = new JFormRuleEquals;
		$xml = simplexml_load_string('<form><field name="foo" /></form>', 'JXMLElement');

		// Test fail conditions.

		// Test pass conditions.

		$this->markTestIncomplete();
	}
}