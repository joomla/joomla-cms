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
	var $_cache		= null;

	var $_cache_key	= null;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  3.3
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
			'defaultgroup'	=> 'page',
			'browsercache'	=> $this->params->get('browsercache', false),
			'caching'		=> false,
		);

		$this->_cache		= JCache::getInstance('page', $options);
		$this->_cache_key	= JUri::getInstance()->toString();
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

		if ($this->app->isAdmin() || count($this->app->getMessageQueue()))
		{
			return;
		}

		if (JFactory::getUser()->get('guest') && $this->app->input->getMethod() == 'GET')
		{
			$this->_cache->setCaching(true);
		}

		$data = $this->_cache->get($this->_cache_key);

		if ($data !== false)
		{
			// Set cached body.
			$this->app->setBody($data);

			echo $this->app->toString($this->app->get('gzip'));

			if (JDEBUG)
			{
				$_PROFILER->mark('afterCache');
			}

			$this->app->close();
		}
	}

	/**
	 * After render.
	 *
	 * @return   void
	 *
	 * @since   1.5
	 */
	public function onAfterRender()
	{
		if ($this->app->isAdmin() || count($this->app->getMessageQueue()))
		{
			return;
		}

		if (JFactory::getUser()->get('guest'))
		{
			// We need to check again here, because auto-login plugins have not been fired before the first aid check.
			$this->_cache->store(null, $this->_cache_key);
		}
	}
}
