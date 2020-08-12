<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cache
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! Page Cache Plugin.
 *
 * @since  1.5
 */
class PlgSystemCache extends JPlugin
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

		// Get the application if not done by JPlugin.
		if (!isset($this->app))
		{
			$this->app = JFactory::getApplication();
		}

		// Set the cache options.
		$options = array(
			'defaultgroup' => 'page',
			'browsercache' => $this->params->get('browsercache', 0),
			'caching'      => false,
		);

		// Instantiate cache with previous options and create the cache key identifier.
		$this->_cache     = JCache::getInstance('page', $options);
		$this->_cache_key = JUri::getInstance()->toString();
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
			JPluginHelper::importPlugin('pagecache');

			$parts = JEventDispatcher::getInstance()->trigger('onPageCacheGetKey');
			$parts[] = JUri::getInstance()->toString();

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
		if ($this->app->isClient('administrator') || $this->app->get('offline', '0') || $this->app->getMessageQueue())
		{
			return;
		}

		// If any pagecache plugins return false for onPageCacheSetCaching, do not use the cache.
		JPluginHelper::importPlugin('pagecache');

		$results = JEventDispatcher::getInstance()->trigger('onPageCacheSetCaching');
		$caching = !in_array(false, $results, true);

		if ($caching && JFactory::getUser()->guest && $this->app->input->getMethod() === 'GET')
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

			// Mark afterCache in debug and run debug onAfterRespond events.
			// e.g., show Joomla Debug Console if debug is active.
			if (JDEBUG)
			{
				JProfiler::getInstance('Application')->mark('afterCache');
				JEventDispatcher::getInstance()->trigger('onAfterRespond');
			}

			// Closes the application.
			$this->app->close();
		}
	}

	/**
	 * After Render Event.
	 * Verify if current page is not excluded from cache.
	 *
	 * @return   void
	 *
	 * @since   3.9.12
	 */
	public function onAfterRender()
	{
		if ($this->_cache->getCaching() === false)
		{
			return;
		}

		// We need to check if user is guest again here, because auto-login plugins have not been fired before the first aid check.
		// Page is excluded if excluded in plugin settings.
		if (!JFactory::getUser()->guest || $this->app->getMessageQueue() || $this->isExcluded() === true)
		{
			$this->_cache->setCaching(false);

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
	 * @since   1.5
	 */
	public function onAfterRespond()
	{
		if ($this->_cache->getCaching() === false)
		{
			return;
		}

		// Saves current page in cache.
		$this->_cache->store($this->app->getBody(), $this->getCacheKey());
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
			$internal_uri	= '/index.php?' . JUri::getInstance()->buildQuery($this->app->getRouter()->getVars());

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
		JPluginHelper::importPlugin('pagecache');

		$results = JEventDispatcher::getInstance()->trigger('onPageCacheIsExcluded');

		return in_array(true, $results, true);
	}
}
