<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Base Cms Model Class
 *
 * @package     Joomla.Libraries
 * @subpackage  Model
 * @since       3.4
 */
abstract class JModelCms extends JModelDatabase implements JModelCmsInterface
{
	/**
	 * Configuration array
	 *
	 * @var    array
	 * @since  3.4
	 */
	protected $config = array();

	/**
	 * Indicates if the internal state has been set
	 *
	 * @var    boolean
	 * @since  3.4
	 */
	protected $stateIsSet = false;

	/**
	 * Flag if the internal state should be updated
	 * from request
	 *
	 * @var    boolean
	 * @since  3.4
	 */
	protected $ignoreRequest;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $text_prefix = null;

	/**
	 * The global dispatcher object.
	 *
	 * @var    JEventDispatcher
	 * @since  3.4
	 */
	protected $dispatcher = null;

	/**
	 * The component name.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $option = null;

	/**
	 * Public constructor
	 *
	 * @param   JRegistry         $state       The state for the model
	 * @param   JDatabaseDriver   $db          The database object
	 * @param   JEventDispatcher  $dispatcher  The dispatcher object
	 * @param   array             $config      Array of config variables
	 *
	 * @since  3.4
	 */
	public function __construct(JRegistry $state = null, JDatabaseDriver $db = null, JEventDispatcher $dispatcher = null, $config = array())
	{
		$this->config     = $config;
		$this->dispatcher = $dispatcher ? $dispatcher : JEventDispatcher::getInstance();

		// Set the model state. Check we have a JRegistry instance as state was a JObject in legacy MVC
		if (array_key_exists('state', $config) && $config['state'] instanceof JRegistry)
		{
			$state = $config['state'];
		}
		else
		{
			$state = $this->loadState();
		}

		// If we don't have a db param see if one got set in the config for legacy purposes
		// @deprecated This if block is deprecated and will be removed in Joomla 4.
		if (!$db && array_key_exists('dbo', $config) && $config['dbo'] instanceof JDatabaseDriver)
		{
			$db = $config['dbo'];

			JLog::add('Passing the database object via the config is deprecated. Use the constructor parameter instead', JLog::WARNING, 'deprecated');
		}

		parent::__construct($state, $db);

		if (!empty($config['ignore_request']))
		{
			$this->ignoreRequest = true;
		}

		$this->option = $config['option'];

		// Guess the JText message prefix. Defaults to the option.
		if (isset($config['text_prefix']))
		{
			$this->text_prefix = strtoupper($config['text_prefix']);
		}
		elseif (empty($this->text_prefix))
		{
			$this->text_prefix = strtoupper($this->option);
		}
	}

	/**
	 * Method to get model state variables
	 *
	 * @param   string  $property  Optional parameter name
	 * @param   mixed   $default   Optional default value
	 *
	 * @return  object  The property in the state
	 *
	 * @since   3.4
	 * @throws  InvalidArgumentException
	 */
	public function getStateVar($property = null, $default = null)
	{
		$state = $this->getState();

		return $state->get($property, $default);
	}

	/**
	 * Method to get model state variables
	 *
	 * @param   string  $property  Optional parameter name
	 * @param   mixed   $default   Optional default value
	 *
	 * @return  object  The property where specified, the state object where omitted
	 *
	 * @since   3.4
	 */
	public function getState($property = null, $default = null)
	{
		if (!$this->stateIsSet)
		{
			// Protected method to auto-populate the model state.
			$this->populateState();

			// Set the model state set flag to true.
			$this->stateIsSet = true;
		}

		return parent::getState();
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @param   string  $ordering   Column to order by. I.E. 'a.title'
	 * @param   string  $direction  'ASC' or 'DESC'
	 *
	 * @return  void
	 *
	 * @note    Calling getState in this method will result in recursion.
	 * @since   3.4
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		if (!$this->stateIsSet)
		{
			// Do something
		}
	}

	/**
	 * Method to authorise the current user for an action.
	 * This method is intended to be overridden to allow for customized access rights
	 *
	 * @param   string  $action        ACL action string. I.E. 'core.create'
	 * @param   string  $assetName     Asset name to check against.
	 * @param   object  $activeRecord  Active record data to check against
	 *
	 * @return bool
	 *
	 * @since  3.4
	 * @see    JUser::authorise
	 */
	public function allowAction($action, $assetName = null, $activeRecord = null)
	{
		if (is_null($assetName))
		{
			$assetName = $this->option;
		}

		$user = JFactory::getUser();

		if ($action == 'core.edit.own')
		{
			// Not a record or isn't tracking ownership
			if (is_null($activeRecord) || !isset($activeRecord->owner))
			{
				$action = 'core.edit';
			}
			// Not the owner so the answer is no
			elseif ($user->id != $activeRecord->owner && !$user->authorise('core.admin', $assetName))
			{
				return false;
			}
		}

		return $user->authorise($action, $assetName);
	}

	/**
	 * Method to get the model context.
	 *
	 * @return string
	 *
	 * @since  3.4
	 */
	public function getContext()
	{
		$context = substr($this->option, 4) . '.' . $this->getName();

		return $context;
	}

	/**
	 * Clean the cache
	 *
	 * @param   string   $group      The cache group
	 * @param   integer  $client_id  The ID of the client
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		$localConfig = $this->config;
		$options     = array();

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
		$this->dispatcher->trigger('onContentCleanCache', $options);
	}

	/**
	 * Method to get the model name
	 *
	 * The model name. By default parsed using the classname or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the model
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$className = get_class($this);
			$modelpos  = strpos($className, 'Model');

			if ($modelpos === false)
			{
				throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_MODEL_GET_NAME'), 500);
			}

			$this->name = strtolower(substr($className, $modelpos + 5));
		}

		return $this->name;
	}
}
