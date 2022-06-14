<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache;

\defined('_JEXEC') or die;

/**
 * Interface to be implemented by classes depending on a cache controller factory.
 *
 * @since  __DEPLOY_VERSION__
 */
interface CacheControllerFactoryAwareInterface
{
	/**
	 * Set the cache controller factory to use.
	 *
	 * @param   CacheControllerFactoryInterface  $factory  The cache controller factory to use.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setCacheControllerFactory(CacheControllerFactoryInterface $factory): void;
}
