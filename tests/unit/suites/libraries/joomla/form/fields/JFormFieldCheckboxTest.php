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
	 * Backup of the SERVER superglobal
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function setUp()
	{
		parent::setUp();

		require_once JPATH_PLATFORM . '/joomla/form/fields/checkbox.php';

		$this->saveFactoryState();

		JFactory::$application = $this->getMockApplication();

		$this->backupServer = $_SERVER;

		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['SCRIPT_NAME'] = '';
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;

		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Tests checked attribute setup by JFormField::setup method
	 *
	 * @covers JFormFieldCheckbox::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupChecked()
	{
		$field = new JFormFieldCheckbox;
		$element = simplexml_load_string(
			'<field name="myName" type="checkbox" checked="true" />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->checked,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
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

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');

		$this->assertEquals(
			'<input type="checkbox" name="myTestName" id="myTestId" value="1" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with no value and no checked attribute did not produce the right html'
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
\
		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'default', 'red');
		TestReflection::setValue($formField, 'value', 'red');

		$this->assertEquals(
			'<input type="checkbox" name="myTestName" id="myTestId" value="red" checked />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with a value and no checked attribute did not produce the right html'
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

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'default', 'red');
		TestReflection::setValue($formField, 'checked', true);

		$this->assertEquals(
			'<input type="checkbox" name="myTestName" id="myTestId" value="red" checked />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with no value and the checked attribute did not produce the right html'
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

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'disabled', true);

		$this->assertEquals(
			'<input type="checkbox" name="myTestName" id="myTestId" value="1" disabled />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field set to disabled did not produce the right html'
		);
	}

	/**
	 * Test the getInput method where the field is required
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputRequired()
	{
		$formField = new JFormFieldCheckbox;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'required', true);

		$this->assertEquals(
			'<input type="checkbox" name="myTestName" id="myTestId" value="1" required aria-required="true" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field set to required did not produce the right html'
		);
	}

	/**
	 * Test the getInput method where the field is autofocussed
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputAutofocus()
	{
		$formField = new JFormFieldCheckbox;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'autofocus', true);

		$this->assertEquals(
			'<input type="checkbox" name="myTestName" id="myTestId" value="1" autofocus />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field set to required did not produce the right html'
		);
	}

	/**
	 * Test the getInput method where the field is having given class
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputClass()
	{
		$formField = new JFormFieldCheckbox;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'class', 'foo bar');

		$this->assertEquals(
			'<input type="checkbox" name="myTestName" id="myTestId" value="1" class="foo bar" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field set to required did not produce the right html'
		);
	}

	/**
	 * Test the getInput method where the field is having onclick and onchange js functions
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputOnchangeOnclick()
	{
		$formField = new JFormFieldCheckbox;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'onclick', 'foo();');
		TestReflection::setValue($formField, 'onchange', 'bar();');

		$this->assertEquals(
			'<input type="checkbox" name="myTestName" id="myTestId" value="1" onclick="foo();" onchange="bar();" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field set to required did not produce the right html'
		);
	}
}
