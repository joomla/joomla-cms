<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cache
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! Page Cache Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  System.cache
 */
class plgSystemCache extends JPlugin
{

	var $_cache = null;

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param	array	$config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		//Set the language in the class
		$config = JFactory::getConfig();
		$options = array(
			'defaultgroup'	=> 'page',
			'browsercache'	=> $this->params->get('browsercache', false),
			'caching'		=> false,
		);

		$this->_cache = JCache::getInstance('page', $options);
	}

	/**
	* Converting the site URL to fit to the HTTP request
	*
	*/
	function onAfterInitialise()
	{
		global $_PROFILER;
		$app	= JFactory::getApplication();
		$user	= JFactory::getUser();

		if ($app->isAdmin() || JDEBUG) {
			return;
		}

		if (count($app->getMessageQueue())) {
			return;
		}

		if ($user->get('guest') && $_SERVER['REQUEST_METHOD'] == 'GET') {
			$this->_cache->setCaching(true);
		}

		$data  = $this->_cache->get();

		if ($data !== false)
		{
			JResponse::setBody($data);

			echo JResponse::toString($app->getCfg('gzip'));

			if (JDEBUG)
			{
				$_PROFILER->mark('afterCache');
				echo implode('', $_PROFILER->getBuffer());
			}

			$app->close();
		}
	}

	function onAfterRender()
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin() || JDEBUG) {
			return;
		}

		if (count($app->getMessageQueue())) {
			return;
		}

		$user = JFactory::getUser();
		if ($user->get('guest')) {
			//We need to check again here, because auto-login plugins have not been fired before the first aid check
			$this->_cache->store();
		}
	}
}
