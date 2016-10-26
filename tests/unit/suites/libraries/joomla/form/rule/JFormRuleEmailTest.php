<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFormRuleEmail.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.1
 */
class JFormRuleEmailTest extends TestCase
{
	/**
	 * Test if a valid EMail is accepted by the JFormRuleEmail::test method.
	 *
	 * @return void
	 */
	public function testValidEmail()
	{
		$rule = new JFormRuleEmail;
		$xml = simplexml_load_string('<form><field name="email1" /><field name="email2" unique="true" /></form>');

		$this->assertTrue($rule->test($xml->field[0], 'me@example.com'));
	}

	/**
	 * Test if an invalid Email result in false for the testing method JFormRuleEmail::test method.
	 *
	 * @return void
	 */
	public function testAnInvalidEmail()
	{
		$rule = new JFormRuleEmail;
		$xml = simplexml_load_string('<form><field name="email1" /><field name="email2" unique="true" /></form>');

		// Test fail conditions.
		$this->assertFalse($rule->test($xml->field[0], 'ThisIsNotALoveSong'));
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
			array('test3@example.c', true),
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
