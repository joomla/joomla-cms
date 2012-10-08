<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/html/user.php';

/**
 * Test class for JHtmlUser.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       11.4
 */
class JHtmlUserTest extends TestCaseDatabase
{
	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  xml dataset
	 *
	 * @since   11.4
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/testfiles/JHtmlTest.xml');
	}

	/**
	 * Tests the JHtmlUser::groups method.
	 *
	 * @return  void
	 *
	 * @since   11.4
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
	 * @since   11.4
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
