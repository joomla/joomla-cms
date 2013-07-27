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
class JFormFieldRadioTest extends TestCase
{
	/**
	 * Sets up dependancies for the test.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();
		require_once JPATH_PLATFORM . '/joomla/form/fields/radio.php';
		require_once JPATH_TESTS . '/stubs/FormInspectors.php';
	}

	/**
	 * Test the getInput method with no options in xml.
	 *
	 * @return void
	 */
	public function testGetInputNoOptions()
	{
		$formField = new JFormFieldRadio;

		$element = simplexml_load_string('<field name="myTestId" type="radio" />');

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'element', $element);

		$this->assertEquals(
			'<fieldset id="myTestId" class="radio" ></fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with no options did not produce the right html'
		);
	}

	/**
	 * Test the getInput method with options in xml.
	 *
	 * @return void
	 */
	public function testGetInputOptions()
	{
		$formField = new JFormFieldRadio;

		$element = simplexml_load_string(
			'<field name="myTestId" type="radio">
				<option value="1">Yes</option>
				<option value="0">No</option>
			</field>');

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'element', $element);

		$this->assertEquals(
			'<fieldset id="myTestId" class="radio" >'
				. '<input type="radio" id="myTestId0" name="myTestName" value="1" />'
				. '<label for="myTestId0" >Yes</label>'
				. '<input type="radio" id="myTestId1" name="myTestName" value="0" />'
				. '<label for="myTestId1" >No</label>'
			. '</fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with options did not produce the right html'
		);
	}

	/**
	 * Test the getInput method with field class set in xml.
	 *
	 * @return void
	 */
	public function testGetInputFieldClass()
	{
		$formField = new JFormFieldRadio;

		$element = simplexml_load_string('<field name="myTestId" class="foo bar" type="radio"></field>');

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'element', $element);
		TestReflection::setValue($formField, 'class', 'foo bar');

		$this->assertEquals(
			'<fieldset id="myTestId" class="radio foo bar" ></fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with class did not produce the right html'
		);
	}

	/**
	 * Test the getInput method with option class set in xml.
	 *
	 * @return void
	 */
	public function testGetInputOptionClass()
	{
		$formField = new JFormFieldRadio;

		$element = simplexml_load_string(
			'<field name="myTestId" type="radio">
				<option value="1" class="foo">Yes</option>
				<option value="0" class="bar">No</option>
			</field>');

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'element', $element);

		$this->assertEquals(
			'<fieldset id="myTestId" class="radio" >'
				. '<input type="radio" id="myTestId0" name="myTestName" value="1" class="foo" />'
				. '<label for="myTestId0" class="foo" >Yes</label>'
				. '<input type="radio" id="myTestId1" name="myTestName" value="0" class="bar" />'
				. '<label for="myTestId1" class="bar" >No</label>'
			. '</fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with options having class did not produce the right html'
		);
	}

	/**
	 * Test the getInput method with required attribute set in xml.
	 *
	 * @return void
	 */
	public function testGetInputRequired()
	{
		$formField = new JFormFieldRadio;

		$element = simplexml_load_string(
			'<field name="myTestId" type="radio" required="true">
				<option value="1" required="true" >Yes</option>
				<option value="0">No</option>
			</field>');

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'element', $element);
		TestReflection::setValue($formField, 'required', true);

		$this->assertEquals(
			'<fieldset id="myTestId" class="radio" required aria-required="true" >'
				. '<input type="radio" id="myTestId0" name="myTestName" value="1" required aria-required="true" />'
				. '<label for="myTestId0" >Yes</label>'
				. '<input type="radio" id="myTestId1" name="myTestName" value="0" />'
				. '<label for="myTestId1" >No</label>'
			. '</fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with required attribute set did not produce the right html'
		);
	}

	/**
	 * Test the getInput method with autofocus set in xml.
	 *
	 * @return void
	 */
	public function testGetInputAutofocus()
	{
		$formField = new JFormFieldRadio;

		$element = simplexml_load_string(
			'<field name="myTestId" type="radio" required="true"></field>');

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'element', $element);
		TestReflection::setValue($formField, 'autofocus', true);

		$this->assertEquals(
			'<fieldset id="myTestId" class="radio" autofocus ></fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with autofocus set did not produce the right html'
		);
	}

	/**
	 * Test the getInput method with readonly attribute set in xml.
	 *
	 * @return void
	 */
	public function testGetInputReadonlyChecked()
	{
		$formField = new JFormFieldRadio;

		$element = simplexml_load_string(
			'<field name="myTestId" type="radio" readonly="true" value="0">
				<option value="1" required="true" >Yes</option>
				<option value="0">No</option>
				<option value="-1">None</option>
			</field>');

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'element', $element);
		TestReflection::setValue($formField, 'readonly', true);
		TestReflection::setValue($formField, 'value', '0');

		$this->assertEquals(
			'<fieldset id="myTestId" class="radio" >'
				. '<input type="radio" id="myTestId0" name="myTestName" value="1" disabled />'
				. '<label for="myTestId0" >Yes</label>'
				. '<input type="radio" id="myTestId1" name="myTestName" value="0" checked />'
				. '<label for="myTestId1" >No</label>'
				. '<input type="radio" id="myTestId2" name="myTestName" value="-1" disabled />'
				. '<label for="myTestId2" >None</label>'
			. '</fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with readonly did not produce the right html'
		);
	}

	/**
	 * Test the getInput method with js functions set in xml.
	 *
	 * @return void
	 */
	public function testGetInputOnclickOnchange()
	{
		$formField = new JFormFieldRadio;

		$element = simplexml_load_string(
			'<field name="myTestId" type="radio">
				<option value="1" onclick="foo();" >Yes</option>
				<option value="0" onchange="bar();">No</option>
			</field>');

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'element', $element);

		$this->assertEquals(
			'<fieldset id="myTestId" class="radio" >'
				. '<input type="radio" id="myTestId0" name="myTestName" value="1" onclick="foo();" />'
				. '<label for="myTestId0" >Yes</label>'
				. '<input type="radio" id="myTestId1" name="myTestName" value="0" onchange="bar();" />'
				. '<label for="myTestId1" >No</label>'
			. '</fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with js functions did not produce the right html'
		);
	}

	/**
	 * Test the getInput method with disabled set in xml.
	 *
	 * @return void
	 */
	public function testGetInputFieldDisabled()
	{
		$formField = new JFormFieldRadio;

		$element = simplexml_load_string(
			'<field name="myTestId" type="radio">
				<option value="1">Yes</option>
				<option value="0">No</option>
			</field>');

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'element', $element);
		TestReflection::setValue($formField, 'disabled', true);

		$this->assertEquals(
			'<fieldset id="myTestId" class="radio" disabled >'
				. '<input type="radio" id="myTestId0" name="myTestName" value="1" />'
				. '<label for="myTestId0" >Yes</label>'
				. '<input type="radio" id="myTestId1" name="myTestName" value="0" />'
				. '<label for="myTestId1" >No</label>'
			. '</fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with disabled did not produce the right html'
		);
	}

	/**
	 * Test the getInput method with option set disabled in xml.
	 *
	 * @return void
	 */
	public function testGetInputOptionDisabled()
	{
		$formField = new JFormFieldRadio;

		$element = simplexml_load_string(
			'<field name="myTestId" type="radio">
				<option value="1" disabled="true">Yes</option>
				<option value="0">No</option>
			</field>');

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'element', $element);

		$this->assertEquals(
			'<fieldset id="myTestId" class="radio" >'
				. '<input type="radio" id="myTestId0" name="myTestName" value="1" disabled />'
				. '<label for="myTestId0" >Yes</label>'
				. '<input type="radio" id="myTestId1" name="myTestName" value="0" />'
				. '<label for="myTestId1" >No</label>'
			. '</fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with options set disabled did not produce the right html'
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
		$form = new JFormInspector('form1');

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
