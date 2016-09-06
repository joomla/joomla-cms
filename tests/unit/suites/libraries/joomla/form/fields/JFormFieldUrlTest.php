<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadFieldClass('url');
require_once __DIR__ . '/TestHelpers/JHtmlFieldUrl-helper-dataset.php';

/**
 * Test class for JFormFieldUrl.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       12.1
 */
class JFormFieldUrlTest extends TestCaseDatabase
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
		return JHtmlFieldUrlTest_DataSet::$getInputTest;
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
		$formField = new JFormFieldUrl;

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
			str_replace($replaces, '', TestReflection::invoke($formField, 'getInput')),
			str_replace($replaces, '', $expected),
			'Line:' . __LINE__ . ' The field did not produce the right html');
	}
}
