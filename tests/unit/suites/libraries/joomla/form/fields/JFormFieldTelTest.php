<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

JFormHelper::loadFieldClass('tel');
require_once __DIR__ . '/TestHelpers/JHtmlFieldTel-helper-dataset.php';

/**
 * Test class for JFormFieldTel.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       3.0.0
 */
class JFormFieldTelTest extends TestCase
{
	/**
	 * Sets up dependencies for the test.
	 *
	 * @return  void
	 *
	 * @since   3.0.0
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
		return JHtmlFieldTelTest_DataSet::$getInputTest;
	}

	/**
	 * Test the getInput method where there is no value from the element.
	 *
	 * @param   array   $data  	   @todo
	 * @param   string  $expected  @todo
	 *
	 * @return  void
	 *
	 * @since   3.0.1
	 *
	 * @dataProvider  getInputData
	 */
	public function testGetInput($data, $expected)
	{
		$formField = new JFormFieldTel;

		TestReflection::setValue($formField, 'element', simplexml_load_string('<field type="tel" />'));

		foreach ($data as $attr => $value)
		{
			TestReflection::setValue($formField, $attr, $value);
		}

		$replaces = array("\n", "\r"," ", "\t");

		$this->assertEquals(str_replace($replaces, '', TestReflection::invoke($formField, 'getInput')), str_replace($replaces, '', $expected), 'Line:' . __LINE__ . ' The field did not produce the right html');
	}
}
