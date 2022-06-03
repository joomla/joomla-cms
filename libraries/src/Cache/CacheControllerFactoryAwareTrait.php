<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Defines the trait for a CacheControllerFactoryInterface Aware Class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait CacheControllerFactoryAwareTrait
{
	/**
	 * CacheControllerFactoryInterface
	 *
	 * @var    CacheControllerFactoryInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $cacheControllerFactory;

	/**
	 * Get the CacheControllerFactoryInterface.
	 *
	 * @return  CacheControllerFactoryInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getCacheControllerFactory(): CacheControllerFactoryInterface
	{
		if ($this->cacheControllerFactory)
		{
			return $this->cacheControllerFactory;
		}

		@trigger_error(
			sprintf('A cache controller is needed in %s. An UnexpectedValueException will be thrown in 5.0.', __CLASS__),
			E_USER_DEPRECATED
		);

		return Factory::getContainer()->get(CacheControllerFactoryInterface::class);
	}

	/**
	 * Set the cache controller factory to use.
	 *
	 * @param   CacheControllerFactoryInterface  $cacheControllerFactory  The cache controller factory to use.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setCacheControllerFactory(CacheControllerFactoryInterface $cacheControllerFactory = null): void
	{
		$this->cacheControllerFactory = $cacheControllerFactory;
	}
}
