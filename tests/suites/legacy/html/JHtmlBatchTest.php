<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/legacy/html/batch.php';

/**
 * Test class for JHtmlBatch.
 *
 * @since  11.3
 */
class JHtmlBatchTest extends TestCaseDatabase
{
	/**
	 * @var    JHtmlBatch
	 * @since  11.3
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.3
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
		return $this->createXMLDataSet(JPATH_TESTS . '/suites/unit/joomla/html/html/testfiles/JHtmlTest.xml');
	}

	/**
	 * Tests the JHtmlBatch::access method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @covers  JHtmlBatch::access
	 */
	public function testAccess()
	{
		$this->assertThat(
			JHtmlBatch::access(),
			$this->StringContains('<option value="1">Public</option>')
		);
	}

	/**
	 * Tests the JHtmlBatch::item method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @covers  JHtmlBatch::item
	 */
	public function testItem()
	{
		$this->assertThat(
			JHtmlBatch::item('com_content'),
			$this->StringContains('<option value="2">Uncategorised</option>')
		);
	}

	/**
	 * Tests the JHtmlBatch::language method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @covers  JHtmlBatch::language
	 */
	public function testLanguage()
	{
		$this->assertThat(
			JHtmlBatch::language(),
			$this->StringContains('<option value="en-GB">English (UK)</option>')
		);
	}

	/**
	 * Tests the JHtmlBatch::user method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 * @covers  JHtmlBatch::user
	 */
	public function testUser()
	{
		$this->assertThat(
			JHtmlBatch::user(true),
			$this->StringContains('<option value="42">Super User</option>')
		);
	}
}
