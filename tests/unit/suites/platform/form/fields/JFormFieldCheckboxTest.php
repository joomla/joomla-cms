<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFormFieldCheckbox.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldCheckboxTest extends TestCase
{
	/**
	 * Sets up dependencies for the test.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function setUp()
	{
		parent::setUp();

		require_once JPATH_PLATFORM . '/joomla/form/fields/checkbox.php';
		require_once JPATH_TESTS . '/stubs/FormInspectors.php';
	}

	/**
	 * Test the getInput method where there is no value from the element
	 * and no checked attribute.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputNoValueNoChecked()
	{
		$formField = new JFormFieldCheckbox;

		// Test with no checked element
		$element = simplexml_load_string(
			'<field name="color" type="checkbox" value="red" />');
		TestReflection::setValue($formField, 'element', $element);
		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');

		$this->assertEquals(
			'<input type="checkbox" name="myTestName" id="myTestId" value="red" />',
			TestReflection::invoke($formField, 'getInput'),
			'The field with no value and no checked attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method where there is a value from the element
	 * and no checked attribute.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputValueNoChecked()
	{
		$formField = new JFormFieldCheckbox;

		// Test with no checked element
		$element = simplexml_load_string(
			'<field name="color" type="checkbox" value="red" />');
		TestReflection::setValue($formField, 'element', $element);
		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'value', 'red');

		$this->assertEquals(
			'<input type="checkbox" name="myTestName" id="myTestId" value="red" checked="checked" />',
			TestReflection::invoke($formField, 'getInput'),
			'The field with a value and no checked attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method where there is a checked attribute
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputNoValueChecked()
	{
		$formField = new JFormFieldCheckbox;

		// Test with checked element
		$element = simplexml_load_string(
			'<field name="color" type="checkbox" value="red" checked="checked" />');
		TestReflection::setValue($formField, 'element', $element);
		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');

		$this->assertEquals(
			'<input type="checkbox" name="myTestName" id="myTestId" value="red" checked="checked" />',
			TestReflection::invoke($formField, 'getInput'),
			'The field with no value and the checked attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method where the field is disabled
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputDisabled()
	{
		$formField = new JFormFieldCheckbox;

		// Test with checked element
		$element = simplexml_load_string(
			'<field name="color" type="checkbox" value="red" disabled="true" />');
		TestReflection::setValue($formField, 'element', $element);
		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');

		$this->assertEquals(
			'<input type="checkbox" name="myTestName" id="myTestId" value="red" disabled="disabled" />',
			TestReflection::invoke($formField, 'getInput'),
			'The field set to disabled did not produce the right html'
		);
	}
}
