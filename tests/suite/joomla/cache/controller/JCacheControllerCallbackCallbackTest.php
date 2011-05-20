<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * A unit test class for SubjectClass
 * The two annotations below are required because we use mocks.  This avoids bringing bogus classes into the main process.
 */
class JCacheControllerCallbackTest_Callback extends PHPUnit_Extensions_OutputTestCase
{

	public function setUp() {
		//require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/bootstrap.php';
		jimport('joomla.cache.cache');

		require_once dirname(dirname(__FILE__)).'/storage/JCacheStorageMock.php';

		require_once dirname(__FILE__).'/JCacheControllerCallback.helper.php';

	}

	public function testCallbackFunction() {
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

	public function testCallbackStatic() {
		$cache =& JCache::getInstance('callback', array('storage'=>'mock'));
		$arg1 = 'e1';
		$arg2 = 'e2';
		$callback = array('testCallbackController', 'staticCallback');
		$this->expectOutputString('e1e1e1e1e1');
		for($i = 0; $i < 5; $i++) {
			$result = $cache->get($callback, array($arg1, $arg2));
			$this->assertTrue($arg2 === $result,
				'Expected: '.$arg2.' Actual: '.$result
			);
		}
	}

	public function testCallbackInstance() {
		$cache =& JCache::getInstance('callback', array('storage'=>'mock'));
		$arg1 = 'e1';
		$arg2 = 'e2';
		$this->expectOutputString('e1e1e1e1e1');
		for($i = 0; $i < 5; $i++) {
			$instance = new testCallbackController();
			$result = $cache->get(array($instance, 'instanceCallback'), array($arg1, $arg2));
			$this->assertTrue($arg2 === $result,
				'Expected: '.$arg2.' Actual: '.$result
			);
			unset($instance);
		}
	}


}

