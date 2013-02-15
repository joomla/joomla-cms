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
		 TODO: Need to test the "field" attribute which adds to the unique test where clause.
		 TODO: Database error is prevents the following tests from working properly.
		*/
		$this->assertThat(
			$rule->test($xml->field[1], 'me@example.com'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The unique rule should pass and return true.'
		);
	}

	/**
	 * Data Provider  for email rule test with no multiple attribute and no tld attribute
	 *
	 * @return array
	 *
	 * @since 11.1
	 */
	public function emailData1()
	{
		return array(
			array('test@example.com', true),
			array('badaddress.com', false),
			array('firstnamelastname@domain.tld', true),
			array('firstname+lastname@domain.tld', true),
			array('firstname+middlename+lastname@domain.tld', true),
			array('firstnamelastname@subdomain.domain.tld', true),
			array('firstname+lastname@subdomain.domain.tld', true),
			array('firstname+middlename+lastname@subdomain.domain.tld', true),
			array('firstname@localhost', true)
		);
	}

	/**
	 * Test the email rule
	 *
	 * @param   string   $emailAddress    Email to be tested
	 * @param   boolean  $expectedResult  Result of test
	 *
	 * @dataProvider emailData1
	 *
	 * @return void
	 *
	 * @since 11.1
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

	/**
	 * Data Provider  for email rule test with multiple attribute and no tld attribute
	 *
	 * @return array
	 *
	 * @since 12.3
	 */
	public function emailData2()
	{
		return array(
			array('test@example.com', true),
			array('test@example.com,test2@example.com,test3@localhost', true),
		);
	}

	/**
	 * Test the email rule with the multiple attribute
	 *
	 * @param   string   $emailAddress    Email to be tested
	 * @param   boolean  $expectedResult  Result of test
	 *
	 * @dataProvider emailData2
	 *
	 * @return void
	 *
	 * @since 12.3
	 */
	public function testEmailData2($emailAddress, $expectedResult)
	{
		$rule = new JFormRuleEmail;
		$xml = simplexml_load_string('<form><field name="email1" multiple="multiple" /></form>');
		$this->assertThat(
			$rule->test($xml->field[0], $emailAddress),
			$this->equalTo($expectedResult),
			$emailAddress . ' should have returned ' . ($expectedResult ? 'true' : 'false') . ' but did not'
		);
	}
	/**
	 * Data Provider  for email rule test with tld attribute
	 *
	 * @return array
	 *
	 * @since 12.3
	 */
	public function emailData3()
	{
		return array(
			array('test@example.com', true),
			array('test3@localhost', false),
			array('test3@example.c', false),
			array('test3@example.ca', true),
			array('test3@example.travel', true),
		);
	}

	/**
	 * Test the email rule with the tld attribute
	 *
	 * @param   string   $emailAddress    Email to be tested
	 * @param   boolean  $expectedResult  Result of test
	 *
	 * @dataProvider emailData3
	 *
	 * @return void
	 *
	 * @since 12.3
	 */
	public function testEmailData3($emailAddress, $expectedResult)
	{
		$rule = new JFormRuleEmail;
		$xml = simplexml_load_string('<form><field name="email1" tld="tld" /></form>');
		$this->assertThat(
			$rule->test($xml->field[0], $emailAddress),
			$this->equalTo($expectedResult),
			$emailAddress . ' should have returned ' . ($expectedResult ? 'true' : 'false') . ' but did not'
		);
	}
}
