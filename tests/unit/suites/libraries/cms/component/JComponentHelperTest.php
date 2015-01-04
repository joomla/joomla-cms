<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JComponentHelper.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.2
 */
class JComponentHelperTest extends TestCaseDatabase
{
	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   3.2
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_extensions', JPATH_TEST_DATABASE . '/jos_extensions.csv');

		return $dataSet;
	}

	/**
	 * Test JComponentHelper::getComponent
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetComponent()
	{
		$component = JComponentHelper::getComponent('com_content');

		$this->assertEquals(
			$component->id,
			22,
			'com_content is extension ID 22'
		);
	}

	/**
	 * Test JComponentHelper::isEnabled
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testIsEnabled()
	{
		$this->assertTrue(
			(bool) JComponentHelper::isEnabled('com_content'),
			'com_content should be enabled'
		);
	}

	/**
	 * Test JComponentHelper::isInstalled
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testIsInstalled()
	{
		$this->assertTrue(
			(bool) JComponentHelper::isInstalled('com_content'),
			'com_content should be installed'
		);

		$this->assertFalse(
			(bool) JComponentHelper::isInstalled('com_willneverhappen'),
			'com_willneverhappen should not be enabled'
		);
	}

	/**
	 * Test JComponentHelper::getParams
	 *
	 * @return  void
	 */
	public function testGetParams()
	{
		$params = JComponentHelper::getParams('com_content');

		$this->assertEquals(
			$params->get('show_print_icon'),
			'1',
			"com_content's show_print_icon param should be set to 1"
		);
	}
}
