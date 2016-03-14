<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Archive
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
	protected $outputPath;

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

		$this->outputPath = __DIR__ . '/output';

		if (!is_dir($this->outputPath))
		{
			mkdir($this->outputPath, 0777);
		}

		if (! is_dir($this->outputPath))
		{
			$this->markTestSkipped('We can not create the output dir, so skip all tests');
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
		if (is_dir($this->outputPath))
		{
			// delete files in output directory
			foreach(glob("{$this->outputPath}/*") as $file)
			{
				unlink($file);
			}
			rmdir($this->outputPath);
		}

		parent::tearDown();
	}
}
