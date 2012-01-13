<?php
/**
 * Joomla! v1.5 Unit Test Facility
 *
 * @package Joomla
 * @subpackage UnitTest
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc.
 *
 */

/**
 * Interface to JCache methods.  Used for testing of cache storage.
 */
class JCacheControllerRaw extends JCache
{
	protected function setUp()
	{
		include_once JPATH_BASE.'/libraries/joomla/cache/cache.php';
		include_once JPATH_BASE.'/libraries/joomla/cache/storage.php';
		include_once JPATH_BASE.'/libraries/joomla/cache/controller.php';

		//$this->object = JCache::getInstance('', array());
	}

}
