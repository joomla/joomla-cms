<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadFieldClass('accesslevel');

/**
 * Test class for JFormFieldAccessLevel.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldAccessLevelTest extends TestCaseDatabase
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

		$dataSet->addTable('jos_viewlevels', JPATH_TEST_DATABASE . '/jos_viewlevels.csv');

		return $dataSet;
	}

	/**
	 * Test the getInput method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetInput()
	{
		$form = new JForm('form1');

		$this->assertThat(
			$form->load('<form><field name="accesslevel" type="accesslevel" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldAccessLevel($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);

		$this->assertThat(
			strlen($field->input),
			$this->greaterThan(0),
			'Line:' . __LINE__ . ' The getInput method should return something without error.'
		);
	}
}
