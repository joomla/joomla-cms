<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\FilesystemHandler;

/**
 * Test class for Joomla\Session\Handler\FilesystemHandler.
 */
class FilesystemHandlerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * {@inheritdoc}
	 */
	public static function setUpBeforeClass()
	{
		// Make sure the handler is supported in this environment
		if (!FilesystemHandler::isSupported())
		{
			static::markTestSkipped('The FilesystemHandler is unsupported in this environment.');
		}
	}

	/**
	 * @covers  Joomla\Session\Handler\FilesystemHandler::isSupported()
	 */
	public function testTheHandlerIsSupported()
	{
		$this->assertTrue(FilesystemHandler::isSupported());
	}

	/**
	 * @covers             Joomla\Session\Handler\FilesystemHandler::__construct()
	 * @expectedException  \InvalidArgumentException
	 */
	public function testTheHandlerHandlesAnInvalidPath()
	{
		new FilesystemHandler('totally;invalid;string;for;this;object');
	}

	/**
	 * @covers  Joomla\Session\Handler\FilesystemHandler::__construct()
	 */
	public function testTheHandlerIsInstantiatedCorrectly()
	{
		$phpSessionPath = ini_get('session.save_path');

		if (empty($phpSessionPath))
		{
			$this->setExpectedException('\InvalidArgumentException');
		}

		$handler = new FilesystemHandler;

		$this->assertSame('files', ini_get('session.save_handler'));
	}

	/**
	 * @covers  Joomla\Session\Handler\FilesystemHandler::__construct()
	 */
	public function testTheHandlerIsInstantiatedCorrectlyAndCreatesTheSavePathIfNeeded()
	{
		$handler = new FilesystemHandler(__DIR__ . '/savepath');

		$this->assertSame(__DIR__ . '/savepath', ini_get('session.save_path'));
		$this->assertTrue(is_dir(realpath(__DIR__ . '/savepath')));

		rmdir(__DIR__ . '/savepath');
	}

	/**
	 * @param   string  $savePath          The path to inject into the handler
	 * @param   string  $expectedSavePath  The expected save path in the PHP configuration
	 * @param   string  $path              The expected filesystem path for the handler
	 *
	 * @dataProvider  savePathDataProvider
	 *
	 * @covers  Joomla\Session\Handler\FilesystemHandler::__construct()
	 */
	public function testTheHandlerIsInstantiatedCorrectlyAndHandlesAllParametersAsExpected($savePath, $expectedSavePath, $path)
	{
		$handler = new FilesystemHandler($savePath);
		$this->assertEquals($expectedSavePath, ini_get('session.save_path'));
		$this->assertTrue(is_dir(realpath($path)));

		rmdir($path);
	}

	/**
	 * Data provider with expected paths for handler construction
	 *
	 * @return  array
	 */
	public function savePathDataProvider()
	{
		$base = sys_get_temp_dir();

		return array(
			array("$base/savepath", "$base/savepath", "$base/savepath"),
			array("5;$base/savepath", "5;$base/savepath", "$base/savepath"),
			array("5;0600;$base/savepath", "5;0600;$base/savepath", "$base/savepath"),
		);
	}
}
