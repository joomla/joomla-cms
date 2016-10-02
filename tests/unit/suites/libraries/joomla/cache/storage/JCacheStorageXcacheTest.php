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
 */
class JCacheStorageXcacheTest extends TestCaseCache
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		if (!JCacheStorageXcache::isSupported())
		{
			$this->markTestSkipped('The XCache cache handler is not supported on this system.');
		}

		parent::setUp();

		$this->handler = new JCacheStorageXcache;

		// Override the lifetime because the JCacheStorage API multiplies it by 60 (converts minutes to seconds)
		$this->handler->_lifetime = 2;
	}
}
