<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cache
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
		global $_PROFILER;

		$app  = JFactory::getApplication();

		if ($app->isAdmin() || count($app->getMessageQueue()) || $app->get('offline','0'))
		{
			return;
		}

		if (JFactory::getUser()->get('guest') && $app->input->getMethod() == 'GET')
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
				$_PROFILER->mark('afterCache');
			}

			$app->close();
		}
	}

	/**
	 * Verifying that the page is not excluded from cache
	 *
	 * @return   void
	 *
	 * @since   3.5
	 */
	public function onAfterRoute()
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin() || count($app->getMessageQueue()) || $app->get('offline','0'))
		{
			return;
		}

		if ($this->isExcluded())
		{
			$this->_cache->setCaching(false);
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
		$app = JFactory::getApplication();

		if ($app->isAdmin() || count($app->getMessageQueue()) || $app->get('offline','0'))
		{
			return;
		}

		if (JFactory::getUser()->get('guest'))
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
		$app = JFactory::getApplication();
		
		// Check if menu items have been excluded
		if ($exclusions = $this->params->get('exclude_menu_items', array()))
		{
			// Get the current menu item
			$active = $app->getMenu()->getActive();
			
			if ($active && $active->id && in_array($active->id, (array) $exclusions))
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

			// Gets internal URI
			$internal_uri	= '/index.php?' . JUri::getInstance()->buildQuery($app->getRouter()->getVars());

			// Loop through each pattern
			if ($exclusions)
			{
				foreach ($exclusions as $exclusion)
				{
					// Make sure the exclusion has some content
					if (strlen($exclusion))
					{
						if (preg_match('/' . preg_quote($exclusion, '/') . '/i', $this->_cache_key . ' ' . $internal_uri, $match))
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
