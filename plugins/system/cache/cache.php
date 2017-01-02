<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
	var $_cache = null;

	var $_cache_key = null;

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
			'browsercache' => $this->params->get('browsercache', false),
			'caching'      => false,
		);

		// Instantiate cache with previous options and create the cache key identifier.
		$this->_cache     = JCache::getInstance('page', $options);
		$this->_cache_key = JUri::getInstance()->toString();
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

		// If user is guest and method is equal to GET enable caching.
		if (JFactory::getUser()->get('guest') && $this->app->input->getMethod() === 'GET')
		{
			$this->_cache->setCaching(true);

			// Gets page from cache.
			$data = $this->_cache->get($this->_cache_key);

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
					JProfiler::getInstance('Application')->mark('afterCache');
					JEventDispatcher::getInstance()->trigger('onAfterRespond');
				}

				// Closes the application.
				$this->app->close();
			}
		}
	}

	/**
	 * After Route Event.
	 * Verify if current page is not excluded from cache.
	 *
	 * @return   void
	 *
	 * @since   __DEPLOY_VERSION__
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
		if (JFactory::getUser()->get('guest'))
		{
			// Saves current page in cache.
			$this->_cache->store(null, $this->_cache_key);
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

			if ($active && $active->id && in_array($active->id, (array) $exclusions))
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
					// Make sure the exclusion has some content.
					if (strlen($exclusion))
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

		return false;
	}
}
