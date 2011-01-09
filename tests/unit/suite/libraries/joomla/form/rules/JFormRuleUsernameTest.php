<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JForm.
 *
 * @package		Joomla.UnitTest
 * @subpackage	Form
 *
 */
class JFormRuleUsernameTest extends JoomlaTestCase
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
		require_once JPATH_BASE.'/libraries/joomla/form/rules/username.php';
	}

	/**
	 * Test the JFormRuleUsername::test method.
	 */
	public function testUsername()
	{
		// Initialise variables.

		$rule = new JFormRuleUsername;
		$xml = simplexml_load_string('<form><field name="foo" /></form>', 'JXMLElement');

		// Test fail conditions.

		// Test pass conditions.

		$this->markTestIncomplete();
	}
}