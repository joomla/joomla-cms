<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

class JCacheTest_Construct extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		jimport('joomla.cache.cache');
	}

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
	 * @dataProvider provider
	 */
	function testConstruct($type)
	{
		$class = 'JCacheController'.ucfirst($type);
		$cache =& JCache::getInstance($type);
		$this -> assertTrue(($cache instanceof $class),
			'Expecting= '.$class.' Returned= '.get_class($cache)
		);
		$cache2 =& JCache::getInstance($type);
		$this -> assertTrue(($cache !== $cache2),
			'Type: '.$type.' Recieved the same instance twice'
		);
	}
}
