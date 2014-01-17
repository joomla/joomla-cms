<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Interface to JCache methods.  Used for testing of cache storage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @since       11.1
 */
class JCacheControllerRaw extends JCache
{
	/**
	 * Setup.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		include_once JPATH_PLATFORM . '/joomla/cache/cache.php';
		include_once JPATH_PLATFORM . '/joomla/cache/storage.php';
		include_once JPATH_PLATFORM . '/joomla/cache/controller.php';

		// @todo remove: $this->object = JCache::getInstance('', array());
	}

}
