<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Plugins
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for FileSystem Local plugin.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Plugins
 * @since       __DEPLOY_VERSION__
 */
class PlgFileSystemLocalTest extends TestCaseDatabase
{
	/**
	 * Variable to hold plugin
	 *
	 * @var   PlgFileSystemLocal
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private $pluginClass = null;

	/**
	 * Variable to hold root path for mock folder
	 *
	 * @var   string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private $root = null;

	/**
	 * Setup for testing
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function setUp()
	{
		// Set up the application and session
		JFactory::$application = $this->getMockCmsApp();
		JFactory::$session     = $this->getMockSession();

		// Register the needed classes
		JLoader::register('JPath', JPATH_PLATFORM . '/joomla/filesystem/path.php');
		JLoader::register('JFolder', JPATH_PLATFORM . '/joomla/filesystem/folder.php');

		// Import plugin
		JLoader::import('filesystem.local.local', JPATH_PLUGINS);

		$dispatcher = $this->getMockDispatcher();
		$plugin = array(
			'name' => 'local',
			'type' => 'filesystem',
			'params' => new \JRegistry,
		);

		// Instantiate plugin
		$this->pluginClass = new PlgFileSystemLocal($dispatcher, $plugin);

		// Set up the temp root folder
		$this->root = JPath::clean(JPATH_TESTS . '/tmp/test/');
		JFolder::create($this->root);
	}

	/**
	 * Cleans the test folder
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function tearDown()
	{
		JFolder::delete($this->root);
	}

	/**
	 * Tests event onFileSystemGetAdapters
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function testOnFileSystemGetAdapters()
	{
		$adapter = $this->pluginClass->onFileSystemGetAdapters($this->root);
		self::assertInstanceOf('MediaFileAdapterLocal', $adapter);
	}
}
