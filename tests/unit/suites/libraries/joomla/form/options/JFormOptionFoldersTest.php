<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadOptionClass('folders');

/**
 * Test class for JFormOptionFolders.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       12.3
 */
class JFormOptionFoldersTest extends TestCase
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
	 * Test that the correct options are generated.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetOptions()
	{
		$element = simplexml_load_string('<option type="folders" directory="." />');
		$options = JFormOption::getOptions($element, 'TestField');

		$this->assertLessThan(
			count($options),
			0,
			'Line:' . __LINE__ . ' There should be some options.'
		);

		// TODO: Test the various attributes.
	}
}
