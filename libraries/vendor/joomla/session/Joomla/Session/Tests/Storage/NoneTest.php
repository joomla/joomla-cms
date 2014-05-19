<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Storage;

use Joomla\Session\Storage\None as StorageNone;
use Joomla\Session\Storage;

/**
 * Test class for Joomla\Session\Storage\None.
 *
 * @since  1.0
 */
class NoneTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test object
	 *
	 * @var    StorageNone
	 * @since  1.0
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->object = Storage::getInstance('None');
	}

	/**
	 * Test Joomla\Session\Storage\None::register().
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testRegister()
	{
		$this->assertThat(
			$this->object->register(),
			$this->equalTo(null)
		);
	}
}
