<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Plugins
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Plugin\Filesystem\Local\Adapter\LocalAdapter;

/**
 * Test class for local file adapter.
 *
 * @package     Joomla.UnitTest
 * @subpackage  com_media
 * @since       4.0.0
 */
class LocalAdapterTest extends TestCaseDatabase
{
	/**
	 * The root folder to work from.
	 *
	 * @var string
	 */
	private $root = null;

	/**
	 * The image folder path related to root
	 *
	 * @var string
	 */
	private $imagePath = null;

	/**
	 * Sets up the environment.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		$this->saveFactoryState();

		// Set up the application and session
		JFactory::$application = $this->getMockCmsApp();
		JFactory::$session     = $this->getMockSession(['get.user.id' => 1]);

		// Register the needed classes
		JLoader::register('JPath', JPATH_PLATFORM . '/joomla/filesystem/path.php');
		JLoader::register('JFile', JPATH_PLATFORM . '/joomla/filesystem/file.php');
		JLoader::register('JFolder', JPATH_PLATFORM . '/joomla/filesystem/folder.php');

		// Set up the temp root folder
		$this->imagePath = 'tmp/test/';
		$this->root      = JPath::clean(JPATH_TESTS . '/tmp/test/');
		JFolder::create($this->root);

		JFactory::$application->getConfig()->set('root_user', 1);
	}

	/**
	 * Cleans up the test folder.
	 *
	 * @return  void
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		// Delete the temp root folder
		JFolder::delete($this->root);
	}


	/**
	 * Test LocalAdapter::getFile
	 *
	 * @return  void
	 */
	public function testGetFile()
	{
		// Make some test files
		JFile::write($this->root . 'test.txt', 'test');

		// Create the adapter
		$adapter = new LocalAdapter($this->root, $this->imagePath);

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
	 * Test LocalAdapter::getFile with an invalid path
	 *
	 * @expectedException \Joomla\Component\Media\Administrator\Exception\FileNotFoundException
	 *
	 * @return  void
	 */
	public function testGetFileInvalidPath()
	{
		// Create the adapter
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		// Fetch the file from the root folder
		$adapter->getFile('invalid');
	}

	/**
	 * Test LocalAdapter::getFile with an invalid path
	 *
	 * @expectedException \Joomla\Component\Media\Administrator\Exception\InvalidPathException
	 *
	 * @return  void
	 */
	public function testGetFileIllegalPath()
	{
		// Create the adapter
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		// Fetch the file from the root folder
		$adapter->getFile('/..');
	}

	/**
	 * Test LocalAdapter::getFiles
	 *
	 * @return  void
	 */
	public function testGetFiles()
	{
		// Make some test files
		JFile::write($this->root . 'test.txt', 'test');
		JFolder::create($this->root . 'unit');

		// Create the adapter
		$adapter = new LocalAdapter($this->root, $this->imagePath);

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
		$this->assertEquals('', $files[0]->size);
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
	 * Test LocalAdapter::getFiles with a single file
	 *
	 * @return  void
	 */
	public function testGetSingleFile()
	{
		// Make some test files
		JFile::write($this->root . 'test.txt', 'test');

		// Create the adapter
		$adapter = new LocalAdapter($this->root, $this->imagePath);

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
	 * Test LocalAdapter::getFiles with an invalid path
	 *
	 * @expectedException \Joomla\Component\Media\Administrator\Exception\FileNotFoundException
	 *
	 * @return  void
	 */
	public function testGetFilesInvalidPath()
	{
		// Create the adapter
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		// Fetch the file from the root folder
		$adapter->getFiles('invalid');
	}

	/**
	 * Test LocalAdapter::getFiles with an invalid path
	 *
	 * @expectedException \Joomla\Component\Media\Administrator\Exception\InvalidPathException
	 *
	 * @return  void
	 */
	public function testGetFilesIllegalPath()
	{
		// Create the adapter
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		// Fetch the file from the root folder
		$adapter->getFiles('../..');
	}

	/**
	 * Test LocalAdapter::createFolder
	 *
	 * @return  void
	 */
	public function testCreateFolder()
	{
		// Create the adapter
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		// Fetch the files from the root folder
		$adapter->createFolder('unit', '/');

		// Check if the file exists
		$this->assertTrue(JFolder::exists($this->root . 'unit'));
	}

	/**
	 * Test LocalAdapter::createFolder with an invalid file name.
	 *
	 * @return  void
	 */
	public function testCreateFolderInvalidName()
	{
		// Create the adapter
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		// Fetch the files from the root folder
		$name = $adapter->createFolder('invalid"name', '/');

		// Check if the illegal characters are stripped
		$this->assertEquals('invalidname', $name);

		// Check if the file exists
		$this->assertTrue(JFolder::exists($this->root . 'invalidname'));
	}

	/**
	 * Test LocalAdapter::createFolder with an illegal path.
	 *
	 * @expectedException \Joomla\Component\Media\Administrator\Exception\InvalidPathException
	 *
	 * @return  void
	 */
	public function testCreateFolderIllegalPath()
	{
		// Create the adapter
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		// Fetch the files from the root folder
		$adapter->createFolder('unit', '/../..');
	}

	/**
	 * Test LocalAdapter::createFile
	 *
	 * @return  void
	 */
	public function testCreateFile()
	{
		// Create the adapter
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		// Fetch the files from the root folder
		$adapter->createFile('unit.txt', '/', 'test');

		// Check if the file exists
		$this->assertFileExists($this->root . 'unit.txt');

		// Check if the contents is correct
		$this->assertEquals('test', file_get_contents($this->root . 'unit.txt'));
	}

	/**
	 * Test LocalAdapter::createFile with an invalid file name.
	 *
	 * @return  void
	 */
	public function testCreateFileInvalidName()
	{
		// Create the adapter
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		// Fetch the files from the root folder
		$name = $adapter->createFile('invalid"name.txt', '/', 'test');

		// Check if the illegal characters are stripped
		$this->assertEquals('invalidname.txt', $name);

		// Check if the file exists
		$this->assertTrue(file_exists($this->root . 'invalidname.txt'));

		// Check if the contents is correct
		$this->assertEquals('test', file_get_contents($this->root . 'invalidname.txt'));
	}

	/**
	 * Test LocalAdapter::createFile with an illegal path.
	 *
	 * @expectedException \Joomla\Component\Media\Administrator\Exception\InvalidPathException
	 *
	 * @return  void
	 */
	public function testCreateFileIllegalName()
	{
		// Create the adapter
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		$adapter->createFile('name.txt', '/../', 'test');
	}

	/**
	 * Test LocalAdapter::updateFile
	 *
	 * @return  void
	 */
	public function testUpdateFile()
	{
		// Make some test files
		JFile::write($this->root . 'unit.txt', 'test');

		// Create the adapter
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		// Fetch the files from the root folder
		$adapter->updateFile('unit.txt', '/', 'test 2');

		// Check if the file exists
		$this->assertFileExists($this->root . 'unit.txt');

		// Check if the contents is correct
		$this->assertEquals('test 2', file_get_contents($this->root . 'unit.txt'));
	}

	/**
	 * Test LocalAdapter::getFile with an invalid path
	 *
	 * @expectedException \Joomla\Component\Media\Administrator\Exception\FileNotFoundException
	 *
	 * @return  void
	 */
	public function testUpdateFileInvalidPath()
	{
		// Create the adapter
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		// Fetch the file from the root folder
		$adapter->updateFile('invalid', '/', 'test');
	}

	/**
	 * Test LocalAdapter::delete
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
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		// Fetch the files from the root folder
		$adapter->delete('unit');

		// Check if there are no folders anymore
		$this->assertEmpty(JFolder::folders($this->root));

		// Check if the files exists
		$this->assertCount(1, JFolder::files($this->root));
	}

	/**
	 * Test LocalAdapter::getFile with an invalid path
	 *
	 * @expectedException \Joomla\Component\Media\Administrator\Exception\FileNotFoundException
	 *
	 * @return  void
	 */
	public function testDeleteInvalidPath()
	{
		// Create the adapter
		$adapter = new LocalAdapter($this->root, $this->imagePath);

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
	 * LocalAdapter::copy with a file
	 *
	 * @return void
	 */
	public function testFileCopy()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		$this->cleanRootFolder();

		JFile::write($this->root . 'test-src.txt', 'test');
		JFolder::create($this->root . 'src');

		// Test file copy
		$adapter->copy('test-src.txt', 'src/test-dest.txt');
		$this->assertTrue(JFile::exists($this->root . 'src/test-dest.txt'));
	}

	/**
	 * LocalAdapter::copy with a file to a folder
	 *
	 * @return void
	 */
	public function testFileCopyToFolder()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		$this->cleanRootFolder();

		JFile::write($this->root . 'test-src.txt', 'test');
		JFolder::create($this->root . 'src');

		// Test file copy
		$adapter->copy('test-src.txt', 'src');
		$this->assertTrue(JFile::exists($this->root . 'src/test-src.txt'));
	}

	/**
	 * LocalAdapter::copy with a file without force condition
	 * When destination already has a file with same name it will throw an exception
	 *
	 * @expectedException \Exception
	 * @return void
	 */
	public function testFileCopyWithoutForce()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		$this->cleanRootFolder();

		JFile::write($this->root . 'test-src.txt', 'test 1');
		JFolder::create($this->root . 'src');
		JFile::write($this->root . 'src/test-src.txt', 'test 2');

		$adapter->copy('test-src.txt', 'src/test-src.txt');
	}

	/**
	 * LocalAdapter::copy with a file with force condition
	 * This will overwrite if file exists on destination
	 *
	 * @return void
	 */
	public function testFileCopyWithForce()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);

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
	 * LocalAdapter::copy with invalid path
	 *
	 * @expectedException \Joomla\Component\Media\Administrator\Exception\FileNotFoundException
	 * @return void
	 */
	public function testFileCopyInvalidPath()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		$this->cleanRootFolder();

		$adapter->copy('invalid', 'invalid');
	}

	/**
	 * LocalAdapter::copy with a file which has an invalid name.
	 *
	 * @return void
	 */
	public function testFileCopyInvalidName()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);

		$this->cleanRootFolder();

		JFile::write($this->root . 'test-src.txt', 'test');
		JFolder::create($this->root . 'src');

		// Test file copy
		$adapter->copy('test-src.txt', 'src/test-"dest.txt');
		$this->assertTrue(JFile::exists($this->root . 'src/test-dest.txt'));
	}

	/**
	 * LocalAdapter::copy with a folder
	 *
	 * @return void
	 */
	public function testFolderCopy()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
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
	 * LocalAdapter::copy with a folder without force condition
	 * When destination has the same folder, it will throw an exception
	 *
	 * @expectedException \Exception
	 * @return void
	 */
	public function testFolderCopyWithoutForce()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
		$this->cleanRootFolder();

		// Make some mock folders in the root
		JFolder::create($this->root . 'src');
		JFile::write($this->root . 'src/bar.txt', 'bar');
		JFile::write($this->root . 'src/file', 'content');

		// Create some conflicts
		JFolder::copy($this->root . 'src', $this->root . 'dest/some/src', '', true);

		// Test folder copy without force
		$adapter->copy('src', 'dest/some/src');
	}

	/**
	 * LocalAdapter::copy with folder, force enabled
	 * It will silently overwrite files in destination
	 *
	 * @return void
	 */
	public function testFolderCopyWithForce()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
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
	 * LocalAdapter::copy a folder to a file
	 *
	 * @return void
	 */
	public function testFolderCopyToFile()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
		$this->cleanRootFolder();

		// Make some mock folders in the root
		JFolder::create($this->root . 'src');
		JFile::write($this->root . 'src/bar.txt', 'bar');
		JFile::write($this->root . 'dest/bar.txt', 'bar');

		// Test folder copy to a file
		$adapter->copy('src', 'dest/bar.txt', true);
		$this->assertTrue(JFile::exists($this->root . 'dest/bar.txt/bar.txt'));
	}

	/**
	 * LocalAdapter::copy with a folder
	 *
	 * @return void
	 */
	public function testFolderCopyInvalidName()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
		$this->cleanRootFolder();

		// Make some mock folders in the root
		JFile::write($this->root . 'test-src.txt', 'test');
		JFolder::create($this->root . 'src');
		JFile::write($this->root . 'src/bar.txt', 'bar');

		// Test Folder copy
		$adapter->copy('src', 'dest"invalid');
		$this->assertTrue(JFolder::exists($this->root . 'destinvalid'));
		$this->assertTrue(JFile::exists($this->root . 'destinvalid/bar.txt'));
	}

	/**
	 * LocalAdapter::move with a file
	 *
	 * @return void
	 */
	public function testMoveFile()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
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
	 * LocalAdapter::move with a file to a folder
	 *
	 * @return void
	 */
	public function testMoveFileToFolder()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
		$this->cleanRootFolder();

		// Make some mock folders in the root
		JFile::write($this->root . 'src/src-text.txt', 'some text here');
		JFolder::create($this->root . 'dest');

		// Test file move
		$adapter->move('src/src-text.txt', 'dest');
		$this->assertTrue(JFile::exists($this->root . 'dest/src-text.txt'));
		$this->assertFalse(JFile::exists('src-text.txt'));
	}

	/**
	 * LocalAdapter::move with a file, without force
	 *
	 * @expectedException \Exception
	 * @return void
	 */
	public function testMoveFileWithoutForce()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
		$this->cleanRootFolder();

		// Create some conflicts
		JFile::write($this->root . 'src/some-text', 'some text');
		JFile::write($this->root . 'src/some-another-text', 'some other text');
		JFolder::create($this->root . 'src/some/folder');
		JFile::write($this->root . 'dest/some-text', 'some another text');

		// Test file move without force
		$adapter->move('src/some-text', 'dest/some-text');
	}

	/**
	 * LocalAdapter::move with a file force enabled
	 * It will silently overwrite the file in destination
	 *
	 * @return void
	 */
	public function testMoveFileWithForce()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
		$this->cleanRootFolder();

		// Create some conflicts
		JFile::write($this->root . 'src/some-text', 'some text');
		JFile::write($this->root . 'dest/some-text', 'some another text');

		// Test file move without force
		$adapter->move('src/some-text', 'dest/some-text', true);
		$this->assertFalse(JFile::exists($this->root . 'src/some-text'));

		// Checks file is the moved one from src
		$string = file_get_contents($this->root . 'dest/some-text');
		$this->assertContains('some text', $string);
	}

	/**
	 * LocalAdapter::move a file with an invalid name
	 *
	 * @return void
	 */
	public function testMoveFileInvalidName()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
		$this->cleanRootFolder();

		// Make some mock folders in the root
		JFile::write($this->root . 'src-text.txt', 'some text here');
		JFolder::create($this->root . 'src');
		JFile::write($this->root . 'src/bar-test.txt', 'bar');

		// Test file move
		$adapter->move('src-text.txt', 'dest-"text.txt');
		$this->assertTrue(JFile::exists($this->root . 'dest-text.txt'));
		$this->assertFalse(JFile::exists('src-text.txt'));
	}

	/**
	 * LocalAdapter::move with a folder
	 *
	 * @return void
	 */
	public function testMoveFolder()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
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
	 * LocalAdapter::move with a folder without force enabled
	 *
	 * @expectedException \Exception
	 * @return void
	 */
	public function testMoveFolderWithoutForce()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
		$this->cleanRootFolder();

		// Create some conflicts
		JFile::write($this->root . 'src/some-text', 'some text');
		JFile::write($this->root . 'src/some-another-text', 'some other text');
		JFolder::create($this->root . 'src/some/folder');
		JFile::write($this->root . 'dest/some-text', 'some another text');

		$adapter->move('src', 'dest');
	}

	/**
	 * LocalAdapter::move with a folder with force enabled
	 * It will silently overwrrite files and folders in the destination
	 *
	 * @return void
	 */
	public function testMoveFolderWithForce()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
		$this->cleanRootFolder();

		// Create some conflicts
		JFile::write($this->root . 'src/some-text', 'some text');
		JFile::write($this->root . 'src/some-another-text', 'some other text');
		JFolder::create($this->root . 'src/some/folder');
		JFile::write($this->root . 'dest/some-text', 'some another text');

		$adapter->move('src', 'dest', true);
		$this->assertTrue(JFile::exists($this->root . 'dest/some-another-text'));
		$this->assertTrue(JFolder::exists($this->root . 'dest/some/folder'));
		$this->assertFalse(JFolder::exists($this->root . 'src'));
	}

	/**
	 * LocalAdapter::move a folder with an invalid name
	 *
	 * @return void
	 */
	public function testMoveFolderInvalidName()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
		$this->cleanRootFolder();

		JFile::write($this->root . 'src-text.txt', 'some text here');
		JFolder::create($this->root . 'src');
		JFile::write($this->root . 'src/bar-test.txt', 'bar');

		$adapter->move('src', 'de"st');
		$this->assertTrue(JFolder::exists($this->root . 'dest'));
		$this->assertTrue(JFile::exists($this->root . 'dest/bar-test.txt'));
		$this->assertFalse(JFile::exists('src'));
	}

	/**
	 * LocalAdapter::move with an invalid path
	 *
	 * @expectedException \Joomla\Component\Media\Administrator\Exception\FileNotFoundException
	 * @return void
	 */
	public function testMoveInvalidPath()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
		$this->cleanRootFolder();

		$adapter->move('invalid', 'invalid-new');
	}

	/**
	 * LocalAdapter::copy a folder to a file
	 *
	 * @return void
	 */
	public function testMoveFolderToFile()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
		$this->cleanRootFolder();

		// Make some mock folders in the root
		JFolder::create($this->root . 'src');
		JFile::write($this->root . 'src/bar.txt', 'bar');
		JFile::write($this->root . 'dest/bar.txt', 'bar');

		// Test folder copy to a file
		$adapter->move('src', 'dest/bar.txt', true);
		$this->assertTrue(JFile::exists($this->root . 'dest/bar.txt/bar.txt'));
		$this->assertFalse(JFile::exists($this->root . 'src/bar.txt'));
	}

	/**
	 * LocalAdapter::getUrl to a file
	 *
	 * @return void
	 */
	public function testGetUrl()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
		$this->cleanRootFolder();

		// Sample name for a file in imagePath
		// We will not create actual file as we only check for valid url returned
		$filePath  = 'foo.bar';
		$urlPath   = $adapter->getUrl($filePath);
		$actualUrl =  \Joomla\CMS\Uri\Uri::root() . JPath::clean($this->imagePath . 'foo.bar');

		$this->assertSame($urlPath, $actualUrl);
	}

	/**
	 * LocalAdapter::getTemporaryUrl to a file
	 *
	 * @return void
	 */
	public function testGetTemporaryUrl()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
		$this->cleanRootFolder();

		// Sample name for a file in imagePath
		// We will not create actual file as we only check for valid url returned
		$filePath  = 'foo.bar';
		$urlPath   = $adapter->getTemporaryUrl($filePath);
		$actualUrl =  \Joomla\CMS\Uri\Uri::root() . JPath::clean($this->imagePath . 'foo.bar');

		$this->assertSame($urlPath, $actualUrl);
	}

	/**
	 * LocalAdapter::getAdapterName for image path
	 *
	 * @return void
	 */
	public function testGetAdapterName()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
		$this->cleanRootFolder();

		// Check if adapter name is equal to imagePath
		$this->assertEquals($adapter->getAdapterName(), $this->imagePath);
	}

	/**
	 * LocalAdapter::search for a file and folder
	 *
	 * @return void
	 */
	public function testSearch()
	{
		$adapter = new LocalAdapter($this->root, $this->imagePath);
		$this->cleanRootFolder();

		JFolder::create($this->root . 'foo/bar/foo.file');

		$results = $adapter->search('/', 'foo', true);
		$this->assertEquals($results[0]->path, '/foo');
		$this->assertEquals($results[1]->path, '/foo/bar/foo.file');
	}
}
