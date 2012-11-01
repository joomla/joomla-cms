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
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @since       11.1
 */
class JFormRuleEmailTest extends TestCase
{
	/**
	 * set up for testing
	 *
	 * @return void
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
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
	}

	/**
	 * Test the JFormRuleEmail::test method.
	 *
	 * @return void
	 */
	public function testEmail()
	{
		$rule = new JFormRuleEmail;
		$xml = simplexml_load_string('<form><field name="email1" /><field name="email2" unique="true" /></form>');

		// Test fail conditions.

		$this->assertThat(
			$rule->test($xml->field[0], 'bogus'),
			$this->isFalse(),
			'Line:' . __LINE__ . ' The rule should fail and return false.'
		);

		// Test pass conditions.

		$this->assertThat(
			$rule->test($xml->field[0], 'me@example.com'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The basic rule should pass and return true.'
		);

		$this->markTestIncomplete('More tests required');

		/*
		 TODO: Need to test the "field" attribute which adds to the unqiue test where clause.
		 TODO: Is the regex as robust/same as the mail class validation check?
		 TODO: Database error is prevents the following tests from working properly.
		*/

		$this->assertThat(
			$rule->test($xml->field[1], 'me@example.com'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The unique rule should pass and return true.'
		);
	}

	/**
	 * Test...
	 *
	 * @return array
	 */
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
	 * Test...
	 *
	 * @param   string  $emailAddress    @todo
	 * @param   string  $expectedResult  @todo
	 *
	 * @dataProvider emailData
	 *
	 * @return void
	 */
	public function testEmailData($emailAddress, $expectedResult)
	{
		$rule = new JFormRuleEmail;
		$xml = simplexml_load_string('<form><field name="email1" /></form>');
		$this->assertThat(
			$rule->test($xml->field[0], $emailAddress),
			$this->equalTo($expectedResult),
			$emailAddress . ' should have returned ' . ($expectedResult ? 'true' : 'false') . ' but did not'
		);
	}
}
