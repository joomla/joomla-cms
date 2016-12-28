<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for local file adapter.
 *
 * @package     Joomla.UnitTest
 * @subpackage  com_media
 * @since       __DEPLOY_VERSION__
 */
class LocalAdapterTest extends PHPUnit_Framework_TestCase
{
	/**
	 * The root folder to work from.
	 *
	 * @var string
	 */
	private $root = null;

	/**
	 * Sets up the environment.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		// Register the needed classes
		JLoader::register('JPath', JPATH_PLATFORM . '/joomla/filesystem/path.php');
		JLoader::register('JFile', JPATH_PLATFORM . '/joomla/filesystem/file.php');
		JLoader::register('JFolder', JPATH_PLATFORM . '/joomla/filesystem/folder.php');

		// Add the media libraries to the class loader
		JLoader::discover('MediaFileAdapter', JPATH_ADMINISTRATOR . '/components/com_media/libraries/media/file/adapter', true, true);

		// Set up the temp root folder
		$this->root = JPath::clean(JPATH_TESTS . '/tmp/test/');
	}

	/**
	 * Cleans up the test folder.
	 *
	 * @return  void
	 */
	protected function tearDown()
	{
		// Delete the temp root folder
		JFolder::delete($this->root);
	}

	/**
	 * Test MediaFileAdapterLocal::getFiles
	 *
	 * @return  void
	 */
	public function testGetFiles()
	{
		// Make some test files
		JFile::write($this->root . 'test.txt', 'test');
		JFolder::create($this->root . 'unit');

		// Create the adapter
		$adapter = new MediaFileAdapterLocal($this->root);

		// Fetch the files from the root folder
		$files = $adapter->getFiles();

		// Check if the array is big enough
		$this->assertNotEmpty($files);
		$this->assertCount(2, $files);

		// Check the folder
		$this->assertInstanceOf('stdClass', $files[0]);
		$this->assertEquals('dir', $files[0]->type);
		$this->assertEquals('unit', $files[0]->name);
		$this->assertEquals('/', $files[0]->path);

		// Check the file
		$this->assertInstanceOf('stdClass', $files[1]);
		$this->assertEquals('file', $files[1]->type);
		$this->assertEquals('test.txt', $files[1]->name);
		$this->assertEquals('/', $files[1]->path);
	}

	/**
	 * Test MediaFileAdapterLocal::delete
	 *
	 * @return  void
	 */
	public function testDelete()
	{
		// Make some test files
		JFile::write($this->root . 'test.txt', 'test');
		JFolder::create($this->root . 'unit');
		JFile::write($this->root . 'unit/test.txt', 'test');

		// Create the adapter
		$adapter = new MediaFileAdapterLocal($this->root);

		// Fetch the files from the root folder
		$adapter->delete('unit');

		// Check if there are no folders anymore
		$this->assertEmpty(JFolder::folders($this->root));

		// Check if the files exists
		$this->assertCount(1, JFolder::files($this->root));
	}
}
