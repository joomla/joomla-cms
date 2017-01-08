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
	 * @since  __DEPLOY_VERSION__
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

		$this->_cache     = JCache::getInstance('page', $options);
		$this->_cache_key = JUri::getInstance()->toString();
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

		if ($user->get('guest') && $app->input->getMethod() === 'GET')
		{
			$this->_cache->setCaching(true);
		}

		$data = $this->_cache->get($this->_cache_key);

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
					if ($exclusion!=='')
					{
						if (preg_match('/' . $exclusion . '/is', $path, $match))
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
