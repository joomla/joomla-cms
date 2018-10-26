<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache;

defined('_JEXEC') or die;

/**
 * Interface defining a factory which can create CacheController objects
 *
 * @since  __DEPLOY_VERSION__
 */
interface CacheControllerFactoryInterface
{
	/**
	 * Method to get an instance of a cache controller.
	 *
	 * @param   string  $type     The cache object type to instantiate
	 * @param   array   $options  Array of options
	 *
	 * @return  CacheController
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	public function createCacheController($type = 'output', $options = array()): CacheController;
}
