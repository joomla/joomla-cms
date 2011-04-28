<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.UnitTest
 */

defined('JPATH_PLATFORM') or die;

/**
 * Test class for JForm.
 *
 * @package		Joomla.UnitTest
 * @subpackage	Form
 *
 */
class JFormRuleTelTest extends JoomlaTestCase
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
		require_once JPATH_PLATFORM.'/joomla/form/rules/tel.php';
	}

	/**
	 * Test the JFormRuleTel::test method.
	 */
	public function testTel()
	{
		// Initialise variables.

		$rule = new JFormRuleTel;
		$xml = simplexml_load_string('<form><field name="tel1" plan="NANP" />
			<field name="tel2" plan="ITU-T" /><field name="tel3" plan="EPP" />
			<field name="tel4" /></form>',
			'JXMLElement');

		// Test fail conditions NANP.
		$this->assertThat(
			$rule->test($xml->field[0], 'bogus'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[0], '123451234512'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[0], 'anything_5555555555'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[0], '5555555555_anything'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);

		// Test fail conditions ITU-T.
		$this->assertThat(
			$rule->test($xml->field[1], 'bogus'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[1], '123451234512'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[1], 'anything_5555555555'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[1], '5555555555_anything'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[1], '1 2 3 4 5 6 '),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[1], '5552345678'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[1], 'anything_555.5555555'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[1], '555.5555555_anything'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);

		// Test fail conditions EPP.
		$this->assertThat(
			$rule->test($xml->field[2], 'bogus'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[2], '12345123451234512345'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[2], '123.1234'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[2], '23.1234'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[2], '3.1234'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		// Test fail conditions no plan.
		$this->assertThat(
			$rule->test($xml->field[3], 'bogus'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);

		$this->assertThat(
			$rule->test($xml->field[3], 'anything_555.5555555'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[3], '555.5555555x555_anything'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[3], '.5555555'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[3], '555.'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		$this->assertThat(
			$rule->test($xml->field[3], '1 2 3 4 5 6 '),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);
		// Test pass conditions.
		//For NANP
		$this->assertThat(
			$rule->test($xml->field[0], '(555) 234-5678'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[0], '1-555-234-5678'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[0], '+1-555-234-5678'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[0], '555-234-5678'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[0], '1-555-234-5678'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[0], '1 555 234 5678'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		//For ITU-T
		$this->assertThat(
			$rule->test($xml->field[1], '+555 234 5678'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[1], '+123 555 234 5678'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[1], '+2 52 34 55'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[1], '+5552345678'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);

		//For EPP
		$this->assertThat(
			$rule->test($xml->field[2], '+123.1234'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[2], '+23.1234'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[2], '+3.1234'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[2], '+3.1234x555'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);

		//For no plan
		$this->assertThat(
			$rule->test($xml->field[3], '555 234 5678'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[3], '+123 555 234 5678'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[3], '+2 52 34 55'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[3], '5552345678'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[3], '+5552345678'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[3], '1 2 3 4 5 6 7'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
		$this->assertThat(
			$rule->test($xml->field[3], '123451234512'),
			$this->isTrue(),
			'Line:'.__LINE__.' The rule should pass and return true.'
		);
	}
}