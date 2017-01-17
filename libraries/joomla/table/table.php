<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.path');

/**
 * Abstract Table class
 *
 * Parent class to all tables.
 *
 * @since  11.1
 * @tutorial  Joomla.Platform/jtable.cls
 */
abstract class JTable extends JObject implements JObservableInterface, JTableInterface
{
	/**
	 * Include paths for searching for JTable classes.
	 *
	 * @var    array
	 * @since  12.1
	 */
	private static $_includePaths = array();

	/**
	 * Name of the database table to model.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl = '';

	/**
	 * Name of the primary key field in the table.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl_key = '';

	/**
	 * Name of the primary key fields in the table.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $_tbl_keys = array();

	/**
	 * JDatabaseDriver object.
	 *
	 * @var    JDatabaseDriver
	 * @since  11.1
	 */
	protected $_db;

	/**
	 * Should rows be tracked as ACL assets?
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $_trackAssets = false;

	/**
	 * The rules associated with this record.
	 *
	 * @var    JAccessRules  A JAccessRules object.
	 * @since  11.1
	 */
	protected $_rules;

	/**
	 * Indicator that the tables have been locked.
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $_locked = false;

	/**
	 * Indicates that the primary keys autoincrement.
	 *
	 * @var    boolean
	 * @since  12.3
	 */
	protected $_autoincrement = true;

	/**
	 * Generic observers for this JTable (Used e.g. for tags Processing)
	 *
	 * @var    JObserverUpdater
	 * @since  3.1.2
	 */
	protected $_observers;

	/**
	 * Array with alias for "special" columns such as ordering, hits etc etc
	 *
	 * @var    array
	 */
	protected $_columnAlias = array();

	/**
	 * An array of key names to be json encoded in the bind function
	 *
	 * @var    array
	 * @since  3.3
	 */
	protected $_jsonEncode = array();

	/**
	 * Object constructor to set table and key fields.  In most cases this will
	 * be overridden by child classes to explicitly set the table and key fields
	 * for a particular database table.
	 *
	 * @param   string           $table  Name of the table to model.
	 * @param   mixed            $key    Name of the primary key field in the table or array of field names that compose the primary key.
	 * @param   JDatabaseDriver  $db     JDatabaseDriver object.
	 *
	 * @since   11.1
	 */
	public function __construct($table, $key, $db)
	{
		// Set internal variables.
		$this->_tbl = $table;

		// Set the key to be an array.
		if (is_string($key))
		{
			$key = array($key);
		}
		elseif (is_object($key))
		{
			$key = (array) $key;
		}

		$this->_tbl_keys = $key;

		if (count($key) == 1)
		{
			$this->_autoincrement = true;
		}
		else
		{
			$this->_autoincrement = false;
		}

		// Set the singular table key for backwards compatibility.
		$this->_tbl_key = $this->getKeyName();

		$this->_db = $db;

		// Initialise the table properties.
		$fields = $this->getFields();

		if ($fields)
		{
			foreach ($fields as $name => $v)
			{
				// Add the field if it is not already present.
				if (!property_exists($this, $name))
				{
					$this->$name = null;
				}
			}
		}

		// If we are tracking assets, make sure an access field exists and initially set the default.
		if (property_exists($this, 'asset_id'))
		{
			$this->_trackAssets = true;
		}

		// If the access property exists, set the default.
		if (property_exists($this, 'access'))
		{
			$this->access = (int) JFactory::getConfig()->get('access');
		}

		// Implement JObservableInterface:
		// Create observer updater and attaches all observers interested by $this class:
		$this->_observers = new JObserverUpdater($this);
		JObserverMapper::attachAllObservers($this);
	}

	/**
	 * Implement JObservableInterface:
	 * Adds an observer to this instance.
	 * This method will be called fron the constructor of classes implementing JObserverInterface
	 * which is instanciated by the constructor of $this with JObserverMapper::attachAllObservers($this)
	 *
	 * @param   JObserverInterface|JTableObserver  $observer  The observer object
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function attachObserver(JObserverInterface $observer)
	{
		$this->_observers->attachObserver($observer);
	}

	/**
	 * Gets the instance of the observer of class $observerClass
	 *
	 * @param   string  $observerClass  The observer class-name to return the object of
	 *
	 * @return  JTableObserver|null
	 *
	 * @since   3.1.2
	 */
	public function getObserverOfClass($observerClass)
	{
		return $this->_observers->getObserverOfClass($observerClass);
	}

	/**
	 * Get the columns from database table.
	 *
	 * @param   bool  $reload  flag to reload cache
	 *
	 * @return  mixed  An array of the field names, or false if an error occurs.
	 *
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 */
	public function getFields($reload = false)
	{
		static $cache = null;

		if ($cache === null || $reload)
		{
			// Lookup the fields for this table only once.
			$name   = $this->_tbl;
			$fields = $this->_db->getTableColumns($name, false);

			if (empty($fields))
			{
				throw new UnexpectedValueException(sprintf('No columns found for %s table', $name));
			}

			$cache = $fields;
		}

		return $cache;
	}

	/**
	 * Static method to get an instance of a JTable class if it can be found in the table include paths.
	 *
	 * To add include paths for searching for JTable classes see JTable::addIncludePath().
	 *
	 * @param   string  $type    The type (name) of the JTable class to get an instance of.
	 * @param   string  $prefix  An optional prefix for the table class name.
	 * @param   array   $config  An optional array of configuration values for the JTable object.
	 *
	 * @return  JTable|boolean   A JTable object if found or boolean false on failure.
	 *
	 * @since   11.1
	 */
	public static function getInstance($type, $prefix = 'JTable', $config = array())
	{
		// Sanitize and prepare the table class name.
		$type       = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$tableClass = $prefix . ucfirst($type);

		// Only try to load the class if it doesn't already exist.
		if (!class_exists($tableClass))
		{
			// Search for the class file in the JTable include paths.
			jimport('joomla.filesystem.path');

			$paths = self::addIncludePath();
			$pathIndex = 0;

			while (!class_exists($tableClass) && $pathIndex < count($paths))
			{
				if ($tryThis = JPath::find($paths[$pathIndex++], strtolower($type) . '.php'))
				{
					// Import the class file.
					JLoader::register($tableClass, $tryThis);
				}
			}

			if (!class_exists($tableClass))
			{
				/*
				* If unable to find the class file in the JTable include paths. Return false.
				* The warning JLIB_DATABASE_ERROR_NOT_SUPPORTED_FILE_NOT_FOUND has been removed in 3.6.3.
				* In 4.0 an Exception (type to be determined) will be thrown.
				* For more info see https://github.com/joomla/joomla-cms/issues/11570
				*/

				return false;
			}
		}

		// If a database object was passed in the configuration array use it, otherwise get the global one from JFactory.
		$db = isset($config['dbo']) ? $config['dbo'] : JFactory::getDbo();

		// Instantiate a new table class and return it.
		return new $tableClass($db);
	}

	/**
	 * Add a filesystem path where JTable should search for table class files.
	 *
	 * @param   array|string  $path  A filesystem path or array of filesystem paths to add.
	 *
	 * @return  array  An array of filesystem paths to find JTable classes in.
	 *
	 * @since   11.1
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
		if (!empty($path))
		{
			// Check and add each individual new path.
			foreach ($path as $dir)
			{
				// Sanitize path.
				$dir = trim($dir);

				// Add to the front of the list so that custom paths are searched first.
				if (!in_array($dir, self::$_includePaths))
				{
					array_unshift(self::$_includePaths, $dir);
				}
			}
		}

		return self::$_includePaths;
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	protected function _getAssetName()
	{
		$keys = array();

		foreach ($this->_tbl_keys as $k)
		{
			$keys[] = (int) $this->$k;
		}

		return $this->_tbl . '.' . implode('.', $keys);
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * In tracking the assets a title is kept for each asset so that there is some context available in a unified access manager.
	 * Usually this would just return $this->title or $this->name or whatever is being used for the primary name of the row.
	 * If this method is not overridden, the asset name is used.
	 *
	 * @return  string  The string to use as the title in the asset table.
	 *
	 * @since   11.1
	 */
	protected function _getAssetTitle()
	{
		return $this->_getAssetName();
	}

	/**
	 * Method to get the parent asset under which to register this one.
	 *
	 * By default, all assets are registered to the ROOT node with ID, which will default to 1 if none exists.
	 * An extended class can define a table and ID to lookup.  If the asset does not exist it will be created.
	 *
	 * @param   JTable   $table  A JTable object for the asset parent.
	 * @param   integer  $id     Id to look up
	 *
	 * @return  integer
	 *
	 * @since   11.1
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		// For simple cases, parent to the asset root.
		$assets = self::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));
		$rootId = $assets->getRootId();

		if (!empty($rootId))
		{
			return $rootId;
		}

		return 1;
	}

	/**
	 * Method to append the primary keys for this table to a query.
	 *
	 * @param   JDatabaseQuery  $query  A query object to append.
	 * @param   mixed           $pk     Optional primary key parameter.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function appendPrimaryKeys($query, $pk = null)
	{
		if (is_null($pk))
		{
			foreach ($this->_tbl_keys as $k)
			{
				$query->where($this->_db->quoteName($k) . ' = ' . $this->_db->quote($this->$k));
			}
		}
		else
		{
			if (is_string($pk))
			{
				$pk = array($this->_tbl_key => $pk);
			}

			$pk = (object) $pk;

			foreach ($this->_tbl_keys as $k)
			{
				$query->where($this->_db->quoteName($k) . ' = ' . $this->_db->quote($pk->$k));
			}
		}
	}

	/**
	 * Method to get the database table name for the class.
	 *
	 * @return  string  The name of the database table being modeled.
	 *
	 * @since   11.1
	 */
	public function getTableName()
	{
		return $this->_tbl;
	}

	/**
	 * Method to get the primary key field name for the table.
	 *
	 * @param   boolean  $multiple  True to return all primary keys (as an array) or false to return just the first one (as a string).
	 *
	 * @return  mixed  Array of primary key field names or string containing the first primary key field.
	 *
	 * @since   11.1
	 */
	public function getKeyName($multiple = false)
	{
		// Count the number of keys
		if (count($this->_tbl_keys))
		{
			if ($multiple)
			{
				// If we want multiple keys, return the raw array.
				return $this->_tbl_keys;
			}
			else
			{
				// If we want the standard method, just return the first key.
				return $this->_tbl_keys[0];
			}
		}

		return '';
	}

	/**
	 * Method to get the JDatabaseDriver object.
	 *
	 * @return  JDatabaseDriver  The internal database driver object.
	 *
	 * @since   11.1
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
	 *
	 * @since   11.1
	 */
	public function setDbo($db)
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
	 *
	 * @since   11.1
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
	 *
	 * @since   11.1
	 */
	public function getRules()
	{
		return $this->_rules;
	}

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties (except $_errors).
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function reset()
	{
		// Get the default values for the class from the table.
		foreach ($this->getFields() as $k => $v)
		{
			// If the property is not the primary key or private, reset it.
			if (!in_array($k, $this->_tbl_keys) && (strpos($k, '_') !== 0))
			{
				$this->$k = $v->Default;
			}
		}

		// Reset table errors
		$this->_errors = array();
	}

	/**
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   array|object  $src     An associative array or object to bind to the JTable instance.
	 * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		// JSON encode any fields required
		if (!empty($this->_jsonEncode))
		{
			foreach ($this->_jsonEncode as $field)
			{
				if (isset($src[$field]) && is_array($src[$field]))
				{
					$src[$field] = json_encode($src[$field]);
				}
			}
		}

		// Check if the source value is an array or object
		if (!is_object($src) && !is_array($src))
		{
			throw new InvalidArgumentException(
				sprintf(
					'Could not bind the data source in %1$s::bind(), the source must be an array or object but a "%2$s" was given.',
					get_class($this),
					gettype($src)
				)
			);
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
		foreach ($this->getProperties() as $k => $v)
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

		return true;
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields to the JTable instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.
	 *                           If not set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 *
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 * @throws  RuntimeException
	 * @throws  UnexpectedValueException
	 */
	public function load($keys = null, $reset = true)
	{
		// Implement JObservableInterface: Pre-processing by observers
		$this->_observers->update('onBeforeLoad', array($keys, $reset));

		if (empty($keys))
		{
			$empty = true;
			$keys  = array();

			// If empty, use the value of the current key
			foreach ($this->_tbl_keys as $key)
			{
				$empty      = $empty && empty($this->$key);
				$keys[$key] = $this->$key;
			}

			// If empty primary key there's is no need to load anything
			if ($empty)
			{
				return true;
			}
		}
		elseif (!is_array($keys))
		{
			// Load by primary key.
			$keyCount = count($this->_tbl_keys);

			if ($keyCount)
			{
				if ($keyCount > 1)
				{
					throw new InvalidArgumentException('Table has multiple primary keys specified, only one primary key value provided.');
				}

				$keys = array($this->getKeyName() => $keys);
			}
			else
			{
				throw new RuntimeException('No table keys defined.');
			}
		}

		if ($reset)
		{
			$this->reset();
		}

		// Initialise the query.
		$query = $this->_db->getQuery(true)
			->select('*')
			->from($this->_tbl);
		$fields = array_keys($this->getProperties());

		foreach ($keys as $field => $value)
		{
			// Check that $field is in the table.
			if (!in_array($field, $fields))
			{
				throw new UnexpectedValueException(sprintf('Missing field in database: %s &#160; %s.', get_class($this), $field));
			}
			// Add the search tuple to the query.
			$query->where($this->_db->quoteName($field) . ' = ' . $this->_db->quote($value));
		}

		$this->_db->setQuery($query);

		$row = $this->_db->loadAssoc();

		// Check that we have a result.
		if (empty($row))
		{
			$result = false;
		}
		else
		{
			// Bind the object with the row and return.
			$result = $this->bind($row);
		}

		// Implement JObservableInterface: Post-processing by observers
		$this->_observers->update('onAfterLoad', array(&$result, $row));

		return $result;
	}

	/**
	 * Method to perform sanity checks on the JTable instance properties to ensure they are safe to store in the database.
	 *
	 * Child classes should override this method to make sure the data they are storing in the database is safe and as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @since   11.1
	 */
	public function check()
	{
		return true;
	}

	/**
	 * Method to store a row in the database from the JTable instance properties.
	 *
	 * If a primary key value is set the row with that primary key value will be updated with the instance property values.
	 * If no primary key value is set a new row will be inserted into the database with the properties from the JTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		$result = true;

		$k = $this->_tbl_keys;

		// Implement JObservableInterface: Pre-processing by observers
		$this->_observers->update('onBeforeStore', array($updateNulls, $k));

		$currentAssetId = 0;

		if (!empty($this->asset_id))
		{
			$currentAssetId = $this->asset_id;
		}

		// The asset id field is managed privately by this class.
		if ($this->_trackAssets)
		{
			unset($this->asset_id);
		}

		// If a primary key exists update the object, otherwise insert it.
		if ($this->hasPrimaryKey())
		{
			$this->_db->updateObject($this->_tbl, $this, $this->_tbl_keys, $updateNulls);
		}
		else
		{
			$this->_db->insertObject($this->_tbl, $this, $this->_tbl_keys[0]);
		}

		// If the table is not set to track assets return true.
		if ($this->_trackAssets)
		{
			if ($this->_locked)
			{
				$this->_unlock();
			}

			/*
			 * Asset Tracking
			 */
			$parentId = $this->_getAssetParentId();
			$name     = $this->_getAssetName();
			$title    = $this->_getAssetTitle();

			$asset = self::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));
			$asset->loadByName($name);

			// Re-inject the asset id.
			$this->asset_id = $asset->id;

			// Check for an error.
			$error = $asset->getError();

			if ($error)
			{
				$this->setError($error);

				return false;
			}
			else
			{
				// Specify how a new or moved node asset is inserted into the tree.
				if (empty($this->asset_id) || $asset->parent_id != $parentId)
				{
					$asset->setLocation($parentId, 'last-child');
				}

				// Prepare the asset to be stored.
				$asset->parent_id = $parentId;
				$asset->name      = $name;
				$asset->title     = $title;

				if ($this->_rules instanceof JAccessRules)
				{
					$asset->rules = (string) $this->_rules;
				}

				if (!$asset->check() || !$asset->store($updateNulls))
				{
					$this->setError($asset->getError());

					return false;
				}
				else
				{
					// Create an asset_id or heal one that is corrupted.
					if (empty($this->asset_id) || ($currentAssetId != $this->asset_id && !empty($this->asset_id)))
					{
						// Update the asset_id field in this table.
						$this->asset_id = (int) $asset->id;

						$query = $this->_db->getQuery(true)
							->update($this->_db->quoteName($this->_tbl))
							->set('asset_id = ' . (int) $this->asset_id);
						$this->appendPrimaryKeys($query);
						$this->_db->setQuery($query)->execute();
					}
				}
			}
		}

		// Implement JObservableInterface: Post-processing by observers
		$this->_observers->update('onAfterStore', array(&$result));

		return $result;
	}

	/**
	 * Method to provide a shortcut to binding, checking and storing a JTable instance to the database table.
	 *
	 * The method will check a row in once the data has been stored and if an ordering filter is present will attempt to reorder
	 * the table rows based on the filter.  The ordering filter is an instance property name.  The rows that will be reordered
	 * are those whose value matches the JTable instance for the property specified.
	 *
	 * @param   array|object  $src             An associative array or object to bind to the JTable instance.
	 * @param   string        $orderingFilter  Filter for the order updating
	 * @param   array|string  $ignore          An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
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
			$this->reorder($orderingFilter ? $this->_db->quoteName($orderingFilter) . ' = ' . $this->_db->quote($filterValue) : '');
		}

		// Set the error to empty and return true.
		$this->setError('');

		return true;
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 */
	public function delete($pk = null)
	{
		if (is_null($pk))
		{
			$pk = array();

			foreach ($this->_tbl_keys as $key)
			{
				$pk[$key] = $this->$key;
			}
		}
		elseif (!is_array($pk))
		{
			$pk = array($this->_tbl_key => $pk);
		}

		foreach ($this->_tbl_keys as $key)
		{
			$pk[$key] = is_null($pk[$key]) ? $this->$key : $pk[$key];

			if ($pk[$key] === null)
			{
				throw new UnexpectedValueException('Null primary key not allowed.');
			}

			$this->$key = $pk[$key];
		}

		// Implement JObservableInterface: Pre-processing by observers
		$this->_observers->update('onBeforeDelete', array($pk));

		// If tracking assets, remove the asset first.
		if ($this->_trackAssets)
		{
			// Get the asset name
			$name  = $this->_getAssetName();
			$asset = self::getInstance('Asset');

			if ($asset->loadByName($name))
			{
				if (!$asset->delete())
				{
					$this->setError($asset->getError());

					return false;
				}
			}
		}

		// Delete the row by primary key.
		$query = $this->_db->getQuery(true)
			->delete($this->_tbl);
		$this->appendPrimaryKeys($query, $pk);

		$this->_db->setQuery($query);

		// Check for a database error.
		$this->_db->execute();

		// Implement JObservableInterface: Post-processing by observers
		$this->_observers->update('onAfterDelete', array($pk));

		return true;
	}

	/**
	 * Method to check a row out if the necessary properties/fields exist.
	 *
	 * To prevent race conditions while editing rows in a database, a row can be checked out if the fields 'checked_out' and 'checked_out_time'
	 * are available. While a row is checked out, any attempt to store the row by a user other than the one who checked the row out should be
	 * held until the row is checked in again.
	 *
	 * @param   integer  $userId  The Id of the user checking out the row.
	 * @param   mixed    $pk      An optional primary key value to check out.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 */
	public function checkOut($userId, $pk = null)
	{
		$checkedOutField = $this->getColumnAlias('checked_out');
		$checkedOutTimeField = $this->getColumnAlias('checked_out_time');

		// If there is no checked_out or checked_out_time field, just return true.
		if (!property_exists($this, $checkedOutField) || !property_exists($this, $checkedOutTimeField))
		{
			return true;
		}

		if (is_null($pk))
		{
			$pk = array();

			foreach ($this->_tbl_keys as $key)
			{
				$pk[$key] = $this->$key;
			}
		}
		elseif (!is_array($pk))
		{
			$pk = array($this->_tbl_key => $pk);
		}

		foreach ($this->_tbl_keys as $key)
		{
			$pk[$key] = is_null($pk[$key]) ? $this->$key : $pk[$key];

			if ($pk[$key] === null)
			{
				throw new UnexpectedValueException('Null primary key not allowed.');
			}
		}

		// Get the current time in the database format.
		$time = JFactory::getDate()->toSql();

		// Check the row out by primary key.
		$query = $this->_db->getQuery(true)
			->update($this->_tbl)
			->set($this->_db->quoteName($checkedOutField) . ' = ' . (int) $userId)
			->set($this->_db->quoteName($checkedOutTimeField) . ' = ' . $this->_db->quote($time));
		$this->appendPrimaryKeys($query, $pk);
		$this->_db->setQuery($query);
		$this->_db->execute();

		// Set table values in the object.
		$this->$checkedOutField      = (int) $userId;
		$this->$checkedOutTimeField = $time;

		return true;
	}

	/**
	 * Method to check a row in if the necessary properties/fields exist.
	 *
	 * Checking a row in will allow other users the ability to edit the row.
	 *
	 * @param   mixed  $pk  An optional primary key value to check out.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 */
	public function checkIn($pk = null)
	{
		$checkedOutField = $this->getColumnAlias('checked_out');
		$checkedOutTimeField = $this->getColumnAlias('checked_out_time');

		// If there is no checked_out or checked_out_time field, just return true.
		if (!property_exists($this, $checkedOutField) || !property_exists($this, $checkedOutTimeField))
		{
			return true;
		}

		if (is_null($pk))
		{
			$pk = array();

			foreach ($this->_tbl_keys as $key)
			{
				$pk[$this->$key] = $this->$key;
			}
		}
		elseif (!is_array($pk))
		{
			$pk = array($this->_tbl_key => $pk);
		}

		foreach ($this->_tbl_keys as $key)
		{
			$pk[$key] = empty($pk[$key]) ? $this->$key : $pk[$key];

			if ($pk[$key] === null)
			{
				throw new UnexpectedValueException('Null primary key not allowed.');
			}
		}

		// Check the row in by primary key.
		$query = $this->_db->getQuery(true)
			->update($this->_tbl)
			->set($this->_db->quoteName($checkedOutField) . ' = 0')
			->set($this->_db->quoteName($checkedOutTimeField) . ' = ' . $this->_db->quote($this->_db->getNullDate()));
		$this->appendPrimaryKeys($query, $pk);
		$this->_db->setQuery($query);

		// Check for a database error.
		$this->_db->execute();

		// Set table values in the object.
		$this->$checkedOutField      = 0;
		$this->$checkedOutTimeField = '';

		return true;
	}

	/**
	 * Validate that the primary key has been set.
	 *
	 * @return  boolean  True if the primary key(s) have been set.
	 *
	 * @since   12.3
	 */
	public function hasPrimaryKey()
	{
		if ($this->_autoincrement)
		{
			$empty = true;

			foreach ($this->_tbl_keys as $key)
			{
				$empty = $empty && empty($this->$key);
			}
		}
		else
		{
			$query = $this->_db->getQuery(true)
				->select('COUNT(*)')
				->from($this->_tbl);
			$this->appendPrimaryKeys($query);

			$this->_db->setQuery($query);
			$count = $this->_db->loadResult();

			if ($count == 1)
			{
				$empty = false;
			}
			else
			{
				$empty = true;
			}
		}

		return !$empty;
	}

	/**
	 * Method to increment the hits for a row if the necessary property/field exists.
	 *
	 * @param   mixed  $pk  An optional primary key value to increment. If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 */
	public function hit($pk = null)
	{
		$hitsField = $this->getColumnAlias('hits');

		// If there is no hits field, just return true.
		if (!property_exists($this, $hitsField))
		{
			return true;
		}

		if (is_null($pk))
		{
			$pk = array();

			foreach ($this->_tbl_keys as $key)
			{
				$pk[$key] = $this->$key;
			}
		}
		elseif (!is_array($pk))
		{
			$pk = array($this->_tbl_key => $pk);
		}

		foreach ($this->_tbl_keys as $key)
		{
			$pk[$key] = is_null($pk[$key]) ? $this->$key : $pk[$key];

			if ($pk[$key] === null)
			{
				throw new UnexpectedValueException('Null primary key not allowed.');
			}
		}

		// Check the row in by primary key.
		$query = $this->_db->getQuery(true)
			->update($this->_tbl)
			->set($this->_db->quoteName($hitsField) . ' = (' . $this->_db->quoteName($hitsField) . ' + 1)');
		$this->appendPrimaryKeys($query, $pk);
		$this->_db->setQuery($query);
		$this->_db->execute();

		// Set table values in the object.
		$this->hits++;

		return true;
	}

	/**
	 * Method to determine if a row is checked out and therefore uneditable by a user.
	 *
	 * If the row is checked out by the same user, then it is considered not checked out -- as the user can still edit it.
	 *
	 * @param   integer  $with     The user ID to preform the match with, if an item is checked out by this user the function will return false.
	 * @param   integer  $against  The user ID to perform the match against when the function is used as a static function.
	 *
	 * @return  boolean  True if checked out.
	 *
	 * @since   11.1
	 */
	public function isCheckedOut($with = 0, $against = null)
	{
		// Handle the non-static case.
		if (isset($this) && ($this instanceof JTable) && is_null($against))
		{
			$checkedOutField = $this->getColumnAlias('checked_out');
			$against = $this->get($checkedOutField);
		}

		// The item is not checked out or is checked out by the same user.
		if (!$against || ($against == $with))
		{
			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(userid)')
			->from($db->quoteName('#__session'))
			->where($db->quoteName('userid') . ' = ' . (int) $against);
		$db->setQuery($query);
		$checkedOut = (boolean) $db->loadResult();

		// If a session exists for the user then it is checked out.
		return $checkedOut;
	}

	/**
	 * Method to get the next ordering value for a group of rows defined by an SQL WHERE clause.
	 *
	 * This is useful for placing a new item last in a group of items in the table.
	 *
	 * @param   string  $where  WHERE clause to use for selecting the MAX(ordering) for the table.
	 *
	 * @return  integer  The next ordering value.
	 *
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 */
	public function getNextOrder($where = '')
	{
		// Check if there is an ordering field set
		$orderingField = $this->getColumnAlias('ordering');

		if (!property_exists($this, $orderingField))
		{
			throw new UnexpectedValueException(sprintf('%s does not support ordering.', get_class($this)));
		}

		// Get the largest ordering value for a given where clause.
		$query = $this->_db->getQuery(true)
			->select('MAX(' . $this->_db->quoteName($orderingField) . ')')
			->from($this->_tbl);

		if ($where)
		{
			$query->where($where);
		}

		$this->_db->setQuery($query);
		$max = (int) $this->_db->loadResult();

		// Return the largest ordering value + 1.
		return $max + 1;
	}

	/**
	 * Get the primary key values for this table using passed in values as a default.
	 *
	 * @param   array  $keys  Optional primary key values to use.
	 *
	 * @return  array  An array of primary key names and values.
	 *
	 * @since   12.3
	 */
	public function getPrimaryKey(array $keys = array())
	{
		foreach ($this->_tbl_keys as $key)
		{
			if (!isset($keys[$key]))
			{
				if (!empty($this->$key))
				{
					$keys[$key] = $this->$key;
				}
			}
		}

		return $keys;
	}

	/**
	 * Method to compact the ordering values of rows in a group of rows defined by an SQL WHERE clause.
	 *
	 * @param   string  $where  WHERE clause to use for limiting the selection of rows to compact the ordering values.
	 *
	 * @return  mixed  Boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 */
	public function reorder($where = '')
	{
		// Check if there is an ordering field set
		$orderingField = $this->getColumnAlias('ordering');

		if (!property_exists($this, $orderingField))
		{
			throw new UnexpectedValueException(sprintf('%s does not support ordering.', get_class($this)));
		}

		$quotedOrderingField = $this->_db->quoteName($orderingField);

		// Get the primary keys and ordering values for the selection.
		$query = $this->_db->getQuery(true)
			->select(implode(',', $this->_tbl_keys) . ', ' . $quotedOrderingField)
			->from($this->_tbl)
			->where($quotedOrderingField . ' >= 0')
			->order($quotedOrderingField);

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
			if ($row->$orderingField >= 0)
			{
				// Only update rows that are necessary.
				if ($row->$orderingField != $i + 1)
				{
					// Update the row ordering field.
					$query->clear()
						->update($this->_tbl)
						->set($quotedOrderingField . ' = ' . ($i + 1));
					$this->appendPrimaryKeys($query, $row);
					$this->_db->setQuery($query);
					$this->_db->execute();
				}
			}
		}

		return true;
	}

	/**
	 * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
	 *
	 * Negative numbers move the row up in the sequence and positive numbers move it down.
	 *
	 * @param   integer  $delta  The direction and magnitude to move the row in the ordering sequence.
	 * @param   string   $where  WHERE clause to use for limiting the selection of rows to compact the ordering values.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 */
	public function move($delta, $where = '')
	{
		// Check if there is an ordering field set
		$orderingField = $this->getColumnAlias('ordering');

		if (!property_exists($this, $orderingField))
		{
			throw new UnexpectedValueException(sprintf('%s does not support ordering.', get_class($this)));
		}

		$quotedOrderingField = $this->_db->quoteName($orderingField);

		// If the change is none, do nothing.
		if (empty($delta))
		{
			return true;
		}

		$row   = null;
		$query = $this->_db->getQuery(true);

		// Select the primary key and ordering values from the table.
		$query->select(implode(',', $this->_tbl_keys) . ', ' . $quotedOrderingField)
			->from($this->_tbl);

		// If the movement delta is negative move the row up.
		if ($delta < 0)
		{
			$query->where($quotedOrderingField . ' < ' . (int) $this->$orderingField)
				->order($quotedOrderingField . ' DESC');
		}
		// If the movement delta is positive move the row down.
		elseif ($delta > 0)
		{
			$query->where($quotedOrderingField . ' > ' . (int) $this->$orderingField)
				->order($quotedOrderingField . ' ASC');
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
			$query->clear()
				->update($this->_tbl)
				->set($quotedOrderingField . ' = ' . (int) $row->$orderingField);
			$this->appendPrimaryKeys($query);
			$this->_db->setQuery($query);
			$this->_db->execute();

			// Update the ordering field for the row to this instance's ordering value.
			$query->clear()
				->update($this->_tbl)
				->set($quotedOrderingField . ' = ' . (int) $this->$orderingField);
			$this->appendPrimaryKeys($query, $row);
			$this->_db->setQuery($query);
			$this->_db->execute();

			// Update the instance value.
			$this->$orderingField = $row->$orderingField;
		}
		else
		{
			// Update the ordering field for this instance.
			$query->clear()
				->update($this->_tbl)
				->set($quotedOrderingField . ' = ' . (int) $this->$orderingField);
			$this->appendPrimaryKeys($query);
			$this->_db->setQuery($query);
			$this->_db->execute();
		}

		return true;
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database table.
	 *
	 * The method respects checked out rows by other users and will attempt to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update. If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user ID of the user performing the operation.
	 *
	 * @return  boolean  True on success; false if $pks is empty.
	 *
	 * @since   11.1
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Sanitize input
		$userId = (int) $userId;
		$state  = (int) $state;

		if (!is_null($pks))
		{
			if (!is_array($pks))
			{
				$pks = array($pks);
			}

			foreach ($pks as $key => $pk)
			{
				if (!is_array($pk))
				{
					$pks[$key] = array($this->_tbl_key => $pk);
				}
			}
		}

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			$pk = array();

			foreach ($this->_tbl_keys as $key)
			{
				if ($this->$key)
				{
					$pk[$key] = $this->$key;
				}
				// We don't have a full primary key - return false
				else
				{
					$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

					return false;
				}
			}

			$pks = array($pk);
		}

		$publishedField = $this->getColumnAlias('published');
		$checkedOutField = $this->getColumnAlias('checked_out');

		foreach ($pks as $pk)
		{
			// Update the publishing state for rows with the given primary keys.
			$query = $this->_db->getQuery(true)
				->update($this->_tbl)
				->set($this->_db->quoteName($publishedField) . ' = ' . (int) $state);

			// If publishing, set published date/time if not previously set
			if ($state && property_exists($this, 'publish_up') && (int) $this->publish_up == 0)
			{
				$nowDate = $this->_db->quote(JFactory::getDate()->toSql());
				$query->set($this->_db->quoteName($this->getColumnAlias('publish_up')) . ' = ' . $nowDate);
			}

			// Determine if there is checkin support for the table.
			if (property_exists($this, 'checked_out') || property_exists($this, 'checked_out_time'))
			{
				$query->where('(' . $this->_db->quoteName($checkedOutField) . ' = 0 OR ' . $this->_db->quoteName($checkedOutField) . ' = ' . (int) $userId . ')');
				$checkin = true;
			}
			else
			{
				$checkin = false;
			}

			// Build the WHERE clause for the primary keys.
			$this->appendPrimaryKeys($query, $pk);

			$this->_db->setQuery($query);

			try
			{
				$this->_db->execute();
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}

			// If checkin is supported and all rows were adjusted, check them in.
			if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
			{
				$this->checkin($pk);
			}

			// If the JTable instance value is in the list of primary keys that were set, set the instance.
			$ours = true;

			foreach ($this->_tbl_keys as $key)
			{
				if ($this->$key != $pk[$key])
				{
					$ours = false;
				}
			}

			if ($ours)
			{
				$this->$publishedField = $state;
			}
		}

		$this->setError('');

		return true;
	}

	/**
	 * Method to lock the database table for writing.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	protected function _lock()
	{
		$this->_db->lockTable($this->_tbl);
		$this->_locked = true;

		return true;
	}

	/**
	 * Method to return the real name of a "special" column such as ordering, hits, published
	 * etc etc. In this way you are free to follow your db naming convention and use the
	 * built in Joomla functions.
	 *
	 * @param   string  $column  Name of the "special" column (ie ordering, hits)
	 *
	 * @return  string  The string that identify the special
	 *
	 * @since   3.4
	 */
	public function getColumnAlias($column)
	{
		// Get the column data if set
		if (isset($this->_columnAlias[$column]))
		{
			$return = $this->_columnAlias[$column];
		}
		else
		{
			$return = $column;
		}

		// Sanitize the name
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
	 *
	 * @since   3.4
	 */
	public function setColumnAlias($column, $columnAlias)
	{
		// Santize the column name alias
		$column = strtolower($column);
		$column = preg_replace('#[^A-Z0-9_]#i', '', $column);

		// Set the column alias internally
		$this->_columnAlias[$column] = $columnAlias;
	}

	/**
	 * Method to unlock the database table for writing.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	protected function _unlock()
	{
		$this->_db->unlockTables();
		$this->_locked = false;

		return true;
	}
}
