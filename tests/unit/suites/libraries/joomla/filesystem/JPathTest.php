<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Filesystem
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JLoader::register('JPath', JPATH_PLATFORM . '/joomla/filesystem/path.php');

/**
 * Tests for the JPath class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Filesystem
 * @since       12.2
 */
class JPathTest extends TestCase
{
	/**
	 * Data provider for testClean() method.
	 *
	 * @return  array
	 *
	 * @since   12.2
	 */
	public function getCleanData()
	{
		return array(
			// Input Path, Directory Separator, Expected Output
			'Nothing to do.' => array('/var/www/foo/bar/baz', '/', '/var/www/foo/bar/baz'),
			'One backslash.' => array('/var/www/foo\\bar/baz', '/', '/var/www/foo/bar/baz'),
			'Two and one backslashes.' => array('/var/www\\\\foo\\bar/baz', '/', '/var/www/foo/bar/baz'),
			'Mixed backslashes and double forward slashes.' => array('/var\\/www//foo\\bar/baz', '/', '/var/www/foo/bar/baz'),
			'UNC path.' => array('\\\\www\\docroot', '\\', '\\\\www\\docroot'),
			'UNC path with forward slash.' => array('\\\\www/docroot', '\\', '\\\\www\\docroot'),
			'UNC path with UNIX directory separator.' => array('\\\\www/docroot', '/', '/www/docroot'),
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testCanChmod().
	 *
	 * @return void
	 */
	public function testCanChmod()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testSetPermissions().
	 *
	 * @return void
	 */
	public function testSetPermissions()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGetPermissions().
	 *
	 * @return void
	 */
	public function testGetPermissions()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testCheck().
	 *
	 * @return void
	 */
	public function testCheck()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Tests the clean method.
	 *
	 * @param   string  $input     @todo
	 * @param   string  $ds        @todo
	 * @param   string  $expected  @todo
	 *
	 * @return  void
	 *
	 * @covers        JPath::clean
	 * @dataProvider  getCleanData
	 * @since         12.2
	 */
	public function testClean($input, $ds, $expected)
	{
		$this->assertEquals(
			$expected,
			JPath::clean($input, $ds)
		);
	}

	/**
	 * Tests the JPath::clean method with an array as an input
	 *
	 * @return  void
	 *
	 * @expectedException  UnexpectedValueException
	 * @since   11.3
	 */
	public function testCleanArrayPath()
	{
		JPath::clean(array('/path/to/folder') );
	}


	/**
	 * Test...
	 *
	 * @todo Implement testIsOwner().
	 *
	 * @return void
	 */
	public function testIsOwner()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testFind().
	 *
	 * @return void
	 */
	public function testFind()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
