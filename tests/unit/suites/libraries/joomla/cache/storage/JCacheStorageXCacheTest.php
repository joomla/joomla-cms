<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCacheStorageXcache.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 * @since       11.1
 */
class JCacheStorageXcacheTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JCacheStorageXcache
	 */
	protected $object;

	/**
	 * @var    boolean
	 */
	protected $extensionAvailable;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$xcachetest = false;

		if (extension_loaded('xcache'))
		{
			// XCache Admin must be disabled for Joomla to use XCache
			$xcache_admin_enable_auth = ini_get('xcache.admin.enable_auth');

			// Some extensions ini variables are reported as strings
			if ($xcache_admin_enable_auth == 'Off')
			{
				$xcachetest = true;
			}

			// We require a string with contents 0, not a null value because it is not set since that then defaults to On/True
			if ($xcache_admin_enable_auth === '0')
			{
				$xcachetest = true;
			}

			// In some enviorments empty is equivalent to Off; See JC: #34044 && Github: #4083
			if ($xcache_admin_enable_auth === '')
			{
				$xcachetest = true;
			}
		}

		$this->extensionAvailable = $xcachetest;

		if ($this->extensionAvailable)
		{
			$this->object = JCacheStorage::getInstance('xcache');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * Testing gc().
	 *
	 * @return  void
	 */
	public function testGc()
	{
		$this->assertTrue(
			$this->object->gc(),
			'Should return default true'
		);
	}

	/**
	 * Testing isSupported().
	 *
	 * @return  void
	 */
	public function testIsSupported()
	{
		$this->assertEquals(
			$this->extensionAvailable,
			$this->object->isSupported(),
			'Claims Wincache is not loaded.'
		);
	}

	/**
	 * Testing lock().
	 *
	 * @return  void
	 */
	public function testLock()
	{
		$this->assertFalse(
			$this->object->lock(),
			'Should return default false'
		);
	}

	/**
	 * Testing unlock().
	 *
	 * @return  void
	 */
	public function testUnlock()
	{
		$this->assertFalse(
			$this->object->unlock(),
			'Should return default false'
		);
	}
}
