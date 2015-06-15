<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadFieldClass('radio');
require_once __DIR__ . '/TestHelpers/JHtmlFieldRadio-helper-dataset.php';

/**
 * Test class for JFormFieldRadio.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldRadioTest extends TestCase
{
	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getInputData()
	{
		return JHtmlFieldRadioTest_DataSet::$getInputTest;
	}

	/**
	 * Test the getInput method where there is no value from the element
	 * and no checked attribute.
	 *
	 * @param   string  $element   @todo
	 * @param   array   $data  	   @todo
	 * @param   string  $expected  @todo
	 *
	 * @return  void
	 *
	 * @since   12.2
	 *
	 * @dataProvider  getInputData
	 */
	public function testGetInput($element, $data, $expected)
	{
		$formField = new JFormFieldRadio;

		TestReflection::setValue($formField, 'element', simplexml_load_string($element));

		foreach ($data as $attr => $value)
		{
			TestReflection::setValue($formField, $attr, $value);
		}

		$this->assertEquals(
			$expected,
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with no value and no checked attribute did not produce the right html'
		);
	}

	/**
	 * Test the getOptions method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetOptions()
	{
		$form = new JForm('form1');

		$this->assertThat(
			$form->load('<form><field name="radio" type="radio"><option value="0">No</option><item value="1">Yes</item></field></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldRadio($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);

		$this->assertThat(
			strlen($field->input),
			$this->logicalNot(
				$this->StringContains('Yes')
			),
			'Line:' . __LINE__ . ' The field should not contain a Yes option.'
		);
	}
}
