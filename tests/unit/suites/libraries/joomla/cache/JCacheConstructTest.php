<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JCache.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @since       1.7.0
 */
class JCacheTest_Construct extends \PHPUnit\Framework\TestCase
{
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
