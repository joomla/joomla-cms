<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadFieldClass('plugins');

/**
 * Test class for JFormFieldPlugins.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.4
 */
class JFormFieldPluginsTest extends TestCaseDatabase
{
	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   12.1
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_extensions', JPATH_TEST_DATABASE . '/jos_extensions.csv');

		return $dataSet;
	}

	/**
	 * Tests folder attribute setup by JFormFieldPlugins::setup method
	 *
	 * @covers JFormField::setup
	 * @covers JFormField::__get
	 *
	 * @return void
	 */
	public function testSetupFolder()
	{
		$field = new JFormFieldPlugins;
		$element = simplexml_load_string(
			'<field name="editors" type="plugins" folder="editors" />');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->folder,
			$this->equalTo("editors"),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
	}

	/**
	 * Test the getInput method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetInput()
	{
		$formField = new JFormFieldPlugins;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'editors');
		TestReflection::setValue($formField, 'folder', 'editors');
		TestReflection::setValue(
			$formField, 'element',
			simplexml_load_string('<field name="editors" type="plugins" folder="editors" />')
		);

		if (!is_null(self::$driver))
		{
			$this->assertThat(
				strlen($formField->input),
				$this->greaterThan(0),
				'Line:' . __LINE__ . ' The getInput method should return something without error.'
			);
		}
		else
		{
			$this->markTestSkipped();
		}

		// TODO: Should check all the attributes have come in properly.
	}
}
