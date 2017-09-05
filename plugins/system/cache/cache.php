<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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

		// Set the language in the class.
		$options = array(
			'defaultgroup' => 'page',
			'browsercache' => $this->params->get('browsercache', false),
			'caching'      => false,
		);

		// Get the application if not done by JPlugin. This may happen during upgrades from Joomla 2.5.
		if (!$this->app)
		{
			$this->app = JFactory::getApplication();
		}

		$this->_cache = JCache::getInstance('page', $options);
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
	 * Converting the site URL to fit to the HTTP request.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function onAfterInitialise()
	{
		$app  = $this->app;
		$user = JFactory::getUser();

		if ($app->isClient('administrator'))
		{
			return;
		}

		if (count($app->getMessageQueue()))
		{
			return;
		}

		// If any pagecache plugins return false for onPageCacheSetCaching, do not use the cache.
		JPluginHelper::importPlugin('pagecache');

		$results = JEventDispatcher::getInstance()->trigger('onPageCacheSetCaching');
		$caching = !in_array(false, $results, true);

		if ($caching && $user->get('guest') && $app->input->getMethod() == 'GET')
		{
			$this->_cache->setCaching(true);
		}

		$data = $this->_cache->get($this->getCacheKey());

		if ($data !== false)
		{
			// Set cached body.
			$app->setBody($data);

			echo $app->toString();

			if (JDEBUG)
			{
				JProfiler::getInstance('Application')->mark('afterCache');
			}

			$app->close();
		}
	}

	/**
	 * After render.
	 *
	 * @return   void
	 *
	 * @since   1.5
	 */
	public function onAfterRespond()
	{
		$app = $this->app;

		if ($app->isClient('administrator'))
		{
			return;
		}

		if (count($app->getMessageQueue()))
		{
			return;
		}

		$user = JFactory::getUser();

		if ($user->get('guest') && !$this->isExcluded())
		{
			// We need to check again here, because auto-login plugins have not been fired before the first aid check.
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
		// Check if menu items have been excluded
		if ($exclusions = $this->params->get('exclude_menu_items', array()))
		{
			// Get the current menu item
			$active = $this->app->getMenu()->getActive();

			if ($active && $active->id && in_array($active->id, (array) $exclusions, true))
			{
				return true;
			}
		}

		// Check if regular expressions are being used
		if ($exclusions = $this->params->get('exclude', ''))
		{
			// Normalize line endings
			$exclusions = str_replace(array("\r\n", "\r"), "\n", $exclusions);

			// Split them
			$exclusions = explode("\n", $exclusions);

			// Get current path to match against
			$path = JUri::getInstance()->toString(array('path', 'query', 'fragment'));

			// Loop through each pattern
			if ($exclusions)
			{
				foreach ($exclusions as $exclusion)
				{
					// Make sure the exclusion has some content
					if ($exclusion !== '')
					{
						if (preg_match('/' . $exclusion . '/is', $path, $match))
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

		if (in_array(true, $results, true))
		{
			return true;
		}

		return false;
	}
}
