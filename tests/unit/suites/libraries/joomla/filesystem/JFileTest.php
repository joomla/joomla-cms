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
}
