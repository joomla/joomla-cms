<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadFieldClass('sql');

/**
 * Test class for JFormFieldSQL.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       12.1
 */
class JFormFieldSQLTest extends TestCaseDatabase
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

		$dataSet->addTable('jos_categories', JPATH_TEST_DATABASE . '/jos_categories.csv');

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
	public function testSetup()
	{
		$field = new JFormFieldSQL;
		$element = simplexml_load_string(
			'<field name="sql" type="sql" value_field="title" key_field="id" query="SELECT * FROM `jos_categories`">' .
			'<option value="*">None</option></field>');

		$this->assertThat(
			$field->setup($element, ''),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true if successful.'
		);

		$this->assertThat(
			$field->keyField,
			$this->equalTo("id"),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->valueField,
			$this->equalTo("title"),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->translate,
			$this->isFalse(),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);

		$this->assertThat(
			$field->query,
			$this->equalTo("SELECT * FROM `jos_categories`"),
			'Line:' . __LINE__ . ' The property should be computed from the XML.'
		);
	}

	/**
	 * Test the getInput method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetInput()
	{
		$formField = new JFormFieldSQL;

		TestReflection::setValue($formField, 'id', 'myTestId');
		TestReflection::setValue($formField, 'name', 'sql');
		TestReflection::setValue($formField, 'valueField', 'title');
		TestReflection::setValue($formField, 'keyField', 'id');
		TestReflection::setValue($formField, 'query', "SELECT * FROM `jos_categories`");
		TestReflection::setValue(
			$formField, 'element',
			simplexml_load_string('<field name="sql" type="sql" value_field="title" key_field="id" query="SELECT * FROM `jos_categories`">' .
			'<option value="*">None</option></field>')
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
	}
}
