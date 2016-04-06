<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadFieldClass('textarea');
require_once __DIR__ . '/TestHelpers/JHtmlFieldTextarea-helper-dataset.php';

/**
 * Test class for JFormFieldTextarea.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
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

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();

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
		return JHtmlFieldTextareaTest_DataSet::$getInputTest;
	}

	/**
	 * Tests rows and columns attribute setup by JFormFieldTextare::setup method
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
		$formField = new JFormFieldTextarea;

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
}
