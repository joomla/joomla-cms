<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * A unit test class for SubjectClass
 * The two annotations below are required because we use mocks.  This avoids bringing bogus classes into the main process.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @since       11.1
 */
class JCacheControllerCallbackTest_Callback extends PHPUnit_Framework_TestCase
{

	/**
	 * Setup.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		// @todo remove: require_once dirname(dirname(dirname(dirname(dirname(__DIR__))))).'/bootstrap.php';
		jimport('joomla.cache.cache');

		require_once dirname(__DIR__) . '/storage/JCacheStorageMock.php';

		require_once __DIR__ . '/JCacheControllerCallback.helper.php';

		// Some tests are affected by the output of the logger, so we clear the logger here.
		JLog::setInstance(null);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testCallbackFunction()
	{
		/*
		$cache =& JCache::getInstance('callback', array('storage'=>'mock'));
				$arg1 = 'e1';
				$arg2 = 'e2';
				$callback = 'testCallbackHandlerFunc';
				$this->expectOutputString('e1e1e1e1e1');
				for($i = 0; $i < 5; $i++) {
					$result = $cache->get($callback, array($arg1, $arg2));
					$this->assertTrue($arg2 === $result,
						'Expected: '.$arg2.' Actual: '.$result
					);
				}*/
	}

	/**
	 * Test callbackStatic
	 *
	 * @return void
	 */
	public function testCallbackStatic()
	{
		$cache =& JCache::getInstance('callback', array('storage' => 'mock'));
		$arg1 = 'e1';
		$arg2 = 'e2';
		$callback = array('testCallbackController', 'staticCallback');
		$this->expectOutputString('e1e1e1e1e1');

		for ($i = 0; $i < 5; $i++)
		{
			$result = $cache->get($callback, array($arg1, $arg2));
			$this->assertTrue(
				$arg2 === $result,
				'Expected: ' . $arg2 . ' Actual: ' . $result
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
		$cache =& JCache::getInstance('callback', array('storage' => 'mock'));
		$arg1 = 'e1';
		$arg2 = 'e2';
		$this->expectOutputString('e1e1e1e1e1');

		for ($i = 0; $i < 5; $i++)
		{
			$instance = new testCallbackController;
			$result = $cache->get(array($instance, 'instanceCallback'), array($arg1, $arg2));
			$this->assertTrue(
				$arg2 === $result,
				'Expected: ' . $arg2 . ' Actual: ' . $result
			);
			unset($instance);
		}
	}

}
