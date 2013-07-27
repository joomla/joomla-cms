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
class JFormFieldPasswordTest extends TestCase
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

		require_once JPATH_PLATFORM . '/joomla/form/fields/password.php';

		$this->saveFactoryState();

		JFactory::$application = $this->getMockApplication();
		JFactory::$database    = $this->getMockDatabase();

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
	 * Tests maxLength attribute setup by JFormFieldText::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupMaxlength()
	{
		$field = new JFormFieldPassword;
		$element = simplexml_load_string(
			'<field name="myName" type="password" maxlength="60" />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->maxLength,
			$this->equalTo(60),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
	}

	/**
	 * Tests threshold attribute setup by JFormFieldText::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupThreshold()
	{
		$field = new JFormFieldPassword;
		$element = simplexml_load_string(
			'<field name="myName" type="password" threshold="75" />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->threshold,
			$this->equalTo(75),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
	}

	/**
	 * Tests meter attribute setup by JFormFieldText::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupMeter()
	{
		$field = new JFormFieldPassword;
		$element = simplexml_load_string(
			'<field name="myName" type="password" strengthmeter="true" />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->meter,
			$this->isTrue(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
	}

	/**
	 * Test the getInput method where there is no value from the element.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputNoValue()
	{
		$formField = new JFormFieldPassword;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');

		$this->assertEquals(
			'<input type="password" name="myTestName" id="myTestId" value="" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with no value attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method where there is value from the element.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputValue()
	{
		$formField = new JFormFieldPassword;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'value', 'foobar');

		$this->assertEquals(
			'<input type="password" name="myTestName" id="myTestId" value="foobar" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with value attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method where there is hint from the element.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputHint()
	{
		$formField = new JFormFieldPassword;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'hint', 'Type your password.');

		$this->assertEquals(
			'<input type="password" name="myTestName" id="myTestId" value="" hint="Type your password." />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with hint attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when autocomplete is off from the element.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputAutocomplete()
	{
		$formField = new JFormFieldPassword;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'autocomplete', false);

		$this->assertEquals(
			'<input type="password" name="myTestName" id="myTestId" value="" autocomplete="off" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with hint attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when class set from the element.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputClass()
	{
		$formField = new JFormFieldPassword;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'class', 'foo bar');

		$this->assertEquals(
			'<input type="password" name="myTestName" id="myTestId" value="" class="foo bar" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with hint attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when readonly and disabled set from the element.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputReadonlyDisabled()
	{
		$formField = new JFormFieldPassword;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'disabled', true);
		TestReflection::setValue($formField, 'readonly', true);

		$this->assertEquals(
			'<input type="password" name="myTestName" id="myTestId" value="" readonly disabled />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with hint attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has size set from xml
	 * and no checked attribute.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputSize()
	{
		$formField = new JFormFieldPassword;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'size', 60);

		$this->assertEquals(
			'<input type="password" name="myTestName" id="myTestId" size="60" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with size attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has maxlength set from xml
	 * and no checked attribute.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputMaxlength()
	{
		$formField = new JFormFieldPassword;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'maxLength', 250);

		$this->assertEquals(
			'<input type="password" name="myTestName" id="myTestId" value="" maxlength="250" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with maxlength attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has required set from xml
	 * and no checked attribute.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputRequired()
	{
		$formField = new JFormFieldPassword;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'required', true);

		$this->assertEquals(
			'<input type="password" name="myTestName" id="myTestId" dirname="" value="" required aria-required="true" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with required attribute set did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has autofocus set to true from xml
	 * and no checked attribute.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputAutofocus()
	{
		$formField = new JFormFieldText;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'autofocus', true);

		$this->assertEquals(
			'<input type="password" name="myTestName" id="myTestId" dirname="" value="" autofocus />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with autofocus attribute did not produce the right html'
		);
	}
}
