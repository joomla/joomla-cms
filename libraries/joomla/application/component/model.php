<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Base class for a Joomla Model
 *
 * Acts as a Factory class for application specific objects and
 * provides many supporting API functions.
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       11.1
 */
abstract class JModel extends JObject
{
	/**
	 * Indicates if the internal state has been set
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $__state_set	= null;

	/**
	 * Database Connector
	 *
	 * @var    object
	 * @since  11.1
	 */
	protected $_db;

	/**
	 * The model (base) name
	 *
	 * @var    string
	 * @note   Replaces _name variable in 11.1
	 * @since  11.1
	 */
	protected $name;

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $option = null;

	/**
	 * A state object
	 *
	 * @var    string
	 * @note   Replaces _state variable in 11.1
	 * @since  11.1
	 */
	protected $state;

	/**
	 * The event to trigger when cleaning cache.
	 *
	 * @var      string
	 * @since    11.1
	 */
	protected $event_clean_cache = null;

	/**
	 * Add a directory where JModel should search for models. You may
	 * either pass a string or an array of directories.
	 *
	 * @param   mixed   $path    A path or array[sting] of paths to search.
	 * @param   string  $prefix  A prefix for models.
	 *
	 * @return  array  An array with directory elements. If prefix is equal to '', all directories are returned.
	 * @since   11.1
	 */
	public static function addIncludePath($path = '', $prefix = '')
	{
		static $paths;

		if (!isset($paths)) {
			$paths = array();
		}

		if (!isset($paths[$prefix])) {
			$paths[$prefix] = array();
		}

		if (!isset($paths[''])) {
			$paths[''] = array();
		}

		if (!empty($path)) {
			jimport('joomla.filesystem.path');

			if (!in_array($path, $paths[$prefix])) {
				array_unshift($paths[$prefix], JPath::clean($path));
			}

			if (!in_array($path, $paths[''])) {
				array_unshift($paths[''], JPath::clean($path));
			}
		}

		return $paths[$prefix];
	}

	/**
	 * Adds to the stack of model table paths in LIFO order.
	 *
	 * @param   mixed  $path  The directory as a string or directories as an array to add.
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function addTablePath($path)
	{
		jimport('joomla.database.table');
		JTable::addIncludePath($path);
	}

	/**
	 * Create the filename for a resource
	 *
	 * @param   string  $type   The resource type to create the filename for.
	 * @param   array   $parts  An associative array of filename information.
	 *
	 * @return  string  The filename
	 * @since   11.1
	 */
	protected static function _createFileName($type, $parts = array())
	{
		$filename = '';

		switch($type) {
			case 'model':
				$filename = strtolower($parts['name']).'.php';
				break;

		}
		return $filename;
	}

	/**
	 * Returns a Model object, always creating it
	 *
	 * @param   string  $type    The model type to instantiate
	 * @param   string  $prefix  Prefix for the model class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  mixed   A model object or false on failure
	 * @since   11.1
	 */
	public static function getInstance($type, $prefix = '', $config = array())
	{
		$type		= preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$modelClass	= $prefix.ucfirst($type);

		if (!class_exists($modelClass)) {
			jimport('joomla.filesystem.path');
			$path = JPath::find(
				JModel::addIncludePath(null, $prefix),
				JModel::_createFileName('model', array('name' => $type))
			);
			if (!$path) {
				$path = JPath::find(
					JModel::addIncludePath(null, ''),
					JModel::_createFileName('model', array('name' => $type))
				);
			}
			if ($path) {
				require_once $path;

				if (!class_exists($modelClass)) {
					JError::raiseWarning(0, JText::sprintf('JLIB_APPLICATION_ERROR_MODELCLASS_NOT_FOUND', $modelClass));
					return false;
				}
			}
			else {
				return false;
			}
		}

		return new $modelClass($config);
	}

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @return  JModel  A JModel object
	 * @since   11.1
	 */
	public function __construct($config = array())
	{
		// Guess the option from the class name (Option)Model(View).
		if (empty($this->option)) {
			$r = null;

			if (!preg_match('/(.*)Model/i', get_class($this), $r)) {
				JError::raiseError(500, JText::_('JLIB_APPLICATION_ERROR_MODEL_GET_NAME'));
			}

			$this->option = 'com_'.strtolower($r[1]);
		}

		// Set the view name
		if (empty($this->name)) {
			if (array_key_exists('name', $config)) {
				$this->name = $config['name'];
			}
			else {
				$this->name = $this->getName();
			}
		}

		// Set the model state
		if (array_key_exists('state', $config)) {
			$this->state = $config['state'];
		}
		else {
			$this->state = new JObject();
		}

		// Set the model dbo
		if (array_key_exists('dbo', $config)) {
			$this->_db = $config['dbo'];
		}
		else {
			$this->_db = JFactory::getDbo();
		}

		// Set the default view search path
		if (array_key_exists('table_path', $config)) {
			$this->addTablePath($config['table_path']);
		}
		else if (defined('JPATH_COMPONENT_ADMINISTRATOR')) {
			$this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
		}

		// Set the internal state marker - used to ignore setting state from the request
		if (!empty($config['ignore_request'])) {
			$this->__state_set = true;
		}

		// Set the clean cache event
		if (isset($config['event_clean_cache'])) {
			$this->event_clean_cache = $config['event_clean_cache'];
		} else  if (empty($this->event_clean_cache)) {
			$this->event_clean_cache = 'onContentCleanCache';
		}

	}

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string   $query       The query.
	 * @param   integer  $limitstart  Offset.
	 * @param   integer  $limit       The number of records.
	 *
	 * @return  array  An array of results.
	 * @since   11.1
	 */
	protected function _getList($query, $limitstart=0, $limit=0)
	{
		$this->_db->setQuery($query, $limitstart, $limit);
		$result = $this->_db->loadObjectList();

		return $result;
	}

	/**
	 * Returns a record count for the query
	 *
	 * @param    string  $query  The query.
	 *
	 * @return   integer  Number of rows for query
	 * @since    11.1
	 */
	protected function _getListCount($query)
	{
		$this->_db->setQuery($query);
		$this->_db->query();

		return $this->_db->getNumRows();
	}

	/**
	 * Method to load and return a model object.
	 *
	 * @param   string  $name    The name of the view
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration settings to pass to JTable::getInsance
	 *
	 * @return  mixed  Model object or boolean false if failed
	 * @since   11.1
	 * @see     JTable::getInstance
	 */
	protected function _createTable($name, $prefix = 'Table', $config = array())
	{
		// Clean the model name
		$name	= preg_replace('/[^A-Z0-9_]/i', '', $name);
		$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		// Make sure we are returning a DBO object
		if (!array_key_exists('dbo', $config)) {
			$config['dbo'] = $this->getDbo();
		}

		return JTable::getInstance($name, $prefix, $config);
	}

	/**
	 * Method to get the database connector object
	 *
	 * @return  JDatabase  JDatabase connector object
	 */
	public function getDbo()
	{
		return $this->_db;
	}

	/**
	 * Method to get the model name
	 *
	 * The model name. By default parsed using the classname or it can be set
	 *                 by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the model
	 * @since   11.1
	 */
	public function getName()
	{
		$name = $this->name;

		if (empty($name)) {
			$r = null;
			if (!preg_match('/Model(.*)/i', get_class($this), $r)) {
				JError::raiseError(500, 'JLIB_APPLICATION_ERROR_MODEL_GET_NAME');
			}
			$name = strtolower($r[1]);
		}

		return $name;
	}

	/**
	 * Method to get model state variables
	 *
	 * @param   string  $property  Optional parameter name
	 * @param   mixed   $default   Optional default value
	 *
	 * @return  object  The property where specified, the state object where omitted
	 * @since   11.1
	 */
	public function getState($property = null, $default = null)
	{
		if (!$this->__state_set) {
			// Protected method to auto-populate the model state.
			$this->populateState();

			// Set the model state set flag to true.
			$this->__state_set = true;
		}

		return $property === null ? $this->state : $this->state->get($property, $default);
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string   $name     The table name. Optional.
	 * @param   string   $prefix   The class prefix. Optional.
	 * @param   array    $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 * @since   11.1
	 */
	public function getTable($name = '', $prefix = '', $options = array())
	{
		if (empty($name)) {
			$name = $this->getName();
		}

		if(empty($prefix)) {
			$prefix = $this->getName() . 'Table';
		}

		if ($table = $this->_createTable($name, $prefix, $options)) {
			return $table;
		}

		JError::raiseError(0, JText::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name));

		return null;
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
	 * @since   11.1
	 */
	protected function populateState()
	{
	}

	/**
	 * Method to set the database connector object
	 *
	 * @param   object  &$db  A JDatabase based object
	 *
	 * @return  void
	 * @since   11.1
	 */
	public function setDbo(&$db)
	{
		$this->_db = &$db;
	}

	/**
	 * Method to set model state variables
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set or null.
	 *
	 * @return  mixed  The previous value of the property or null if not set.
	 * @since   11.1
	 */
	public function setState($property, $value = null)
	{
		return $this->state->set($property, $value);
	}

	/**
	 * Clean the cache
	 *
	 * @param   string  $group      The cache group
	 * @param   string  $client_id  The ID of the client
	 *
	 * @return  void
	 *
	 * @since    11.1
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		// Initialise variables;
		$conf = JFactory::getConfig();
		$dispatcher = JDispatcher::getInstance();

		$options = array(
			'defaultgroup' 	=> ($group) 	? $group : (isset($this->option) ? $this->option : JRequest::getCmd('option')),
			'cachebase'		=> ($client_id) ? JPATH_ADMINISTRATOR . '/cache' : $conf->get('cache_path', JPATH_SITE . '/cache')
		);

		jimport('joomla.cache.cache');
		$cache = JCache::getInstance('callback', $options);
		$cache->clean();

		// Trigger the onContentCleanCache event.
		$dispatcher->trigger($this->event_clean_cache, $options);
	}
}
