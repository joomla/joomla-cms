<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHtmlUser.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlUserTest extends TestCaseDatabase
{
	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_XmlDataSet
	 *
	 * @since   3.1
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/data/JHtmlTest.xml');
	}

	/**
	 * Tests the JHtmlUser::groups method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGroups()
	{
		$this->assertThat(
			JHtmlUser::groups(),
			$this->arrayHasKey('3'),
			'Line:' . __LINE__ . ' The groups method should an array with eight keys; key 3 is "- - - Super Users".'
		);
	}

	/**
	 * Tests the JHtmlUser::userlist method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testUserlist()
	{
		$this->assertThat(
			JHtmlUser::userlist(),
			$this->arrayHasKey('2'),
			'Line:' . __LINE__ . ' The userlist method should an array with four keys; key 2 is "Super User".'
		);
	}
}
