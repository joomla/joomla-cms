<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JFile.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Event
 * @since       1.7.0
 */
class JFileTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testExists()
	{
		$this->assertTrue(
			JFile::exists(__FILE__)
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetName()
	{
		$this->assertEquals(
			'file.php',
			JFile::getName('C:\path\on\windows\file.php')
		);

		$this->assertEquals(
			'image.png',
			JFile::getName('/full/path/image.png')
		);

		$this->assertEquals(
			'nopath.csv',
			JFile::getName('nopath.csv')
		);
	}

	/**
	 * Data provider for testGetExt() method.
	 *
	 * @return  array
	 *
	 * @since  3.9.22
	 */
	public function getGetExtData()
	{
		return array(
			array(__FILE__),
			array('C:\\server\\joomla\\.drone.yml'),
			array('/home/joomla/.php_cs'),
			array('joomla-cms/LICENSE'),
			array('/joomla.git/tmpfile'),
			array('\\joomla.git\\tmpfile'),
			array('filewith.extentionand\behindit'),
		);
	}

	/**
	 * Test getExt() method.
	 *
	 * @param   string  $file  The file name
	 *
	 * @return  void
	 *
	 * @dataProvider  getGetExtData
	 * @since         3.9.22
	 */
	public function testGetExt($file)
	{
		$this->assertEquals(
			pathinfo($file, PATHINFO_EXTENSION),
			JFile::getExt($file)
		);
	}
}
