<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadFieldClass('email');
require_once __DIR__ . '/TestHelpers/JHtmlFieldEmail-helper-dataset.php';

/**
 * Test class for JFormFieldEMail.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       12.1
 */
class JFormFieldEMailTest extends TestCaseDatabase
{
	/**
	 * Sets up dependencies for the test.
	 *
	 * @return  void
	 *
	 * @since   12.1
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
		return JHtmlFieldEmailTest_DataSet::$getInputTest;
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
		$formField = new JFormFieldEmail;

		$xml = '<field ';
		$curvalue = null;
		foreach ($data as $attr => $value)
		{
			if ($attr == 'value')
			{
				$curvalue = $value;
			}
			else
			{
				if ($value === false)
				{
					$value = 'false';
				}
				$xml .= $attr . '="' . $value . '" ';
			}
		}
		$xml .= '/>';

		$formField->setup(simplexml_load_string($xml), $curvalue);

		$replaces = array("\n", "\r"," ", "\t");

		$this->assertEquals(
			str_replace($replaces, '', $expected),
			str_replace($replaces, '', TestReflection::invoke($formField, 'getInput')),
			'Line:' . __LINE__ . ' The field with no value and no checked attribute did not produce the right html'
		);
	}
}
