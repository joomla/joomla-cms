<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JModelCms
{
	/**
	 * Configuration array
	 * @var array
	 */
	protected $config = array();

	/**
	 * A state object
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $state;

	/**
	 * Indicates if the internal state has been set
	 *
	 * @var    boolean
	 * @since  12.2
	 */
	protected $stateIsSet = false;

	/**
	 * Flag if the internal state should be updated
	 * from request
	 * @var unknown_type
	 */
	protected $ignoreRequest;

	public function __construct($config = array())
	{
		$this->config = $config;

		if (array_key_exists('state', $config) && ($config['state'] instanceof JObject))
		{
			$this->state = $config['state'];
		}
		else
		{
			$this->state = new JObject();
		}

		if (!empty($config['ignore_request']))
		{
			$this->ignoreRequest = true;
		}
	}

	/**
	 * Method to set model state variables
	 *
	 * @param   string $property The name of the property.
	 * @param   mixed  $value    The value of the property to set or null.
	 *
	 * @return  mixed  The previous value of the property or null if not set.
	 *
	 * @since   12.2
	 */
	public function setState($property, $value = null)
	{
		return $this->state->set($property, $value);
	}

	/**
	 * Method to get model state variables
	 *
	 * @param   string $property Optional parameter name
	 * @param   mixed  $default  Optional default value
	 *
	 * @return  object  The property where specified, the state object where omitted
	 *
	 * @since   12.2
	 */
	public function getState($property = null, $default = null)
	{
		if (!$this->ignoreRequest && !$this->stateIsSet)
		{
			// Protected method to auto-populate the model state.
			$this->populateState();

			// Set the model state set flag to true.
			$this->stateIsSet = true;
		}

		if ($property === null)
		{
			$returnProperty = $this->state;
		}
		else
		{
			$returnProperty = $this->state->get($property, $default);
		}

		return $returnProperty;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return  void
	 *
	 * @note    Calling getState in this method will result in recursion.
	 * @since   12.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		if (!$this->stateIsSet)
		{
			//do something
		}
	}

	/**
	 * Clean the cache
	 *
	 * @param   string  $group     The cache group
	 * @param   integer $client_id The ID of the client
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		$localConfig = $this->config;
		$dispatcher  = $this->getDispatcher();

		$options = array();

		if ($group)
		{
			$options['defaultgroup'] = $group;
		}
		else
		{
			$options['defaultgroup'] = $localConfig['option'];
		}

		if ($client_id)
		{
			$options['cachebase'] = JPATH_ADMINISTRATOR . '/cache';
		}
		else
		{
			$globalConfig        = JFactory::getConfig();
			$options['cachbase'] = $globalConfig->get('cache_path', JPATH_SITE . '/cache');
		}

		$cache = JCache::getInstance('callback', $options);
		$cache->clean();

		// Trigger the onContentCleanCache event.
		$dispatcher->trigger('onContentCleanCache', $options);
	}

	/**
	 * Method to authorise the current user for an action.
	 * This method is intended to be overriden to allow for customized access rights
	 *
	 * @param string $action
	 * @param string $assetName
	 * @param object $activeRecord active record data to check against
	 */
	public function allowAction($action, $assetName = null, $activeRecord = null)
	{
		if (is_null($assetName))
		{
			$config    = $this->config;
			$assetName = $config['option'];
		}

		$user = JFactory::getUser();

		return $user->authorise($action, $assetName);
	}

	/**
	 * Method to check the session token
	 */
	protected function validateSession()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
	}

	/**
	 * Method to get the model context.
	 * $context = $config['option'].'.'.$config['subject'];
	 * @return string
	 */
	public function getContext()
	{
		$config  = $this->config;
		$context = $config['option'] . '.' . $config['subject'];

		return $context;
	}

	/**
	 * Method to get a dispatcher
	 * @return JEventDispatcher
	 */
	protected function getDispatcher()
	{
		$version = new JVersion();
		if ($version->isCompatible('3.0'))
		{
			// Get the dispatcher.
			$dispatcher = JEventDispatcher::getInstance();
		}
		else
		{
			// Get the dispatcher.
			$dispatcher = JDispatcher::getInstance();
		}

		return $dispatcher;
	}
}
