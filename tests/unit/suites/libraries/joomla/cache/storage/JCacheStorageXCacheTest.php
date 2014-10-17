<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCacheStorageXCache.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @since       11.1
 */
class JCacheStorageXCacheTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JCacheStorageXCache
	 * @access protected
	 */
	protected $object;

	/**
	 * @var    JCacheStorageXCache
	 * @access protected
	 */
	protected $xcacheAvailable;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		include_once JPATH_PLATFORM . '/joomla/cache/storage.php';
		include_once JPATH_PLATFORM . '/joomla/cache/storage/xcache.php';

		$this->xcacheAvailable = extension_loaded('xcache');
		$this->object = JCacheStorage::getInstance('xcache');
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Testing isSupported().
	 *
	 * @return void
	 */
	public function testIsSupported()
	{
		$this->assertThat(
			$this->object->isSupported(),
			$this->equalTo($this->xcacheAvailable),
			'Claims xcache is not loaded.'
		);
	}
}
