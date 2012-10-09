<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/html/list.php';

/**
 * Test class for JHtmlList.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       11.1
 */
class JHtmlListTest extends TestCaseDatabase
{
	/**
	 * @var    JHtmlList
	 * @since  11.3
	 */
	protected $object;

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  xml dataset
	 *
	 * @since   11.3
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/testfiles/JHtmlTest.xml');
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
	 * @since   11.3
	 */
	public function testUsers()
	{
		$this->assertThat(
			JHtmlList::users('user-list', '43', '1'),
			$this->StringContains('<option value="43" selected="selected">Publisher</option>')
		);

		$this->assertThat(
			JHtmlList::users('user-list', '42'),
			$this->StringContains('<option value="43">Publisher</option>')
		);
	}

	/**
	 * Tests the JHtmlList::positions method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testPositions()
	{
		// TODO: Replace JGLOBAL_LEFT with translated string
		$this->assertThat(
			JHtmlList::positions('position-list', 'center', null, '1', '1', '1', '1', 'positions'),
			$this->StringContains('<option value="left">JGLOBAL_LEFT</option>')
		);

	}
}
