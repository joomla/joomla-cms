<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache;

\defined('_JEXEC') or die;

/**
 * Interface defining a factory which can create CacheController objects
 *
 * @since  4.0.0
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
	 * @since   4.0.0
	 * @throws  \RuntimeException
	 */
	public function createCacheController($type = 'output', $options = array()): CacheController;
}
