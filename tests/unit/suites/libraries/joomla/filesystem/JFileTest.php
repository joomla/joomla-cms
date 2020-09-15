<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
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
	 * Test...
	 *
	 * @return void
	 */
	public function testGetExt()
	{
		$this->assertEquals(
			'php',
			JFile::getExt(__FILE__)
		);

		$this->assertEquals(
			'yml',
			JFile::getExt('C:\\server\\joomla\\.drone.yml')
		);

		$this->assertEquals(
			'php_cs',
			JFile::getExt('/home/joomla/.php_cs')
		);

		$this->assertEquals(
			'',
			JFile::getExt('joomla-cms/LICENSE')
		);

		$this->assertEquals(
			'',
			JFile::getExt('/joomla.git/tmpfile')
		);

		$this->assertEquals(
			'',
			JFile::getExt('\\joomla.git\\tmpfile')
		);
	}
}
