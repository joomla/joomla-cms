<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cache
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Profiler\Profiler;
use Joomla\CMS\Uri\Uri;

/**
 * Joomla! Page Cache Plugin.
 *
 * @since  1.5
 */
class PlgSystemCache extends CMSPlugin
{
	/**
	 * Cache instance.
	 *
	 * @var    JCache
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
	 * @var    JApplicationCms
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
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		// Get the application if not done by JPlugin. This may happen during upgrades from Joomla 2.5.
		if (!isset($this->app))
		{
			$this->app = Factory::getApplication();
		}

		// Set the cache options.
		$options = array(
			'defaultgroup' => 'page',
			'browsercache' => $this->params->get('browsercache', 0),
			'caching'      => false,
		);

		// Instantiate cache with previous options and create the cache key identifier.
		$this->_cache     = Cache::getInstance('page', $options);
		$this->_cache_key = Uri::getInstance()->toString();
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
	 * After Initialise Event.
	 * Checks if URL exists in cache, if so dumps it directly and closes.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function onAfterInitialise()
	{
		if ($this->app->isClient('administrator') || $this->app->get('offline', '0') || count($this->app->getMessageQueue()))
		{
			return;
		}

		// If any pagecache plugins return false for onPageCacheSetCaching, do not use the cache.
		PluginHelper::importPlugin('pagecache');

		$results = $this->app->triggerEvent('onPageCacheSetCaching');
		$caching = !in_array(false, $results, true);
		$user    = Factory::getUser();

		if ($caching && $user->get('guest') && $this->app->input->getMethod() == 'GET')
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
			echo $this->app->toString();

			// Mark afterCache in debug and run debug onAfterRespond events.
			// e.g., show Joomla Debug Console if debug is active.
			if (JDEBUG)
			{
				Profiler::getInstance('Application')->mark('afterCache');
				$this->app->triggerEvent('onAfterRespond');
			}

			// Closes the application.
			$this->app->close();
		}
	}

	/**
	 * After Route Event.
	 * Verify if current page is not excluded from cache.
	 *
	 * @return   void
	 *
	 * @since   3.9.0
	 */
	public function onAfterRoute()
	{
		if ($this->app->isClient('administrator') || $this->app->get('offline', '0') || count($this->app->getMessageQueue()))
		{
			return;
		}

		// Page is excluded if excluded in plugin settings.
		if ($this->isExcluded())
		{
			$this->_cache->setCaching(false);
		}
	}

	/**
	 * After Respond Event.
	 * Stores page in cache.
	 *
	 * @return   void
	 *
	 * @since   1.5
	 */
	public function onAfterRespond()
	{
		if ($this->app->isClient('administrator') || $this->app->get('offline', '0') || count($this->app->getMessageQueue()))
		{
			return;
		}

		// We need to check if user is guest again here, because auto-login plugins have not been fired before the first aid check.
		if (Factory::getUser()->get('guest'))
		{
			// Saves current page in cache.
			$this->_cache->store(null, $this->getCacheKey());
		}
	}

	/**
	 * Check if the page is excluded from the cache or not.
	 *
	 * @return   boolean  True if the page is excluded else false
	 *
	 * @since    3.5
	 */
	protected function isExcluded()
	{
		// Check if menu items have been excluded.
		if ($exclusions = $this->params->get('exclude_menu_items', array()))
		{
			// Get the current menu item.
			$active = $this->app->getMenu()->getActive();

			if ($active && $active->id && in_array((int) $active->id, (array) $exclusions))
			{
				return true;
			}
		}

		// Check if regular expressions are being used.
		if ($exclusions = $this->params->get('exclude', ''))
		{
			// Normalize line endings.
			$exclusions = str_replace(array("\r\n", "\r"), "\n", $exclusions);

			// Split them.
			$exclusions = explode("\n", $exclusions);

			// Gets internal URI.
			$internal_uri	= '/index.php?' . Uri::getInstance()->buildQuery($this->app->getRouter()->getVars());

			// Loop through each pattern.
			if ($exclusions)
			{
				foreach ($exclusions as $exclusion)
				{
					// Make sure the exclusion has some content
					if ($exclusion !== '')
					{
						// Test both external and internal URI
						if (preg_match('#' . $exclusion . '#i', $this->_cache_key . ' ' . $internal_uri, $match))
						{
							return true;
						}
					}
				}
			}
		}

		// If any pagecache plugins return true for onPageCacheIsExcluded, exclude.
		PluginHelper::importPlugin('pagecache');

		$results = $this->app->triggerEvent('onPageCacheIsExcluded');

		if (in_array(true, $results, true))
		{
			return true;
		}

		return false;
	}
}
