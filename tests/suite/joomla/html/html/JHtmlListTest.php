<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/html/html/list.php';

/**
 * Test class for JHtmlList.
 *
 * @since  11.1
 */
class JHtmlListTest extends JoomlaDatabaseTestCase
{
	/**
	 * @var    JHtmlBatch
	 * @since  11.3
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  xml dataset
	 *
	 * @since   11.3
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__.'/testfiles/JHtmlTest.xml');
	}

	/**
	 * @todo Implement testAccesslevel().
	 */
	public function testAccesslevel()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testImages().
	 */
	public function testImages()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testGenericordering().
	 */
	public function testGenericordering()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testSpecificordering().
	 */
	public function testSpecificordering()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testOrdering().
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
	 */
	public function testUsers()
	{
		$this->assertThat(
			JHtmlList::users('user-list', '43'),
			$this->StringContains('<option value="43" selected="selected">Publisher</option>')
		);
	}

	/**
	 * @todo Implement testPositions().
	 */
	public function testPositions()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testCategory().
	 */
	public function testCategory()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}
}