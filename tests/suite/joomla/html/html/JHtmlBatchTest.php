<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/html/html/batch.php';

/**
 * Test class for JHtmlBatch.
 *
 * @since  11.3
 */
class JHtmlBatchTest extends JoomlaDatabaseTestCase
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
		return $this->createXMLDataSet(dirname(__FILE__).'/testfiles/JHtmlBatchTest.xml');
	}

	/**
	 * Tests the JHtmlBatch::access method.
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
	 * @param	string	$extension
	 */
	public function testItem()
	{
		$this->assertThat(
			JHtmlBatch::item('com_content'),
			$this->StringContains('<option value="2">Uncategorised</option>')
		);
	}
}