<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Storage;

use Joomla\Session\Tests\StorageCase;
use Joomla\Session\Storage\Memcached as StorageMemcached;
use Joomla\Session\Storage;

/**
 * Test class for Joomla\Session\Storage\Memcached.
 *
 * @since  1.0
 */
class MemcachedTest extends StorageCase
{
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
		if (!class_exists('Memcached'))
		{
			$this->markTestSkipped(
				'The Memcached class does not exist.'
			);

			return;
		}

		// Create the caching object
		static::$object = Storage::getInstance('Memcached');

		// Parent contains the rest of the setup
		parent::setUp();
	}

	/**
	 * Test read default key and value,
	 * Storage\Memcached lets PHP read/write data directly
	 * via Session handlers so read is always null.
	 *
	 * @return void
	 */
	public function testRead()
	{
		static::$object->write(static::$key, static::$value);
		$this->assertThat(static::$object->read(static::$key), $this->equalTo(null), __LINE__);
	}

}
