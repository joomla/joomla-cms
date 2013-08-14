<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Archive
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Abstract test case for archive package tests
 *
 * @package     Joomla.UnitTest
 * @subpackage  Archive
 * @since       3.1
 */
abstract class JArchiveTestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * Output path
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected static $outputPath;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function setUp()
	{
		parent::setUp();

		static::$outputPath = __DIR__ . '/output';

		if (!is_dir(static::$outputPath))
		{
			mkdir(static::$outputPath, 0777);
		}
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function tearDown()
	{
		if (!is_dir(static::$outputPath))
		{
			rmdir(static::$outputPath);
		}

		parent::tearDown();
	}
}
