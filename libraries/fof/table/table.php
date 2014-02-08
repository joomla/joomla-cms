<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  table
 * @copyright   Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die;

/**
 * Normally this shouldn't be required. Some PHP versions, however, seem to
 * require this. Why? No idea whatsoever. If I remove it, FOF crashes on some
 * hosts. Same PHP version on another host and no problem occurs. Any takers?
 */
if (class_exists('FOFTable', false))
{
	return;
}

if (!interface_exists('JTableInterface', true))
{
	interface JTableInterface {}
}

/**
 * FrameworkOnFramework Table class. The Table is one part controller, one part
 * model and one part data adapter. It's supposed to handle operations for single
 * records.
 *
 * @package  FrameworkOnFramework
 * @since    1.0
 */
class FOFTable extends JObject implements JTableInterface
{
	/**
	 * Cache array for instances
	 *
	 * @var    array
	 */
	private static $instances = array();

	/**
	 * Include paths for searching for FOFTable classes.
	 *
	 * @var    array
	 */
	private static $_includePaths = array();

	/**
	 * The configuration parameters array
	 *
	 * @var  array
	 */
	protected $config = array();

	/**
	 * Name of the database table to model.
	 *
	 * @var    string
	 */
	protected $_tbl = '';

	/**
	 * Name of the primary key field in the table.
	 *
	 * @var    string
	 */
	protected $_tbl_key = '';

	/**
	 * JDatabaseDriver object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $_db;

	/**
	 * Should rows be tracked as ACL assets?
	 *
	 * @var    boolean
	 */
	protected $_trackAssets = false;

	/**
	 * Does the resource support joomla tags?
	 *
	 * @var    boolean
	 */
	protected $_has_tags = false;

	/**
	 * The rules associated with this record.
	 *
	 * @var    JAccessRules  A JAccessRules object.
	 */
	protected $_rules;

	/**
	 * Indicator that the tables have been locked.
	 *
	 * @var    boolean
	 */
	protected $_locked = false;

	/**
	 * If this is set to true, it triggers automatically plugin events for
	 * table actions
	 *
	 * @var    boolean
	 */
	protected $_trigger_events = false;

	/**
	 * Table alias used in queries
	 *
	 * @var    string
	 */
	protected $_tableAlias = false;

	/**
	 * Array with alias for "special" columns such as ordering, hits etc etc
	 *
	 * @var    array
	 */
	protected $_columnAlias = array();

	/**
	 * If set to true, it enabled automatic checks on fields based on columns properties
	 *
	 * @var    boolean
	 */
	protected $_autoChecks = false;

	/**
	 * Array with fields that should be skipped by automatic checks
	 *
	 * @var    array
	 */
	protected $_skipChecks = array();

	/**
	 * Does the table actually exist? We need that to avoid PHP notices on
	 * table-less views.
	 *
	 * @var    boolean
	 */
	protected $_tableExists = true;

	/**
	 * The asset key for items in this table. It's usually something in the
	 * com_example.viewname format. They asset name will be this key appended
	 * with the item's ID, e.g. com_example.viewname.123
	 *
	 * @var    string
	 */
	protected $_assetKey = '';

	/**
	 * The input data
	 *
	 * @var    FOFInput
	 */
	protected $input = null;

	/**
	 * Extended query including joins with other tables
	 *
	 * @var    JDatabaseQuery
	 */
	protected $_queryJoin = null;

	/**
	 * The prefix for the table class
	 *
	 * @var		string
	 */
	protected $_tablePrefix = '';

	/**
	 * The known fields for this table
	 *
	 * @var		array
	 */
	protected $knownFields = array();

	/**
	 * A list of table fields, keyed per table
	 *
	 * @var array
	 */
	protected static $tableFieldCache = array();

	/**
	 * A list of tables in the database
	 *
	 * @var array
	 */
	protected static $tableCache = array();

	/**
	 * An instance of FOFConfigProvider to provision configuration overrides
	 *
	 * @var    FOFConfigProvider
	 */
	protected $configProvider = null;

	/**
	 * FOFTableDispatcherBehavior for dealing with extra behaviors
	 *
	 * @var    FOFTableDispatcherBehavior
	 */
	protected $tableDispatcher = null;

	/**
	 * List of default behaviors to apply to the table
	 *
	 * @var    array
	 */
	protected $default_behaviors = array('tags', 'assets');

	/**
	 * Returns a static object instance of a particular table type
	 *
	 * @param   string  $type    The table name
	 * @param   string  $prefix  The prefix of the table class
	 * @param   array   $config  Optional configuration variables
	 *
	 * @return FOFTable
	 */
	public static function getInstance($type, $prefix = 'JTable', $config = array())
	{
		return self::getAnInstance($type, $prefix, $config);
	}

	/**
	 * Returns a static object instance of a particular table type
	 *
	 * @param   string  $type    The table name
	 * @param   string  $prefix  The prefix of the table class
	 * @param   array   $config  Optional configuration variables
	 *
	 * @return FOFTable
	 */
	public static function &getAnInstance($type = null, $prefix = 'JTable', $config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array) $config;
		}
		elseif (!is_array($config))
		{
			$config = array();
		}

		// Guess the component name
		if (!array_key_exists('input', $config))
		{
			$config['input'] = new FOFInput;
		}

		if ($config['input'] instanceof FOFInput)
		{
			$tmpInput = $config['input'];
		}
		else
		{
			$tmpInput = new FOFInput($config['input']);
		}

		$option = $tmpInput->getCmd('option', '');
		$tmpInput->set('option', $option);
		$config['input'] = $tmpInput;

		if (!in_array($prefix, array('Table', 'JTable')))
		{
			preg_match('/(.*)Table$/', $prefix, $m);
			$option = 'com_' . strtolower($m[1]);
		}

		if (array_key_exists('option', $config))
		{
			$option = $config['option'];
		}

		$config['option'] = $option;

		if (!array_key_exists('view', $config))
		{
			$config['view'] = $config['input']->getCmd('view', 'cpanel');
		}

		if (is_null($type))
		{
			if ($prefix == 'JTable')
			{
				$prefix = 'Table';
			}

			$type = $config['view'];
		}

		$type       = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$tableClass = $prefix . ucfirst($type);

		$config['_table_type'] = $type;
		$config['_table_class'] = $tableClass;

		$configProvider = new FOFConfigProvider;
		$configProviderKey = $option . '.views.' . FOFInflector::singularize($type) . '.config.';

		if (!array_key_exists($tableClass, self::$instances))
		{
			if (!class_exists($tableClass))
			{
				$componentPaths = FOFPlatform::getInstance()->getComponentBaseDirs($config['option']);

				$searchPaths = array(
					$componentPaths['main'] . '/tables',
					$componentPaths['admin'] . '/tables'
				);

				if (array_key_exists('tablepath', $config))
				{
					array_unshift($searchPaths, $config['tablepath']);
				}

				$altPath = $configProvider->get($configProviderKey . 'table_path', null);

				if ($altPath)
				{
					array_unshift($searchPaths, $componentPaths['admin'] . '/' . $altPath);
				}

				JLoader::import('joomla.filesystem.path');
				$path = JPath::find(
					$searchPaths, strtolower($type) . '.php'
				);

				if ($path)
				{
					require_once $path;
				}
			}

			if (!class_exists($tableClass))
			{
				$tableClass = 'FOFTable';
			}

			$component = str_replace('com_', '', $config['option']);
			$tbl_common = $component . '_';

			if (!array_key_exists('tbl', $config))
			{
				$config['tbl'] = strtolower('#__' . $tbl_common . strtolower(FOFInflector::pluralize($type)));
			}

			$altTbl = $configProvider->get($configProviderKey . 'tbl', null);

			if ($altTbl)
			{
				$config['tbl'] = $altTbl;
			}

			if (!array_key_exists('tbl_key', $config))
			{
				$keyName           = FOFInflector::singularize($type);
				$config['tbl_key'] = strtolower($tbl_common . $keyName . '_id');
			}

			$altTblKey = $configProvider->get($configProviderKey . 'tbl_key', null);

			if ($altTblKey)
			{
				$config['tbl_key'] = $altTblKey;
			}

			if (!array_key_exists('db', $config))
			{
				$config['db'] = JFactory::getDBO();
			}

			// Assign the correct table alias
			if (array_key_exists('table_alias', $config))
			{
				$table_alias = $config['table_alias'];
			}
			else
			{
				$configProviderTableAliasKey = $option . '.tables.' . FOFInflector::singularize($type) . '.tablealias';
				$table_alias = $configProvider->get($configProviderTableAliasKey, false	);
			}

			// Can we use the FOF cache?
			if (!array_key_exists('use_table_cache', $config))
			{
				$config['use_table_cache'] = FOFPlatform::getInstance()->isGlobalFOFCacheEnabled();
			}

			$alt_use_table_cache = $configProvider->get($configProviderKey . 'use_table_cache', null);

			if (!is_null($alt_use_table_cache))
			{
				$config['use_table_cache'] = $alt_use_table_cache;
			}

			// Create a new table instance
			$instance = new $tableClass($config['tbl'], $config['tbl_key'], $config['db'], $config);
			$instance->setInput($tmpInput);
			$instance->setTablePrefix($prefix);
			$instance->setTableAlias($table_alias);

			// Determine and set the asset key for this table
			$assetKey = 'com_' . $component . '.' . strtolower(FOFInflector::singularize($type));
			$assetKey = $configProvider->get($configProviderKey . 'asset_key', $assetKey);
			$instance->setAssetKey($assetKey);

			if (array_key_exists('trigger_events', $config))
			{
				$instance->setTriggerEvents($config['trigger_events']);
			}

			if (version_compare(JVERSION, '3.1', 'ge'))
			{
				if (array_key_exists('has_tags', $config))
				{
					$instance->setHasTags($config['has_tags']);
				}

				$altHasTags = $configProvider->get($configProviderKey . 'has_tags', null);

				if ($altHasTags)
				{
					$instance->setHasTags($altHasTags);
				}
			}
			else
			{
				$instance->setHasTags(false);
			}

			$configProviderFieldmapKey = $option . '.tables.' . FOFInflector::singularize($type) . '.field';
			$aliases = $configProvider->get($configProviderFieldmapKey, $instance->_columnAlias);
			$instance->_columnAlias = array_merge($instance->_columnAlias, $aliases);

			self::$instances[$tableClass] = $instance;
		}

		return self::$instances[$tableClass];
	}

	/**
	 * Force an instance inside class cache. Setting arguments to null nukes all or part of the cache
	 *
	 * @param    string|null       $key        TableClass to replace. Set it to null to nuke the entire cache
	 * @param    FOFTable|null     $instance   Instance to replace. Set it to null to nuke $key instances
	 *
	 * @return   bool              Did I correctly switch the instance?
	 */
	public static function forceInstance($key = null, $instance = null)
	{
		if(is_null($key))
		{
			self::$instances = array();

			return true;
		}
		elseif($key && isset(self::$instances[$key]))
		{
			// I'm forcing an instance, but it's not a FOFTable, abort! abort!
			if(!$instance || ($instance && $instance instanceof FOFTable))
			{
				self::$instances[$key] = $instance;

				return true;
			}
		}

		return false;
	}

	/**
	 * Class Constructor.
	 *
	 * @param   string           $table   Name of the database table to model.
	 * @param   string           $key     Name of the primary key field in the table.
	 * @param   JDatabaseDriver  &$db     Database driver
	 * @param   array            $config  The configuration parameters array
	 */
	public function __construct($table, $key, &$db, $config = array())
	{
		$this->_tbl     = $table;
		$this->_tbl_key = $key;
		$this->_db      = $db;

		// Make sure the use FOF cache information is in the config
		if (!array_key_exists('use_table_cache', $config))
		{
			$config['use_table_cache'] = FOFPlatform::getInstance()->isGlobalFOFCacheEnabled();
		}
		$this->config   = $config;

		// Load the configuration provider
		$this->configProvider = new FOFConfigProvider;

		// Load the behavior dispatcher
		$this->tableDispatcher = new FOFTableDispatcherBehavior;

		// Initialise the table properties.

		if ($fields = $this->getTableFields())
		{
			// Do I have anything joined?
			$j_fields = $this->getQueryJoinFields();

			if ($j_fields)
			{
				$fields = array_merge($fields, $j_fields);
			}

			$this->setKnownFields(array_keys($fields), true);
			$this->reset();
		}
		else
		{
			$this->_tableExists = false;
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

		// Set the $name/$_name variable
		$component = $this->input->getCmd('option', 'com_foobar');

		if (array_key_exists('option', $config))
		{
			$component = $config['option'];
		}

		$this->input->set('option', $component);

		// Apply table behaviors
		$type = explode("_", $this->_tbl);
		$type = $type[count($type) - 1];

		$configKey = $component . '.tables.' . FOFInflector::singularize($type) . '.behaviors';

		if (isset($config['behaviors']))
		{
			$behaviors = (array) $config['behaviors'];
		}
		elseif ($behaviors = $this->configProvider->get($configKey, null))
		{
			$behaviors = explode(',', $behaviors);
		}
		else
		{
			$behaviors = $this->default_behaviors;
		}

		if (is_array($behaviors) && count($behaviors))
		{
			foreach ($behaviors as $behavior)
			{
				$this->addBehavior($behavior);
			}
		}

		// If we are tracking assets, make sure an access field exists and initially set the default.
		$asset_id_field	= $this->getColumnAlias('asset_id');
		$access_field	= $this->getColumnAlias('access');

		if (in_array($asset_id_field, $this->getKnownFields()))
		{
			JLoader::import('joomla.access.rules');
			$this->_trackAssets = true;
		}

		// If the acess property exists, set the default.
		if (in_array($access_field, $this->getKnownFields()))
		{
			$this->$access_field = (int) JFactory::getConfig()->get('access');
		}

		$this->config = $config;
	}

	/**
	 * Replace the entire known fields array
	 *
	 * @param   array    $fields      A simple array of known field names
	 * @param   boolean  $initialise  Should we initialise variables to null?
	 *
	 * @return  void
	 */
	public function setKnownFields($fields, $initialise = false)
	{
		$this->knownFields = $fields;

		if ($initialise)
		{
			foreach ($this->knownFields as $field)
			{
				$this->$field = null;
			}
		}
	}

	/**
	 * Get the known fields array
	 *
	 * @return  array
	 */
	public function getKnownFields()
	{
		return $this->knownFields;
	}

	/**
	 * Add a field to the known fields array
	 *
	 * @param   string   $field       The name of the field to add
	 * @param   boolean  $initialise  Should we initialise the variable to null?
	 *
	 * @return  void
	 */
	public function addKnownField($field, $initialise = false)
	{
		if (!in_array($field, $this->knownFields))
		{
			$this->knownFields[] = $field;

			if ($initialise)
			{
				$this->$field = null;
			}
		}
	}

	/**
	 * Remove a field from the known fields array
	 *
	 * @param   string  $field  The name of the field to remove
	 *
	 * @return  void
	 */
	public function removeKnownField($field)
	{
		if (in_array($field, $this->knownFields))
		{
			$pos = array_search($field, $this->knownFields);
			unset($this->knownFields[$pos]);
		}
	}

	/**
	 * Adds a behavior to the table
	 *
	 * @param   string  $name    The name of the behavior
	 * @param   array   $config  Optional Behavior configuration
	 *
	 * @return  boolean
	 */
	public function addBehavior($name, $config = array())
	{
		// First look for ComponentnameTableViewnameBehaviorName (e.g. FoobarTableItemsBehaviorTags)
		if (isset($this->config['option']))
		{
			$option_name = str_replace('com_', '', $this->config['option']);
			$behaviorClass = $this->config['_table_class'] . 'Behavior' . ucfirst(strtolower($name));

			if (class_exists($behaviorClass))
			{
				$behavior = new $behaviorClass($this->tableDispatcher, $config);

				return true;
			}

			// Then look for ComponentnameTableBehaviorName (e.g. FoobarTableBehaviorTags)
			$option_name = str_replace('com_', '', $this->config['option']);
			$behaviorClass = ucfirst($option_name) . 'TableBehavior' . ucfirst(strtolower($name));

			if (class_exists($behaviorClass))
			{
				$behavior = new $behaviorClass($this->tableDispatcher, $config);

				return true;
			}
		}

		// Nothing found? Return false.

		$behaviorClass = 'FOFTableBehavior' . ucfirst(strtolower($name));

		if (class_exists($behaviorClass) && $this->tableDispatcher)
		{
			$behavior = new $behaviorClass($this->tableDispatcher, $config);

			return true;
		}

		return false;
	}

	/**
	 * Sets the events trigger switch state
	 *
	 * @param   boolean  $newState  The new state of the switch (what else could it be?)
	 *
	 * @return  void
	 */
	public function setTriggerEvents($newState = false)
	{
		$this->_trigger_events = $newState ? true : false;
	}

	/**
	 * Gets the events trigger switch state
	 *
	 * @return  boolean
	 */
	public function getTriggerEvents()
	{
		return $this->_trigger_events;
	}

	/**
	 * Gets the has tags switch state
	 *
	 * @return bool
	 */
	public function hasTags()
	{
		return $this->_has_tags;
	}

	/**
	 * Sets the has tags switch state
	 *
	 * @param   bool  $newState
	 */
	public function setHasTags($newState = false)
	{
		$this->_has_tags = false;

		// Tags are available only in 3.1+
		if (version_compare(JVERSION, '3.1', 'ge'))
		{
			$this->_has_tags = $newState ? true : false;
		}
	}

	/**
	 * Set the class prefix
	 *
	 * @param string $prefix The prefix
	 */
	public function setTablePrefix($prefix)
	{
		$this->_tablePrefix = $prefix;
	}

	/**
	 * Sets fields to be skipped from automatic checks.
	 *
	 * @param   array/string  $skip  Fields to be skipped by automatic checks
	 *
	 * @return void
	 */
	public function setSkipChecks($skip)
	{
		$this->_skipChecks = (array) $skip;
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the FOFTable instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 *
	 * @throws  RuntimeException
	 * @throws  UnexpectedValueException
	 */
	public function load($keys = null, $reset = true)
	{
		if (!$this->_tableExists)
		{
			$result = false;

            return $this->onAfterLoad($result);
		}

		if (empty($keys))
		{
			// If empty, use the value of the current key
			$keyName = $this->_tbl_key;

			if (isset($this->$keyName))
			{
				$keyValue = $this->$keyName;
			}
			else
			{
				$keyValue = null;
			}

			// If empty primary key there's is no need to load anything

			if (empty($keyValue))
			{
				$result = true;

				return $this->onAfterLoad($result);
			}

			$keys = array($keyName => $keyValue);
		}
		elseif (!is_array($keys))
		{
			// Load by primary key.
			$keys = array($this->_tbl_key => $keys);
		}

		if ($reset)
		{
			$this->reset();
		}

		// Initialise the query.
		$query = $this->_db->getQuery(true);
		$query->select($this->_tbl . '.*');
		$query->from($this->_tbl);

		// Joined fields are ok, since I initialized them in the constructor
		$fields = $this->getKnownFields();

		foreach ($keys as $field => $value)
		{
			// Check that $field is in the table.

			if (!in_array($field, $fields))
			{
				throw new UnexpectedValueException(sprintf('Missing field in table %s : %s.', $this->_tbl, $field));
			}

			// Add the search tuple to the query.
			$query->where($this->_db->qn($this->_tbl . '.' . $field) . ' = ' . $this->_db->q($value));
		}

		// Do I have any joined table?
		$j_query = $this->getQueryJoin();

		if ($j_query)
		{
			if ($j_query->select && $j_query->select->getElements())
			{
				//$query->select($this->normalizeSelectFields($j_query->select->getElements(), true));
				$query->select($j_query->select->getElements());
			}

			if ($j_query->join)
			{
				foreach ($j_query->join as $join)
				{
					$t = (string) $join;

					// Joomla doesn't provide any access to the "name" variable, so I have to work with strings...
					if (stripos($t, 'inner') !== false)
					{
						$query->innerJoin($join->getElements());
					}
					elseif (stripos($t, 'left') !== false)
					{
						$query->leftJoin($join->getElements());
					}
					elseif (stripos($t, 'right') !== false)
					{
						$query->rightJoin($join->getElements());
					}
					elseif (stripos($t, 'outer') !== false)
					{
						$query->outerJoin($join->getElements());
					}
				}
			}
		}

		$this->_db->setQuery($query);

		$row = $this->_db->loadAssoc();

		// Check that we have a result.
		if (empty($row))
		{
			$result = true;

			return $this->onAfterLoad($result);
		}

		// Bind the object with the row and return.
		$result = $this->bind($row);

		$this->onAfterLoad($result);

		return $result;
	}

	/**
	 * Based on fields properties (nullable column), checks if the field is required or not
	 *
	 * @return boolean
	 */
	public function check()
	{
		if (!$this->_autoChecks)
		{
			return true;
		}

		$fields = $this->getTableFields();

        // No fields? Why in the hell am I here?
        if(!$fields)
        {
            return false;
        }

        $result       = true;
        $known        = $this->getKnownFields();
        $skipFields[] = $this->_tbl_key;

        if(in_array($this->getColumnAlias('hits'), $known))         $skipFields[] = $this->getColumnAlias('hits');
        if(in_array($this->getColumnAlias('created_on'), $known))   $skipFields[] = $this->getColumnAlias('created_on');
        if(in_array($this->getColumnAlias('created_by'), $known))   $skipFields[] = $this->getColumnAlias('created_by');
        if(in_array($this->getColumnAlias('modified_on'), $known))  $skipFields[] = $this->getColumnAlias('modified_on');
        if(in_array($this->getColumnAlias('modified_by'), $known))  $skipFields[] = $this->getColumnAlias('modified_by');
        if(in_array($this->getColumnAlias('locked_by'), $known))    $skipFields[] = $this->getColumnAlias('locked_by');
        if(in_array($this->getColumnAlias('locked_on'), $known))    $skipFields[] = $this->getColumnAlias('locked_on');

        // Let's merge it with custom skips
        $skipFields = array_merge($skipFields, $this->_skipChecks);

		foreach ($fields as $field)
		{
			$fieldName = $field->Field;

			if (empty($fieldName))
			{
				$fieldName = $field->column_name;
			}

			// Field is not nullable but it's null, set error

			if ($field->Null == 'NO' && $this->$fieldName == '' && !in_array($fieldName, $skipFields))
			{
				$text = str_replace('#__', 'COM_', $this->getTableName()) . '_ERR_' . $fieldName;
				$this->setError(JText::_(strtoupper($text)));
				$result = false;
			}
		}

		return $result;
	}

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties.
	 *
	 * @return void
	 */
	public function reset()
	{
		if (!$this->onBeforeReset())
		{
			return false;
		}

		// Get the default values for the class from the table.
		$fields   = $this->getTableFields();
		$j_fields = $this->getQueryJoinFields();

		if ($j_fields)
		{
			$fields = array_merge($fields, $j_fields);
		}

		foreach ($fields as $k => $v)
		{
			// If the property is not the primary key or private, reset it.

			if ($k != $this->_tbl_key && (strpos($k, '_') !== 0))
			{
				$this->$k = $v->Default;
			}
		}

		if (!$this->onAfterReset())
		{
			return false;
		}
	}

	/**
	 * Generic check for whether dependencies exist for this object in the db schema
	 *
	 * @param   integer  $oid    The primary key of the record to delete
	 * @param   array    $joins  Any joins to foreign table, used to determine if dependent records exist
	 *
	 * @return  boolean  True if the record can be deleted
	 */
	public function canDelete($oid = null, $joins = null)
	{
		$k = $this->_tbl_key;

		if ($oid)
		{
			$this->$k = intval($oid);
		}

		if (is_array($joins))
		{
			$db      = $this->_db;
			$query   = $db->getQuery(true)
				->select($db->qn('master') . '.' . $db->qn($k))
				->from($db->qn($this->_tbl) . ' AS ' . $db->qn('master'));
			$tableNo = 0;

			foreach ($joins as $table)
			{
				$tableNo++;
				$query->select(
					array(
						'COUNT(DISTINCT ' . $db->qn('t' . $tableNo) . '.' . $db->qn($table['idfield']) . ') AS ' . $db->qn($table['idalias'])
					)
				);
				$query->join('LEFT', $db->qn($table['name']) .
					' AS ' . $db->qn('t' . $tableNo) .
					' ON ' . $db->qn('t' . $tableNo) . '.' . $db->qn($table['joinfield']) .
					' = ' . $db->qn('master') . '.' . $db->qn($k)
				);
			}

			$query->where($db->qn('master') . '.' . $db->qn($k) . ' = ' . $db->q($this->$k));
			$query->group($db->qn('master') . '.' . $db->qn($k));
			$this->_db->setQuery((string) $query);

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				try
				{
					$obj = $this->_db->loadObject();
				}
				catch (JDatabaseException $e)
				{
					$this->setError($e->getMessage());
				}
			}
			else
			{
				if (!$obj = $this->_db->loadObject())
				{
					$this->setError($this->_db->getErrorMsg());

					return false;
				}
			}

			$msg = array();
			$i   = 0;

			foreach ($joins as $table)
			{
				$k = $table['idalias'];

				if ($obj->$k > 0)
				{
					$msg[] = JText::_($table['label']);
				}

				$i++;
			}

			if (count($msg))
			{
				$option  = $this->input->getCmd('option', 'com_foobar');
				$comName = str_replace('com_', '', $option);
				$tview   = str_replace('#__' . $comName . '_', '', $this->_tbl);
				$prefix  = $option . '_' . $tview . '_NODELETE_';

				foreach ($msg as $key)
				{
					$this->setError(JText::_($prefix . $key));
				}

				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * Method to bind an associative array or object to the FOFTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the FOFTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		if (!$this->onBeforeBind($src))
		{
			return false;
		}

		// If the source value is not an array or object return false.
		if (!is_object($src) && !is_array($src))
		{
			throw new InvalidArgumentException(sprintf('%s::bind(*%s*)', get_class($this), gettype($src)));
		}

		// If the source value is an object, get its accessible properties.
		if (is_object($src))
		{
			$src = get_object_vars($src);
		}

		// If the ignore value is a string, explode it over spaces.
		if (!is_array($ignore))
		{
			$ignore = explode(' ', $ignore);
		}

		// Bind the source value, excluding the ignored fields.
		foreach ($this->getKnownFields() as $k)
		{
			// Only process fields not in the ignore array.
			if (!in_array($k, $ignore))
			{
				if (isset($src[$k]))
				{
					$this->$k = $src[$k];
				}
			}
		}

		$result = $this->onAfterBind($src);

		return $result;
	}

	/**
	 * Method to store a row in the database from the FOFTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * FOFTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		if (!$this->onBeforeStore($updateNulls))
		{
			return false;
		}

		$k = $this->_tbl_key;

		if ($this->$k == 0)
		{
			$this->$k = null;
		}

		// Create the object used for inserting/udpating data to the database
		$fields     = $this->getTableFields();
		$properties = $this->getKnownFields();
		$keys       = array();

		foreach ($properties as $property)
		{
			// 'input' property is a reserved name

			if (isset($fields[$property]))
			{
				$keys[] = $property;
			}
		}

		$updateObject = array();
		foreach ($keys as $key)
		{
			$updateObject[$key] = $this->$key;
		}
		$updateObject = (object)$updateObject;

		// If a primary key exists update the object, otherwise insert it.
		if ($this->$k)
		{
			$result = $this->_db->updateObject($this->_tbl, $updateObject, $this->_tbl_key, $updateNulls);
		}
		else
		{
			$result = $this->_db->insertObject($this->_tbl, $updateObject, $this->_tbl_key);
		}

		if ($result !== true)
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$this->bind($updateObject);

		if ($this->_locked)
		{
			$this->_unlock();
		}

		$result = $this->onAfterStore();

		return $result;
	}

	/**
	 * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
	 * Negative numbers move the row up in the sequence and positive numbers move it down.
	 *
	 * @param   integer  $delta  The direction and magnitude to move the row in the ordering sequence.
	 * @param   string   $where  WHERE clause to use for limiting the selection of rows to compact the
	 *                           ordering values.
	 *
	 * @return  mixed    Boolean  True on success.
	 *
	 * @throws  UnexpectedValueException
	 */
	public function move($delta, $where = '')
	{
		if (!$this->onBeforeMove($delta, $where))
		{
			return false;
		}

		// If there is no ordering field set an error and return false.
		$ordering_field = $this->getColumnAlias('ordering');

		if (!in_array($ordering_field, $this->getKnownFields()))
		{
			throw new UnexpectedValueException(sprintf('%s does not support ordering.', $this->_tbl));
		}

		// If the change is none, do nothing.
		if (empty($delta))
		{
			$result = $this->onAfterMove();

			return $result;
		}

		$k     = $this->_tbl_key;
		$row   = null;
		$query = $this->_db->getQuery(true);

        // If the table is not loaded, return false
        if (empty($this->$k))
        {
            return false;
        }

		// Select the primary key and ordering values from the table.
		$query->select(array($this->_db->qn($this->_tbl_key), $this->_db->qn($ordering_field)));
		$query->from($this->_tbl);

		// If the movement delta is negative move the row up.

		if ($delta < 0)
		{
			$query->where($this->_db->qn($ordering_field) . ' < ' . $this->_db->q((int) $this->$ordering_field));
			$query->order($this->_db->qn($ordering_field) . ' DESC');
		}

		// If the movement delta is positive move the row down.

		elseif ($delta > 0)
		{
			$query->where($this->_db->qn($ordering_field) . ' > ' . $this->_db->q((int) $this->$ordering_field));
			$query->order($this->_db->qn($ordering_field) . ' ASC');
		}

		// Add the custom WHERE clause if set.

		if ($where)
		{
			$query->where($where);
		}

		// Select the first row with the criteria.
		$this->_db->setQuery($query, 0, 1);
		$row = $this->_db->loadObject();

		// If a row is found, move the item.

		if (!empty($row))
		{
			// Update the ordering field for this instance to the row's ordering value.
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set($this->_db->qn($ordering_field) . ' = ' . $this->_db->q((int) $row->$ordering_field));
			$query->where($this->_tbl_key . ' = ' . $this->_db->q($this->$k));
			$this->_db->setQuery($query);
			$this->_db->execute();

			// Update the ordering field for the row to this instance's ordering value.
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set($this->_db->qn($ordering_field) . ' = ' . $this->_db->q((int) $this->$ordering_field));
			$query->where($this->_tbl_key . ' = ' . $this->_db->q($row->$k));
			$this->_db->setQuery($query);
			$this->_db->execute();

			// Update the instance value.
			$this->$ordering_field = $row->$ordering_field;
		}
		else
		{
			// Update the ordering field for this instance.
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set($this->_db->qn($ordering_field) . ' = ' . $this->_db->q((int) $this->$ordering_field));
			$query->where($this->_tbl_key . ' = ' . $this->_db->q($this->$k));
			$this->_db->setQuery($query);
			$this->_db->execute();
		}

		$result = $this->onAfterMove();

		return $result;
	}

    /**
     * Change the ordering of the records of the table
     *
     * @param   string   $where  The WHERE clause of the SQL used to fetch the order
     *
     * @return  boolean  True is successful
     *
     * @throws  UnexpectedValueException
     */
	public function reorder($where = '')
	{
		if (!$this->onBeforeReorder($where))
		{
			return false;
		}

		// If there is no ordering field set an error and return false.

		$order_field = $this->getColumnAlias('ordering');

		if (!in_array($order_field, $this->getKnownFields()))
		{
			throw new UnexpectedValueException(sprintf('%s does not support ordering.', $this->_tbl_key));
		}

		$k = $this->_tbl_key;

		// Get the primary keys and ordering values for the selection.
		$query = $this->_db->getQuery(true);
		$query->select($this->_tbl_key . ', ' . $this->_db->qn($order_field));
		$query->from($this->_tbl);
		$query->where($this->_db->qn($order_field) . ' >= ' . $this->_db->q(0));
		$query->order($this->_db->qn($order_field));

		// Setup the extra where and ordering clause data.

		if ($where)
		{
			$query->where($where);
		}

		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();

		// Compact the ordering values.

		foreach ($rows as $i => $row)
		{
			// Make sure the ordering is a positive integer.

			if ($row->$order_field >= 0)
			{
				// Only update rows that are necessary.

				if ($row->$order_field != $i + 1)
				{
					// Update the row ordering field.
					$query = $this->_db->getQuery(true);
					$query->update($this->_tbl);
					$query->set($this->_db->qn($order_field) . ' = ' . $this->_db->q($i + 1));
					$query->where($this->_tbl_key . ' = ' . $this->_db->q($row->$k));
					$this->_db->setQuery($query);
					$this->_db->execute();
				}
			}
		}

		$result = $this->onAfterReorder();

		return $result;
	}

	/**
	 * Check out (lock) a record
	 *
	 * @param   integer  $userId  The locking user's ID
	 * @param   integer  $oid     The primary key value of the record to lock
	 *
	 * @return  boolean  True on success
	 */
	public function checkout($userId, $oid = null)
	{
		$fldLockedBy = $this->getColumnAlias('locked_by');
		$fldLockedOn = $this->getColumnAlias('locked_on');

		if (!(in_array($fldLockedBy, $this->getKnownFields())
			|| in_array($fldLockedOn, $this->getKnownFields())))
		{
			return true;
		}

		$k = $this->_tbl_key;

		if ($oid !== null)
		{
			$this->$k = $oid;
		}

        // No primary key defined, stop here
        if (!$this->$k)
        {
            return false;
        }

		$date = JFactory::getDate();
		$time = $date->toSql();

		$query = $this->_db->getQuery(true)
			->update($this->_db->qn($this->_tbl))
			->set(
				array(
					$this->_db->qn($fldLockedBy) . ' = ' . $this->_db->q((int) $userId),
					$this->_db->qn($fldLockedOn) . ' = ' . $this->_db->q($time)
				)
			)
			->where($this->_db->qn($this->_tbl_key) . ' = ' . $this->_db->q($this->$k));
		$this->_db->setQuery((string) $query);

		$this->$fldLockedBy = $userId;
		$this->$fldLockedOn = $time;

		return $this->_db->execute();
	}

	/**
	 * Check in (unlock) a record
	 *
	 * @param   integer  $oid  The primary key value of the record to unlock
	 *
	 * @return  boolean  True on success
	 */
	public function checkin($oid = null)
	{
		$fldLockedBy = $this->getColumnAlias('locked_by');
		$fldLockedOn = $this->getColumnAlias('locked_on');

		if (!(in_array($fldLockedBy, $this->getKnownFields())
			|| in_array($fldLockedOn, $this->getKnownFields())))
		{
			return true;
		}

		$k = $this->_tbl_key;

		if ($oid !== null)
		{
			$this->$k = $oid;
		}

		if ($this->$k == null)
		{
			return false;
		}

		$query = $this->_db->getQuery(true)
			->update($this->_db->qn($this->_tbl))
			->set(
				array(
					$this->_db->qn($fldLockedBy) . ' = 0',
					$this->_db->qn($fldLockedOn) . ' = ' . $this->_db->q($this->_db->getNullDate())
				)
			)
			->where($this->_db->qn($this->_tbl_key) . ' = ' . $this->_db->q($this->$k));
		$this->_db->setQuery((string) $query);

		$this->$fldLockedBy = 0;
		$this->$fldLockedOn = '';

		return $this->_db->execute();
	}

    /**
     * Is a record locked?
     *
     * @param   integer $with            The userid to preform the match with. If an item is checked
     *                                   out by this user the function will return false.
     * @param   integer $unused_against  Junk inherited from JTable; ignore
     *
     * @throws  UnexpectedValueException
     *
     * @return  boolean  True if the record is locked by another user
     */
	public function isCheckedOut($with = 0, $unused_against = null)
	{
        $against     = null;
		$fldLockedBy = $this->getColumnAlias('locked_by');

        $k  = $this->_tbl_key;

        // If no primary key is given, return false.

        if ($this->$k === null)
        {
            throw new UnexpectedValueException('Null primary key not allowed.');
        }

		if (isset($this) && is_a($this, 'FOFTable') && !$against)
		{
			$against = $this->get($fldLockedBy);
		}

		// Item is not checked out, or being checked out by the same user

		if (!$against || $against == $with)
		{
			return false;
		}

		$session = JTable::getInstance('session');

		return $session->exists($against);
	}

	/**
	 * Copy (duplicate) one or more records
	 *
	 * @param   integer|array  $cid  The primary key value (or values) or the record(s) to copy
	 *
	 * @return  boolean  True on success
	 */
	public function copy($cid = null)
	{
		//We have to cast the id as array, or the helper function will return an empty set
		if($cid)
		{
			$cid = (array) $cid;
		}

		JArrayHelper::toInteger($cid);
		$k = $this->_tbl_key;

		if (count($cid) < 1)
		{
			if ($this->$k)
			{
				$cid = array($this->$k);
			}
			else
			{
				$this->setError("No items selected.");

				return false;
			}
		}

		$created_by  = $this->getColumnAlias('created_by');
		$created_on  = $this->getColumnAlias('created_on');
		$modified_by = $this->getColumnAlias('modified_by');
		$modified_on = $this->getColumnAlias('modified_on');

		$locked_byName = $this->getColumnAlias('locked_by');
		$checkin       = in_array($locked_byName, $this->getKnownFields());

		foreach ($cid as $item)
		{
			// Prevent load with id = 0

			if (!$item)
			{
				continue;
			}

			$this->load($item);

			if ($checkin)
			{
				// We're using the checkin and the record is used by someone else

				if ($this->isCheckedOut($item))
				{
					continue;
				}
			}

			// TODO Should we notify the user that we had a problem with this record?
			if (!$this->onBeforeCopy($item))
			{
				continue;
			}

			$this->$k           = null;
			$this->$created_by  = null;
			$this->$created_on  = null;
			$this->$modified_on = null;
			$this->$modified_by = null;

			// Let's fire the event only if everything is ok
			// TODO Should we notify the user that we had a problem with this record?
			if ($this->store())
			{
				// TODO Should we notify the user that we had a problem with this record?
				$this->onAfterCopy($item);
			}

			$this->reset();
		}

		return true;
	}

	/**
	 * Publish or unpublish records
	 *
	 * @param   integer|array  $cid      The primary key value(s) of the item(s) to publish/unpublish
	 * @param   integer        $publish  1 to publish an item, 0 to unpublish
	 * @param   integer        $user_id  The user ID of the user (un)publishing the item.
	 *
	 * @return  boolean  True on success, false on failure (e.g. record is locked)
	 */
	public function publish($cid = null, $publish = 1, $user_id = 0)
	{
		$enabledName   = $this->getColumnAlias('enabled');
		$locked_byName = $this->getColumnAlias('locked_by');

		// Mhm... you called the publish method on a table without publish support...
		if(!in_array($enabledName, $this->getKnownFields()))
		{
			return false;
		}

		//We have to cast the id as array, or the helper function will return an empty set
		if($cid)
		{
			$cid = (array) $cid;
		}

		JArrayHelper::toInteger($cid);
		$user_id = (int) $user_id;
		$publish = (int) $publish;
		$k       = $this->_tbl_key;

		if (count($cid) < 1)
		{
			if ($this->$k)
			{
				$cid = array($this->$k);
			}
			else
			{
				$this->setError("No items selected.");

				return false;
			}
		}

		if (!$this->onBeforePublish($cid, $publish))
		{
			return false;
		}

		$query = $this->_db->getQuery(true)
			->update($this->_db->qn($this->_tbl))
			->set($this->_db->qn($enabledName) . ' = ' . (int) $publish);

		$checkin = in_array($locked_byName, $this->getKnownFields());

		if ($checkin)
		{
			$query->where(
				' (' . $this->_db->qn($locked_byName) .
					' = 0 OR ' . $this->_db->qn($locked_byName) . ' = ' . (int) $user_id . ')', 'AND'
			);
		}

		//Why this crazy statement?
		// TODO Rewrite this statment using IN. Check if it work in SQLServer and PostgreSQL
		$cids = $this->_db->qn($k) . ' = ' . implode(' OR ' . $this->_db->qn($k) . ' = ', $cid);

		$query->where('(' . $cids . ')');

		$this->_db->setQuery((string) $query);

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			try
			{
				$this->_db->execute();
			}
			catch (JDatabaseException $e)
			{
				$this->setError($e->getMessage());
			}
		}
		else
		{
			if (!$this->_db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
		}

		if (count($cid) == 1 && $checkin)
		{
			if ($this->_db->getAffectedRows() == 1)
			{
				// TODO should we check for its return value?
				$this->checkin($cid[0]);

				if ($this->$k == $cid[0])
				{
					$this->$enabledName = $publish;
				}
			}
		}

		$this->setError('');

		return true;
	}

	/**
	 * Delete a record
	 *
	 * @param   integer $oid  The primary key value of the item to delete
	 *
	 * @throws  UnexpectedValueException
	 *
	 * @return  boolean  True on success
	 */
	public function delete($oid = null)
	{
		if ($oid)
		{
			$this->load($oid);
		}

		$k  = $this->_tbl_key;
		$pk = (!$oid) ? $this->$k : $oid;

		// If no primary key is given, return false.
		if (!$pk)
		{
			throw new UnexpectedValueException('Null primary key not allowed.');
		}

		// Execute the logic only if I have a primary key, otherwise I could have weird results
		if (!$this->onBeforeDelete($oid))
		{
			return false;
		}

		// Delete the row by primary key.
		$query = $this->_db->getQuery(true);
		$query->delete();
		$query->from($this->_tbl);
		$query->where($this->_tbl_key . ' = ' . $this->_db->q($pk));
		$this->_db->setQuery($query);

		// @TODO Check for a database error.
		$this->_db->execute();

		$result = $this->onAfterDelete($oid);

		return $result;
	}

	/**
	 * Register a hit on a record
	 *
	 * @param   integer  $oid  The primary key value of the record
	 * @param   boolean  $log  Should I log the hit?
	 *
	 * @return  boolean  True on success
	 */
	public function hit($oid = null, $log = false)
	{
		if (!$this->onBeforeHit($oid, $log))
		{
			return false;
		}

		// If there is no hits field, just return true.
		$hits_field = $this->getColumnAlias('hits');

		if (!in_array($hits_field, $this->getKnownFields()))
		{
			return true;
		}

		$k  = $this->_tbl_key;
		$pk = ($oid) ? $oid : $this->$k;

		// If no primary key is given, return false.
		if (!$pk)
		{
			$result = false;
		}
		else
		{
			// Check the row in by primary key.
			$query = $this->_db->getQuery(true)
						  ->update($this->_tbl)
						  ->set($this->_db->qn($hits_field) . ' = (' . $this->_db->qn($hits_field) . ' + 1)')
						  ->where($this->_tbl_key . ' = ' . $this->_db->q($pk));

			$this->_db->setQuery($query)->execute();

			// In order to update the table object, I have to load the table
			if(!$this->$k)
			{
				$query = $this->_db->getQuery(true)
							  ->select($this->_db->qn($hits_field))
							  ->from($this->_db->qn($this->_tbl))
							  ->where($this->_db->qn($this->_tbl_key) . ' = ' . $this->_db->q($pk));

				$this->$hits_field = $this->_db->setQuery($query)->loadResult();
			}
			else
			{
				// Set table values in the object.
				$this->$hits_field++;
			}

			$result = true;
		}

		if ($result)
		{
			$result = $this->onAfterHit($oid);
		}

		return $result;
	}

	/**
	 * Export the item as a CSV line
	 *
	 * @param   string  $separator  CSV separator. Tip: use "\t" to get a TSV file instead.
	 *
	 * @return  string  The CSV line
	 */
	public function toCSV($separator = ',')
	{
		$csv = array();

		foreach (get_object_vars($this) as $k => $v)
		{
			if (!in_array($k, $this->getKnownFields()))
			{
				continue;
			}

			$csv[] = '"' . str_replace('"', '""', $v) . '"';
		}

		$csv = implode($separator, $csv);

		return $csv;
	}

	/**
	 * Exports the table in array format
	 *
	 * @return  array
	 */
	public function getData()
	{
		$ret = array();

		foreach (get_object_vars($this) as $k => $v)
		{
			if (!in_array($k, $this->getKnownFields()))
			{
				continue;
			}

			$ret[$k] = $v;
		}

		return $ret;
	}

	/**
	 * Get the header for exporting item list to CSV
	 *
	 * @param   string  $separator  CSV separator. Tip: use "\t" to get a TSV file instead.
	 *
	 * @return  string  The CSV file's header
	 */
	public function getCSVHeader($separator = ',')
	{
		$csv = array();

		foreach (get_object_vars($this) as $k => $v)
		{
			if (!in_array($k, $this->getKnownFields()))
			{
				continue;
			}

			$csv[] = '"' . str_replace('"', '\"', $k) . '"';
		}

		$csv = implode($separator, $csv);

		return $csv;
	}

	/**
	 * Get the columns from a database table.
	 *
	 * @param   string  $tableName  Table name. If null current table is used
	 *
	 * @return  mixed  An array of the field names, or false if an error occurs.
	 */
	public function getTableFields($tableName = null)
	{
		// Should I load the cached data?
		$useCache = array_key_exists('use_table_cache', $this->config) ? $this->config['use_table_cache'] : false;

		// Make sure we have a list of tables in this db

		if (empty(self::$tableCache))
		{
			if ($useCache)
			{
				// Try to load table cache from a cache file
				$cacheData = FOFPlatform::getInstance()->getCache('tables', null);

				// Unserialise the cached data, or set the table cache to empty
				// if the cache data wasn't loaded.
				if (!is_null($cacheData))
				{
					self::$tableCache = json_decode($cacheData, true);
				}
				else
				{
					self::$tableCache = array();
				}
			}

			// This check is true if the cache data doesn't exist / is not loaded
			if (empty(self::$tableCache))
			{
				self::$tableCache = $this->_db->getTableList();

				if ($useCache)
				{
					FOFPlatform::getInstance()->setCache('tables', json_encode(self::$tableCache));
				}
			}
		}

		// Make sure the cached table fields cache is loaded
		if (empty(self::$tableFieldCache))
		{
			if ($useCache)
			{
				// Try to load table cache from a cache file
				$cacheData = FOFPlatform::getInstance()->getCache('tablefields', null);

				// Unserialise the cached data, or set to empty if the cache
				// data wasn't loaded.
				if (!is_null($cacheData))
				{
					$decoded = json_decode($cacheData, true);
					$tableCache = array();

					if (count($decoded))
					{
						foreach ($decoded as $myTableName => $tableFields)
						{
							$temp = array();

							if (is_array($tableFields))
							{
								foreach($tableFields as $field => $def)
								{
									$temp[$field] = (object)$def;
								}
								$tableCache[$myTableName] = $temp;
							}
							elseif (is_object($tableFields) || is_bool($tableFields))
							{
								$tableCache[$myTableName] = $tableFields;
							}
						}
					}

					self::$tableFieldCache = $tableCache;
				}
				else
				{
					self::$tableFieldCache = array();
				}
			}
		}

		if (!$tableName)
		{
			$tableName = $this->_tbl;
		}

		// Try to load again column specifications if the table is not loaded OR if it's loaded and
		// the previous call returned an error
		if (!array_key_exists($tableName, self::$tableFieldCache) ||
			(isset(self::$tableFieldCache[$tableName]) && !self::$tableFieldCache[$tableName]))
		{
			// Lookup the fields for this table only once.
			$name = $tableName;

			$prefix = $this->_db->getPrefix();

			if (substr($name, 0, 3) == '#__')
			{
				$checkName = $prefix . substr($name, 3);
			}
			else
			{
				$checkName = $name;
			}

			if (!in_array($checkName, self::$tableCache))
			{
				// The table doesn't exist. Return false.
				self::$tableFieldCache[$tableName] = false;
			}
			elseif (version_compare(JVERSION, '3.0', 'ge'))
			{
				$fields = $this->_db->getTableColumns($name, false);

				if (empty($fields))
				{
					$fields = false;
				}

				self::$tableFieldCache[$tableName] = $fields;
			}
			else
			{
				$fields = $this->_db->getTableFields($name, false);

				if (!isset($fields[$name]))
				{
					$fields = false;
				}

				self::$tableFieldCache[$tableName] = $fields[$name];
			}

			// PostgreSQL date type compatibility
			if (($this->_db->name == 'postgresql') && (self::$tableFieldCache[$tableName] != false))
			{
				foreach (self::$tableFieldCache[$tableName] as $field)
				{
					if (strtolower($field->type) == 'timestamp without time zone')
					{
						if (stristr($field->Default, '\'::timestamp without time zone'))
						{
							list ($date, $junk) = explode('::', $field->Default, 2);
							$field->Default = trim($date, "'");
						}
					}
				}
			}

			// Save the data for this table into the cache
			if ($useCache)
			{
				$cacheData = FOFPlatform::getInstance()->setCache('tablefields', json_encode(self::$tableFieldCache));
			}
		}

		return self::$tableFieldCache[$tableName];
	}

	public function getTableAlias()
	{
		return $this->_tableAlias;
	}

	public function setTableAlias($string)
	{
		$string = preg_replace('#[^A-Z0-9_]#i', '', $string);
		$this->_tableAlias = $string;
	}

	/**
	 * Method to return the real name of a "special" column such as ordering, hits, published
	 * etc etc. In this way you are free to follow your db naming convention and use the
	 * built in Joomla functions.
	 *
	 * @param   string  $column  Name of the "special" column (ie ordering, hits etc etc)
	 *
	 * @return  string  The string that identify the special
	 */
	public function getColumnAlias($column)
	{
		if (isset($this->_columnAlias[$column]))
		{
			$return = $this->_columnAlias[$column];
		}
		else
		{
			$return = $column;
		}

		$return = preg_replace('#[^A-Z0-9_]#i', '', $return);

		return $return;
	}

	/**
	 * Method to register a column alias for a "special" column.
	 *
	 * @param   string  $column       The "special" column (ie ordering)
	 * @param   string  $columnAlias  The real column name (ie foo_ordering)
	 *
	 * @return  void
	 */
	public function setColumnAlias($column, $columnAlias)
	{
		$column = strtolower($column);

		$column                      = preg_replace('#[^A-Z0-9_]#i', '', $column);
		$this->_columnAlias[$column] = $columnAlias;
	}

	/**
	 * Get a JOIN query, used to join other tables
	 *
	 * @param   boolean  $asReference  Return an object reference instead of a copy
	 *
	 * @return  JDatabaseQuery  Query used to join other tables
	 */
	public function getQueryJoin($asReference = false)
	{
		if ($asReference)
		{
			return $this->_queryJoin;
		}
		else
		{
			if ($this->_queryJoin)
			{
				return clone $this->_queryJoin;
			}
			else
			{
				return null;
			}
		}
	}

	/**
	 * Sets the query with joins to other tables
	 *
	 * @param   JDatabaseQuery  $query  The JOIN query to use
	 *
	 * @return  void
	 */
	public function setQueryJoin(JDatabaseQuery $query)
	{
		$this->_queryJoin = $query;
	}

	/**
	 * Extracts the fields from the join query
	 *
	 * @return   array    Fields contained in the join query
	 */
	protected function getQueryJoinFields()
	{
		$query = $this->getQueryJoin();

		if (!$query)
		{
			return array();
		}

		$tables   = array();
		$j_tables = array();
		$j_fields = array();

		// Get joined tables. Ignore FROM clause, since it should not be used (the starting point is the table "table")
		$joins    = $query->join;

		foreach ($joins as $join)
		{
			$tables = array_merge($tables, $join->getElements());
		}

		// Clean up table names
		foreach($tables as $table)
		{
			preg_match('#(.*)((\w)*(on|using))(.*)#i', $table, $matches);

			if($matches && isset($matches[1]))
			{
				// I always want the first part, no matter what
				$parts = explode(' ', $matches[1]);
				$t_table = $parts[0];

				if($this->isQuoted($t_table))
				{
					$t_table = substr($t_table, 1, strlen($t_table) - 2);
				}

				if(!in_array($t_table, $j_tables))
				{
					$j_tables[] =  $t_table;
				}
			}
		}

		// Do I have the current table inside the query join? Remove it (its fields are already ok)
		$find = array_search($this->getTableName(), $j_tables);
		if($find !== false)
		{
			unset($j_tables[$find]);
		}

		// Get table fields
		$fields = array();

		foreach ($j_tables as $table)
		{
			$t_fields = $this->getTableFields($table);

			if ($t_fields)
			{
				$fields = array_merge($fields, $t_fields);
			}
		}

		// Remove any fields that aren't in the joined select
		$j_select = $query->select;

		if ($j_select && $j_select->getElements())
		{
			$j_fields = $this->normalizeSelectFields($j_select->getElements());
		}

		// I can intesect the keys
		$fields   = array_intersect_key($fields, $j_fields);

		// Now I walk again the array to change the key of columns that have an alias
		foreach ($j_fields as $column => $alias)
		{
			if ($column != $alias)
			{
				$fields[$alias] = $fields[$column];
				unset($fields[$column]);
			}
		}

		return $fields;
	}

	/**
	 * Normalizes the fields, returning an associative array with all the fields.
	 * Ie array('foobar as foo, bar') becomes array('foobar' => 'foo', 'bar' => 'bar')
	 *
	 * @param   array $fields    Array with column fields
	 *
	 * @return  array  Normalized array
	 */
	protected function normalizeSelectFields($fields)
	{
		$db     = JFactory::getDbo();
		$return = array();

		foreach ($fields as $field)
		{
			$t_fields = explode(',', $field);

			foreach ($t_fields as $t_field)
			{
				// Is there any alias?
				$parts  = preg_split('#\sas\s#i', $t_field);

				// Do I have a table.column situation? Let's get the field name
				$tableField  = explode('.', $parts[0]);

				if(isset($tableField[1]))
				{
					$column = trim($tableField[1]);
				}
				else
				{
					$column = trim($tableField[0]);
				}

				// Is this field quoted? If so, remove the quotes
				if($this->isQuoted($column))
				{
					$column = substr($column, 1, strlen($column) - 2);
				}

				if(isset($parts[1]))
				{
					$alias = trim($parts[1]);

					// Is this field quoted? If so, remove the quotes
					if($this->isQuoted($alias))
					{
						$alias = substr($alias, 1, strlen($alias) - 2);
					}
				}
				else
				{
					$alias = $column;
				}

				$return[$column] = $alias;
			}
		}

		return $return;
	}

	/**
	 * Is the field quoted?
	 *
	 * @param   string  $column     Column field
	 *
	 * @return  bool    Is the field quoted?
	 */
	protected function isQuoted($column)
	{
		// Empty string, un-quoted by definition
		if(!$column)
		{
			return false;
		}

		// I need some "magic". If the first char is not a letter, a number
		// an underscore or # (needed for table), then most likely the field is quoted
		preg_match_all('/^[a-z0-9_#]/i', $column, $matches);

		if(!$matches[0])
		{
			return true;
		}

		return false;
	}

	/**
	 * The event which runs before binding data to the table
	 *
	 * NOTE TO 3RD PARTY DEVELOPERS:
	 *
	 * When you override the following methods in your child classes,
	 * be sure to call parent::method *AFTER* your code, otherwise the
	 * plugin events do NOT get triggered
	 *
	 * Example:
	 * protected function onAfterStore(){
	 *       // Your code here
	 *     return parent::onAfterStore() && $your_result;
	 * }
	 *
	 * Do not do it the other way around, e.g. return $your_result && parent::onAfterStore()
	 * Due to  PHP short-circuit boolean evaluation the parent::onAfterStore()
	 * will not be called if $your_result is false.
	 *
	 * @param   object|array  &$from  The data to bind
	 *
	 * @return  boolean  True on success
	 */
	protected function onBeforeBind(&$from)
	{
		// Call the behaviors
		$result = $this->tableDispatcher->trigger('onBeforeBind', array(&$this, &$from));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onBeforeBind' . ucfirst($name), array(&$this, &$from));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs after loading a record from the database
	 *
	 * @param   boolean  &$result  Did the load succeeded?
	 *
	 * @return  void
	 */
	protected function onAfterLoad(&$result)
	{
		// Call the behaviors
		$eventRistult = $this->tableDispatcher->trigger('onAfterLoad', array(&$this, &$result));

		if (in_array(false, $eventRistult, true))
		{
			// Behavior failed, return false
			$result = false;
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			FOFPlatform::getInstance()->runPlugins('onAfterLoad' . ucfirst($name), array(&$this, &$result));
		}
	}

	/**
	 * The event which runs before storing (saving) data to the database
	 *
	 * @param   boolean  $updateNulls  Should nulls be saved as nulls (true) or just skipped over (false)?
	 *
	 * @return  boolean  True to allow saving
	 */
	protected function onBeforeStore($updateNulls)
	{
		// Do we have a "Created" set of fields?
		$created_on  = $this->getColumnAlias('created_on');
		$created_by  = $this->getColumnAlias('created_by');
		$modified_on = $this->getColumnAlias('modified_on');
		$modified_by = $this->getColumnAlias('modified_by');
		$locked_on   = $this->getColumnAlias('locked_on');
		$locked_by   = $this->getColumnAlias('locked_by');
		$title       = $this->getColumnAlias('title');
		$slug        = $this->getColumnAlias('slug');

		$hasCreatedOn = in_array($created_on, $this->getKnownFields());
		$hasCreatedBy = in_array($created_by, $this->getKnownFields());

		if ($hasCreatedOn && $hasCreatedBy)
		{
			$hasModifiedOn = in_array($modified_on, $this->getKnownFields());
			$hasModifiedBy = in_array($modified_by, $this->getKnownFields());

			$nullDate = $this->_db->getNullDate();

			if (empty($this->$created_by) || ($this->$created_on == $nullDate) || empty($this->$created_on))
			{
				$uid = FOFPlatform::getInstance()->getUser()->id;

				if ($uid)
				{
					$this->$created_by = FOFPlatform::getInstance()->getUser()->id;
				}
				JLoader::import('joomla.utilities.date');
				$date = new JDate();

				$this->$created_on = $date->toSql();
			}
			elseif ($hasModifiedOn && $hasModifiedBy)
			{
				$uid = FOFPlatform::getInstance()->getUser()->id;

				if ($uid)
				{
					$this->$modified_by = FOFPlatform::getInstance()->getUser()->id;
				}
				JLoader::import('joomla.utilities.date');
				$date = new JDate();

				$this->$modified_on = $date->toSql();
			}
		}

		// Do we have a set of title and slug fields?
		$hasTitle = in_array($title, $this->getKnownFields());
		$hasSlug  = in_array($slug, $this->getKnownFields());

		if ($hasTitle && $hasSlug)
		{
			if (empty($this->$slug))
			{
				// Create a slug from the title
				$this->$slug = FOFStringUtils::toSlug($this->$title);
			}
			else
			{
				// Filter the slug for invalid characters
				$this->$slug = FOFStringUtils::toSlug($this->$slug);
			}

			// Make sure we don't have a duplicate slug on this table
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select($db->qn($slug))
				->from($this->_tbl)
				->where($db->qn($slug) . ' = ' . $db->q($this->$slug))
				->where('NOT ' . $db->qn($this->_tbl_key) . ' = ' . $db->q($this->{$this->_tbl_key}));
			$db->setQuery($query);
			$existingItems = $db->loadAssocList();

			$count   = 0;
			$newSlug = $this->$slug;

			while (!empty($existingItems))
			{
				$count++;
				$newSlug = $this->$slug . '-' . $count;
				$query   = $db->getQuery(true)
					->select($db->qn($slug))
					->from($this->_tbl)
					->where($db->qn($slug) . ' = ' . $db->q($newSlug))
					->where('NOT '. $db->qn($this->_tbl_key) . ' = ' . $db->q($this->{$this->_tbl_key}));
				$db->setQuery($query);
				$existingItems = $db->loadAssocList();
			}

			$this->$slug = $newSlug;
		}

		// Call the behaviors
		$result = $this->tableDispatcher->trigger('onBeforeStore', array(&$this, $updateNulls));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		// Execute onBeforeStore<tablename> events in loaded plugins
		if ($this->_trigger_events)
		{
			$name       = FOFInflector::pluralize($this->getKeyName());
			$result     = FOFPlatform::getInstance()->runPlugins('onBeforeStore' . ucfirst($name), array(&$this, $updateNulls));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs after binding data to the class
	 *
	 * @param   object|array  &$src  The data to bind
	 *
	 * @return  boolean  True to allow binding without an error
	 */
	protected function onAfterBind(&$src)
	{
		// Call the behaviors
		$options = array(
			'component' 	=> $this->input->get('option'),
			'view'			=> $this->input->get('view'),
			'table_prefix'	=> $this->_tablePrefix
		);

		$result = $this->tableDispatcher->trigger('onAfterBind', array(&$this, &$src, $options));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onAfterBind' . ucfirst($name), array(&$this, &$src));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs after storing (saving) data to the database
	 *
	 * @return  boolean  True to allow saving without an error
	 */
	protected function onAfterStore()
	{
		// Call the behaviors
		$result = $this->tableDispatcher->trigger('onAfterStore', array(&$this));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onAfterStore' . ucfirst($name), array(&$this));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs before moving a record
	 *
	 * @param   boolean  $updateNulls  Should nulls be saved as nulls (true) or just skipped over (false)?
	 *
	 * @return  boolean  True to allow moving
	 */
	protected function onBeforeMove($updateNulls)
	{
		// Call the behaviors
		$result = $this->tableDispatcher->trigger('onBeforeMove', array(&$this, $updateNulls));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onBeforeMove' . ucfirst($name), array(&$this, $updateNulls));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs after moving a record
	 *
	 * @return  boolean  True to allow moving without an error
	 */
	protected function onAfterMove()
	{
		// Call the behaviors
		$result = $this->tableDispatcher->trigger('onAfterMove', array(&$this));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onAfterMove' . ucfirst($name), array(&$this));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs before reordering a table
	 *
	 * @param   string  $where  The WHERE clause of the SQL query to run on reordering (record filter)
	 *
	 * @return  boolean  True to allow reordering
	 */
	protected function onBeforeReorder($where = '')
	{
		// Call the behaviors
		$result = $this->tableDispatcher->trigger('onBeforeReorder', array(&$this, $where));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onBeforeReorder' . ucfirst($name), array(&$this, $where));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs after reordering a table
	 *
	 * @return  boolean  True to allow the reordering to complete without an error
	 */
	protected function onAfterReorder()
	{
		// Call the behaviors
		$result = $this->tableDispatcher->trigger('onAfterReorder', array(&$this));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onAfterReorder' . ucfirst($name), array(&$this));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs before deleting a record
	 *
	 * @param   integer  $oid  The PK value of the record to delete
	 *
	 * @return  boolean  True to allow the deletion
	 */
	protected function onBeforeDelete($oid)
	{
		// Call the behaviors
		$result = $this->tableDispatcher->trigger('onBeforeDelete', array(&$this, $oid));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onBeforeDelete' . ucfirst($name), array(&$this, $oid));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs after deleting a record
	 *
	 * @param   integer  $oid  The PK value of the record which was deleted
	 *
	 * @return  boolean  True to allow the deletion without errors
	 */
	protected function onAfterDelete($oid)
	{
		// Call the behaviors
		$result = $this->tableDispatcher->trigger('onAfterDelete', array(&$this, $oid));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onAfterDelete' . ucfirst($name), array(&$this, $oid));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs before hitting a record
	 *
	 * @param   integer  $oid  The PK value of the record to hit
	 * @param   boolean  $log  Should we log the hit?
	 *
	 * @return  boolean  True to allow the hit
	 */
	protected function onBeforeHit($oid, $log)
	{
		// Call the behaviors
		$result = $this->tableDispatcher->trigger('onBeforeHit', array(&$this, $oid, $log));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onBeforeHit' . ucfirst($name), array(&$this, $oid, $log));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs after hitting a record
	 *
	 * @param   integer  $oid  The PK value of the record which was hit
	 *
	 * @return  boolean  True to allow the hitting without errors
	 */
	protected function onAfterHit($oid)
	{
		// Call the behaviors
		$result = $this->tableDispatcher->trigger('onAfterHit', array(&$this, $oid));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onAfterHit' . ucfirst($name), array(&$this, $oid));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The even which runs before copying a record
	 *
	 * @param   integer  $oid  The PK value of the record being copied
	 *
	 * @return  boolean  True to allow the copy to take place
	 */
	protected function onBeforeCopy($oid)
	{
		// Call the behaviors
		$result = $this->tableDispatcher->trigger('onBeforeCopy', array(&$this, $oid));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onBeforeCopy' . ucfirst($name), array(&$this, $oid));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The even which runs after copying a record
	 *
	 * @param   integer  $oid  The PK value of the record which was copied (not the new one)
	 *
	 * @return  boolean  True to allow the copy without errors
	 */
	protected function onAfterCopy($oid)
	{
		// Call the behaviors
		$result = $this->tableDispatcher->trigger('onAfterCopy', array(&$this, $oid));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onAfterCopy' . ucfirst($name), array(&$this, $oid));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs before a record is (un)published
	 *
	 * @param   integer|array  &$cid     The PK IDs of the records being (un)published
	 * @param   integer        $publish  1 to publish, 0 to unpublish
	 *
	 * @return  boolean  True to allow the (un)publish to proceed
	 */
	protected function onBeforePublish(&$cid, $publish)
	{
		// Call the behaviors
		$result = $this->tableDispatcher->trigger('onBeforePublish', array(&$this, &$cid, $publish));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onBeforePublish' . ucfirst($name), array(&$this, &$cid, $publish));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs after the object is reset to its default values.
	 *
	 * @return  boolean  True to allow the reset to complete without errors
	 */
	protected function onAfterReset()
	{
		// Call the behaviors
		$result = $this->tableDispatcher->trigger('onAfterReset', array(&$this));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onAfterReset' . ucfirst($name), array(&$this));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The even which runs before the object is reset to its default values.
	 *
	 * @return  boolean  True to allow the reset to complete
	 */
	protected function onBeforeReset()
	{
		// Call the behaviors
		$result = $this->tableDispatcher->trigger('onBeforeReset', array(&$this));

		if (in_array(false, $result, true))
		{
			// Behavior failed, return false
			return false;
		}

		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onBeforeReset' . ucfirst($name), array(&$this));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * Replace the input object of this table with the provided FOFInput object
	 *
	 * @param   FOFInput  $input  The new input object
	 *
	 * @return  void
	 */
	public function setInput(FOFInput $input)
	{
		$this->input = $input;
	}

	/**
	 * Get the columns from database table.
	 *
	 * @return  mixed  An array of the field names, or false if an error occurs.
	 *
	 * @deprecated  2.1
	 */
	public function getFields()
	{
		return $this->getTableFields();
	}

	/**
	 * Add a filesystem path where FOFTable should search for table class files.
	 * You may either pass a string or an array of paths.
	 *
	 * @param   mixed  $path  A filesystem path or array of filesystem paths to add.
	 *
	 * @return  array  An array of filesystem paths to find FOFTable classes in.
	 */
	public static function addIncludePath($path = null)
	{
		// If the internal paths have not been initialised, do so with the base table path.
		if (empty(self::$_includePaths))
		{
			self::$_includePaths = array(__DIR__);
		}

		// Convert the passed path(s) to add to an array.
		settype($path, 'array');

		// If we have new paths to add, do so.
		if (!empty($path) && !in_array($path, self::$_includePaths))
		{
			// Check and add each individual new path.
			foreach ($path as $dir)
			{
				// Sanitize path.
				$dir = trim($dir);

				// Add to the front of the list so that custom paths are searched first.
				array_unshift(self::$_includePaths, $dir);
			}
		}

		return self::$_includePaths;
	}

	/**
	 * Loads the asset table related to this table.
	 * This will help tests, too, since we can mock this function.
	 *
	 * @return bool|JTableAsset     False on failure, otherwise JTableAsset
	 */
	protected function getAsset()
	{
		$name     = $this->_getAssetName();

		// Do NOT touch JTable here -- we are loading the core asset table which is a JTable, not a FOFTable
		$asset    = JTable::getInstance('Asset');

		if (!$asset->loadByName($name))
		{
			return false;
		}

		return $asset;
	}

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form table_name.id
     * where id is the value of the primary key of the table.
     *
     * @throws  UnexpectedValueException
     *
     * @return  string
     */
	public function getAssetName()
	{
		$k = $this->_tbl_key;

        // If there is no assetKey defined, stop here, or we'll get a wrong name
        if(!$this->_assetKey || !$this->$k)
        {
            throw new UnexpectedValueException('Table must have an asset key defined and a value for the table id in order to track assets');
        }

		return $this->_assetKey . '.' . (int) $this->$k;
	}

	/**
     * Method to compute the default name of the asset.
     * The default name is in the form table_name.id
     * where id is the value of the primary key of the table.
     *
     * @throws  UnexpectedValueException
     *
     * @return  string
     */
	public function getAssetKey()
	{
		return $this->_assetKey;
	}

	/**
	 * Method to return the title to use for the asset table.  In
	 * tracking the assets a title is kept for each asset so that there is some
	 * context available in a unified access manager.  Usually this would just
	 * return $this->title or $this->name or whatever is being used for the
	 * primary name of the row. If this method is not overridden, the asset name is used.
	 *
	 * @return  string  The string to use as the title in the asset table.
	 */
	public function getAssetTitle()
	{
		return $this->getAssetName();
	}

	/**
	 * Method to get the parent asset under which to register this one.
	 * By default, all assets are registered to the ROOT node with ID,
	 * which will default to 1 if none exists.
	 * The extended class can define a table and id to lookup.  If the
	 * asset does not exist it will be created.
	 *
	 * @param   FOFTable  $table  A FOFTable object for the asset parent.
	 * @param   integer   $id     Id to look up
	 *
	 * @return  integer
	 */
	public function getAssetParentId($table = null, $id = null)
	{
		// For simple cases, parent to the asset root.
		$assets = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));
		$rootId = $assets->getRootId();

		if (!empty($rootId))
		{
			return $rootId;
		}

		return 1;
	}

	/**
	 * This method sets the asset key for the items of this table. Obviously, it
	 * is only meant to be used when you have a table with an asset field.
	 *
	 * @param   string  $assetKey  The name of the asset key to use
	 *
	 * @return  void
	 */
	public function setAssetKey($assetKey)
	{
		$this->_assetKey = $assetKey;
	}

	/**
	 * Method to get the database table name for the class.
	 *
	 * @return  string  The name of the database table being modeled.
	 */
	public function getTableName()
	{
		return $this->_tbl;
	}

	/**
	 * Method to get the primary key field name for the table.
	 *
	 * @return  string  The name of the primary key for the table.
	 */
	public function getKeyName()
	{
		return $this->_tbl_key;
	}

	/**
	 * Method to get the JDatabaseDriver object.
	 *
	 * @return  JDatabaseDriver  The internal database driver object.
	 */
	public function getDbo()
	{
		return $this->_db;
	}

	/**
	 * Method to set the JDatabaseDriver object.
	 *
	 * @param   JDatabaseDriver  $db  A JDatabaseDriver object to be used by the table object.
	 *
	 * @return  boolean  True on success.
	 */
	public function setDBO(JDatabaseDriver $db)
	{
		$this->_db = $db;

		return true;
	}

	/**
	 * Method to set rules for the record.
	 *
	 * @param   mixed  $input  A JAccessRules object, JSON string, or array.
	 *
	 * @return  void
	 */
	public function setRules($input)
	{
		if ($input instanceof JAccessRules)
		{
			$this->_rules = $input;
		}
		else
		{
			$this->_rules = new JAccessRules($input);
		}
	}

	/**
	 * Method to get the rules for the record.
	 *
	 * @return  JAccessRules object
	 */
	public function getRules()
	{
		return $this->_rules;
	}

	/**
	 * Method to check if the record is treated as an ACL asset
	 *
	 * @return  boolean [description]
	 */
	public function isAssetsTracked()
	{
		return $this->_trackAssets;
	}

    /**
     * Method to manually set this record as ACL asset or not.
     * We have to do this since the automatic check is made in the constructor, but here we can't set any alias.
     * So, even if you have an alias for `asset_id`, it wouldn't be reconized and assets won't be tracked.
     *
     * @param $state
     */
    public function setAssetsTracked($state)
    {
        $state = (bool) $state;

        if($state)
        {
            JLoader::import('joomla.access.rules');
        }

        $this->_trackAssets = $state;
    }

	/**
	 * Method to provide a shortcut to binding, checking and storing a FOFTable
	 * instance to the database table.  The method will check a row in once the
	 * data has been stored and if an ordering filter is present will attempt to
	 * reorder the table rows based on the filter.  The ordering filter is an instance
	 * property name.  The rows that will be reordered are those whose value matches
	 * the FOFTable instance for the property specified.
	 *
	 * @param   mixed   $src             An associative array or object to bind to the FOFTable instance.
	 * @param   string  $orderingFilter  Filter for the order updating
	 * @param   mixed   $ignore          An optional array or space separated list of properties
	 *                                   to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 */
	public function save($src, $orderingFilter = '', $ignore = '')
	{
		// Attempt to bind the source to the instance.
		if (!$this->bind($src, $ignore))
		{
			return false;
		}

		// Run any sanity checks on the instance and verify that it is ready for storage.
		if (!$this->check())
		{
			return false;
		}

		// Attempt to store the properties to the database table.
		if (!$this->store())
		{
			return false;
		}

		// Attempt to check the row in, just in case it was checked out.
		if (!$this->checkin())
		{
			return false;
		}

		// If an ordering filter is set, attempt reorder the rows in the table based on the filter and value.
		if ($orderingFilter)
		{
			$filterValue = $this->$orderingFilter;
			$this->reorder($orderingFilter ? $this->_db->qn($orderingFilter) . ' = ' . $this->_db->q($filterValue) : '');
		}

		// Set the error to empty and return true.
		$this->setError('');

		return true;
	}

	/**
	 * Method to get the next ordering value for a group of rows defined by an SQL WHERE clause.
	 * This is useful for placing a new item last in a group of items in the table.
	 *
	 * @param   string  $where  WHERE clause to use for selecting the MAX(ordering) for the table.
	 *
	 * @return  mixed  Boolean false an failure or the next ordering value as an integer.
	 */
	public function getNextOrder($where = '')
	{
		// If there is no ordering field set an error and return false.
		$ordering = $this->getColumnAlias('ordering');
		if (!in_array($ordering, $this->getKnownFields()))
		{
			throw new UnexpectedValueException(sprintf('%s does not support ordering.', get_class($this)));
		}

		// Get the largest ordering value for a given where clause.
		$query = $this->_db->getQuery(true);
		$query->select('MAX('.$this->_db->qn($ordering).')');
		$query->from($this->_tbl);

		if ($where)
		{
			$query->where($where);
		}

		$this->_db->setQuery($query);
		$max = (int) $this->_db->loadResult();

		// Return the largest ordering value + 1.
		return ($max + 1);
	}

	/**
	 * Method to lock the database table for writing.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  RuntimeException
	 */
	protected function _lock()
	{
		$this->_db->lockTable($this->_tbl);
		$this->_locked = true;

		return true;
	}

	/**
	 * Method to unlock the database table for writing.
	 *
	 * @return  boolean  True on success.
	 */
	protected function _unlock()
	{
		$this->_db->unlockTables();
		$this->_locked = false;

		return true;
	}

	public function setConfig(array $config)
	{
		$this->config = $config;
	}

	/**
	 * Get the content type for ucm
	 *
	 * @return string The content type alias
	 */
	public function getContentType()
	{
		$component = $this->input->get('option');

		$view = FOFInflector::singularize($this->input->get('view'));
		$alias = $component . '.' . $view;

		return $alias;
	}
}
