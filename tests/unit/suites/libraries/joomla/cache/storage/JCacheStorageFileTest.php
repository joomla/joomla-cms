<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JCacheStorageFile.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @since       11.1
 */
class JCacheStorageFileTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JCacheStorageFile
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

		$this->extensionAvailable = is_writable(JPATH_BASE . '/cache');

		if ($this->extensionAvailable)
		{
			$this->object = JCacheStorage::getInstance('file', array('cachebase' => JPATH_BASE . '/cache'));
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * Test Cases for get() / store()
	 *
	 * @return array
	 */
	public function casesStore()
	{
		return array(
			'souls' => array(
				42,
				'_testing',
				'And this is the cache that tries men\'s souls',
				true,
			),
			'again' => array(
				43,
				'_testing',
				'The summer coder and the sunshine developer.',
				true,
			),
		);
	}

	/**
	 * Testing store() and get()
	 *
	 * @param   string  $id         element ID
	 * @param   string  $group      group
	 * @param   string  $data       string to be cached
	 * @param   string  $checktime  True to verify cache time expiration threshold
	 *
	 * @return  void
	 *
	 * @dataProvider  casesStore
	 */
	public function testStoreAndGet($id, $group, $data, $checktime)
	{
		$this->assertTrue(
			$this->object->store($id, $group, $data),
			'Should store the data properly'
		);

		$this->assertEquals(
			$data,
			$this->object->get($id, $group, $checktime),
			'Should retrieve the data properly'
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetAll()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Testing remove()
	 *
	 * @return  void
	 */
	public function testRemove()
	{
		$this->object->store(42, '_testing', 'And this is the cache that tries men\'s souls');

		$this->assertEquals(
			'And this is the cache that tries men\'s souls',
			$this->object->get(42, '_testing', true)
		);

		$this->assertTrue(
			$this->object->remove(42, '_testing')
		);

		$this->assertFalse(
			$this->object->get(42, '_testing', true)
		);
	}

	/**
	 * Test clean()
	 *
	 * @return  void
	 */
	public function testClean()
	{
		$this->object->store(42, '_testing', 'And this is the cache that tries men\'s souls');
		$this->object->store(43, '_testing', 'The summer coder and the sunshine developer.');
		$this->object->store(44, '_nottesting', 'Now is the time for all good developers to cry');
		$this->object->store(45, '_testing', 'Do not go gentle into that good night');

		$this->assertEquals(
			'And this is the cache that tries men\'s souls',
			$this->object->get(42, '_testing', true)
		);

		$this->assertTrue(
			$this->object->clean('_testing', 'group')
		);

		$this->assertFalse(
			$this->object->get(42, '_testing', true)
		);

		$this->assertFalse(
			$this->object->get(43, '_testing', true)
		);

		$this->assertEquals(
			'Now is the time for all good developers to cry',
			$this->object->get(44, '_nottesting', true)
		);

		$this->assertFalse(
			$this->object->get(45, '_testing', true)
		);

		$this->assertTrue(
			(bool) $this->object->clean('_testing', 'notgroup')
		);

		$this->assertFalse(
			$this->object->get(44, '_nottesting', true)
		);
	}

	/**
	 * Test gc()
	 *
	 * @return  void
	 */
	public function testGc()
	{
		$this->assertTrue(
			(bool) $this->object->gc()
		);
	}

	/**
	 * Testing isSupported().
	 *
	 * @return void
	 */
	public function testIsSupported()
	{
		$this->assertEquals(
			$this->extensionAvailable,
			$this->object->isSupported(),
			'Claims File is not loaded.'
		);
	}
}
