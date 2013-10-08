<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHtmlBatch.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlBatchTest extends TestCaseDatabase
{
	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockApplication();
		JFactory::$language = JLanguage::getInstance('en-GB', false);

		$this->backupServer = $_SERVER;

		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['SCRIPT_NAME'] = '';
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;

		$this->restoreFactoryState();

		parent::tearDown();
	}

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

		$dataSet->addTable('jos_categories', JPATH_TEST_DATABASE . '/jos_categories.csv');
		$dataSet->addTable('jos_languages', JPATH_TEST_DATABASE . '/jos_languages.csv');
		$dataSet->addTable('jos_users', JPATH_TEST_DATABASE . '/jos_users.csv');
		$dataSet->addTable('jos_viewlevels', JPATH_TEST_DATABASE . '/jos_viewlevels.csv');

		return $dataSet;
	}

	/**
	 * Tests the access method.
	 */
	public function testAccess()
	{
		$this->assertThat(
			JHtmlBatch::access(),
			$this->StringContains('<option value="1">Public</option>')
		);
	}

	/**
	 * Tests the item method.
	 */
	public function testItem()
	{
		$this->assertThat(
			JHtmlBatch::item('com_content'),
			$this->StringContains('<option value="9">Uncategorised</option>')
		);
	}

	/**
	 * Tests the language method.
	 */
	public function testLanguage()
	{
		$this->assertThat(
			JHtmlBatch::language(),
			$this->StringContains('<option value="en-GB">English (UK)</option>')
		);
	}

	/**
	 * Tests the user method.
	 */
	public function testUser()
	{
		$this->assertThat(
			JHtmlBatch::user(true),
			$this->StringContains('<option value="42">Super User</option>')
		);
	}
}
