<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cache
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! Page Cache Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  System.cache
 */
class PlgSystemCache extends JPlugin
{

	private $_cache		= null;

	private $_cache_key	= null;

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param   object	$subject The object to observe
	 * @param   array  $config  An array that holds the plugin configuration
	 * @since   1.0
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		//Set the language in the class
		$options = array(
			'defaultgroup'	=> 'page',
			'browsercache'	=> $this->params->get('browsercache', false),
			'caching'		=> false,
		);

		$this->_cache		= JCache::getInstance('page', $options);
		$this->_cache_key	= JUri::getInstance()->toString();
	}

	/**
	* Converting the site URL to fit to the HTTP request
	*
	*/
	public function onAfterRoute()
	{
		global $_PROFILER;
		
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!$this->isCachingEnabled())
		{
			return;
		}

		if ($user->get('guest') && $app->input->getMethod() == 'GET')
		{
			$this->_cache->setCaching(true);
		}

		$data = $this->_cache->get($this->_cache_key);

		if ($data !== false)
		{
			// Set cached body
			JResponse::setBody($data);
			
			echo JResponse::toString($app->getCfg('gzip'));

			if (JDEBUG)
			{
				$_PROFILER->mark('afterCache');
			}

			$app->close();
		}
	}

	public function onAfterRender()
	{
		$app = JFactory::getApplication();

		if (!$this->isCachingEnabled())
		{
			return;
		}
		
		if (count($app->getMessageQueue()))
		{
			return;
		}

		$user = JFactory::getUser();
		if ($user->get('guest'))
		{
			// We need to check again here, because auto-login plugins have not been fired before the first aid check
			$this->_cache->store(null, $this->_cache_key);
		}
	}
	
	private function isCachingEnabled()
	{
		$app  = JFactory::getApplication();
		
		if ($app->isAdmin())
		{
			return false;
		}
		
		if (count($app->getMessageQueue()))
		{
			return false;
		}
		
		// check for menu items to include
		$menuItems = $this->params->get('menuitems');
		if (!empty($menuItems))
		{
			if (!is_array($menuItems))
			{
				$menuItems = array($menuItems);
			}
				
			if (!in_array(JRequest::getInt('Itemid'), $menuItems))
			{
				return false;
			}
		}
		
		$menuItems = $this->params->get('menuitems_exclude');
		if (!empty($menuItems))
		{
			if (!is_array($menuItems))
			{
				$menuItems = array($menuItems);
			}
				
			if (in_array(JRequest::getInt('Itemid'), $menuItems))
			{
				return false;
			}
		}
		
		// check for components to include
		$components = $this->params->get('components');
		if (!empty($components))
		{
			if (!is_array($components))
			{
				$components = array($components);
			}
				
			if (!in_array(JRequest::getCmd('option'), $components))
			{
				return false;
			}
		}
		
		$components = $this->params->get('components_exclude');
		if (!empty($components))
		{
			if (!is_array($components))
			{
				$components = array($components);
			}
				
			if (in_array(JRequest::getCmd('option'), $components))
			{
				return false;
			}
		}
		
		return true;
	}
}
