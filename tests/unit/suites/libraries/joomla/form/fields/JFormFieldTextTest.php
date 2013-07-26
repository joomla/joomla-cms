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
class JFormFieldTextTest extends TestCase
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

		require_once JPATH_PLATFORM . '/joomla/form/fields/text.php';

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
	public function testSetupMaxlength()
	{
		$field = new JFormFieldText;
		$element = simplexml_load_string(
			'<field name="myName" type="text" maxlength="60" />');

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
	 * Test the getInput method where there is no value from the element.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputNoValue()
	{
		$formField = new JFormFieldText;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'dirname', 'myTestName_dir');
		TestReflection::setValue(
			$formField, 'element',
			simplexml_load_string('<field id="myTestId" name="myTestName" />')
		);

		$this->assertEquals(
			'<input type="text" name="myTestName" id="myTestId" dirname="myTestName_dir" value="" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with no value attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method where there is value from the element
	 * and no checked attribute.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputValue()
	{
		$formField = new JFormFieldText;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'value', 'http://foobar.com');
		TestReflection::setValue(
			$formField, 'element',
			simplexml_load_string('<field id="myTestId" name="myTestName" />')
		);

		$this->assertEquals(
			'<input type="text" name="myTestName" id="myTestId" dirname="" value="http://foobar.com" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with value attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has class set from xml
	 * and no checked attribute.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputClass()
	{
		$formField = new JFormFieldText;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'class', 'foo bar');
		TestReflection::setValue(
			$formField, 'element',
			simplexml_load_string('<field id="myTestId" name="myTestName" />')
		);

		$this->assertEquals(
			'<input type="text" name="myTestName" id="myTestId" dirname="" value="" class="foo bar" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with class attribute did not produce the right html'
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
		$formField = new JFormFieldText;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'size', 60);
		TestReflection::setValue(
			$formField, 'element',
			simplexml_load_string('<field id="myTestId" name="myTestName" dirname="" />')
		);

		$this->assertEquals(
			'<input type="text" name="myTestName" id="myTestId" dirname="" value="" size="60" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with size attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has disabled and readonly set from xml
	 * and no checked attribute.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputDisabledReadonly()
	{
		$formField = new JFormFieldText;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'disabled', true);
		TestReflection::setValue($formField, 'readonly', true);
		TestReflection::setValue(
			$formField, 'element',
			simplexml_load_string('<field id="myTestId" name="myTestName" />')
		);

		$this->assertEquals(
			'<input type="text" name="myTestName" id="myTestId" dirname="" value="" disabled readonly />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with diabled and readonly attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has hint (placeholder) set from xml
	 * and no checked attribute.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputHint()
	{
		$formField = new JFormFieldText;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'hint', 'Type any url.');
		TestReflection::setValue(
			$formField, 'element',
			simplexml_load_string('<field id="myTestId" name="myTestName" />')
		);

		$this->assertEquals(
			'<input type="text" name="myTestName" id="myTestId" dirname="" value="" placeholder="Type any url." />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with hint attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has autocomplete set to off from xml
	 * and no checked attribute.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputAutocomplete()
	{
		$formField = new JFormFieldText;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'autocomplete', false);
		TestReflection::setValue(
			$formField, 'element',
			simplexml_load_string('<field id="myTestId" name="myTestName" />')
		);

		$this->assertEquals(
			'<input type="text" name="myTestName" id="myTestId" dirname="" value="" autocomplete="off" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with autocomplete attribute did not produce the right html'
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
		TestReflection::setValue(
			$formField, 'element',
			simplexml_load_string('<field id="myTestId" name="myTestName" />')
		);

		$this->assertEquals(
			'<input type="text" name="myTestName" id="myTestId" dirname="" value="" autofocus />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with autofocus attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has spellcheck set to false from xml
	 * and no checked attribute.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputSpellcheck()
	{
		$formField = new JFormFieldText;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'spellcheck', false);
		TestReflection::setValue(
			$formField, 'element',
			simplexml_load_string('<field id="myTestId" name="myTestName" />')
		);

		$this->assertEquals(
			'<input type="text" name="myTestName" id="myTestId" dirname="" value="" spellcheck="false" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with spellcheck attribute did not produce the right html'
		);
	}

	/**
	 * Test the getInput method when field has onchange set to js function from xml
	 * and no checked attribute.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testGetInputOnchange()
	{
		$formField = new JFormFieldText;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'onchange', 'foobar();');
		TestReflection::setValue(
			$formField, 'element',
			simplexml_load_string('<field id="myTestId" name="myTestName" />')
		);

		$this->assertEquals(
			'<input type="text" name="myTestName" id="myTestId" dirname="" value="" onchange="foobar();" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with onchange attribute did not produce the right html'
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
		$formField = new JFormFieldText;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'maxLength', 250);
		TestReflection::setValue(
			$formField, 'element',
			simplexml_load_string('<field id="myTestId" name="myTestName" />')
		);

		$this->assertEquals(
			'<input type="text" name="myTestName" id="myTestId" dirname="" value="" maxlength="250" />',
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
		$formField = new JFormFieldText;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'myTestName');
		TestReflection::setValue($formField, 'required', true);
		TestReflection::setValue(
			$formField, 'element',
			simplexml_load_string('<field id="myTestId" name="myTestName" />')
		);

		$this->assertEquals(
			'<input type="text" name="myTestName" id="myTestId" dirname="" value="" required aria-required="true" />',
			TestReflection::invoke($formField, 'getInput'),
			'Line:' . __LINE__ . ' The field with required attribute set did not produce the right html'
		);
	}
}
