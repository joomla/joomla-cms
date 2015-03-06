<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JModelCms implements JObservableInterface
{

	/**
	 * Configuration array
	 *
	 * @var  array
	 */
	protected $config = array();

	/**
	 * A state object
	 *
	 * @var  JRegistryCms
	 */
	protected $state;

	/**
	 * Indicates if the internal state has been set
	 *
	 * @var    boolean
	 */
	protected $stateIsSet = false;

	/**
	 * Flag if the internal state should be updated
	 * from request
	 *
	 * @var boolean
	 */
	protected $ignoreRequest;

	/**
	 * Generic Observer Updater for table (Used e.g. for tags Processing)
	 *
	 * @var    JObserverUpdaterCms
	 */
	protected $observers;

	public function __construct($config = array())
	{
		if (!isset($config['resource']))
		{
			$r = null;
			if (!preg_match('/Model(.*)/i', get_class($this), $r))
			{
				throw new Exception(JText::_('JLIB_APPLICATION_ERROR_MODEL_GET_NAME'), 500);
			}
			$config['resource'] = strtolower($r[1]);
		}

		$this->config = $config;

		$this->state = new JRegistryCms();
		if (array_key_exists('state', $config) && ($config['state'] instanceof JRegistryCms))
		{
			$this->state = $config['state'];
		}

		if (array_key_exists('dispatcher', $config) && ($config['dispatcher'] instanceof JEventDispatcher))
		{
			$this->dispatcher = $config['dispatcher'];
		}

		if (!empty($config['ignore_request']))
		{
			$this->ignoreRequest = true;
		}

		$this->observers = new JObserverUpdaterCms($this);
		JObserverMapperCms::attachAllObservers($this);

		$this->config['class'] = get_class($this);
	}

	/**
	 * Method to get the models configuration array
	 *
	 * @return array
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Method to get model state variables
	 *
	 * @param   string $property Optional parameter name
	 * @param   mixed  $default  Optional default value
	 *
	 * @return  object  The property where specified, the state object where omitted
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

		$returnProperty = $this->state;
		if ($property !== null)
		{
			$returnProperty = $this->state->get($property, $default);
		}

		return $returnProperty;
	}

	/**
	 * Method to set model state variables
	 *
	 * @param   string $property The name of the property.
	 * @param   mixed  $value    The value of the property to set or null.
	 *
	 * @return  mixed  The previous value of the property or null if not set.
	 */
	public function setState($property, $value = null)
	{
		return $this->state->set($property, $value);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @param string $ordering  column to order by. I.E. 'a.title'
	 * @param string $direction 'ASC' or 'DESC'
	 *
	 * @return  void
	 *
	 * @note    Calling getState in this method will result in recursion.
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		if (!$this->stateIsSet)
		{
			//do something
		}
	}

	/**
	 * Method to authorise the current user for an action.
	 * This method is intended to be overridden to allow for customized access rights
	 *
	 * @param string $action       ACL action string. I.E. 'core.create'
	 * @param string $assetName    asset name to check against.
	 * @param object $activeRecord active record data to check against
	 *
	 * @return bool
	 * @see JUser::authorise
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
	 * Method to get the model context.
	 * $context = $config['option'].'.'.$config['resource'];
	 * @return string
	 */
	public function getContext()
	{
		$config = $this->config;

		return $config['option'] . '.' . $config['resource'];
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

		$options = array();

		$options['defaultgroup'] = $localConfig['option'];
		if ($group)
		{
			$options['defaultgroup'] = $group;
		}

		$options['cachebase'] = JPATH_ADMINISTRATOR . '/cache';
		if ($client_id === 0)
		{
			$globalConfig        = JFactory::getConfig();
			$options['cachbase'] = $globalConfig->get('cache_path', JPATH_SITE . '/cache');
		}

		$cache = JCache::getInstance('callback', $options);
		$cache->clean();

		// Trigger the onContentCleanCache event.
		$dispatcher = $this->getDispatcher();
		$dispatcher->trigger('onContentCleanCache', $options);
	}

	/**
	 * Method to get a dispatcher
	 *
	 * @param array $groups (OPTIONAL) array of plugin groups to import
	 *
	 * @return JEventDispatcher
	 */
	public function getDispatcher($groups = array())
	{
		foreach ($groups AS $pluginGroup)
		{
			JPluginHelper::importPlugin($pluginGroup);
		}

		return JEventDispatcher::getInstance();
	}

	/**
	 * Method to attach an observer to the model
	 *
	 * @param JObserverInterface $observer
	 */
	public function attachObserver(JObserverInterface $observer)
	{
		$this->observers->attachObserver($observer);
	}

	/**
	 *  Method to enable observer events
	 */
	public function enableEvents()
	{
		$this->observers->enableEvents();
	}

	/**
	 * Method to disable observer events
	 */
	public function disableEvents()
	{
		$this->observers->disableEvents();
	}
}
