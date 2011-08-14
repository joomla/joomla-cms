<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JForm.
 *
 * @package		Joomla.UnitTest
 * @subpackage  Form
 *
 */
class JFormRuleColorTest extends JoomlaTestCase
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
		require_once JPATH_PLATFORM.'/joomla/form/rules/color.php';
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
	 * Test the JFormRuleColor::test method.
	 */
	public function testColor()
	{
		echo "color";
		
		// Initialise variables.
		$rule = new JFormRuleColor;
		$xml = simplexml_load_string('<form><field name="color" /></form>', 'JXMLElement');

		// Test fail conditions.
		$this->assertThat(
			$rule->test($xml->field[0], 'bogus'),
			$this->isFalse(),
			'Line:'.__LINE__.' The rule should fail and return false.'
		);

		// Test pass conditions.
		$this->assertThat(
			$rule->test($xml->field[0], '#000000'),
			$this->isTrue(),
			'Line:'.__LINE__.' The basic rule should pass and return true.'
		);
	}

	public function colorData()
	{
		return array(
			array('#000000', true),
			array('#', false),
			array('#000', true),
			array('#FFFFFF', true),
			array('#EEE', true),
			array('#A0A0A0', true),
			array('#GGGGGG', false),
			array('FFFFFF', false),
			array('#GGG', false),
			array('', true)
		);
	}

	/**
	 * @dataProvider colorData
	 */
	public function testColorData($color, $expectedResult)
	{
		echo "colordata";
		
		$rule = new JFormRuleColor;
		$xml = simplexml_load_string('<form><field name="color1" /></form>', 'JXMLElement');
		$this->assertThat(
			$rule->test($xml->field[0], $color),
			$this->equalTo($expectedResult),
			$color.' should have returned '.($expectedResult ? 'true' : 'false').' but did not'
		);
	}
}
