<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cache
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Cache\Helpers;

defined('_JEXEC') or die;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Cache\Controller\PageController;

/**
 * Joomla! Page Cache Plugin - PageCacheStorage.
 *
 * @since  4.1
 */
final class PageCacheStorage
{
	/**
	 * Cache instance.
	 *
	 * @var    Joomla\CMS\Cache\Controller\PageController
	 * @since  __DEPLOY_VERSION__
	 */
	private $cache;

	/**
	 * Constructor.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(int $browsercache)
	{
		$this->cache = $this->createCache($browsercache);
	}

	/**
	 * Store
	 *
	 * @param   object $body  		The page body to be stored.
	 * @param   string $cacheKey	Cache Key
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function store(string $body, string $cacheKey): void
	{
		$this->cache->store($body, $cacheKey);
	}

	/**
	 * Read
	 *
	 * @param   string $cacheKey 	Cache Key
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function read(string $cacheKey)
	{
		return $this->cache->get($cacheKey);
	}

	/**
	 * Create Cache
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function createCache(int $browsercache): PageController
	{
		// Set the cache options.
		$options = array(
			'defaultgroup' => 'page',
			'browsercache' => $browsercache,
			'caching'      => true,
		);

		// Instantiate cache with previous options and create the cache key identifier.
		return Cache::getInstance('page', $options);
	}
}
