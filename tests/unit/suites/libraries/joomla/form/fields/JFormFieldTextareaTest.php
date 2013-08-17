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
class JFormFieldTextareaTest extends TestCase
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

		require_once JPATH_PLATFORM . '/joomla/form/fields/textarea.php';

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
	 * Tests maxLength attribute setup by JFormFieldText::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupRowsColumns()
	{
		$field = new JFormFieldTextarea;
		$element = simplexml_load_string(
			'<field name="myName" type="textarea" rows="60" cols="70" />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->rows,
			$this->equalTo(60),
			'Line:' . __LINE__ . '  The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->columns,
			$this->equalTo(70),
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
		$formField = new JFormFieldTextarea;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');

		$this->assertEquals(
			'<textarea name="myTestName" id="myTestId" ></textarea>',
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
		$formField = new JFormFieldTextarea;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'value', 'This is textarea text.');

		$this->assertEquals(
			'<textarea name="myTestName" id="myTestId" >This is textarea text.</textarea>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with value attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has rows and cols from the element.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputRowsColumns()
	{
		$formField = new JFormFieldTextarea;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'rows', 55);
		TestReflection::setValue($formField, 'columns', 80);

		$this->assertEquals(
			'<textarea name="myTestName" id="myTestId" cols="80" rows="55" ></textarea>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with rows and columns attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has class from the element.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputClass()
	{
		$formField = new JFormFieldTextarea;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'class', 'foo bar');

		$this->assertEquals(
			'<textarea name="myTestName" id="myTestId" class="foo bar" ></textarea>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with class attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has hint from the element.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputHint()
	{
		$formField = new JFormFieldTextarea;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'hint', 'Placeholder for textarea.');

		$this->assertEquals(
			'<textarea name="myTestName" id="myTestId" placeholder="Placeholder for textarea." ></textarea>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with hint attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has disabled and readonly from the element.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputDisabledReadonly()
	{
		$formField = new JFormFieldTextarea;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'disabled', true);
		TestReflection::setValue($formField, 'readonly', true);

		$this->assertEquals(
			'<textarea name="myTestName" id="myTestId" disabled readonly ></textarea>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with disabled and readonly attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has onchange and onclick from the element.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputOnchangeOnclick()
	{
		$formField = new JFormFieldTextarea;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'onchange', 'foobar();');
		TestReflection::setValue($formField, 'onclick', 'barfoo();');

		$this->assertEquals(
			'<textarea name="myTestName" id="myTestId" onchange="foobar();" onclick="barfoo();" ></textarea>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with onchange and onclick attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has required set from the element.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputRequired()
	{
		$formField = new JFormFieldTextarea;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'required', true);

		$this->assertEquals(
			'<textarea name="myTestName" id="myTestId" required aria-required="true" ></textarea>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with required attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has autocomplete set from the element.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputAutocomplete()
	{
		$formField = new JFormFieldTextarea;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'autocomplete', 'off');

		$this->assertEquals(
			'<textarea name="myTestName" id="myTestId" autocomplete="off" ></textarea>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with autocomplete attribute did not produce the right html'
		);

		TestReflection::setValue($formField, 'autocomplete', 'on');

		$this->assertEquals(
			'<textarea name="myTestName" id="myTestId" ></textarea>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with autocomplete attribute did not produce the right html'
		);

		TestReflection::setValue($formField, 'autocomplete', 'on name');

		$this->assertEquals(
			'<textarea name="myTestName" id="myTestId" autocomplete="on name" ></textarea>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with autocomplete attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has autofocus set from the element.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputAutofocus()
	{
		$formField = new JFormFieldTextarea;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'autofocus', true);

		$this->assertEquals(
			'<textarea name="myTestName" id="myTestId" autofocus ></textarea>',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with autofocus attribute did not produce the right html'
		);
	}
}
