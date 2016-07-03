<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadFieldClass('password');
require_once __DIR__ . '/TestHelpers/JHtmlFieldPassword-helper-dataset.php';

/**
 * Test class for JFormFieldPassword.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
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

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
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
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getInputData()
	{
		return JHtmlFieldPasswordTest_DataSet::$getInputTest;
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

		$this->assertTrue(
			$field->setup($element, ''),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertTrue(
			$field->meter,
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
	}

	/**
	 * Tests meter attribute setup by using the magic set method
	 *
	 * @covers JFormFieldPassword::__set
	 *
	 * @return void
	 */
	public function testSetMeter()
	{
		$field = new JFormFieldPassword;
		$element = simplexml_load_string(
			'<field name="myName" type="password" />');

		$this->assertTrue(
			$field->setup($element, ''),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertFalse(
			$field->meter,
			'Line:' . __LINE__ . ' The property is false by default.'
		);

		$field->meter = true;

		$this->assertTrue(
			$field->meter,
			'Line:' . __LINE__ . ' The magic set method should set the property correctly.'
		);
	}

	/**
	 * Test the getInput method where there is no value from the element
	 * and no checked attribute.
	 *
	 * @param   array   $data  	   @todo
	 * @param   string  $expected  @todo
	 *
	 * @return  void
	 *
	 * @since   12.2
	 *
	 * @dataProvider  getInputData
	 */
	public function testGetInput($data, $expected)
	{
		$formField = new JFormFieldPassword;

		foreach ($data as $attr => $value)
		{
			TestReflection::setValue($formField, $attr, $value);
		}

		$replaces = array("\n", "\r"," ", "\t");

		$this->assertEquals(
			str_replace($replaces, '', $expected),
			str_replace($replaces, '', TestReflection::invoke($formField, 'getInput')),
			'Line:' . __LINE__ . ' The field with no value and no checked attribute did not produce the right html'
		);
	}
}
