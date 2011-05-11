<?php
/**
 * Joomla! v1.5 Unit Test Facility
 *
 * @package Joomla
 * @subpackage UnitTest
 * @copyright Copyright (C) 2005 - 2011 Open Source Matters, Inc.
 * @version $Id$
 *
 */


jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');

require_once 'JFolder-helper-dataset.php';

/**
 * A unit test class for JRequest
 */
class JFolderTest_static extends PHPUnit_Framework_TestCase
{

	/**
	 * @todo Implement testCopy().
	 */
	public function testCopy() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testCreate().
	 */
	public function testCreate() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testDelete().
	 */
	public function testDelete() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testMove().
	 */
	public function testMove() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testExists().
	 */
	public function testExists() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * JFolder::files give an array of files found
	 */
	public function testFiles()
	{
		$this->_cleanupTestFiles();
		mkdir(JPath::clean(JPATH_ROOT . "/tmp/test"), 0777, true);
		file_put_contents(JPath::clean(JPATH_ROOT . "/tmp/test/index.html"), 'test');
		file_put_contents(JPath::clean(JPATH_ROOT . "/tmp/test/index.txt"), 'test');
		mkdir(JPath::clean(JPATH_ROOT . "/tmp/test/test"), 0777, true);
		file_put_contents(JPath::clean(JPATH_ROOT . "/tmp/test/test/index.html"), 'test');
		file_put_contents(JPath::clean(JPATH_ROOT . "/tmp/test/test/index.txt"), 'test');

		$expected = array(
			JPath::clean(JPATH_ROOT . "/tmp/test/index.txt"),
			JPath::clean(JPATH_ROOT . "/tmp/test/test/index.txt"));
		$this->assertEquals(
			$expected,
			JFolder::files(JPath::clean(JPATH_ROOT . "/tmp/test"), 'index.*', true, true, array('index.html')),
			'Line: ' . __LINE__. ' Should exclude index.html files');
		$this->assertEquals(
			array(
				JPath::clean(JPATH_ROOT . "/tmp/test/index.html"),
				JPath::clean(JPATH_ROOT . "/tmp/test/test/index.html")),
			JFolder::files(JPath::clean(JPATH_ROOT . "/tmp/test"), 'index.html', true, true),
			'Line: ' . __LINE__. ' Should include full path of both index.html files');
		$this->assertEquals(
			array(
				JPath::clean("index.html"),
				JPath::clean("index.html")),
			JFolder::files(JPath::clean(JPATH_ROOT . "/tmp/test"), 'index.html', true, false),
			'Line: ' . __LINE__. ' Should include only file names of both index.html files');
		$this->assertEquals(
			array(
				JPath::clean(JPATH_ROOT . "/tmp/test/index.html")),
			JFolder::files(JPath::clean(JPATH_ROOT . "/tmp/test"), 'index.html', false, true),
			'Line: ' . __LINE__. ' Non-recursive should only return top folder file full path');
		$this->assertEquals(
			array(
				JPath::clean("index.html")),
			JFolder::files(JPath::clean(JPATH_ROOT . "/tmp/test"), 'index.html', false, false),
			'Line: ' . __LINE__. ' non-recursive should return only file name of top folder file');

		$this->assertFalse(
			JFolder::files('/this/is/not/a/path'), 'Line: ' . __LINE__. ' Non-existent path should return false');

		$this->assertEquals(
			array(),
			JFolder::files(JPath::clean(JPATH_ROOT . "/tmp/test"), 'nothing.here', true, true, array(), array()),
			'Line: ' . __LINE__. ' When nothing matches the filter, should return empty array');

		$this->_cleanupTestFiles();
	}

	/**
	 * JFolder::folders give an array of folders found
	 */
	public function testFolders()
	{
		mkdir(JPath::clean(JPATH_ROOT . "/tmp/test"), 0777, true);
		mkdir(JPath::clean(JPATH_ROOT . "/tmp/test/foo1"), 0777, true);
		mkdir(JPath::clean(JPATH_ROOT . "/tmp/test/foo1/bar1"), 0777, true);
		mkdir(JPath::clean(JPATH_ROOT . "/tmp/test/foo1/bar2"), 0777, true);
		mkdir(JPath::clean(JPATH_ROOT . "/tmp/test/foo2"), 0777, true);
		mkdir(JPath::clean(JPATH_ROOT . "/tmp/test/foo2/bar1"), 0777, true);
		mkdir(JPath::clean(JPATH_ROOT . "/tmp/test/foo2/bar2"), 0777, true);
		$this->assertEquals(
			array(),
			JFolder::folders(JPath::clean(JPATH_ROOT . "/tmp/test"), 'bar1', true, true, array('foo1', 'foo2')));
		$this->assertEquals(
			array(
				JPath::clean(JPATH_ROOT . "/tmp/test/foo2/bar1"),
			),
			JFolder::folders(JPath::clean(JPATH_ROOT . "/tmp/test"), 'bar1', true, true, array('foo1')));
		$this->assertEquals(
			array(
				JPath::clean(JPATH_ROOT . "/tmp/test/foo1/bar1"),
				JPath::clean(JPATH_ROOT . "/tmp/test/foo2/bar1"),
			),
			JFolder::folders(JPath::clean(JPATH_ROOT . "/tmp/test"), 'bar1', true, true));
		$this->assertEquals(
			array(
				JPath::clean(JPATH_ROOT . "/tmp/test/foo1/bar1"),
				JPath::clean(JPATH_ROOT . "/tmp/test/foo1/bar2"),
				JPath::clean(JPATH_ROOT . "/tmp/test/foo2/bar1"),
				JPath::clean(JPATH_ROOT . "/tmp/test/foo2/bar2"),
			),
			JFolder::folders(JPath::clean(JPATH_ROOT . "/tmp/test"), 'bar', true, true));
		$this->assertEquals(
			array(
				JPath::clean(JPATH_ROOT . "/tmp/test/foo1"),
				JPath::clean(JPATH_ROOT . "/tmp/test/foo1/bar1"),
				JPath::clean(JPATH_ROOT . "/tmp/test/foo1/bar2"),
				JPath::clean(JPATH_ROOT . "/tmp/test/foo2"),
				JPath::clean(JPATH_ROOT . "/tmp/test/foo2/bar1"),
				JPath::clean(JPATH_ROOT . "/tmp/test/foo2/bar2"),
			),
			JFolder::folders(JPath::clean(JPATH_ROOT . "/tmp/test"), '.', true, true));
		$this->assertEquals(
			array(
				JPath::clean("bar1"),
				JPath::clean("bar1"),
				JPath::clean("bar2"),
				JPath::clean("bar2"),
				JPath::clean("foo1"),
				JPath::clean("foo2"),
			),
			JFolder::folders(JPath::clean(JPATH_ROOT . "/tmp/test"), '.', true, false));
		$this->assertEquals(
			array(
				JPath::clean(JPATH_ROOT . "/tmp/test/foo1"),
				JPath::clean(JPATH_ROOT . "/tmp/test/foo2"),
			),
			JFolder::folders(JPath::clean(JPATH_ROOT . "/tmp/test"), '.', false, true));
		$this->assertEquals(
			array(
				JPath::clean("foo1"),
				JPath::clean("foo2"),
			),
			JFolder::folders(JPath::clean(JPATH_ROOT . "/tmp/test"), '.', false, false, array(), array()));

		$this->assertFalse(
			JFolder::folders('this/is/not/a/path'),
			'Line: ' . __LINE__. ' Non-existent path should return false');

		rmdir(JPath::clean(JPATH_ROOT . "/tmp/test/foo2/bar2"));
		rmdir(JPath::clean(JPATH_ROOT . "/tmp/test/foo2/bar1"));
		rmdir(JPath::clean(JPATH_ROOT . "/tmp/test/foo2"));
		rmdir(JPath::clean(JPATH_ROOT . "/tmp/test/foo1/bar2"));
		rmdir(JPath::clean(JPATH_ROOT . "/tmp/test/foo1/bar1"));
		rmdir(JPath::clean(JPATH_ROOT . "/tmp/test/foo1"));
		rmdir(JPath::clean(JPATH_ROOT . "/tmp/test"));
	}

	/**
	 * @todo Implement ListFolderTree().
	 */
	public function testListFolderTree() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	static public function makeSafeData() {
		return JFolderTest_DataSet::$makeSafeTests;
	}

	/**
	 * @dataProvider makeSafeData
	 */
	public function testMakeSafeFromDataSet($path, $expect) {
		$actual = JFolder::makeSafe($path);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Convenience method to cleanup for testFiles
	 */
	private function _cleanupTestFiles() {
		$this->_cleanupFile(JPath::clean(JPATH_ROOT . "/tmp/test/test/index.html"));
		$this->_cleanupFile(JPath::clean(JPATH_ROOT . "/tmp/test/test/index.txt"));
		$this->_cleanupFile(JPath::clean(JPATH_ROOT . "/tmp/test/test"));
		$this->_cleanupFile(JPath::clean(JPATH_ROOT . "/tmp/test/index.html"));
		$this->_cleanupFile(JPath::clean(JPATH_ROOT . "/tmp/test/index.txt"));
		$this->_cleanupFile(JPath::clean(JPATH_ROOT . "/tmp/test"));
	}

	/**
	 * Convenience method to clean up for files test
	 *
	 */
	private function _cleanupFile($path) {
		if (file_exists($path)) {
			if (is_file($path)) {
				unlink($path);
			}
			elseif (is_dir($path)) {
				rmdir($path);
			}
		}

	}
}

