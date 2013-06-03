<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/form/fields/sql.php';
require_once JPATH_TESTS . '/stubs/FormInspectors.php';

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
	 * Test the getInput method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetInput()
	{
		$form = new JFormInspector('form1');

		$expected = '<form><field name="sql" type="sql" value_field="title" key_field="id" query="SELECT * FROM `jos_categories`">' .
			'<option value="*">None</option></field></form>';

		$this->assertThat(
			$form->load($expected),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldSQL($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);

		if (!is_null(self::$driver))
		{
			$this->assertThat(
				strlen($field->input),
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
