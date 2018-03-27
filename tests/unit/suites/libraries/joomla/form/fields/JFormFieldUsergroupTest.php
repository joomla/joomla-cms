<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

JFormHelper::loadFieldClass('usergroup');

/**
 * Test class for JFormFieldUsergroup.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldUsergroupTest extends TestCaseDatabase
{
	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  \PHPUnit\DbUnit\DataSet\CsvDataSet
	 *
	 * @since   12.1
	 */
	protected function getDataSet()
	{
		$dataSet = new \PHPUnit\DbUnit\DataSet\CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_usergroups', JPATH_TEST_DATABASE . '/jos_usergroups.csv');

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

		$expected = '<form><field name="usergroup" type="usergroup" class="inputbox" disabled="true" onclick="window.reload()">' .
			'<option value="*">None</option><item value="fake">Fake</item></field></form>';

		$this->assertThat(
			$form->load($expected),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldUsergroup($form);

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

		// TODO: Should check all the attributes have come in properly.
	}
}
