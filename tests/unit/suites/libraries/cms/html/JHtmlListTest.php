<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHtmlList.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       3.1
 */
class JHtmlListTest extends TestCaseDatabase
{
	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   3.1
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_users', JPATH_TEST_DATABASE . '/jos_users.csv');

		return $dataSet;
	}

	/**
	 * Test...
	 *
	 * @todo Implement testImages().
	 *
	 * @return void
	 */
	public function testImages()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGenericordering().
	 *
	 * @return void
	 */
	public function testGenericordering()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testOrdering().
	 *
	 * @return void
	 */
	public function testOrdering()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Tests the JHtmlList::users method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testUsers()
	{
		$this->assertThat(
			JHtml::_('list.users', 'user-list', '43', '1'),
			$this->StringContains('<option value="43" selected="selected">Publisher</option>')
		);

		$this->assertThat(
			JHtml::_('list.users', 'user-list', '42'),
			$this->StringContains('<option value="43">Publisher</option>')
		);
	}

	/**
	 * Tests the JHtmlList::positions method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testPositions()
	{
		$this->assertThat(
			JHtml::_('list.positions', 'position-list', 'center', null, '1', '1', '1', '1', 'positions'),
			$this->StringContains('<option value="left">Left</option>')
		);

	}
}
