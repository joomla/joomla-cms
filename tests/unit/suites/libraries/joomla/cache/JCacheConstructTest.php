<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JCache.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @since       11.1
 */
class JCacheTest_Construct extends TestCase
{
	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
	}

	/**
	 * Tears down the fixture.
	 *
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Test...
	 *
	 * @return array
	 */
	public static function provider()
	{
		return array(
			array('callback'),
			array('output'),
			array('page'),
			array('view')
		);
	}

	/**
	 * Test...
	 *
	 * @param   string  $type  @todo
	 *
	 * @dataProvider provider
	 *
	 * @return void
	 */
	public function testConstruct($type)
	{
		$class = 'JCacheController' . ucfirst($type);
		$cache = JCache::getInstance($type);
		$this->assertInstanceOf(
			$class, $cache, 'Expecting= ' . $class . ' Returned= ' . get_class($cache)
		);
		$cache2 = JCache::getInstance($type);
		$this->assertNotSame(
			$cache, $cache2, 'Type: ' . $type . ' received the same instance twice'
		);
	}
}
