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
class MediaFileAdapterLocalTest extends TestCaseDatabase
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
		// Set up the application and session
		JFactory::$application = $this->getMockCmsApp();
		JFactory::$session     = $this->getMockSession();

		// Register the needed classes
		JLoader::register('JPath', JPATH_PLATFORM . '/joomla/filesystem/path.php');
		JLoader::register('JFile', JPATH_PLATFORM . '/joomla/filesystem/file.php');
		JLoader::register('JFolder', JPATH_PLATFORM . '/joomla/filesystem/folder.php');

		JLoader::import('filesystem.local.adapter.adapter', JPATH_PLUGINS);

		// Set up the temp root folder
		$this->root = JPath::clean(JPATH_TESTS . '/tmp/test/');
		JFolder::create($this->root);
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
	 * Test MediaFileAdapterLocal::getFile
	 *
	 * @return  void
	 */
	public function testGetFile()
	{
		// Make some test files
		JFile::write($this->root . 'test.txt', 'test');

		// Create the adapter
		$adapter = new MediaFileAdapterLocal($this->root);

		// Fetch the file from the root folder
		$file = $adapter->getFile('test.txt');

		// Check if the array is big enough
		$this->assertNotEmpty($file);

		// Check the file
		$this->assertInstanceOf('stdClass', $file);
		$this->assertEquals('file', $file->type);
		$this->assertEquals('test.txt', $file->name);
		$this->assertEquals('/test.txt', $file->path);
		$this->assertEquals('txt', $file->extension);
		$this->assertGreaterThan(1, $file->size);
		$this->assertNotEmpty($file->create_date);
		$this->assertNotEmpty($file->modified_date);
		$this->assertEquals('text/plain', $file->mime_type);
		$this->assertEquals(0, $file->width);
		$this->assertEquals(0, $file->height);
	}

	/**
	 * Test MediaFileAdapterLocal::getFile with an invalid path
	 *
	 * @expectedException MediaFileAdapterFilenotfoundexception
	 *
	 * @return  void
	 */
	public function testGetFileInvalidPath()
	{
		// Create the adapter
		$adapter = new MediaFileAdapterLocal($this->root);

		// Fetch the file from the root folder
		$adapter->getFile('invalid');
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
		$this->assertEquals('/unit', $files[0]->path);
		$this->assertEquals('', $files[0]->extension);
		$this->assertEquals(0, $files[0]->size);
		$this->assertNotEmpty($files[0]->create_date);
		$this->assertNotEmpty($files[0]->modified_date);
		$this->assertEquals('directory', $files[0]->mime_type);
		$this->assertEquals(0, $files[0]->width);
		$this->assertEquals(0, $files[0]->height);

		// Check the file
		$this->assertInstanceOf('stdClass', $files[1]);
		$this->assertEquals('file', $files[1]->type);
		$this->assertEquals('test.txt', $files[1]->name);
		$this->assertEquals('/test.txt', $files[1]->path);
		$this->assertEquals('txt', $files[1]->extension);
		$this->assertGreaterThan(1, $files[1]->size);
		$this->assertNotEmpty($files[1]->create_date);
		$this->assertNotEmpty($files[1]->modified_date);
		$this->assertEquals('text/plain', $files[1]->mime_type);
		$this->assertEquals(0, $files[1]->width);
		$this->assertEquals(0, $files[1]->height);
	}

	/**
	 * Test MediaFileAdapterLocal::getFiles with a filter
	 *
	 * @return  void
	 */
	public function testGetFilteredFiles()
	{
		// Make some test files
		JFile::write($this->root . 'test.txt', 'test');
		JFile::write($this->root . 'foo.txt', 'test');
		JFile::write($this->root . 'bar.txt', 'test');
		JFolder::create($this->root . 'unit');
		JFolder::create($this->root . 'foo');

		// Create the adapter
		$adapter = new MediaFileAdapterLocal($this->root);

		// Fetch the files from the root folder
		$files = $adapter->getFiles('/', 'foo');

		// Check if the array is big enough
		$this->assertNotEmpty($files);
		$this->assertCount(2, $files);

		// Check the folder
		$this->assertEquals('dir', $files[0]->type);
		$this->assertEquals('foo', $files[0]->name);
		$this->assertEquals('/foo', $files[0]->path);

		// Check the file
		$this->assertInstanceOf('stdClass', $files[1]);
		$this->assertEquals('file', $files[1]->type);
		$this->assertEquals('foo.txt', $files[1]->name);
		$this->assertEquals('/foo.txt', $files[1]->path);
	}

	/**
	 * Test MediaFileAdapterLocal::getFiles with a single file
	 *
	 * @return  void
	 */
	public function testGetSingleFile()
	{
		// Make some test files
		JFile::write($this->root . 'test.txt', 'test');

		// Create the adapter
		$adapter = new MediaFileAdapterLocal($this->root);

		// Fetch the files from the root folder
		$files = $adapter->getFiles('test.txt');

		// Check if the array is big enough
		$this->assertNotEmpty($files);
		$this->assertCount(1, $files);

		// Check the file
		$this->assertInstanceOf('stdClass', $files[0]);
		$this->assertEquals('file', $files[0]->type);
		$this->assertEquals('test.txt', $files[0]->name);
		$this->assertEquals('/test.txt', $files[0]->path);
		$this->assertEquals('txt', $files[0]->extension);
		$this->assertGreaterThan(1, $files[0]->size);
		$this->assertNotEmpty($files[0]->create_date);
		$this->assertNotEmpty($files[0]->modified_date);
		$this->assertEquals('text/plain', $files[0]->mime_type);
		$this->assertEquals(0, $files[0]->width);
		$this->assertEquals(0, $files[0]->height);
	}

	/**
	 * Test MediaFileAdapterLocal::getFiles with an invalid path
	 *
	 * @expectedException MediaFileAdapterFilenotfoundexception
	 *
	 * @return  void
	 */
	public function testGetFilesInvalidPath()
	{
		// Create the adapter
		$adapter = new MediaFileAdapterLocal($this->root);

		// Fetch the file from the root folder
		$adapter->getFiles('invalid');
	}

	/**
	 * Test MediaFileAdapterLocal::createFolder
	 *
	 * @return  void
	 */
	public function testCreateFolder()
	{
		// Create the adapter
		$adapter = new MediaFileAdapterLocal($this->root);

		// Fetch the files from the root folder
		$adapter->createFolder('unit', '/');

		// Check if the file exists
		$this->assertTrue(JFolder::exists($this->root . 'unit'));
	}

	/**
	 * Test MediaFileAdapterLocal::createFile
	 *
	 * @return  void
	 */
	public function testCreateFile()
	{
		// Create the adapter
		$adapter = new MediaFileAdapterLocal($this->root);

		// Fetch the files from the root folder
		$adapter->createFile('unit.txt', '/', 'test');

		// Check if the file exists
		$this->assertTrue(file_exists($this->root . 'unit.txt'));

		// Check if the contents is correct
		$this->assertEquals('test', file_get_contents($this->root . 'unit.txt'));
	}

	/**
	 * Test MediaFileAdapterLocal::updateFile
	 *
	 * @return  void
	 */
	public function testUpdateFile()
	{
		// Make some test files
		JFile::write($this->root . 'unit.txt', 'test');

		// Create the adapter
		$adapter = new MediaFileAdapterLocal($this->root);

		// Fetch the files from the root folder
		$adapter->updateFile('unit.txt', '/', 'test 2');

		// Check if the file exists
		$this->assertTrue(file_exists($this->root . 'unit.txt'));

		// Check if the contents is correct
		$this->assertEquals('test 2', file_get_contents($this->root . 'unit.txt'));
	}

	/**
	 * Test MediaFileAdapterLocal::getFile with an invalid path
	 *
	 * @expectedException MediaFileAdapterFilenotfoundexception
	 *
	 * @return  void
	 */
	public function testUpdateFileInvalidPath()
	{
		// Create the adapter
		$adapter = new MediaFileAdapterLocal($this->root);

		// Fetch the file from the root folder
		$adapter->updateFile('invalid', '/', 'test');
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

	/**
	 * Test MediaFileAdapterLocal::getFile with an invalid path
	 *
	 * @expectedException MediaFileAdapterFilenotfoundexception
	 *
	 * @return  void
	 */
	public function testDeleteInvalidPath()
	{
		// Create the adapter
		$adapter = new MediaFileAdapterLocal($this->root);

		// Fetch the file from the root folder
		$adapter->delete('invalid');
	}

	/**
	 * Cleans the root folder
	 *
	 */
	private function cleanRootFolder()
	{
		JFolder::delete($this->root);
		JFolder::create($this->root);
	}

	/**
	 * MediaFileAdapterLocal::copy with a file
	 *
	 * @return void
	 */
	public function testFileCopy()
	{
		$adapter = new MediaFileAdapterLocal($this->root);

		$this->cleanRootFolder();

		JFile::write($this->root . 'test-src.txt', 'test');
		JFolder::create($this->root . 'src');

		// Test file copy
		$adapter->copy('test-src.txt', 'src/test-dest.txt');
		$this->assertTrue(JFile::exists($this->root . 'src/test-dest.txt'));
	}

	/**
	 * MediaFileAdapterLocal::copy with a file without force condition
	 * When destination already has a file with same name it will throw an exception
	 *
	 * @return void
	 */
	public function testFileCopyWithoutForce()
	{
		$adapter = new MediaFileAdapterLocal($this->root);

		$this->cleanRootFolder();

		JFile::write($this->root . 'test-src.txt', 'test 1');
		JFolder::create($this->root . 'src');
		JFile::write($this->root . 'src/test-src.txt', 'test 2');

		$this->setExpectedException('Exception');
		$adapter->copy('test-src.txt', 'src/test-src.txt');
	}

	/**
	 * MediaFileAdapterLocal::copy with a file with force condition
	 * This will overwrite if file exists on destination
	 *
	 * @return void
	 */
	public function testFileCopyWithForce()
	{
		$adapter = new MediaFileAdapterLocal($this->root);

		$this->cleanRootFolder();

		JFile::write($this->root . 'test-src.txt', 'test 1');
		JFolder::create($this->root . 'src');
		JFile::write($this->root . 'src/test-src.txt', 'test 2');

		$adapter->copy('test-src.txt', 'src/test-src.txt', true);
		$this->assertTrue(JFile::exists($this->root . 'src/test-src.txt'));

		$str = file_get_contents($this->root . 'src/test-src.txt');
		$this->assertContains('test 1', $str);
	}

	/**
	 * MediaFileAdapterLocal::copy with invalid path
	 *
	 * @expectedException MediaFileAdapterFilenotfoundexception
	 * @return void
	 */
	public function testFileCopyInvalidPath()
	{
		$adapter = new MediaFileAdapterLocal($this->root);

		$this->cleanRootFolder();

		$this->setExpectedException('MediaFileAdapterFilenotfoundexception');
		$adapter->copy('invalid', 'invalid');
	}

	/**
	 * MediaFileAdapterLocal::copy with a folder
	 *
	 * @return void
	 */
	public function testFolderCopy()
	{
		$adapter = new MediaFileAdapterLocal($this->root);
		$this->cleanRootFolder();

		// Make some mock folders in the root
		JFile::write($this->root . 'test-src.txt', 'test');
		JFolder::create($this->root . 'src');
		JFile::write($this->root . 'src/bar.txt', 'bar');

		// Test Folder copy
		$adapter->copy('src', 'dest');
		$this->assertTrue(JFolder::exists($this->root . 'dest'));
		$this->assertTrue(JFile::exists($this->root . 'dest/bar.txt'));
	}

	/**
	 * MediaFileAdapterLocal::copy with a folder without force condition
	 * When destination has the same folder, it will throw an exception
	 *
	 * @expectedException Exception
	 * @return void
	 */
	public function testFolderCopyWithoutForce()
	{
		$adapter = new MediaFileAdapterLocal($this->root);
		$this->cleanRootFolder();

		// Make some mock folders in the root
		JFolder::create($this->root . 'src');
		JFile::write($this->root . 'src/bar.txt', 'bar');
		JFile::write($this->root . 'src/file', 'content');

		// Create some conflicts
		JFolder::copy($this->root . 'src', $this->root . 'dest/some/src', '', true);

		// Test folder copy without force
		$this->setExpectedException('Exception');
		$adapter->copy('src', 'dest/some/src');
	}

	/**
	 * MediaFileAdapterLocal::copy with folder, force enabled
	 * It will silently overwrite files in destination
	 *
	 * @return void
	 */
	public function testFolderCopyWithForce()
	{
		$adapter = new MediaFileAdapterLocal($this->root);
		$this->cleanRootFolder();

		// Make some mock folders in the root
		JFolder::create($this->root . 'src');
		JFile::write($this->root . 'src/bar.txt', 'bar');
		JFile::write($this->root . 'src/file', 'content');

		// Create some conflicts
		JFolder::copy($this->root . 'src', $this->root . 'dest/some/src', '', true);

		// Test folder copy without force
		$adapter->copy('src', 'dest/some/src', true);
		$this->assertTrue(JFile::exists($this->root . 'dest/some/src/file'));
	}

	/**
	 * MediaFileAdapterLocal::move with a file
	 *
	 * @return void
	 */
	public function testMoveFile()
	{
		$adapter = new MediaFileAdapterLocal($this->root);
		$this->cleanRootFolder();

		// Make some mock folders in the root
		JFile::write($this->root . 'src-text.txt', 'some text here');
		JFolder::create($this->root . 'src');
		JFile::write($this->root . 'src/bar-test.txt', 'bar');

		// Test file move
		$adapter->move('src-text.txt', 'dest-text.txt');
		$this->assertTrue(JFile::exists($this->root . 'dest-text.txt'));
		$this->assertFalse(JFile::exists('src-text.txt'));
	}

	/**
	 * MediaFileAdapterLocal::move with a file, without force
	 *
	 * @expectedException Exception
	 * @return void
	 */
	public function testMoveFileWithoutForce()
	{
		$adapter = new MediaFileAdapterLocal($this->root);
		$this->cleanRootFolder();

		// Create some conflicts
		JFile::write($this->root. 'src/some-text', 'some text');
		JFile::write($this->root. 'src/some-another-text', 'some other text');
		JFolder::create($this->root . 'src/some/folder');
		JFile::write($this->root . 'dest/some-text', 'some another text');

		// Test file move without force
		$this->setExpectedException('Exception');
		$adapter->move('src/some-text', 'dest/some-text');
	}

	/**
	 * MediaFileAdapterLocal::move with a file force enabled
	 * It will silently overwrite the file in destination
	 *
	 * @return void
	 */
	public function testMoveFileWithForce()
	{
		$adapter = new MediaFileAdapterLocal($this->root);
		$this->cleanRootFolder();

		// Create some conflicts
		JFile::write($this->root. 'src/some-text', 'some text');
		JFile::write($this->root . 'dest/some-text', 'some another text');

		// Test file move without force
		$adapter->move('src/some-text', 'dest/some-text', true);
		$this->assertFalse(JFile::exists($this->root . 'src/some-text'));

		// Checks file is the moved one from src
		$string = file_get_contents($this->root . 'dest/some-text');
		$this->assertContains('some text', $string);
	}

	/**
	 * MediaFileAdapterLocal::move with a folder
	 *
	 * @return void
	 */
	public function testMoveFolder()
	{
		$adapter = new MediaFileAdapterLocal($this->root);
		$this->cleanRootFolder();

		JFile::write($this->root . 'src-text.txt', 'some text here');
		JFolder::create($this->root . 'src');
		JFile::write($this->root . 'src/bar-test.txt', 'bar');

		$adapter->move('src', 'dest');
		$this->assertTrue(JFolder::exists($this->root . 'dest'));
		$this->assertTrue(JFile::exists($this->root . 'dest/bar-test.txt'));
		$this->assertFalse(JFile::exists('src'));
	}

	/**
	 * MediaFileAdapterLocal::move with a folder without force enabled
	 *
	 * @expectedException Exception
	 * @return void
	 */
	public function testMoveFolderWithoutForce()
	{
		$adapter = new MediaFileAdapterLocal($this->root);
		$this->cleanRootFolder();

		// Create some conflicts
		JFile::write($this->root. 'src/some-text', 'some text');
		JFile::write($this->root. 'src/some-another-text', 'some other text');
		JFolder::create($this->root . 'src/some/folder');
		JFile::write($this->root . 'dest/some-text', 'some another text');

		$this->setExpectedException('Exception');
		$adapter->move('src', 'dest');
	}

	/**
	 * MediaFileAdapterLocal::move with a folder with force enabled
	 * It will silently overwrrite files and folders in the destination
	 *
	 * @return void
	 */
	public function testMoveFolderWithForce()
	{
		$adapter = new MediaFileAdapterLocal($this->root);
		$this->cleanRootFolder();

		// Create some conflicts
		JFile::write($this->root. 'src/some-text', 'some text');
		JFile::write($this->root. 'src/some-another-text', 'some other text');
		JFolder::create($this->root . 'src/some/folder');
		JFile::write($this->root . 'dest/some-text', 'some another text');

		$adapter->move('src', 'dest', true);
		$this->assertTrue(JFile::exists($this->root . 'dest/some-another-text'));
		$this->assertTrue(JFolder::exists($this->root . 'dest/some/folder'));
		$this->assertFalse(JFolder::exists($this->root . 'src'));
	}

	/**
	 * MediaFileAdapterLocal::move with an invalid path
	 *
	 * @expectedException MediaFileAdapterFilenotfoundexception
	 * @return void
	 */
	public function testMoveInvalidPath()
	{
		$adapter = new MediaFileAdapterLocal($this->root);
		$this->cleanRootFolder();

		$this->setExpectedException('MediaFileAdapterFilenotfoundexception');
		$adapter->move('invalid', 'invalid-new');
	}

}
