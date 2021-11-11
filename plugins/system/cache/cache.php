<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cache
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Profiler\Profiler;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\System\Cache\Helpers\PageCacheKeyGenerator;
use Joomla\Plugin\System\Cache\Helpers\PageCacheStorage;
use Joomla\Plugin\System\Cache\Helpers\PageCachingChecker;

// Why is not autolading the <namespace path="src">Joomla\Plugin\System\Cache</namespace>?
require 'src/Helpers/PageCacheKeyGenerator.php';
require 'src/Helpers/PageCachingChecker.php';
require 'src/Helpers/PageCacheStorage.php';

/**
 * Joomla! Page Cache Plugin.
 *
 * @since  1.5
 */
final class PlgSystemCache extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 * @since  3.8.0
	 */
	protected $app;

	/**
	 * Page Cache Storage
	 *
	 * @var    Joomla\Plugin\System\Cache\Helpers\PageCacheStorage
	 * @since  __DEPLOY_VERSION__
	 */
	protected $cacheStorage;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		$pageCacheKeyGenerator = new PageCacheKeyGenerator(Uri::getInstance());
		$cacheKey = $pageCacheKeyGenerator->getKey();

		if (!PageCachingChecker::canPageBeCached($cacheKey)) {
			return [];
		}

		// Singleton to the PageCacheKeyGenerator.
		Factory::getContainer()->set(
			PageCacheKeyGenerator::class,
			$pageCacheKeyGenerator,
			true
		);

		return [
			'onAfterRender' => 'verifyCanBeCached',
			'onAfterRoute' => 'checkAndDumpPage',
			'onAfterRespond' => 'storePage',
		];
	}

	/**
	 * Verify that current page is not excluded from cache.
	 *
	 * @return   void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function verifyCanBeCached()
	{
		// Check if the user is a guest again because auto-login plugins
		// 	have not been fired when getSubscribedEvents was called.
		if (!PageCachingChecker::canPageBeCached($this->getCacheKey()))
		{
			return;
		}

		// Disable compression before caching the page.
		$this->app->set('gzip', false);
	}

	/**
	 * After Respond Event.
	 * Stores page in cache.
	 *
	 * @return   void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function storePage()
	{
		// if it can't be cached due to execution conditions
		if (!$this->cacheStorage)
		{
			return;
		}

		// Saves current page in cache.
		$this->cacheStorage->store($this->app->getBody(), $this->getCacheKey());
	}

	/**
	 * Checks if URL exists in cache, if so dumps it directly and closes.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function checkAndDumpPage()
	{
		$cacheKey = $this->getCacheKey();

		// If any pagecache plugins return false for onPageCacheSetCaching, do not use the cache.
		PluginHelper::importPlugin('pagecache');

		$results = $this->app->triggerEvent('onPageCacheSetCaching');
		$onPageCacheSetCaching = !in_array(false, $results, true);

		// Double check for BC
		if (!$onPageCacheSetCaching || !PageCachingChecker::canPageBeCached($cacheKey))
		{
			return;
		}

		$this->cacheStorage = new PageCacheStorage($this->params->get('browsercache', 0));
		$body = $this->cacheStorage->read($cacheKey);

		if (!$body)
		{
			return;
		}

		$this->dumpCachedPage($body);
	}

	/**
	 * Get a cache key for the current page based on the url and possible other factors.
	 *
	 * @return  string
	 *
	 * @since   3.7
	 */
	protected function getCacheKey()
	{
		return Factory::getContainer()->get(PageCacheKeyGenerator::class)->getKey();
	}

	/**
	 * Dump Cached Page.
	 *
	 * @param   object $body	The cached page body.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function dumpCachedPage(string $body)
	{
		// Set HTML page from cache.
		$this->app->setBody($body);

		// Dumps HTML page.
		echo $this->app->toString((bool) $this->app->get('gzip'));

		// Mark afterCache in debug and run debug onAfterRespond events, e.g. show Joomla Debug Console if debug is active.
		if (JDEBUG)
		{
			// Create a document instance and load it into the application.
			$document = Factory::getContainer()->get('document.factory')->createDocument($this->app->input->get('format', 'html'));
			$this->app->loadDocument($document);

			Profiler::getInstance('Application')->mark('afterCache');
			$this->app->triggerEvent('onAfterRespond');
		}

		// Closes the application.
		$this->app->close();
	}
}
