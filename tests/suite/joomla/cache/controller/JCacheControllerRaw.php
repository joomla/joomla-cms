<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Interface to JCache methods.  Used for testing of cache storage.
 */
class JCacheControllerRaw extends JCache
{
	protected function setUp()
	{
		include_once JPATH_PLATFORM.'/joomla/cache/cache.php';
		include_once JPATH_PLATFORM.'/joomla/cache/storage.php';
		include_once JPATH_PLATFORM.'/joomla/cache/controller.php';

		//$this->object = JCache::getInstance('', array());
	}

}
