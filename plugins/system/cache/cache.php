<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cache
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Profiler\Profiler;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

/**
 * Joomla! Page Cache Plugin.
 *
 * @since  1.5
 */
class PlgSystemCache extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Cache instance.
	 *
	 * @var    \Joomla\CMS\Cache\CacheController
	 * @since  1.5
	 */
	public $_cache;

	/**
	 * Cache key
	 *
	 * @var    string
	 * @since  3.0
	 */
	public $_cache_key;

	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 * @since  3.8.0
	 */
	protected $app;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   1.5
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Set the cache options.
		$options = array(
			'defaultgroup' => 'page',
			'browsercache' => $this->params->get('browsercache', 0),
			'caching'      => false,
		);

		// Instantiate cache with previous options and create the cache key identifier.
		$this->_cache     = Cache::getInstance('page', $options);
		$this->_cache_key = self::simpleCacheKey();
	}

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		if (!self::canPageBeCached()) {
			return [];
		}

		return [
			'onAfterRender' => 'verifyThatCurrentPageCanBeCached',
			'onAfterRoute' => 'dumpCachedPage',
			'onAfterRespond' => 'storePage',
		];
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
		static $key;

		if (!$key)
		{
			PluginHelper::importPlugin('pagecache');

			$parts = $this->app->triggerEvent('onPageCacheGetKey');
			$parts[] = Uri::getInstance()->toString();

			$key = md5(serialize($parts));
		}

		return $key;
	}

	/**
	 * onAfterRoute.
	 * Only for BC, not used on J4
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onAfterRoute()
	{
		$this->dumpCachedPage();
	}

	/**
	 * Checks if URL exists in cache, if so dumps it directly and closes.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function dumpCachedPage()
	{
		// If any pagecache plugins return false for onPageCacheSetCaching, do not use the cache.
		PluginHelper::importPlugin('pagecache');

		$results = $this->app->triggerEvent('onPageCacheSetCaching');
		$caching = !in_array(false, $results, true);

		// Double check for BC
		if ($caching && $this->canPageBeCached())
		{
			$this->_cache->setCaching(true);
		}

		$data = $this->_cache->get($this->getCacheKey());

		// If page exist in cache, show cached page.
		if ($data !== false)
		{
			// Set HTML page from cache.
			$this->app->setBody($data);

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

	/**
	 * onAfterRender.
	 * Only for BC, not used on J4.
	 *
	 * @return   void
	 *
	 * @since   3.9.12
	 */
	public function onAfterRender()
	{
		$this->verifyThatCurrentPageCanBeCached();
	}

	/**
	 * Verify that current page is not excluded from cache.
	 *
	 * @return   void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function verifyThatCurrentPageCanBeCached()
	{
		// Check if the user is a guest again because auto-login plugins
		// 	have not been fired when getSubscribedEvents was called.
		if (!$this->canPageBeCached())
		{
			$this->_cache->setCaching(false);

			return;
		}

		// Disable compression before caching the page.
		$this->app->set('gzip', false);
	}

	/**
	 * onAfterRespond.
	 * Only for BC, not used on J4
	 *
	 * @return   void
	 *
	 * @since   1.5
	 */
	public function onAfterRespond()
	{
		$this->storePage();
	}

	/**
	 * After Respond Event.
	 * Stores page in cache.
	 *
	 * @return   void
	 *
	 * @since   1.5
	 */
	public function storePage()
	{
		// if it can't be cached due to execution conditions (check verifyThatCurrentPageCanBeCached/canPageBeCached)
		if ($this->_cache->getCaching() === false)
		{
			return;
		}

		// Saves current page in cache.
		$this->_cache->store($this->app->getBody(), $this->getCacheKey());
	}

	/**
	 * Check if the page can be cached according to all conditions.
	 *
	 * @return   boolean  True if the page can be cached
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	private static function canPageBeCached(): bool
	{
		$app = Factory::getApplication();

		// Only GET requests are cached.
		if ($app->input->getMethod() !== 'GET')
		{
			return false;
		}

		// Run only when we're on Site Application side
		if (!$app->isClient('site'))
		{
			return false;
		}

		// If the site is offline, don't do anything
		if ((bool) $app->get('offline', '0'))
		{
			return false;
		}

		// The user is authenticated
		if (!$app->getIdentity()->guest) {
			return false;
		}

		// If there are messages in the queue, don't cache the page
		if (!empty($app->getMessageQueue()))
		{
			return false;
		}

		if (self::isExcludedPage()) {
			return false;
		}

		return true;
	}

	/**
	 * onAfterRoute.
	 * Only for BC, not used on J4
	 *
	 * @return   boolean  True if the page is excluded else false
	 *
	 * @since    3.5
	 */
	protected static function isExcluded()
	{
		return self::isExcludedPage();
	}

	/**
	 * Check if the page is excluded from the cache or not.
	 *
	 * @return   boolean  True if the page is excluded else false
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	private static function isExcludedPage(): bool
	{
		$app = Factory::getApplication();
		$plugin = PluginHelper::getPlugin('system', 'cache');
		$params = new Registry($plugin->params);

		// Check if menu items have been excluded.
		if ($exclusions = $params->get('exclude_menu_items', array()))
		{
			// Get the current menu item.
			$active = $app->getMenu()->getActive();

			if ($active && $active->id && in_array((int) $active->id, (array) $exclusions))
			{
				return true;
			}
		}

		// Check if regular expressions are being used.
		if ($exclusions = $params->get('exclude', ''))
		{
			// Normalize line endings.
			$exclusions = str_replace(array("\r\n", "\r"), "\n", $exclusions);

			// Split them.
			$exclusions = explode("\n", $exclusions);

			// Gets internal URI.
			$internalUri = '/index.php?' . Uri::getInstance()->buildQuery($app->getRouter()->getVars());

			// Loop through each pattern.
			if ($exclusions)
			{
				foreach ($exclusions as $exclusion)
				{
					// Make sure the exclusion has some content
					if ($exclusion !== '')
					{
						// Test both external and internal URI
						if (preg_match('#' . $exclusion . '#i', self::simpleCacheKey() . ' ' . $internalUri, $match))
						{
							return true;
						}
					}
				}
			}
		}

		// If any pagecache plugins return true for onPageCacheIsExcluded, exclude.
		PluginHelper::importPlugin('pagecache');

		$results = $app->triggerEvent('onPageCacheIsExcluded');

		return in_array(true, $results, true);
	}

	/**
	 * Generate the cache key.
	 *
	 * @return   string  The cache key
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	private static function simpleCacheKey(): string
	{
		return Uri::getInstance()->toString();
	}
}
