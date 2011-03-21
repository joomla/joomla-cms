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
class JFormRuleEmailTest extends JoomlaTestCase
{
	/**
	 * set up for testing
	 *
	 * @return void
	 */
	public function setUp()
	{
		$this->saveFactoryState();
		jimport('joomla.form.formrule');
		jimport('joomla.utilities.xmlelement');
		require_once JPATH_PLATFORM.'/joomla/form/rules/email.php';
	}

	/**
	 * Tear down test
	 *
	 * @return void
	 */
	function tearDown()
	{
		$this->restoreFactoryState();
	}

	/**
	 * Test the JFormRuleEmail::test method.
	 */
	public function testEmail()
	{
		// Initialise variables.

		$rule = new JFormRuleEmail;
		$xml = simplexml_load_string('<form><field name="email1" /><field name="email2" unique="true" /></form>', 'JXMLElement');

		// Test fail conditions.

		$this->assertThat(
			$rule->test($xml->field[0], 'bogus'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);

		// Test pass conditions.

		$this->assertThat(
			$rule->test($xml->field[0], 'me@example.com'),
			$this->isTrue(),
			'Line:'.__LINE__.' The basic rule should pass and return true.'
		);

		$this->markTestIncomplete('More tests required');

		// TODO: Need to test the "field" attribute which adds to the unqiue test where clause.
		// TODO: Is the regex as robust/same as the mail class validation check?
		// TODO: Database error is prevents the following tests from working properly.
		// TODO:

		$this->assertThat(
			$rule->test($xml->field[1], 'me@example.com'),
			$this->isTrue(),
			'Line:'.__LINE__.' The unique rule should pass and return true.'
		);
	}

	public function emailData()
	{
		return array(
			array('test@example.com', true),
			array('badaddress.com', false),
			array('firstnamelastname@domain.tld', true),
			array('firstname+lastname@domain.tld', true),
			array('firstname+middlename+lastname@domain.tld', true),
			array('firstnamelastname@subdomain.domain.tld', true),
			array('firstname+lastname@subdomain.domain.tld', true),
			array('firstname+middlename+lastname@subdomain.domain.tld', true)
		);
	}

	/**
	 * @dataProvider emailData
	 */
	public function testEmailData($emailAddress, $expectedResult)
	{
		$rule = new JFormRuleEmail;
		$xml = simplexml_load_string('<form><field name="email1" /></form>', 'JXMLElement');
		$this->assertThat(
			$rule->test($xml->field[0], $emailAddress),
			$this->equalTo($expectedResult),
			$emailAddress.' should have returned '.($expectedResult ? 'true' : 'false').' but did not'
		);
	}
}
