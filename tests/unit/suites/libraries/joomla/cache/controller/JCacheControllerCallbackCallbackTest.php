<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 */
class JCacheControllerCallbackTest_Callback extends TestCase
{

	/**
	 * Setup.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		require_once dirname(__DIR__) . '/storage/JCacheStorageMock.php';

		require_once __DIR__ . '/JCacheControllerCallback.helper.php';

		// Some tests are affected by the output of the logger, so we clear the logger here.
		JLog::setInstance(null);

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
	 * Test callbackStatic
	 *
	 * @return void
	 */
	public function testCallbackStatic()
	{
		$cache = JCache::getInstance('callback', array('storage' => 'mock'));
		$arg1 = 'e1';
		$arg2 = 'e2';
		$callback = array('testCallbackController', 'staticCallback');
		$this->expectOutputString('e1e1e1e1e1');

		for ($i = 0; $i < 5; $i++)
		{
			$result = $cache->get($callback, array($arg1, $arg2));
			$this->assertSame(
				$arg2,
				$result
			);
		}
	}

	/**
	 * Test callbackInstance
	 *
	 * @return void
	 */
	public function testCallbackInstance()
	{
		$cache = JCache::getInstance('callback', array('storage' => 'mock'));
		$arg1 = 'e1';
		$arg2 = 'e2';
		$this->expectOutputString('e1e1e1e1e1');

		for ($i = 0; $i < 5; $i++)
		{
			$instance = new testCallbackController;
			$result = $cache->get(array($instance, 'instanceCallback'), array($arg1, $arg2));
			$this->assertSame(
				$arg2,
				$result
			);
			unset($instance);
		}
	}
}
