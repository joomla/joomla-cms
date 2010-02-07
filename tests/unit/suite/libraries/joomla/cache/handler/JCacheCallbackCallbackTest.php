<?php
/**
 * JCache Callback Tests
 *
 * @package Joomla
 * @subpackage UnitTest
 * @version $Id$
 * @author Anthony Ferrara
 */


require_once 'PHPUnit/Extensions/OutputTestCase.php';


/**
 * A unit test class for SubjectClass
 * The two annotations below are required because we use mocks.  This avoids bringing bogus classes into the main process.
 * @runTestsInSeparateProcesses
 */
class JCacheCallbackTest_Callback extends PHPUnit_Extensions_OutputTestCase
{

	public function setUp() {
		require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/bootstrap.php');
		jimport('joomla.cache.cache');

		require_once(dirname(dirname(__FILE__)).DS.'storage'.DS.'JCacheStorageMock.php');

		require_once(dirname(__FILE__).DS.'JCacheCallback.helper.php');

	}

	public function testCallbackFunction() {
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
		}
	}

	public function testCallbackStatic() {
		$cache =& JCache::getInstance('callback', array('storage'=>'mock'));
		$arg1 = 'e1';
		$arg2 = 'e2';
		$callback = array('testCallbackHandler', 'staticCallback');
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
			$instance = new testCallbackHandler();
			$result = $cache->get(array($instance, 'instanceCallback'), array($arg1, $arg2));
			$this->assertTrue($arg2 === $result,
				'Expected: '.$arg2.' Actual: '.$result
			);
			unset($instance);
		}
	}


}

