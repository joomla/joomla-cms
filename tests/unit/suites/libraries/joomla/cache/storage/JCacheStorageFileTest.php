<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
	 * @access protected
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		include_once JPATH_PLATFORM . '/joomla/cache/storage.php';
		include_once JPATH_PLATFORM . '/joomla/cache/storage/file.php';

		$this->object = JCacheStorage::getInstance('file', array('cachebase' => JPATH_BASE . '/cache'));
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
				false,
			),
			'again' => array(
				43,
				'_testing',
				'The summer coder and the sunshine developer.',
				true,
				false,
			),
		);
	}

	/**
	 * Testing store() and get()
	 *
	 * @param   string  $id         element ID
	 * @param   string  $group      group
	 * @param   string  $data       string to be cached
	 * @param   string  $checktime  @todo
	 * @param   string  $expected   @todo
	 *
	 * @return void
	 *
	 * @dataProvider casesStore
	 */
	public function testStoreAndGet($id, $group, $data, $checktime, $expected)
	{
		$this->assertThat(
			$this->object->store($id, $group, $data),
			$this->isTrue(),
			'Should store the data properly'
		);

		$this->assertThat(
			$this->object->get($id, $group, $checktime),
			$this->equalTo($data),
			'Should retrieve the data properly'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testRemove().
	 *
	 * @return void
	 */
	public function testRemove()
	{
		$this->object->store(42, '_testing', 'And this is the cache that tries men\'s souls');

		$this->assertThat(
			$this->object->get(42, '_testing', true),
			$this->equalTo('And this is the cache that tries men\'s souls')
		);
		$this->assertThat(
			$this->object->remove(42, '_testing'),
			$this->isTrue()
		);
		$this->assertThat(
			$this->object->get(42, '_testing', true),
			$this->isFalse()
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testClean().
	 *
	 * @return void
	 */
	public function testClean()
	{
		$this->object->store(42, '_testing', 'And this is the cache that tries men\'s souls');
		$this->object->store(43, '_testing', 'The summer coder and the sunshine developer.');
		$this->object->store(44, '_nottesting', 'Now is the time for all good developers to cry');
		$this->object->store(45, '_testing', 'Do not go gentle into that good night');

		$this->assertThat(
			$this->object->get(42, '_testing', true),
			$this->equalTo('And this is the cache that tries men\'s souls')
		);

		$this->assertThat(
			$this->object->clean('_testing', 'group'),
			$this->isTrue()
		);

		$this->assertThat(
			$this->object->get(42, '_testing', true),
			$this->isFalse()
		);

		$this->assertThat(
			$this->object->get(43, '_testing', true),
			$this->isFalse()
		);

		$this->assertThat(
			$this->object->get(44, '_nottesting', true),
			$this->equalTo('Now is the time for all good developers to cry')
		);

		$this->assertThat(
			$this->object->get(45, '_testing', true),
			$this->isFalse()
		);

		$this->assertThat(
			(bool) $this->object->clean('_testing', 'notgroup'),
			$this->equalTo(true)
		);

		$this->assertThat(
			$this->object->get(44, '_nottesting', true),
			$this->isFalse()
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGc().
	 *
	 * @return void
	 */
	public function testGc()
	{
		$this->assertThat(
			(bool) $this->object->gc(),
			$this->isTrue()
		);
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
			$this->equalTo(is_writable(JPATH_BASE . '/cache')),
			'Claims File is not loaded.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement test_setExpire().
	 *
	 * @return void
	 */
	public function test_setExpire()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement test_getFilePath().
	 *
	 * @return void
	 */
	public function test_getFilePath()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
