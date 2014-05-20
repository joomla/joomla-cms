<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input\Tests;

use Joomla\Input\Files;

/**
 * Test class for JInputFiles.
 *
 * @since  1.0
 */
class FilesTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var    Files
	 * @since  1.0
	 */
	private $instance;

	/**
	 * Test the Joomla\Input\Files::get method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Files::get
	 * @since   1.0
	 */
	public function testGet()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Test the Joomla\Input\Files::set method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Files::set
	 * @since   1.0
	 */
	public function testSet()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Sets up the fixture.
	 *
	 * @return void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->instance = new Files;
	}
}
