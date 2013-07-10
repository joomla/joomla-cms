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

	var $_cache		= null;

	var $_cache_key	= null;

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param   object	$subject The object to observe
	 * @param   array  $config  An array that holds the plugin configuration
	 * @since   1.0
	 */
	function __construct(& $subject, $config)
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
	function onAfterInitialise()
	{
		global $_PROFILER;
		
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		if ($app->isAdmin())
		{
			return;
		}

		if (count($app->getMessageQueue()))
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

	function onAfterRender()
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
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
}
