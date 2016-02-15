<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFormFieldCategory.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 */
class JFormFieldCategoryTest extends TestCaseDatabase
{
	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_categories', JPATH_TEST_DATABASE . '/jos_categories.csv');

		return $dataSet;
	}

	/**
	 * Test the getInput method.
	 */
	public function testGetInput()
	{
		$field = new JFormFieldCategory();

		$this->assertTrue(
			$field->setup(
				new SimpleXmlElement('<field name="category" type="category" extension="com_content" />'),
				'value'
			)
		);

		$this->assertNotEmpty(
			$field->input
		);
	}
}
