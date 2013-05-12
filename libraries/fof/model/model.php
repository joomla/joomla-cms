<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * FrameworkOnFramework model class
 *
 * FrameworkOnFramework is a set of classes whcih extend Joomla! 1.5 and later's
 * MVC framework with features making maintaining complex software much easier,
 * without tedious repetitive copying of the same code over and over again.
 *
 * @package  FrameworkOnFramework.Model
 * @since    1.0
 */
class FOFModel extends JObject
{

	/**
	 * Indicates if the internal state has been set
	 *
	 * @var    boolean
	 * @since  12.2
	 */
	protected $__state_set = null;

	/**
	 * Database Connector
	 *
	 * @var    object
	 * @since  12.2
	 */
	protected $_db;

	/**
	 * The event to trigger after deleting the data.
	 * @var    string
	 */
	protected $event_after_delete = 'onContentAfterDelete';

	/**
	 * The event to trigger after saving the data.
	 * @var    string
	 */
	protected $event_after_save = 'onContentAfterSave';

	/**
	 * The event to trigger before deleting the data.
	 * @var    string
	 */
	protected $event_before_delete = 'onContentBeforeDelete';

	/**
	 * The event to trigger before saving the data.
	 * @var    string
	 */
	protected $event_before_save = 'onContentBeforeSave';

	/**
	 * The event to trigger after changing the published state of the data.
	 * @var    string
	 */
	protected $event_change_state = 'onContentChangeState';

	/**
	 * The event to trigger when cleaning cache.
	 *
	 * @var      string
	 * @since    12.2
	 */
	protected $event_clean_cache = null;

	/**
	 * Stores a list of IDs passed to the model's state
	 * @var array
	 */
	protected $id_list = array();

	/**
	 * The first row ID passed to the model's state
	 * @var int
	 */
	protected $id = null;

	/**
	 * Input variables, passed on from the controller, in an associative array
	 * @var array
	 */
	protected $input = array();

	/**
	 * The list of records made available through getList
	 * @var array
	 */
	protected $list = null;

	/**
	 * The model (base) name
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $name;

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $option = null;

	/**
	 * The table object, populated when saving data
	 * @var FOFTable
	 */
	protected $otable = null;

	/**
	 * Pagination object
	 * @var JPagination
	 */
	protected $pagination = null;

	/**
	 * The table object, populated when retrieving data
	 * @var FOFTable
	 */
	protected $record = null;

	/**
	 * A state object
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $state;

	/**
	 * The name of the table to use
	 * @var string
	 */
	protected $table = null;

	/**
	 * Total rows based on the filters set in the model's state
	 * @var int
	 */
	protected $total = 0;

	/**
	 * Should I save the model's state in the session?
	 * @var bool
	 */
	protected $_savestate = null;

	/**
	 * Array of form objects.
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $_forms = array();

	/**
	 * The data to load into a form
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $_formData = array();

	/**
	 * An instance of FOFConfigProvider to provision configuration overrides
	 *
	 * @var    FOFConfigProvider
	 */
	protected $configProvider = null;

	/**
	 * Returns a new model object. Unless overriden by the $config array, it will
	 * try to automatically populate its state from the request variables.
	 *
	 * @param   string  $type    Model type, e.g. 'Items'
	 * @param   string  $prefix  Model prefix, e.g. 'FoobarModel'
	 * @param   array   $config  Model configuration variables
	 *
	 * @return  FOFModel
	 */
	public static function &getAnInstance($type, $prefix = '', $config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array)$config;
		} elseif (!is_array($config))
		{
			$config = array();
		}

		$type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$modelClass = $prefix . ucfirst($type);
		$result = false;

		// Guess the component name and include path
		if (!empty($prefix))
		{
			preg_match('/(.*)Model$/', $prefix, $m);
			$component = 'com_' . strtolower($m[1]);
		}
		else
		{
			$component = '';
		}

		if (array_key_exists('input', $config))
		{
			if (!($config['input'] instanceof FOFInput))
			{
				if (!is_array($config['input']))
				{
					$config['input'] = (array) $config['input'];
				}
				$config['input'] = array_merge($_REQUEST, $config['input']);
				$config['input'] = new FOFInput($config['input']);
			}
		}
		else
		{
			$config['input'] = new FOFInput;
		}

		if (empty($component))
		{
			$component = $config['input']->get('option', 'com_foobar');
		}
		$config['option'] = $component;

		$needsAView = true;

		if (array_key_exists('view', $config))
		{
			if (!empty($config['view']))
			{
				$needsAView = false;
			}
		}

		if ($needsAView)
		{
			$config['view'] = strtolower($type);
		}

		$config['input']->set('option', $config['option']);
		$config['input']->set('view', $config['view']);

		// Try to load the requested model class
		if (!class_exists($modelClass))
		{
			$include_paths = self::addIncludePath();

			list($isCLI, $isAdmin) = FOFDispatcher::isCliAdmin();

			if ($isAdmin)
			{
				$extra_paths = array(
					JPATH_ADMINISTRATOR . '/components/' . $component . '/models',
					JPATH_SITE . '/components/' . $component . '/models'
				);
			}
			else
			{
				$extra_paths = array(
					JPATH_SITE . '/components/' . $component . '/models',
					JPATH_ADMINISTRATOR . '/components/' . $component . '/models'
				);
			}
			$include_paths = array_merge($extra_paths, $include_paths);

			// Try to load the model file
			JLoader::import('joomla.filesystem.path');

			$path = JPath::find(
					$include_paths, self::_createFileName('model', array('name' => $type))
			);

			if ($path)
			{
				require_once $path;
			}
		}

		// Fallback to the Default model class, e.g. FoobarModelDefault
		if (!class_exists($modelClass))
		{
			$modelClass = $prefix . 'Default';

			if (!class_exists($modelClass))
			{
				$include_paths = self::addIncludePath();

				list($isCLI, $isAdmin) = FOFDispatcher::isCliAdmin();

				if ($isAdmin)
				{
					$extra_paths = array(
						JPATH_ADMINISTRATOR . '/components/' . $component . '/models',
						JPATH_SITE . '/components/' . $component . '/models'
					);
				}
				else
				{
					$extra_paths = array(
						JPATH_SITE . '/components/' . $component . '/models',
						JPATH_ADMINISTRATOR . '/components/' . $component . '/models'
					);
				}
				$include_paths = array_merge($extra_paths, $include_paths);

				// Try to load the model file
				JLoader::import('joomla.filesystem.path');

				$path = JPath::find(
						$include_paths, self::_createFileName('model', array('name' => 'default'))
				);

				if ($path)
				{
					require_once $path;
				}
			}
		}

		// Fallback to the generic FOFModel model class

		if (!class_exists($modelClass))
		{
			$modelClass = 'FOFModel';
		}

		$result = new $modelClass($config);

		return $result;
	}

	/**
	 * Returns a new instance of a model, with the state reset to defaults
	 *
	 * @param   string  $type    Model type, e.g. 'Items'
	 * @param   string  $prefix  Model prefix, e.g. 'FoobarModel'
	 * @param   array   $config  Model configuration variables
	 *
	 * @return FOFModel
	 */
	public static function &getTmpInstance($type, $prefix = '', $config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array)$config;
		} elseif (!is_array($config))
		{
			$config = array();
		}

		$ret = self::getAnInstance($type, $prefix, $config)
			->getClone()
			->clearState()
			->clearInput()
			->reset()
			->limitstart(0)
			->limit(0)
			->savestate(0);

		return $ret;
	}

	/**
	 * Add a directory where FOFModel should search for models. You may
	 * either pass a string or an array of directories.
	 *
	 * @param   mixed   $path    A path or array[sting] of paths to search.
	 * @param   string  $prefix  A prefix for models.
	 *
	 * @return  array  An array with directory elements. If prefix is equal to '', all directories are returned.
	 *
	 * @since   12.2
	 */
	public static function addIncludePath($path = '', $prefix = '')
	{
		static $paths;

		if (!isset($paths))
		{
			$paths = array();
		}

		if (!isset($paths[$prefix]))
		{
			$paths[$prefix] = array();
		}

		if (!isset($paths['']))
		{
			$paths[''] = array();
		}

		if (!empty($path))
		{
			jimport('joomla.filesystem.path');

			if (!in_array($path, $paths[$prefix]))
			{
				array_unshift($paths[$prefix], JPath::clean($path));
			}

			if (!in_array($path, $paths['']))
			{
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
	 *
	 * @since   12.2
	 */
	public static function addTablePath($path)
	{
		JTable::addIncludePath($path);
	}

	/**
	 * Create the filename for a resource
	 *
	 * @param   string  $type   The resource type to create the filename for.
	 * @param   array   $parts  An associative array of filename information.
	 *
	 * @return  string  The filename
	 *
	 * @since   12.2
	 */
	protected static function _createFileName($type, $parts = array())
	{
		$filename = '';

		switch ($type)
		{
			case 'model':
				$filename = strtolower($parts['name']) . '.php';
				break;

		}
		return $filename;
	}

	/**
	 * Public class constructor
	 *
	 * @param   type  $config  The configuration array
	 */
	public function __construct($config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array)$config;
		} elseif (!is_array($config))
		{
			$config = array();
		}

		// Get the input
		if (array_key_exists('input', $config))
		{
			if ($config['input'] instanceof FOFInput)
			{
				$this->input = $config['input'];
			}
			else
			{
				$this->input = new FOFInput($config['input']);
			}
		}
		else
		{
			$this->input = new FOFInput;
		}

		// Load the configuration provider
		$this->configProvider = new FOFConfigProvider;

		// Set the $name/$_name variable
		$component = $this->input->getCmd('option', 'com_foobar');

		if (array_key_exists('option', $config))
		{
			$component = $config['option'];
		}

		$this->input->set('option', $component);
		$name = str_replace('com_', '', strtolower($component));

		if (array_key_exists('name', $config))
		{
			$name = $config['name'];
		}

		$this->name = $name;
		$this->option = $component;

		// Get the view name
		$className = get_class($this);

		if ($className == 'FOFModel')
		{
			if (array_key_exists('view', $config))
			{
				$view = $config['view'];
			}

			if (empty($view))
			{
				$view = $this->input->getCmd('view', 'cpanel');
			}
		}
		else
		{
			$eliminatePart = ucfirst($name) . 'Model';
			$view = strtolower(str_replace($eliminatePart, '', $className));
		}

		// Set the model state
		if (array_key_exists('state', $config))
		{
			$this->state = $config['state'];
		}
		else
		{
			$this->state = new JObject;
		}

		// Set the model dbo
		if (array_key_exists('dbo', $config))
		{
			$this->_db = $config['dbo'];
		}
		else
		{
			$this->_db = JFactory::getDbo();
		}

		// Set the default view search path
		if (array_key_exists('table_path', $config))
		{
			$this->addTablePath($config['table_path']);
		}
		else
		{
			$path = JPATH_ADMINISTRATOR . '/components/' . $this->option . '/tables';
			$altPath = $this->configProvider->get($this->option . '.views.' . FOFInflector::singularize($this->name) . '.config.table_path', null);
			if ($altPath)
			{
				$path = JPATH_ADMINISTRATOR . '/components/' . $this->option . '/' . $altPath;
			}

			$this->addTablePath($path);
		}

		// Assign the correct table
		if (array_key_exists('table', $config))
		{
			$this->table = $config['table'];
		}
		else
		{
			$table = $this->configProvider->get($this->option . '.views.' . FOFInflector::singularize($this->name) . '.config.table', FOFInflector::singularize($view));
			$this->table = $table;
		}

		// Set the internal state marker - used to ignore setting state from the request
		if (
			!empty($config['ignore_request']) ||
			!is_null($this->configProvider->get($this->option . '.views.' . FOFInflector::singularize($this->name) . '.config.ignore_request', null))
		)
		{
			$this->__state_set = true;
		}

		// Get and store the pagination request variables
		$this->populateSavesate();
		list($isCLI, $isAdmin) = FOFDispatcher::isCliAdmin();

		if ($isCLI)
		{
			$limit = 20;
			$limitstart = 0;
		}
		else
		{
			$app = JFactory::getApplication();

			if (method_exists($app, 'getCfg'))
			{
				$default_limit = $app->getCfg('list_limit');
			}
			else
			{
				$default_limit = 20;
			}

			$limit = $this->getUserStateFromRequest($component . '.' . $view . '.limit', 'limit', $default_limit, 'int', $this->_savestate);
			$limitstart = $this->getUserStateFromRequest($component . '.' . $view . '.limitstart', 'limitstart', 0, 'int', $this->_savestate);
		}
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// Get the ID or list of IDs from the request or the configuration
		if (array_key_exists('cid', $config))
		{
			$cid = $config['cid'];
		}
		elseif ($cid = $this->configProvider->get($this->option . '.views.' . FOFInflector::singularize($this->name) . '.config.cid', null))
		{
			$cid = explode(',', $cid);
		}
		else
		{
			$cid = $this->input->get('cid', array(), 'array');
		}

		if (array_key_exists('id', $config))
		{
			$id = $config['id'];
		}
		elseif ($id = $this->configProvider->get($this->option . '.views.' . FOFInflector::singularize($this->name) . '.config.id', null))
		{
			$id = explode(',', $id);
			$id = array_shift($id);
		}
		else
		{
			$id = $this->input->getInt('id', 0);
		}

		if (is_array($cid) && !empty($cid))
		{
			$this->setIds($cid);
		}
		else
		{
			$this->setId($id);
		}

		// Populate the event names from the $config array

		$configKey = $this->option . '.views.' . FOFInflector::singularize($view) . '.config.';

		if (isset($config['event_after_delete']))
		{
			$this->event_after_delete = $config['event_after_delete'];
		}
		else
		{
			$this->event_after_delete = $this->configProvider->get($configKey . 'event_after_delete',
				$this->event_after_delete);
		}

		if (isset($config['event_after_save']))
		{
			$this->event_after_save = $config['event_after_save'];
		}
		else
		{
			$this->event_after_save = $this->configProvider->get($configKey . 'event_after_save',
				$this->event_after_save);
		}

		if (isset($config['event_before_delete']))
		{
			$this->event_before_delete = $config['event_before_delete'];
		}
		else
		{
			$this->event_before_delete = $this->configProvider->get($configKey . 'event_before_delete',
				$this->event_before_delete);
		}

		if (isset($config['event_before_save']))
		{
			$this->event_before_save = $config['event_before_save'];
		}
		else
		{
			$this->event_before_save = $this->configProvider->get($configKey . 'event_before_save',
				$this->event_before_save);
		}

		if (isset($config['event_change_state']))
		{
			$this->event_change_state = $config['event_change_state'];
		}
		else
		{
			$this->event_change_state = $this->configProvider->get($configKey . 'event_change_state',
				$this->event_change_state);
		}

		if (isset($config['event_clean_cache']))
		{
			$this->event_clean_cache = $config['event_clean_cache'];
		}
		else
		{
			$this->event_clean_cache = $this->configProvider->get($configKey . 'event_clean_cache',
				$this->event_clean_cache);
		}

	}

	/**
	 * Sets the list of IDs from the request data
	 *
	 * @return FOFModel
	 */
	public function setIDsFromRequest()
	{
		// Get the ID or list of IDs from the request or the configuration
		$cid = $this->input->get('cid', array(), 'array');
		$id = $this->input->getInt('id', 0);
		$kid = $this->input->getInt($this->getTable($this->table)->getKeyName(), 0);

		if (is_array($cid) && !empty($cid))
		{
			$this->setIds($cid);
		}
		else
		{
			if (empty($id))
			{
				$this->setId($kid);
			}
			else
			{
				$this->setId($id);
			}
		}

		return $this;
	}

	/**
	 * Sets the ID and resets internal data
	 *
	 * @param   integer  $id  The ID to use
	 *
	 * @return FOFModel
	 */
	public function setId($id = 0)
	{
		$this->reset();
		$this->id = (int) $id;
		$this->id_list = array($this->id);

		return $this;
	}

	/**
	 * Returns the currently set ID
	 *
	 * @return  integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Sets a list of IDs for batch operations from an array and resets the model
	 *
	 * @param   array  $idlist  An array of item IDs to be set to the model's state
	 *
	 * @return  FOFModel
	 */
	public function setIds($idlist)
	{
		$this->reset();
		$this->id_list = array();
		$this->id = 0;

		if (is_array($idlist) && !empty($idlist))
		{
			foreach ($idlist as $value)
			{
				$this->id_list[] = (int) $value;
			}
			$this->id = $this->id_list[0];
		}

		return $this;
	}

	/**
	 * Returns the list of IDs for batch operations
	 *
	 * @return  array  An array of integers
	 */
	public function getIds()
	{
		return $this->id_list;
	}

	/**
	 * Resets the model, like it was freshly loaded
	 *
	 * @return  FOFModel
	 */
	public function reset()
	{
		$this->id = 0;
		$this->id_list = null;
		$this->record = null;
		$this->list = null;
		$this->pagination = null;
		$this->total = 0;
		$this->otable = null;

		return $this;
	}

	/**
	 * Clears the model state, but doesn't touch the internal lists of records,
	 * record tables or record id variables. To clear these values, please use
	 * reset().
	 *
	 * @return  FOFModel
	 */
	public function clearState()
	{
		$this->state = new JObject;

		return $this;
	}

	/**
	 * Clears the input array.
	 *
	 * @return  FOFModel
	 */
	public function clearInput()
	{
		$this->input = new FOFInput(array());

		return $this;
	}

	/**
	 * Resets the saved state for this view
	 *
	 * @return  FOFModel
	 */
	public function resetSavedState()
	{
		JFactory::getApplication()->setUserState(substr($this->getHash(), 0, -1), null);

		return $this;
	}

	/**
	 * Returns a single item. It uses the id set with setId, or the first ID in
	 * the list of IDs for batch operations
	 *
	 * @param   integer  $id  Force a primary key ID to the model. Use null to use the id from the state.
	 *
	 * @return  FOFTable  A copy of the item's FOFTable array
	 */
	public function &getItem($id = null)
	{
		if (!is_null($id))
		{
			$this->record = null;
			$this->setId($id);
		}

		if (empty($this->record))
		{
			$table = $this->getTable($this->table);
			$table->load($this->id);
			$this->record = $table;

			// Do we have saved data?
			$session = JFactory::getSession();
			$serialized = $session->get($this->getHash() . 'savedata', null);

			if (!empty($serialized))
			{
				$data = @unserialize($serialized);

				if ($data !== false)
				{
					$k = $table->getKeyName();

					if (!array_key_exists($k, $data))
					{
						$data[$k] = null;
					}

					if ($data[$k] != $this->id)
					{
						$session->set($this->getHash() . 'savedata', null);
					}
					else
					{
						$this->record->bind($data);
					}
				}
			}

			$this->onAfterGetItem($this->record);
		}

		return $this->record;
	}

	/**
	 * Alias for getItemList
	 *
	 * @param   boolean  $overrideLimits  Should I override set limits?
	 * @param   string   $group           The group by clause
	 *
	 * @return  array
	 */
	public function &getList($overrideLimits = false, $group = '')
	{
		return $this->getItemList($overrideLimits, $group);
	}

	/**
	 * Returns a list of items
	 *
	 * @param   boolean  $overrideLimits  Should I override set limits?
	 * @param   string   $group           The group by clause
	 *
	 * @return  array
	 */
	public function &getItemList($overrideLimits = false, $group = '')
	{
		if (empty($this->list))
		{
			$query = $this->buildQuery($overrideLimits);

			if (!$overrideLimits)
			{
				$limitstart = $this->getState('limitstart');
				$limit = $this->getState('limit');
				$this->list = $this->_getList((string) $query, $limitstart, $limit, $group);
			}
			else
			{
				$this->list = $this->_getList((string) $query, 0, 0, $group);
			}
		}

		return $this->list;
	}

	/**
	 * A cross-breed between getItem and getItemList. It runs the complete query,
	 * like getItemList does. However, instead of returning an array of ad-hoc
	 * objects, it binds the data from the first item fetched on the list to an
	 * instance of the table object and returns that table object instead.
	 *
	 * @param   boolean  $overrideLimits  Should I override set limits?
	 *
	 * @return  FOFTable
	 */
	public function &getFirstItem($overrideLimits = false)
	{
		// we have to clone the instance, or when multiple getFirstItem calls occuer,
		// we'll update EVERY instance created
		$table = clone $this->getTable($this->table);

		$list = $this->getItemList($overrideLimits);

		if (!empty($list))
		{
			$firstItem = array_shift($list);
			$table->bind($firstItem);
		}
		unset($list);

		return $table;
	}

	/**
	 * Binds the data to the model and tries to save it
	 *
	 * @param   array|object  $data  The source data array or object
	 *
	 * @return  boolean  True on success
	 */
	public function save($data)
	{
		$this->otable = null;

		$table = $this->getTable($this->table);

		if (is_object($data))
		{
			$data = clone($data);
		}

		$key = $table->getKeyName();

		if (array_key_exists($key, (array) $data))
		{
			$aData = (array) $data;
			$oid = $aData[$key];
			$table->load($oid);
		}

		if (!$this->onBeforeSave($data, $table))
		{
			return false;
		}

		if (!$table->save($data))
		{
			foreach ($table->getErrors() as $error)
			{
				if (!empty($error))
				{
					$this->setError($error);
					$session = JFactory::getSession();
					$tableprops = $table->getProperties(true);
					unset($tableprops['input']);
					$hash = $this->getHash() . 'savedata';
					$session->set($hash, serialize($tableprops));
				}
			}

			return false;
		}
		else
		{
			$this->id = $table->$key;

			// Remove the session data
			JFactory::getSession()->set($this->getHash() . 'savedata', null);
		}

		$this->onAfterSave($table);

		$this->otable = $table;

		return true;
	}

	/**
	 * Copy one or more records
	 *
	 * @return  boolean  True on success
	 */
	public function copy()
	{
		if (is_array($this->id_list) && !empty($this->id_list))
		{

			$table = $this->getTable($this->table);

			if (!$this->onBeforeCopy($table))
			{
				return false;
			}

			if (!$table->copy($this->id_list))
			{
				$this->setError($table->getError());

				return false;
			}
			else
			{
				// Call our internal event
				$this->onAfterCopy($table);

				// @todo Should we fire the content plugin?
			}
		}

		return true;
	}

	/**
	 * Returns the table object after the last save() operation
	 *
	 * @return  FOFTable
	 */
	public function getSavedTable()
	{
		return $this->otable;
	}

	/**
	 * Deletes one or several items
	 *
	 * @return  boolean True on success
	 */
	public function delete()
	{
		if (is_array($this->id_list) && !empty($this->id_list))
		{
			$table = $this->getTable($this->table);

			foreach ($this->id_list as $id)
			{
				if (!$this->onBeforeDelete($id, $table))
				{
					continue;
				}

				if (!$table->delete($id))
				{
					$this->setError($table->getError());

					return false;
				}
				else
				{
					$this->onAfterDelete($id);
				}
			}
		}

		return true;
	}

	/**
	 * Toggles the published state of one or several items
	 *
	 * @param   integer  $publish  The publishing state to set (e.g. 0 is unpublished)
	 * @param   integer  $user     The user ID performing this action
	 *
	 * @return  boolean True on success
	 */
	public function publish($publish = 1, $user = null)
	{
		if (is_array($this->id_list) && !empty($this->id_list))
		{
			if (empty($user))
			{
				$oUser = JFactory::getUser();
				$user = $oUser->id;
			}
			$table = $this->getTable($this->table);

			if (!$this->onBeforePublish($table))
			{
				return false;
			}

			if (!$table->publish($this->id_list, $publish, $user))
			{
				$this->setError($table->getError());

				return false;
			}
			else
			{
				// Call our itnernal event
				$this->onAfterPublish($table);

				// Call the plugin events
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('content');
				$name = $this->input->getCmd('view', 'cpanel');
				$context = $this->option . '.' . $name;
				$result = $dispatcher->trigger($this->event_change_state, array($context, $this->id_list, $publish));
			}
		}

		return true;
	}

	/**
	 * Checks out the current item
	 *
	 * @return  boolean
	 */
	public function checkout()
	{
		$table = $this->getTable($this->table);
		$status = $table->checkout(JFactory::getUser()->id, $this->id);

		if (!$status)
		{
			$this->setError($table->getError());
		}

		return $status;
	}

	/**
	 * Checks in the current item
	 *
	 * @return  boolean
	 */
	public function checkin()
	{
		$table = $this->getTable($this->table);
		$status = $table->checkin($this->id);

		if (!$status)
		{
			$this->setError($table->getError());
		}

		return $status;
	}

	/**
	 * Tells you if the current item is checked out or not
	 *
	 * @return  boolean
	 */
	public function isCheckedOut()
	{
		$table = $this->getTable($this->table);
		$status = $table->isCheckedOut($this->id);

		if (!$status)
		{
			$this->setError($table->getError());
		}

		return $status;
	}

	/**
	 * Increments the hit counter
	 *
	 * @return  boolean
	 */
	public function hit()
	{
		$table = $this->getTable($this->table);

		if (!$this->onBeforeHit($table))
		{
			return false;
		}

		$status = $table->hit($this->id);

		if (!$status)
		{
			$this->setError($table->getError());
		}
		else
		{
			$this->onAfterHit($table);
		}

		return $status;
	}

	/**
	 * Moves the current item up or down in the ordering list
	 *
	 * @param   string  $dirn  The direction and magnitude to use (2 means move up by 2 positions, -3 means move down three positions)
	 *
	 * @return  boolean  True on success
	 */
	public function move($dirn)
	{
		$table = $this->getTable($this->table);

		$id = $this->getId();
		$status = $table->load($id);

		if (!$status)
		{
			$this->setError($table->getError());
		}

		if (!$status)
		{
			return false;
		}

		if (!$this->onBeforeMove($table))
		{
			return false;
		}

		$status = $table->move($dirn);

		if (!$status)
		{
			$this->setError($table->getError());
		}
		else
		{
			$this->onAfterMove($table);
		}

		return $status;
	}

	/**
	 * Reorders all items in the table
	 *
	 * @return  boolean
	 */
	public function reorder()
	{
		$table = $this->getTable($this->table);

		if (!$this->onBeforeReorder($table))
		{
			return false;
		}

		$status = $table->reorder($this->getReorderWhere());

		if (!$status)
		{
			$this->setError($table->getError());
		}
		else
		{
			if (!$this->onAfterReorder($table))
			{
				return false;
			}
		}

		return $status;
	}

	/**
	 * Get a pagination object
	 *
	 * @return  JPagination
	 */
	public function getPagination()
	{
		if (empty($this->pagination))
		{
			// Import the pagination library
			JLoader::import('joomla.html.pagination');

			// Prepare pagination values
			$total = $this->getTotal();
			$limitstart = $this->getState('limitstart');
			$limit = $this->getState('limit');

			// Create the pagination object
			$this->pagination = new JPagination($total, $limitstart, $limit);
		}

		return $this->pagination;
	}

	/**
	 * Get the number of all items
	 *
	 * @return  integer
	 */
	public function getTotal()
	{
		if (empty($this->total))
		{
			$query = $this->buildCountQuery();

			if ($query === false)
			{
				$sql = (string) $this->buildQuery(false);
				$query = $this->_db->getQuery(true)
					->select('COUNT(*)')
					->from("($sql) AS a");
			}

			$this->_db->setQuery((string) $query);
			$this->_db->execute();

			$this->total = $this->_db->loadResult();
		}

		return $this->total;
	}

	/**
	 * Returns a record count for the query
	 *
	 * @param   string  $query  The query.
	 *
	 * @return  integer  Number of rows for query
	 *
	 * @since   12.2
	 */
	protected function _getListCount($query)
	{
		return $this->getTotal();
	}

	/**
	 * Get a filtered state variable
	 *
	 * @param   string  $key          The name of the state variable
	 * @param   mixed   $default      The default value to use
	 * @param   string  $filter_type  Filter type
	 *
	 * @return  mixed  The variable's value
	 */
	public function getState($key = null, $default = null, $filter_type = 'raw')
	{
		if (empty($key))
		{
			return $this->_real_getState();
		}

		// Get the savestate status
		$value = $this->_real_getState($key);

		if (is_null($value))
		{
			$value = $this->getUserStateFromRequest($this->getHash() . $key, $key, $value, 'none', $this->_savestate);

			if (is_null($value))
			{
				return $default;
			}
		}

		if (strtoupper($filter_type) == 'RAW')
		{
			return $value;
		}
		else
		{
			JLoader::import('joomla.filter.filterinput');
			$filter = new JFilterInput;

			return $filter->clean($value, $filter_type);
		}
	}

	/**
	 * Method to get model state variables
	 *
	 * @param   string  $property  Optional parameter name
	 * @param   mixed   $default   Optional default value
	 *
	 * @return  object  The property where specified, the state object where omitted
	 *
	 * @since   12.2
	 */
	protected function _real_getState($property = null, $default = null)
	{
		if (!$this->__state_set)
		{
			// Protected method to auto-populate the model state.
			$this->populateState();

			// Set the model state set flag to true.
			$this->__state_set = true;
		}

		return $property === null ? $this->state : $this->state->get($property, $default);
	}

	/**
	 * Returns a hash for this component and view, e.g. "foobar.items.", used
	 * for determining the keys of the variables which will be placed in the
	 * session storage.
	 *
	 * @return  string  The hash
	 */
	public function getHash()
	{
		$option = $this->input->getCmd('option', 'com_foobar');
		$view = FOFInflector::pluralize($this->input->getCmd('view', 'cpanel'));

		return "$option.$view.";
	}

	/**
	 * Gets the value of a user state variable.
	 *
	 * @param   string   $key           The key of the user state variable.
	 * @param   string   $request       The name of the variable passed in a request.
	 * @param   string   $default       The default value for the variable if not found. Optional.
	 * @param   string   $type          Filter for the variable, for valid values see {@link JFilterInput::clean()}. Optional.
	 * @param   boolean  $setUserState  Should I save the variable in the user state? Default: true. Optional.
	 *
	 * @return  The request user state.
	 */
	protected function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $setUserState = true)
	{
		list($isCLI, $isAdmin) = FOFDispatcher::isCliAdmin();

		if ($isCLI)
		{
			return $default;
		}

		$app = JFactory::getApplication();

		if (method_exists($app, 'getUserState'))
		{
			$old_state = $app->getUserState($key);
		}
		else
		{
			$old_state = null;
		}

		$cur_state = (!is_null($old_state)) ? $old_state : $default;
		$new_state = $this->input->get($request, null, $type);

		// Save the new value only if it was set in this request
		if ($setUserState)
		{
			if ($new_state !== null)
			{
				$app->setUserState($key, $new_state);
			}
			else
			{
				$new_state = $cur_state;
			}
		}
		elseif (is_null($new_state))
		{
			$new_state = $cur_state;
		}

		return $new_state;
	}

	/**
	 * Returns an object list
	 *
	 * @param   string   $query       The query
	 * @param   integer  $limitstart  Offset from start
	 * @param   integer  $limit       The number of records
	 * @param   string   $group       The group by clause
	 *
	 * @return  array  Array of objects
	 */
	protected function &_getList($query, $limitstart = 0, $limit = 0, $group = '')
	{
		$this->_db->setQuery($query, $limitstart, $limit);
		$result = $this->_db->loadObjectList($group);

		$this->onProcessList($result);

		return $result;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  FOFTable  A FOFTable object
	 */
	public function getTable($name = '', $prefix = null, $options = array())
	{
		if (empty($name))
		{
			$name = $this->table;

			if (empty($name))
			{
				$name = FOFInflector::singularize($this->getName());
			}
		}

		if (empty($prefix))
		{
			$prefix = ucfirst($this->getName()) . 'Table';
		}

		if (empty($options))
		{
			$options = array('input' => $this->input);
		}

		if ($table = $this->_createTable($name, $prefix, $options))
		{
			return $table;
		}

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
		}
		else
		{
			JError::raiseError(0, JText::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name));
		}

		return null;
	}

	/**
	 * Method to load and return a model object.
	 *
	 * @param   string  $name    The name of the view
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The configuration array to pass to the table
	 *
	 * @return  FOFTable  Table object or boolean false if failed
	 */
	protected function &_createTable($name, $prefix = 'Table', $config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array)$config;
		} elseif (!is_array($config))
		{
			$config = array();
		}

		$result = null;

		// Clean the model name
		$name = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		// Make sure we are returning a DBO object

		if (!array_key_exists('dbo', $config))
		{
			$config['dbo'] = $this->getDBO();
		}

		$instance = FOFTable::getAnInstance($name, $prefix, $config);

		return $instance;
	}

	/**
	 * Creates the WHERE part of the reorder query
	 *
	 * @return  string
	 */
	public function getReorderWhere()
	{
		return '';
	}

	/**
	 * Builds the SELECT query
	 *
	 * @param   boolean  $overrideLimits  Are we requested to override the set limits?
	 *
	 * @return  JDatabaseQuery
	 */
	public function buildQuery($overrideLimits = false)
	{
		$table = $this->getTable();
		$tableName = $table->getTableName();
		$tableKey = $table->getKeyName();
		$db = $this->getDBO();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn($tableName));

		$where = array();

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$fields = $db->getTableColumns($tableName, true);
		}
		else
		{
			$fieldsArray = $db->getTableFields($tableName, true);
			$fields = array_shift($fieldsArray);
		}

		foreach ($fields as $fieldname => $fieldtype)
		{
			$filterName = ($fieldname == $tableKey) ? 'id' : $fieldname;
			$filterState = $this->getState($filterName, null);

			if ($filterName == $table->getColumnAlias('enabled'))
			{
				if (!is_null($filterState) && ($filterState !== ''))
				{
					$query->where($db->qn($fieldname) . ' = ' . $db->q((int) $filterState));
				}
			}
			elseif (!empty($filterState) || ($filterState === '0'))
			{
				switch ($fieldname)
				{
					case $table->getColumnAlias('title'):
					case $table->getColumnAlias('description'):
						$query->where('(' . $db->qn($fieldname) . ' LIKE ' . $db->q('%' . $filterState . '%') . ')');

						break;

					default:
						if (is_array($filterState))
						{
							$tmp = array();
							foreach ($filterState as $k => $v)
							{
								$tmp[] = $db->q($v);
							}
							$query->where('(' . $db->qn($fieldname) . ' IN(' . implode(',', $tmp) . '))');
						}
						else
						{
							$query->where('(' . $db->qn($fieldname) . '=' . $db->q($filterState) . ')');
						}
						break;
				}
			}
		}

		if (!$overrideLimits)
		{
			$order = $this->getState('filter_order', null, 'cmd');

			if (!in_array($order, array_keys($this->getTable()->getData())))
			{
				$order = $tableKey;
			}

			$dir = $this->getState('filter_order_Dir', 'ASC', 'cmd');
			$query->order($db->qn($order) . ' ' . $dir);
		}

		return $query;
	}

	/**
	 * Builds the count query used in getTotal()
	 *
	 * @return  boolean
	 */
	public function buildCountQuery()
	{
		return false;
	}

	/**
	 * Clones the model object and returns the clone
	 *
	 * @return  FOFModel
	 */
	public function &getClone()
	{
		$clone = clone($this);

		return $clone;
	}

	/**
	 * Magic getter; allows to use the name of model state keys as properties
	 *
	 * @param   string  $name  The name of the variable to get
	 *
	 * @return  mixed  The value of the variable
	 */
	public function __get($name)
	{
		return $this->getState($name);
	}

	/**
	 * Magic setter; allows to use the name of model state keys as properties
	 *
	 * @param   string  $name   The name of the variable
	 * @param   mixed   $value  The value to set the variable to
	 *
	 * @return  void
	 */
	public function __set($name, $value)
	{
		return $this->setState($name, $value);
	}

	/**
	 * Magic caller; allows to use the name of model state keys as methods to
	 * set their values.
	 *
	 * @param   string  $name       The name of the state variable to set
	 * @param   mixed   $arguments  The value to set the state variable to
	 *
	 * @return  FOFModel  Reference to self
	 */
	public function __call($name, $arguments)
	{
		$arg1 = array_shift($arguments);
		$this->setState($name, $arg1);

		return $this;
	}

	/**
	 * Sets the model state auto-save status. By default the model is set up to
	 * save its state to the session.
	 *
	 * @param   boolean  $newState  True to save the state, false to not save it.
	 *
	 * @return  FOFModel  Reference to self
	 */
	public function &savestate($newState)
	{
		$this->_savestate = $newState ? true : false;

		return $this;
	}

	/**
	 * Initialises the _savestate variable
	 *
	 * @return  void
	 */
	public function populateSavesate()
	{
		if (is_null($this->_savestate))
		{
			$savestate = $this->input->getInt('savestate', -999);

			if ($savestate == -999)
			{
				$savestate = true;
			}

			$this->savestate($savestate);
		}
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
	protected function populateState()
	{
	}

	/**
	 * Applies view access level filtering for the specified user. Useful to
	 * filter a front-end items listing.
	 *
	 * @param   integer  $userID  The user ID to use. Skip it to use the currently logged in user.
	 *
	 * @return  FOFModel  Reference to self
	 */
	public function applyAccessFiltering($userID = null)
	{
		$user = JFactory::getUser($userID);
		$this->setState('access', $user->getAuthorisedViewLevels());

		return $this;
	}

	/**
	 * A method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 * @param   boolean  $source    The name of the form. If not set we'll try the form_name state variable or fall back to default.
	 *
	 * @return  mixed  A FOFForm object on success, false on failure
	 *
	 * @since   2.0
	 */
	public function getForm($data = array(), $loadData = true, $source = null)
	{
		$this->_formData = $data;

		$name = $this->input->getCmd('option', 'com_foobar') . '.'
			. $this->input->getCmd('view', 'cpanels');

		if (empty($source))
		{
			$source = $this->getState('form_name', null);
		}

		if (empty($source))
		{
			$source = 'form.' . $this->input->getCmd('view', 'cpanels');
		}

		$options = array(
			'control'	 => false,
			'load_data'	 => $loadData,
		);

		$form = $this->loadForm($name, $source, $options);

		return $form;
	}

	/**
	 * Method to get a form object.
	 *
	 * @param   string   $name     The name of the form.
	 * @param   string   $source   The form source. Can be XML string if file flag is set to false.
	 * @param   array    $options  Optional array of options for the form creation.
	 * @param   boolean  $clear    Optional argument to force load a new form.
	 * @param   string   $xpath    An optional xpath to search for the fields.
	 *
	 * @return  mixed  FOFForm object on success, False on error.
	 *
	 * @see     FOFForm
	 * @since   2.0
	 */
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		// Handle the optional arguments.
		$options['control'] = JArrayHelper::getValue($options, 'control', false);

		// Create a signature hash.
		$hash = md5($source . serialize($options));

		// Check if we can use a previously loaded form.

		if (isset($this->_forms[$hash]) && !$clear)
		{
			return $this->_forms[$hash];
		}

		// Try to find the name and path of the form to load
		$formFilename = $this->findFormFilename($source);

		// No form found? Quit!

		if ($formFilename === false)
		{
			return false;
		}

		// Set up the form name and path
		$source = basename($formFilename, '.xml');
		FOFForm::addFormPath(dirname($formFilename));

		// Set up field paths
		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();

		$option = $this->input->getCmd('option', 'com_foobar');
		$view = $this->input->getCmd('view', 'cpanels');
		$file_root = ($isAdmin ? JPATH_ADMINISTRATOR : JPATH_SITE);
		$file_root .= '/components/' . $option;
		$alt_file_root = ($isAdmin ? JPATH_SITE : JPATH_ADMINISTRATOR);
		$alt_file_root .= '/components/' . $option;

		FOFForm::addFieldPath($file_root . '/fields');
		FOFForm::addFieldPath($file_root . '/models/fields');
		FOFForm::addFieldPath($alt_file_root . '/fields');
		FOFForm::addFieldPath($alt_file_root . '/models/fields');

		FOFForm::addHeaderPath($file_root . '/fields/header');
		FOFForm::addHeaderPath($file_root . '/models/fields/header');
		FOFForm::addHeaderPath($alt_file_root . '/fields/header');
		FOFForm::addHeaderPath($alt_file_root . '/models/fields/header');

		// Get the form.
		try
		{
			$form = FOFForm::getInstance($name, $source, $options, false, $xpath);

			if (isset($options['load_data']) && $options['load_data'])
			{
				// Get the data for the form.
				$data = $this->loadFormData();
			}
			else
			{
				$data = array();
			}

			// Allow for additional modification of the form, and events to be triggered.
			// We pass the data because plugins may require it.
			$this->preprocessForm($form, $data);

			// Load the data into the form after the plugins have operated.
			$form->bind($data);
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Store the form for later.
		$this->_forms[$hash] = $form;

		return $form;
	}

	/**
	 * Guesses the best candidate for the path to use for a particular form.
	 *
	 * @param   string  $source  The name of the form file to load, without the .xml extension
	 *
	 * @return  string  The path and filename of the form to load
	 *
	 * @since   2.0
	 */
	public function findFormFilename($source)
	{
		// Get some useful variables
		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();

		$option = $this->input->getCmd('option', 'com_foobar');
		$view 	= $this->input->getCmd('view', 'cpanels');

		if(!$isCli)
		{
			$template = JFactory::getApplication()->getTemplate();
		}
		else
		{
			$template = 'cli';
		}

		$file_root = ($isAdmin ? JPATH_ADMINISTRATOR : JPATH_SITE);
		$file_root .= '/components/' . $option;
		$alt_file_root = ($isAdmin ? JPATH_SITE : JPATH_ADMINISTRATOR);
		$alt_file_root .= '/components/' . $option;
		$template_root = ($isAdmin ? JPATH_ADMINISTRATOR : JPATH_SITE);
		$template_root .= '/templates/' . $template . '/html/' . $option;

		// Set up the paths to look into
		$paths = array(
			// In the template override
			$template_root . '/' . $view,
			$template_root . '/' . FOFInflector::singularize($view),
			$template_root . '/' . FOFInflector::pluralize($view),
			// In this side of the component
			$file_root . '/views/' . $view . '/tmpl',
			$file_root . '/views/' . FOFInflector::singularize($view) . '/tmpl',
			$file_root . '/views/' . FOFInflector::pluralize($view) . '/tmpl',
			// In the other side of the component
			$alt_file_root . '/views/' . $view . '/tmpl',
			$alt_file_root . '/views/' . FOFInflector::singularize($view) . '/tmpl',
			$alt_file_root . '/views/' . FOFInflector::pluralize($view) . '/tmpl',
			// In the models/forms of this side
			$file_root . '/models/forms',
			// In the models/forms of the other side
			$alt_file_root . '/models/forms',
		);

		// Set up the suffixes to look into
		$jversion = new JVersion;
		$versionParts = explode('.', $jversion->RELEASE);
		$majorVersion = array_shift($versionParts);
		$suffixes = array(
			'.j' . str_replace('.', '', $jversion->getHelpVersion()) . '.xml',
			'.j' . $majorVersion . '.xml',
			'.xml',
		);
		unset($jversion, $versionParts, $majorVersion);

		// Look for all suffixes in all paths
		JLoader::import('joomla.filesystem.file');
		$result = false;

		foreach ($paths as $path)
		{
			foreach ($suffixes as $suffix)
			{
				$filename = $path . '/' . $source . $suffix;

				if (JFile::exists($filename))
				{
					$result = $filename;
					break;
				}
			}

			if ($result)
			{
				break;
			}
		}

		return $result;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array    The default data is an empty array.
	 *
	 * @since   2.0
	 */
	protected function loadFormData()
	{
		if (empty($this->_formData))
		{
			return array();
		}
		else
		{
			return $this->_formData;
		}
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   FOFForm  $form   A FOFForm object.
	 * @param   mixed    $data   The data expected for the form.
	 * @param   string   $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @see     FOFFormField
	 * @since   2.0
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(FOFForm $form, $data, $group = 'content')
	{
		// Import the appropriate plugin group.
		JLoader::import('joomla.plugin.helper');
		JPluginHelper::importPlugin($group);

		// Trigger the form preparation event.
		$app = JFactory::getApplication();
		$results = $app->triggerEvent('onContentPrepareForm', array($form, $data));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true))
		{
			// Get the last error.
			$dispatcher = JDispatcher::getInstance();
			$error = $dispatcher->getError();

			if (!($error instanceof Exception))
			{
				throw new Exception($error);
			}
		}
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   FOFForm  $form   The form to validate against.
	 * @param   array    $data   The data to validate.
	 * @param   string   $group  The name of the field group to validate.
	 *
	 * @return  mixed   Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   2.0
	 */
	public function validateForm($form, $data, $group = null)
	{
		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof Exception)
		{
			$this->setError($return->getMessage());

			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message)
			{
				$this->setError($message);
			}

			return false;
		}

		return $data;
	}

	/**
	 * This method can be overriden to automatically do something with the
	 * list results array. You are supposed to modify the list which was passed
	 * in the parameters; DO NOT return a new array!
	 *
	 * @param   array  &$resultArray  An array of objects, each row representing a record
	 *
	 * @return  void
	 */
	protected function onProcessList(&$resultArray)
	{

	}

	/**
	 * This method runs after an item has been gotten from the database in a read
	 * operation. You can modify it before it's returned to the MVC triad for
	 * further processing.
	 *
	 * @param   FOFTable  $record
	 *
	 * @return  void
	 */
	protected function onAfterGetItem(&$record)
	{

	}

	/**
	 * This method runs before the $data is saved to the $table. Return false to
	 * stop saving.
	 *
	 * @param   array     $data   The data to save
	 * @param   FOFTable  $table  The table to save the data to
	 *
	 * @return  boolean  Return false to prevent saving, true to allow it
	 */
	protected function onBeforeSave(&$data, &$table)
	{
		list($isCLI, $isAdmin) = FOFDispatcher::isCliAdmin();

		// Let's import the plugin only if we're not in CLI (content plugin needs a user)

		if (!$isCLI)
		{
			JPluginHelper::importPlugin('content');
		}

		$dispatcher = JDispatcher::getInstance();

		try
		{
			// Do I have a new record?
			$key = $table->getKeyName();

			if ($data instanceof FOFTable)
			{
				$allData = $data->getData();
			}
			elseif (is_object($data))
			{
				$allData = (array) $data;
			}
			else
			{
				$allData = $data;
			}

			$pk = (!empty($allData[$key])) ? $allData[$key] : 0;

			$this->_isNewRecord = $pk <= 0;

			// Bind the data
			$table->bind($allData);

			// Call the plugin
			$name = $this->name;
			$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $name, &$table, $this->_isNewRecord));

			if (in_array(false, $result, true))
			{
				// Plugin failed, return false
				$this->setError($table->getError());

				return false;
			}
			else
			{
				// Plugin successful, refetch the possibly modified data
				if ($data instanceof FOFTable)
				{
					$data->bind($allData);
				}
				elseif (is_object($data))
				{
					$data = (object) $allData;
				}
				else
				{
					$data = $allData;
				}
			}
		}
		catch (Exception $e)
		{
			// Oops, an exception occured!
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * This method runs after the data is saved to the $table.
	 *
	 * @param   FOFTable  $table
	 *
	 * @return  boolean
	 */
	protected function onAfterSave(&$table)
	{
		list($isCLI, $isAdmin) = FOFDispatcher::isCliAdmin();

		// Let's import the plugin only if we're not in CLI (content plugin needs a user)

		if (!$isCLI)
		{
			JPluginHelper::importPlugin('content');
		}

		$dispatcher = JDispatcher::getInstance();

		try
		{
			$name = $this->name;
			$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $name, &$table, $this->_isNewRecord));
		}
		catch (Exception $e)
		{
			// Oops, an exception occured!
			$this->setError($e->getMessage());

			return false;
		}
	}

	/**
	 * This method runs before the record with key value of $id is deleted from $table
	 *
	 * @param   FOFTable  $table
	 *
	 * @return  boolean
	 */
	protected function onBeforeDelete(&$id, &$table)
	{
		list($isCLI, $isAdmin) = FOFDispatcher::isCliAdmin();

		// Let's import the plugin only if we're not in CLI (content plugin needs a user)

		if (!$isCLI)
		{
			JPluginHelper::importPlugin('content');
		}

		$dispatcher = JDispatcher::getInstance();

		try
		{
			$table->load($id);

			$name = $this->input->getCmd('view', 'cpanel');
			$context = $this->option . '.' . $name;
			$result = $dispatcher->trigger($this->event_before_delete, array($context, $table));

			if (in_array(false, $result, true))
			{
				// Plugin failed, return false
				$this->setError($table->getError());

				return false;
			}

			$this->_recordForDeletion = clone $table;
		}
		catch (Exception $e)
		{
			// Oops, an exception occured!
			$this->setError($e->getMessage());

			return false;
		}
		return true;
	}

	/**
	 * This method runs after a record with key value $id is deleted
	 *
	 * @param   integer  $id  The id of the record which was deleted
	 *
	 * @return  boolean  Return false to raise an error, true otherwise
	 */
	protected function onAfterDelete($id)
	{
		list($isCLI, $isAdmin) = FOFDispatcher::isCliAdmin();

		// Let's import the plugin only if we're not in CLI (content plugin needs a user)
		if (!$isCLI)
		{
			JPluginHelper::importPlugin('content');
		}

		$dispatcher = JDispatcher::getInstance();

		try
		{
			$name = $this->input->getCmd('view', 'cpanel');
			$context = $this->option . '.' . $name;
			$result = $dispatcher->trigger($this->event_after_delete, array($context, $this->_recordForDeletion));
			unset($this->_recordForDeletion);
		}
		catch (Exception $e)
		{
			// Oops, an exception occured!
			$this->setError($e->getMessage());

			return false;
		}
	}

	protected function onBeforeCopy(&$table)
	{
		return true;
	}

	protected function onAfterCopy(&$table)
	{
		return true;
	}

	protected function onBeforePublish(&$table)
	{
		return true;
	}

	protected function onAfterPublish(&$table)
	{
		return true;
	}

	protected function onBeforeHit(&$table)
	{
		return true;
	}

	protected function onAfterHit(&$table)
	{

	}

	protected function onBeforeMove(&$table)
	{
		return true;
	}

	protected function onAfterMove(&$table)
	{

	}

	protected function onBeforeReorder(&$table)
	{
		return true;
	}

	protected function onAfterReorder(&$table)
	{

	}

	/**
	 * Method to get the database driver object
	 *
	 * @return  JDatabaseDriver
	 */
	public function getDbo()
	{
		return $this->_db;
	}

	/**
	 * Method to get the model name
	 *
	 * The model name. By default parsed using the classname or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the model
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$r = null;
			if (!preg_match('/Model(.*)/i', get_class($this), $r))
			{
				throw new Exception(JText::_('JLIB_APPLICATION_ERROR_MODEL_GET_NAME'), 500);
			}
			$this->name = strtolower($r[1]);
		}

		return $this->name;
	}

	/**
	 * Method to set the database driver object
	 *
	 * @param   JDatabaseDriver  $db  A JDatabaseDriver based object
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function setDbo($db)
	{
		$this->_db = $db;
	}

	/**
	 * Method to set model state variables
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set or null.
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
	 * Clean the cache
	 *
	 * @param   string   $group      The cache group
	 * @param   integer  $client_id  The ID of the client
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		$conf = JFactory::getConfig();
		$dispatcher = JEventDispatcher::getInstance();

		$options = array(
			'defaultgroup' => ($group) ? $group : (isset($this->option) ? $this->option : JFactory::getApplication()->input->get('option')),
			'cachebase' => ($client_id) ? JPATH_ADMINISTRATOR . '/cache' : $conf->get('cache_path', JPATH_SITE . '/cache'));

		$cache = JCache::getInstance('callback', $options);
		$cache->clean();

		// Trigger the onContentCleanCache event.
		$dispatcher->trigger($this->event_clean_cache, $options);
	}
}