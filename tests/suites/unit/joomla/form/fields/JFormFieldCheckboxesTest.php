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
 * @since       11.3
 */
class JFormFieldCheckboxesTest extends TestCase
{
	/**
	 * Sets up dependencies for the test.
	 *
	 * @since       11.3
	 */
	protected function setUp()
	{
		require_once JPATH_PLATFORM.'/joomla/form/fields/checkboxes.php';
		include_once dirname(__DIR__).'/inspectors.php';
	}

	/**
	 * Test the getInput method with no value and no checked attribute.
	 *
	 * @since       12.2
	 */
	public function testGetInputNoValueNoChecked()
	{
		$formField = new JFormFieldCheckboxes;

		// Test with no value, no checked element
		$element = simplexml_load_string(
			'<field name="color" type="checkboxes">
			<option value="red">red</option>
			<option value="blue">blue</option>
			</field>');
		TestReflection::setValue($formField, 'element', $element);
		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');

		$this->assertEquals(
			'<fieldset id="myTestId" class="checkboxes"><ul><li><input type="checkbox" id="myTestId0" name="myTestName" value="red"/><label for="myTestId0">red</label></li><li><input type="checkbox" id="myTestId1" name="myTestName" value="blue"/><label for="myTestId1">blue</label></li></ul></fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'The field with no value and no checked values did not produce the right html'
		);
	}

	/**
	 * Test the getInput method with one value selected and no checked attribute.
	 *
	 * @since       12.2
	 */
	public function testGetInputValueNoChecked()
	{
		$formField = new JFormFieldCheckboxes;

		// Test with one value checked, no checked element
		$element = simplexml_load_string(
			'<field name="color" type="checkboxes">
			<option value="red">red</option>
			<option value="blue">blue</option>
			</field>');
		TestReflection::setValue($formField, 'element', $element);
		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'value', 'red');
		TestReflection::setValue($formField, 'name', 'myTestName');

		$this->assertEquals(
			'<fieldset id="myTestId" class="checkboxes"><ul><li><input type="checkbox" id="myTestId0" name="myTestName" value="red" checked="checked"/><label for="myTestId0">red</label></li><li><input type="checkbox" id="myTestId1" name="myTestName" value="blue"/><label for="myTestId1">blue</label></li></ul></fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'The field with one value did not produce the right html'
		);
	}

	/**
	 * Test the getInput method with one value that is an array and no checked attribute.
	 *
	 * @since       12.2
	 */
	public function testGetInputValueArrayNoChecked()
	{
		$formField = new JFormFieldCheckboxes;

		// Test with one value checked, no checked element
		$element = simplexml_load_string(
			'<field name="color" type="checkboxes">
			<option value="red">red</option>
			<option value="blue">blue</option>
			</field>');
		$valuearray = array ('red');
		TestReflection::setValue($formField, 'element', $element);
		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'value', $valuearray);
		TestReflection::setValue($formField, 'name', 'myTestName');

		$this->assertEquals(
			'<fieldset id="myTestId" class="checkboxes"><ul><li><input type="checkbox" id="myTestId0" name="myTestName" value="red" checked="checked"/><label for="myTestId0">red</label></li><li><input type="checkbox" id="myTestId1" name="myTestName" value="blue"/><label for="myTestId1">blue</label></li></ul></fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'The field with one value did not produce the right html'
		);
	}

	/**
	 * Test the getInput method  with no value and one value in checked.
	 *
	 * @since       12.2
	 */
	public function testGetInputNoValueOneChecked()
	{
		$formField = new JFormFieldCheckboxes;
		
		// Test with nothing checked, one value in checked element
		$element = simplexml_load_string(
			'<field name="color" type="checkboxes" checked="blue">
			<option value="red">red</option>
			<option value="blue">blue</option>
			</field>');
		TestReflection::setValue($formField, 'element', $element);
		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');

		$this->assertEquals(
			'<fieldset id="myTestId" class="checkboxes"><ul><li><input type="checkbox" id="myTestId0" name="myTestName" value="red"/><label for="myTestId0">red</label></li><li><input type="checkbox" id="myTestId1" name="myTestName" value="blue" checked="checked"/><label for="myTestId1">blue</label></li></ul></fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'The field with no values and one value in the checked element did not produce the right html'
		);
	}

	/**
	 * Test the getInput method with no value and two values in the checked element.
	 *
	 * @since       12.2
	 */
	public function testGetInputNoValueTwoChecked()
	{
		$formField = new JFormFieldCheckboxes;
		
		// Test with nothing checked, two values in checked element
		$element = simplexml_load_string(
			'<field name="color" type="checkboxes" checked="red,blue">
			<option value="red">red</option>
			<option value="blue">blue</option>
			</field>');
		TestReflection::setValue($formField, 'element', $element);
		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'value', '""');

		$this->assertEquals(
			'<fieldset id="myTestId" class="checkboxes"><ul><li><input type="checkbox" id="myTestId0" name="myTestName" value="red"/><label for="myTestId0">red</label></li><li><input type="checkbox" id="myTestId1" name="myTestName" value="blue"/><label for="myTestId1">blue</label></li></ul></fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'The field with no values and two items in the checked element did not produce the right html'
		);
	}

	/**
	 * Test the getInput method with one value and a different checked value.
	 *
	 * @since       12.2
	 */
	public function testGetInputValueChecked()
	{
		$formField = new JFormFieldCheckboxes;

		// Test with one item checked, a different value in checked element
		$element = simplexml_load_string(
			'<field name="color" type="checkboxes" checked="blue">
			<option value="red">red</option>
			<option value="blue">blue</option>
			</field>');
		TestReflection::setValue($formField, 'element', $element);
		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'value', 'red');
		TestReflection::setValue($formField, 'name', 'myTestName');

		$this->assertEquals(
			'<fieldset id="myTestId" class="checkboxes"><ul><li><input type="checkbox" id="myTestId0" name="myTestName" value="red" checked="checked"/><label for="myTestId0">red</label></li><li><input type="checkbox" id="myTestId1" name="myTestName" value="blue"/><label for="myTestId1">blue</label></li></ul></fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'The field with one value and a different value in the checked element did not produce the right html'
		);
	}

	/**
	 * Test the getInput method with multiple values, no checked.
	 *
	 * @since       12.2
	 */
	public function testGetInputValuesNoChecked()
	{
		$formField = new JFormFieldCheckboxes;

		// Test with two values checked, no checked element
		$element = simplexml_load_string(
			'<field name="color" type="checkboxes">
			<option value="yellow">yellow</option>
			<option value="green">green</option>
			</field>');
		TestReflection::setValue($formField, 'element', $element);
		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'value', 'yellow,green');
		TestReflection::setValue($formField, 'name', 'myTestName');

		$this->assertEquals(
			'<fieldset id="myTestId" class="checkboxes"><ul><li><input type="checkbox" id="myTestId0" name="myTestName" value="yellow" checked="checked"/><label for="myTestId0">yellow</label></li><li><input type="checkbox" id="myTestId1" name="myTestName" value="green" checked="checked"/><label for="myTestId1">green</label></li></ul></fieldset>',
			TestReflection::invoke($formField, 'getInput'),
			'The field with two values did not produce the right html'
		);
		// TODO: Should check any other attributes have come in properly.
	}

	/**
	 * Test the getOptions method.
	 *
	 * @since       12.2
	 */
	public function testGetOptions()
	{
		$this->markTestIncomplete();	
	}
}
