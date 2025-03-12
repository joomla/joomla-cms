<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Event\Checkin\AfterCheckinEvent as GlobalAfterCheckinEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\LegacyErrorHandlingTrait;
use Joomla\CMS\Object\LegacyPropertyManagementTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseQuery;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Filesystem\Path;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Abstract Table class
 *
 * Parent class to all tables.
 *
 * @since  1.7.0
 */
abstract class Table extends \stdClass implements TableInterface, DispatcherAwareInterface
{
    use DispatcherAwareTrait;
    use LegacyErrorHandlingTrait;
    use LegacyPropertyManagementTrait;


    /**
     * Include paths for searching for Table classes.
     *
     * @var    array
     * @since  3.0.0
     */
    private static $_includePaths = [];

    /**
     * Table fields cache
     *
     * @var   array
     * @since 3.10.4
     */
    private static $tableFields;

    /**
     * Name of the database table to model.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $_tbl = '';

    /**
     * Name of the primary key field in the table.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $_tbl_key = '';

    /**
     * Name of the primary key fields in the table.
     *
     * @var    array
     * @since  3.0.1
     */
    protected $_tbl_keys = [];

    /**
     * DatabaseInterface object.
     *
     * @var    DatabaseInterface
     * @since  1.7.0
     */
    protected $_db;

    /**
     * Should rows be tracked as ACL assets?
     *
     * @var    boolean
     * @since  1.7.0
     */
    protected $_trackAssets = false;

    /**
     * The rules associated with this record.
     *
     * @var    Rules  A Rules object.
     * @since  1.7.0
     */
    protected $_rules;

    /**
     * Indicator that the tables have been locked.
     *
     * @var    boolean
     * @since  1.7.0
     */
    protected $_locked = false;

    /**
     * Indicates that the primary keys autoincrement.
     *
     * @var    boolean
     * @since  3.1.4
     */
    protected $_autoincrement = true;

    /**
     * Array with alias for "special" columns such as ordering, hits etc etc
     *
     * @var    array
     * @since  3.4.0
     */
    protected $_columnAlias = [];

    /**
     * An array of key names to be json encoded in the bind function
     *
     * @var    array
     * @since  3.3
     */
    protected $_jsonEncode = [];

    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  3.10.0
     */
    protected $_supportNullValue = false;

    /**
     * The UCM type alias. Used for tags, content versioning etc. Leave blank to effectively disable these features.
     *
     * @var    string
     * @since  4.0.0
     */
    public $typeAlias = null;

    /**
     * Object constructor to set table and key fields.  In most cases this will
     * be overridden by child classes to explicitly set the table and key fields
     * for a particular database table.
     *
     * @param   string                $table       Name of the table to model.
     * @param   mixed                 $key         Name of the primary key field in the table or array of field names that compose the primary key.
     * @param   DatabaseInterface     $db          DatabaseInterface object.
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   1.7.0
     */
    public function __construct($table, $key, DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        // Set internal variables.
        $this->_tbl = $table;

        // Set the key to be an array.
        if (\is_string($key)) {
            $key = [$key];
        } elseif (\is_object($key)) {
            $key = (array) $key;
        }

        $this->_tbl_keys = $key;

        if (\count($key) == 1) {
            $this->_autoincrement = true;
        } else {
            $this->_autoincrement = false;
        }

        // Set the singular table key for backwards compatibility.
        $this->_tbl_key = $this->getKeyName();

        $this->_db = $db;

        // Initialise the table properties.
        $fields = $this->getFields();

        if ($fields) {
            foreach ($fields as $name => $v) {
                // Add the field if it is not already present.
                if (!$this->hasField($name)) {
                    $this->$name = null;
                }
            }
        }

        // If we are tracking assets, make sure an access field exists and initially set the default.
        if ($this->hasField('asset_id')) {
            $this->_trackAssets = true;
        }

        // If the access property exists, set the default.
        if ($this->hasField('access')) {
            $this->access = (int) Factory::getApplication()->get('access');
        }

        // Create or set a Dispatcher
        if (!\is_object($dispatcher) || !($dispatcher instanceof DispatcherInterface)) {
            // @todo Maybe we should use a dedicated "behaviour" dispatcher for performance reasons and to prevent system plugins from butting in?
            $dispatcher = Factory::getApplication()->getDispatcher();
        }

        $this->setDispatcher($dispatcher);

        $event = AbstractEvent::create(
            'onTableObjectCreate',
            [
                'subject' => $this,
            ]
        );
        $this->getDispatcher()->dispatch('onTableObjectCreate', $event);
    }

    /**
     * Get the columns from database table.
     *
     * @param   bool  $reload  flag to reload cache
     *
     * @return  mixed  An array of the field names, or false if an error occurs.
     *
     * @since   1.7.0
     * @throws  \UnexpectedValueException
     */
    public function getFields($reload = false)
    {
        $key = $this->_db->getServerType() . ':' . $this->_db->getName() . ':' . $this->_tbl;

        if (!isset(self::$tableFields[$key]) || $reload) {
            // Lookup the fields for this table only once.
            $name   = $this->_tbl;
            $fields = $this->_db->getTableColumns($name, false);

            if (empty($fields)) {
                throw new \UnexpectedValueException(\sprintf('No columns found for %s table', $name));
            }

            self::$tableFields[$key] = $fields;
        }

        return self::$tableFields[$key];
    }

    /**
     * Static method to get an instance of a Table class if it can be found in the table include paths.
     *
     * To add include paths for searching for Table classes see Table::addIncludePath().
     *
     * @param   string  $type    The type (name) of the Table class to get an instance of.
     * @param   string  $prefix  An optional prefix for the table class name.
     * @param   array   $config  An optional array of configuration values for the Table object.
     *
     * @return  Table|boolean   A Table object if found or boolean false on failure.
     *
     * @since       1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use the MvcFactory instead or instantiate the table class directly.
     *              Example: Factory::getApplication()->bootComponent('...')->getMVCFactory()->createTable($name, $prefix, $config);
     *              $table = new \Joomla\CMS\Table\Content($db);
     */
    public static function getInstance($type, $prefix = 'JTable', $config = [])
    {
        /**
         * For B/C reasons we don't change the $prefix to \\Joomla\\CMS\\Table\\ since extensions which
         * use JTable as table prefix instead of an own prefix and not adding 'JTable' as prefix will
         * fail to load the table. We can't detect this situation.
         * Example:
         * class JTableMytable {}
         * JTable::getInstance('Mytable');
         * This will fail when we change the function default $prefix from JTable to \\Joomla\\CMS\\Table\\
         *
         * In case of $prefix is 'JTable' we make an additional check for '\\Joomla\\CMS\\Table\\' $type
         *
         */

        // Sanitize and prepare the table class name.
        $type       = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);

        $tableClass       = $prefix . ucfirst($type);
        $tableClassLegacy = $tableClass;

        if ($prefix === 'JTable') {
            $tableClass = '\\Joomla\\CMS\\Table\\' . ucfirst($type);
        }

        // Only try to load the class if it doesn't already exist.
        if (!class_exists($tableClass) && !class_exists($tableClassLegacy)) {
            // Search for the class file in the JTable include paths.
            $paths     = self::addIncludePath();
            $pathIndex = 0;

            while (!class_exists($tableClass) && !class_exists($tableClassLegacy) && $pathIndex < \count($paths)) {
                if ($tryThis = Path::find($paths[$pathIndex++], strtolower($type) . '.php')) {
                    // Import the class file.
                    include_once $tryThis;
                }
            }
        }

        if (!class_exists($tableClass) && class_exists($tableClassLegacy)) {
            $tableClass = $tableClassLegacy;
        }

        if (!class_exists($tableClass)) {
            /*
            * If unable to find the class file in the Table include paths. Return false.
            * The warning JLIB_DATABASE_ERROR_NOT_SUPPORTED_FILE_NOT_FOUND has been removed in 3.6.3.
            * In 6.0 an Exception (type to be determined) will be thrown.
            * For more info see https://github.com/joomla/joomla-cms/issues/11570
            */

            return false;
        }

        // If a database object was passed in the configuration array use it, otherwise get the global one from Factory.
        $db = $config['dbo'] ?? Factory::getContainer()->get(DatabaseInterface::class);

        // Check for a possible service from the container otherwise manually instantiate the class
        if (Factory::getContainer()->has($tableClass)) {
            return Factory::getContainer()->get($tableClass);
        }

        // Instantiate a new table class and return it.
        return new $tableClass($db);
    }

    /**
     * Add a filesystem path where Table should search for table class files.
     *
     * @param   array|string  $path  A filesystem path or array of filesystem paths to add.
     *
     * @return  array  An array of filesystem paths to find Table classes in.
     *
     * @since       1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Should not be used anymore as tables are loaded through the MvcFactory
     */
    public static function addIncludePath($path = null)
    {
        // If the internal paths have not been initialised, do so with the base table path.
        if (empty(self::$_includePaths)) {
            self::$_includePaths = [__DIR__];
        }

        // Convert the passed path(s) to add to an array.
        settype($path, 'array');

        // If we have new paths to add, do so.
        if (!empty($path)) {
            // Check and add each individual new path.
            foreach ($path as $dir) {
                // Sanitize path.
                $dir = trim($dir);

                // Add to the front of the list so that custom paths are searched first.
                if (!\in_array($dir, self::$_includePaths)) {
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
     * @since   1.7.0
     */
    protected function _getAssetName()
    {
        $keys = [];

        foreach ($this->_tbl_keys as $k) {
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
     * @since   1.7.0
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
     * @param   ?Table    $table  A Table object for the asset parent.
     * @param   ?integer  $id     Id to look up
     *
     * @return  integer
     *
     * @since   1.7.0
     */
    protected function _getAssetParentId(?Table $table = null, $id = null)
    {
        // For simple cases, parent to the asset root.
        $assets = new Asset($this->getDbo(), $this->getDispatcher());
        $rootId = $assets->getRootId();

        if (!empty($rootId)) {
            return $rootId;
        }

        return 1;
    }

    /**
     * Method to append the primary keys for this table to a query.
     *
     * @param   DatabaseQuery  $query  A query object to append.
     * @param   mixed          $pk     Optional primary key parameter.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function appendPrimaryKeys($query, $pk = null)
    {
        if (\is_null($pk)) {
            foreach ($this->_tbl_keys as $k) {
                $query->where($this->_db->quoteName($k) . ' = ' . $this->_db->quote($this->$k));
            }
        } else {
            if (\is_string($pk)) {
                $pk = [$this->_tbl_key => $pk];
            }

            $pk = (object) $pk;

            foreach ($this->_tbl_keys as $k) {
                $query->where($this->_db->quoteName($k) . ' = ' . $this->_db->quote($pk->$k));
            }
        }
    }

    /**
     * Method to get the database table name for the class.
     *
     * @return  string  The name of the database table being modeled.
     *
     * @since   1.7.0
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
     * @since   1.7.0
     */
    public function getKeyName($multiple = false)
    {
        // Count the number of keys
        if (\count($this->_tbl_keys)) {
            if ($multiple) {
                // If we want multiple keys, return the raw array.
                return $this->_tbl_keys;
            }

            // If we want the standard method, just return the first key.
            return $this->_tbl_keys[0];
        }

        return '';
    }

    /**
     * Returns the identity (primary key) value of this record
     *
     * @return  mixed
     *
     * @since   4.0.0
     */
    public function getId()
    {
        $key = $this->getKeyName();

        return $this->$key;
    }

    /**
     * Method to get the DatabaseInterface object.
     *
     * @return  DatabaseInterface  The internal database driver object.
     *
     * @since   1.7.0
     */
    public function getDbo()
    {
        return $this->_db;
    }

    /**
     * Method to set the DatabaseInterface object.
     *
     * @param   DatabaseInterface  $db  A DatabaseInterface object to be used by the table object.
     *
     * @return  boolean  True on success.
     *
     * @since   1.7.0
     */
    public function setDbo(DatabaseInterface $db)
    {
        $this->_db = $db;

        return true;
    }

    /**
     * Method to set rules for the record.
     *
     * @param   mixed  $input  A Rules object, JSON string, or array.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function setRules($input)
    {
        if ($input instanceof Rules) {
            $this->_rules = $input;
        } else {
            $this->_rules = new Rules($input);
        }
    }

    /**
     * Method to get the rules for the record.
     *
     * @return  Rules object
     *
     * @since   1.7.0
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
     * @since   1.7.0
     */
    public function reset()
    {
        $event = AbstractEvent::create(
            'onTableBeforeReset',
            [
                'subject' => $this,
            ]
        );
        $this->getDispatcher()->dispatch('onTableBeforeReset', $event);

        // Get the default values for the class from the table.
        foreach ($this->getFields() as $k => $v) {
            // If the property is not the primary key or private, reset it.
            if (!\in_array($k, $this->_tbl_keys) && (!str_starts_with($k, '_'))) {
                $this->$k = $v->Default;
            }
        }

        // Reset table errors
        $this->_errors = [];

        $event = AbstractEvent::create(
            'onTableAfterReset',
            [
                'subject' => $this,
            ]
        );
        $this->getDispatcher()->dispatch('onTableAfterReset', $event);
    }

    /**
     * Method to bind an associative array or object to the Table instance.This
     * method only binds properties that are publicly accessible and optionally
     * takes an array of properties to ignore when binding.
     *
     * @param   array|object  $src     An associative array or object to bind to the Table instance.
     * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  boolean  True on success.
     *
     * @since   1.7.0
     * @throws  \InvalidArgumentException
     */
    public function bind($src, $ignore = [])
    {
        // Check if the source value is an array or object
        if (!\is_object($src) && !\is_array($src)) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Could not bind the data source in %1$s::bind(), the source must be an array or object but a "%2$s" was given.',
                    \get_class($this),
                    \gettype($src)
                )
            );
        }

        // If the ignore value is a string, explode it over spaces.
        if (!\is_array($ignore)) {
            $ignore = explode(' ', $ignore);
        }

        $event = AbstractEvent::create(
            'onTableBeforeBind',
            [
                'subject' => $this,
                'src'     => $src,
                'ignore'  => $ignore,
            ]
        );
        $this->getDispatcher()->dispatch('onTableBeforeBind', $event);

        // If the source value is an object, get its accessible properties.
        if (\is_object($src)) {
            $src = get_object_vars($src);
        }

        // JSON encode any fields required
        if (!empty($this->_jsonEncode)) {
            foreach ($this->_jsonEncode as $field) {
                if (isset($src[$field]) && \is_array($src[$field])) {
                    $src[$field] = json_encode($src[$field]);
                }
            }
        }

        // Bind the source value, excluding the ignored fields.
        foreach ($this->getProperties() as $k => $v) {
            // Only process fields not in the ignore array.
            if (!\in_array($k, $ignore)) {
                if (\array_key_exists($k, $src)) {
                    $this->$k = $src[$k];
                }
            }
        }

        $event = AbstractEvent::create(
            'onTableAfterBind',
            [
                'subject' => $this,
                'src'     => $src,
                'ignore'  => $ignore,
            ]
        );
        $this->getDispatcher()->dispatch('onTableAfterBind', $event);

        return true;
    }

    /**
     * Method to load a row from the database by primary key and bind the fields to the Table instance properties.
     *
     * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.
     *                           If not set the instance property value is used.
     * @param   boolean  $reset  True to reset the default values before loading the new row.
     *
     * @return  boolean  True if successful. False if row not found.
     *
     * @since   1.7.0
     * @throws  \InvalidArgumentException
     * @throws  \RuntimeException
     * @throws  \UnexpectedValueException
     */
    public function load($keys = null, $reset = true)
    {
        // Pre-processing by observers
        $event = AbstractEvent::create(
            'onTableBeforeLoad',
            [
                'subject' => $this,
                'keys'    => $keys,
                'reset'   => $reset,
            ]
        );
        $this->getDispatcher()->dispatch('onTableBeforeLoad', $event);

        if (empty($keys)) {
            $empty = true;
            $keys  = [];

            // If empty, use the value of the current key
            foreach ($this->_tbl_keys as $key) {
                $empty      = $empty && empty($this->$key);
                $keys[$key] = $this->$key;
            }

            // If empty primary key there's is no need to load anything
            if ($empty) {
                return true;
            }
        } elseif (!\is_array($keys)) {
            // Load by primary key.
            $keyCount = \count($this->_tbl_keys);

            if ($keyCount) {
                if ($keyCount > 1) {
                    throw new \InvalidArgumentException('Table has multiple primary keys specified, only one primary key value provided.');
                }

                $keys = [$this->getKeyName() => $keys];
            } else {
                throw new \RuntimeException('No table keys defined.');
            }
        }

        if ($reset) {
            $this->reset();
        }

        // Initialise the query.
        $query = $this->_db->getQuery(true)
            ->select('*')
            ->from($this->_db->quoteName($this->_tbl));
        $fields = array_keys($this->getProperties());

        foreach ($keys as $field => $value) {
            // Check that $field is in the table.
            if (!\in_array($field, $fields)) {
                throw new \UnexpectedValueException(\sprintf('Missing field in database: %s &#160; %s.', \get_class($this), $field));
            }

            // Add the search tuple to the query.
            $query->where($this->_db->quoteName($field) . ' = ' . $this->_db->quote($value));
        }

        $this->_db->setQuery($query);

        $row = $this->_db->loadAssoc();

        // Check that we have a result.
        if (empty($row)) {
            $result = false;
        } else {
            // Bind the object with the row and return.
            $result = $this->bind($row);
        }

        // Post-processing by observers
        $event = AbstractEvent::create(
            'onTableAfterLoad',
            [
                'subject' => $this,
                'result'  => &$result,
                'row'     => $row,
            ]
        );
        $this->getDispatcher()->dispatch('onTableAfterLoad', $event);

        return $result;
    }

    /**
     * Method to perform sanity checks on the Table instance properties to ensure they are safe to store in the database.
     *
     * Child classes should override this method to make sure the data they are storing in the database is safe and as expected before storage.
     *
     * @return  boolean  True if the instance is sane and able to be stored in the database.
     *
     * @since   1.7.0
     */
    public function check()
    {
        // Post-processing by observers
        $event = AbstractEvent::create(
            'onTableCheck',
            [
                'subject' => $this,
            ]
        );
        $this->getDispatcher()->dispatch('onTableCheck', $event);

        return true;
    }

    /**
     * Method to store a row in the database from the Table instance properties.
     *
     * If a primary key value is set the row with that primary key value will be updated with the instance property values.
     * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since   1.7.0
     */
    public function store($updateNulls = false)
    {
        $result = true;

        $k = $this->_tbl_keys;

        // Pre-processing by observers
        $event = AbstractEvent::create(
            'onTableBeforeStore',
            [
                'subject'     => $this,
                'updateNulls' => $updateNulls,
                'k'           => $k,
            ]
        );
        $this->getDispatcher()->dispatch('onTableBeforeStore', $event);

        $currentAssetId = 0;

        if (!empty($this->asset_id)) {
            $currentAssetId = $this->asset_id;
        }

        // The asset id field is managed privately by this class.
        if ($this->_trackAssets) {
            unset($this->asset_id);
        }

        // We have to unset typeAlias since updateObject / insertObject will try to insert / update all public variables...
        $typeAlias = $this->typeAlias;
        unset($this->typeAlias);

        try {
            // If a primary key exists update the object, otherwise insert it.
            if ($this->hasPrimaryKey()) {
                $this->_db->updateObject($this->_tbl, $this, $this->_tbl_keys, $updateNulls);
            } else {
                $this->_db->insertObject($this->_tbl, $this, $this->_tbl_keys[0]);
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            $result = false;
        }

        $this->typeAlias = $typeAlias;

        // If the table is not set to track assets return true.
        if ($this->_trackAssets) {
            if ($this->_locked) {
                $this->_unlock();
            }

            /*
             * Asset Tracking
             */
            $parentId = $this->_getAssetParentId();
            $name     = $this->_getAssetName();
            $title    = $this->_getAssetTitle();
            $asset    = new Asset($this->getDbo(), $this->getDispatcher());

            $asset->loadByName($name);

            // Re-inject the asset id.
            $this->asset_id = $asset->id;

            // Check for an error.
            $error = $asset->getError();

            if ($error) {
                $this->setError($error);

                return false;
            }

            // Specify how a new or moved node asset is inserted into the tree.
            if (empty($this->asset_id) || $asset->parent_id != $parentId) {
                $asset->setLocation($parentId, 'last-child');
            }

            // Prepare the asset to be stored.
            $asset->parent_id = $parentId;
            $asset->name      = $name;

            // Respect the table field limits
            $asset->title = StringHelper::substr($title, 0, 100);

            if ($this->_rules instanceof Rules) {
                $asset->rules = (string) $this->_rules;
            }

            if (!$asset->check() || !$asset->store()) {
                $this->setError($asset->getError());

                return false;
            }

            // Create an asset_id or heal one that is corrupted.
            if (empty($this->asset_id) || ($currentAssetId != $this->asset_id && !empty($this->asset_id))) {
                // Update the asset_id field in this table.
                $this->asset_id = (int) $asset->id;

                $query = $this->_db->getQuery(true)
                    ->update($this->_db->quoteName($this->_tbl))
                    ->set('asset_id = ' . (int) $this->asset_id);
                $this->appendPrimaryKeys($query);
                $this->_db->setQuery($query)->execute();
            }
        }

        // Post-processing by observers
        $event = AbstractEvent::create(
            'onTableAfterStore',
            [
                'subject' => $this,
                'result'  => &$result,
            ]
        );
        $this->getDispatcher()->dispatch('onTableAfterStore', $event);

        return $result;
    }

    /**
     * Method to provide a shortcut to binding, checking and storing a Table instance to the database table.
     *
     * The method will check a row in once the data has been stored and if an ordering filter is present will attempt to reorder
     * the table rows based on the filter.  The ordering filter is an instance property name.  The rows that will be reordered
     * are those whose value matches the Table instance for the property specified.
     *
     * @param   array|object  $src             An associative array or object to bind to the Table instance.
     * @param   string        $orderingFilter  Filter for the order updating
     * @param   array|string  $ignore          An optional array or space separated list of properties to ignore while binding.
     *
     * @return  boolean  True on success.
     *
     * @since   1.7.0
     */
    public function save($src, $orderingFilter = '', $ignore = '')
    {
        // Attempt to bind the source to the instance.
        if (!$this->bind($src, $ignore)) {
            return false;
        }

        // Run any sanity checks on the instance and verify that it is ready for storage.
        if (!$this->check()) {
            return false;
        }

        // Attempt to store the properties to the database table.
        if (!$this->store()) {
            return false;
        }

        // Attempt to check the row in, just in case it was checked out.
        if (!$this->checkIn()) {
            return false;
        }

        // If an ordering filter is set, attempt reorder the rows in the table based on the filter and value.
        if ($orderingFilter) {
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
     * @since   1.7.0
     * @throws  \UnexpectedValueException
     */
    public function delete($pk = null)
    {
        if (\is_null($pk)) {
            $pk = [];

            foreach ($this->_tbl_keys as $key) {
                $pk[$key] = $this->$key;
            }
        } elseif (!\is_array($pk)) {
            $pk = [$this->_tbl_key => $pk];
        }

        foreach ($this->_tbl_keys as $key) {
            $pk[$key] = \is_null($pk[$key]) ? $this->$key : $pk[$key];

            if ($pk[$key] === null) {
                throw new \UnexpectedValueException('Null primary key not allowed.');
            }

            $this->$key = $pk[$key];
        }

        // Pre-processing by observers
        $event = AbstractEvent::create(
            'onTableBeforeDelete',
            [
                'subject' => $this,
                'pk'      => $pk,
            ]
        );
        $this->getDispatcher()->dispatch('onTableBeforeDelete', $event);

        // If tracking assets, remove the asset first.
        if ($this->_trackAssets) {
            // Get the asset name
            $name  = $this->_getAssetName();
            $asset = new Asset($this->getDbo(), $this->getDispatcher());

            if ($asset->loadByName($name)) {
                if (!$asset->delete()) {
                    $this->setError($asset->getError());

                    return false;
                }
            }
        }

        // Delete the row by primary key.
        $query = $this->_db->getQuery(true)
            ->delete($this->_db->quoteName($this->_tbl));
        $this->appendPrimaryKeys($query, $pk);

        $this->_db->setQuery($query);

        // Check for a database error.
        $this->_db->execute();

        // Post-processing by observers
        $event = AbstractEvent::create(
            'onTableAfterDelete',
            [
                'subject' => $this,
                'pk'      => $pk,
            ]
        );
        $this->getDispatcher()->dispatch('onTableAfterDelete', $event);

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
     * @since   1.7.0
     * @throws  \UnexpectedValueException
     */
    public function checkOut($userId, $pk = null)
    {
        // Pre-processing by observers
        $event = AbstractEvent::create(
            'onTableBeforeCheckout',
            [
                'subject' => $this,
                'userId'  => $userId,
                'pk'      => $pk,
            ]
        );
        $this->getDispatcher()->dispatch('onTableBeforeCheckout', $event);

        // If there is no checked_out or checked_out_time field, just return true.
        if (!$this->hasField('checked_out') || !$this->hasField('checked_out_time')) {
            return true;
        }

        if (\is_null($pk)) {
            $pk = [];

            foreach ($this->_tbl_keys as $key) {
                $pk[$key] = $this->$key;
            }
        } elseif (!\is_array($pk)) {
            $pk = [$this->_tbl_key => $pk];
        }

        foreach ($this->_tbl_keys as $key) {
            $pk[$key] = \is_null($pk[$key]) ? $this->$key : $pk[$key];

            if ($pk[$key] === null) {
                throw new \UnexpectedValueException('Null primary key not allowed.');
            }
        }

        // Get column names.
        $checkedOutField     = $this->getColumnAlias('checked_out');
        $checkedOutTimeField = $this->getColumnAlias('checked_out_time');

        // Get the current time in the database format.
        $time = Factory::getDate()->toSql();

        // Check the row out by primary key.
        $query = $this->_db->getQuery(true)
            ->update($this->_db->quoteName($this->_tbl))
            ->set($this->_db->quoteName($checkedOutField) . ' = ' . (int) $userId)
            ->set($this->_db->quoteName($checkedOutTimeField) . ' = ' . $this->_db->quote($time));
        $this->appendPrimaryKeys($query, $pk);
        $this->_db->setQuery($query);
        $this->_db->execute();

        // Set table values in the object.
        $this->$checkedOutField      = (int) $userId;
        $this->$checkedOutTimeField  = $time;

        // Post-processing by observers
        $event = AbstractEvent::create(
            'onTableAfterCheckout',
            [
                'subject' => $this,
                'userId'  => $userId,
                'pk'      => $pk,
            ]
        );
        $this->getDispatcher()->dispatch('onTableAfterCheckout', $event);

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
     * @since   1.7.0
     * @throws  \UnexpectedValueException
     */
    public function checkIn($pk = null)
    {
        // Pre-processing by observers
        $event = AbstractEvent::create(
            'onTableBeforeCheckin',
            [
                'subject' => $this,
                'pk'      => $pk,
            ]
        );
        $this->getDispatcher()->dispatch('onTableBeforeCheckin', $event);

        // If there is no checked_out or checked_out_time field, just return true.
        if (!$this->hasField('checked_out') || !$this->hasField('checked_out_time')) {
            return true;
        }

        if (\is_null($pk)) {
            $pk = [];

            foreach ($this->_tbl_keys as $key) {
                $pk[$this->$key] = $this->$key;
            }
        } elseif (!\is_array($pk)) {
            $pk = [$this->_tbl_key => $pk];
        }

        foreach ($this->_tbl_keys as $key) {
            $pk[$key] = empty($pk[$key]) ? $this->$key : $pk[$key];

            if ($pk[$key] === null) {
                throw new \UnexpectedValueException('Null primary key not allowed.');
            }
        }

        // Get column names.
        $checkedOutField     = $this->getColumnAlias('checked_out');
        $checkedOutTimeField = $this->getColumnAlias('checked_out_time');

        $nullDate = $this->_supportNullValue ? 'NULL' : $this->_db->quote($this->_db->getNullDate());
        $nullID   = $this->_supportNullValue ? 'NULL' : '0';

        // Check the row in by primary key.
        $query = $this->_db->getQuery(true)
            ->update($this->_db->quoteName($this->_tbl))
            ->set($this->_db->quoteName($checkedOutField) . ' = ' . $nullID)
            ->set($this->_db->quoteName($checkedOutTimeField) . ' = ' . $nullDate);
        $this->appendPrimaryKeys($query, $pk);
        $this->_db->setQuery($query);

        // Check for a database error.
        $this->_db->execute();

        // Set table values in the object.
        $this->$checkedOutField     = $this->_supportNullValue ? null : 0;
        $this->$checkedOutTimeField = $this->_supportNullValue ? null : '';

        // Post-processing by observers
        $event = AbstractEvent::create(
            'onTableAfterCheckin',
            [
                'subject' => $this,
                'pk'      => $pk,
            ]
        );
        $this->getDispatcher()->dispatch('onTableAfterCheckin', $event);
        $this->getDispatcher()->dispatch('onAfterCheckin', new GlobalAfterCheckinEvent('onAfterCheckin', [
            'subject' => $this->_tbl,
        ]));

        return true;
    }

    /**
     * Validate that the primary key has been set.
     *
     * @return  boolean  True if the primary key(s) have been set.
     *
     * @since   3.1.4
     */
    public function hasPrimaryKey()
    {
        if ($this->_autoincrement) {
            $empty = true;

            foreach ($this->_tbl_keys as $key) {
                $empty = $empty && empty($this->$key);
            }
        } else {
            $query = $this->_db->getQuery(true)
                ->select('COUNT(*)')
                ->from($this->_db->quoteName($this->_tbl));
            $this->appendPrimaryKeys($query);

            $this->_db->setQuery($query);
            $count = $this->_db->loadResult();

            if ($count == 1) {
                $empty = false;
            } else {
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
     * @since   1.7.0
     * @throws  \UnexpectedValueException
     */
    public function hit($pk = null)
    {
        // Pre-processing by observers
        $event = AbstractEvent::create(
            'onTableBeforeHit',
            [
                'subject' => $this,
                'pk'      => $pk,
            ]
        );
        $this->getDispatcher()->dispatch('onTableBeforeHit', $event);

        // If there is no hits field, just return true.
        if (!$this->hasField('hits')) {
            return true;
        }

        if (\is_null($pk)) {
            $pk = [];

            foreach ($this->_tbl_keys as $key) {
                $pk[$key] = $this->$key;
            }
        } elseif (!\is_array($pk)) {
            $pk = [$this->_tbl_key => $pk];
        }

        foreach ($this->_tbl_keys as $key) {
            $pk[$key] = \is_null($pk[$key]) ? $this->$key : $pk[$key];

            if ($pk[$key] === null) {
                throw new \UnexpectedValueException('Null primary key not allowed.');
            }
        }

        // Get column name.
        $hitsField = $this->getColumnAlias('hits');

        // Check the row in by primary key.
        $query = $this->_db->getQuery(true)
            ->update($this->_db->quoteName($this->_tbl))
            ->set($this->_db->quoteName($hitsField) . ' = (' . $this->_db->quoteName($hitsField) . ' + 1)');
        $this->appendPrimaryKeys($query, $pk);
        $this->_db->setQuery($query);
        $this->_db->execute();

        // Set table values in the object.
        $this->hits++;

        // Pre-processing by observers
        $event = AbstractEvent::create(
            'onTableAfterHit',
            [
                'subject' => $this,
                'pk'      => $pk,
            ]
        );
        $this->getDispatcher()->dispatch('onTableAfterHit', $event);

        return true;
    }

    /**
     * Method to determine if a row is checked out and therefore uneditable by a user.
     *
     * If the row is checked out by the same user, then it is considered not checked out -- as the user can still edit it.
     *
     * @param   integer  $with     The user ID to perform the match with, if an item is checked out by this user the function will return false.
     * @param   integer  $against  The user ID to perform the match against when the function is used as a static function.
     *
     * @return  boolean  True if checked out.
     *
     * @since   1.7.0
     */
    public function isCheckedOut($with = 0, $against = null)
    {
        // Handle the non-static case.
        if (isset($this) && ($this instanceof Table) && \is_null($against)) {
            $checkedOutField = $this->getColumnAlias('checked_out');
            $against         = $this->$checkedOutField;
        }

        // The item is not checked out or is checked out by the same user.
        if (!$against || ($against == $with)) {
            return false;
        }

        // This last check can only be relied on if tracking session metadata
        if (Factory::getApplication()->get('session_metadata', true)) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('COUNT(userid)')
                ->from($db->quoteName('#__session'))
                ->where($db->quoteName('userid') . ' = ' . (int) $against);
            $db->setQuery($query);
            $checkedOut = (bool) $db->loadResult();

            // If a session exists for the user then it is checked out.
            return $checkedOut;
        }

        // Assume if we got here that there is a value in the checked out column but it doesn't match the given user
        return true;
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
     * @since   1.7.0
     * @throws  \UnexpectedValueException
     */
    public function getNextOrder($where = '')
    {
        // Check if there is an ordering field set
        if (!$this->hasField('ordering')) {
            throw new \UnexpectedValueException(\sprintf('%s does not support ordering.', \get_class($this)));
        }

        // Get the largest ordering value for a given where clause.
        $query = $this->_db->getQuery(true)
            ->select('MAX(' . $this->_db->quoteName($this->getColumnAlias('ordering')) . ')')
            ->from($this->_db->quoteName($this->_tbl));

        if ($where) {
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
     * @since   3.1.4
     */
    public function getPrimaryKey(array $keys = [])
    {
        foreach ($this->_tbl_keys as $key) {
            if (!isset($keys[$key])) {
                if (!empty($this->$key)) {
                    $keys[$key] = $this->$key;
                }
            }
        }

        return $keys;
    }

    /**
     * Method to compact the ordering values of rows in a group of rows defined by an SQL WHERE clause.
     *
     * @param   string|string[]  $where  WHERE clause to use for limiting the selection of rows to compact the ordering values.
     *
     * @return  mixed  Boolean  True on success.
     *
     * @since   1.7.0
     * @throws  \UnexpectedValueException
     */
    public function reorder($where = '')
    {
        // Check if there is an ordering field set
        if (!$this->hasField('ordering')) {
            throw new \UnexpectedValueException(\sprintf('%s does not support ordering.', \get_class($this)));
        }

        $quotedOrderingField = $this->_db->quoteName($this->getColumnAlias('ordering'));

        $subquery = $this->_db->getQuery(true)
            ->from($this->_db->quoteName($this->_tbl))
            ->selectRowNumber($quotedOrderingField, 'new_ordering');

        $query = $this->_db->getQuery(true)
            ->update($this->_db->quoteName($this->_tbl))
            ->set($quotedOrderingField . ' = sq.new_ordering');

        $innerOn = [];

        // Get the primary keys for the selection.
        foreach ($this->_tbl_keys as $i => $k) {
            $subquery->select($this->_db->quoteName($k, 'pk__' . $i));
            $innerOn[] = $this->_db->quoteName($k) . ' = sq.' . $this->_db->quoteName('pk__' . $i);
        }

        // Setup the extra where and ordering clause data.
        if ($where) {
            $subquery->where($where);
            $query->where($where);
        }

        $subquery->where($quotedOrderingField . ' >= 0');
        $query->where($quotedOrderingField . ' >= 0');
        $query->innerJoin('(' . (string) $subquery . ') AS sq ');

        foreach ($innerOn as $key) {
            $query->where($key);
        }

        // Pre-processing by observers
        $event = AbstractEvent::create(
            'onTableBeforeReorder',
            [
                'subject' => $this,
                'query'   => $query,
                'where'   => $where,
            ]
        );
        $this->getDispatcher()->dispatch('onTableBeforeReorder', $event);

        $this->_db->setQuery($query);
        $this->_db->execute();

        // Post-processing by observers
        $event = AbstractEvent::create(
            'onTableAfterReorder',
            [
                'subject' => $this,
                'where'   => $where,
            ]
        );
        $this->getDispatcher()->dispatch('onTableAfterReorder', $event);

        return true;
    }

    /**
     * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
     *
     * Negative numbers move the row up in the sequence and positive numbers move it down.
     *
     * @param   integer          $delta  The direction and magnitude to move the row in the ordering sequence.
     * @param   string|string[]  $where  WHERE clause to use for limiting the selection of rows to compact the ordering values.
     *
     * @return  boolean  True on success.
     *
     * @since   1.7.0
     * @throws  \UnexpectedValueException
     */
    public function move($delta, $where = '')
    {
        // Check if there is an ordering field set
        if (!$this->hasField('ordering')) {
            throw new \UnexpectedValueException(\sprintf('%s does not support ordering.', \get_class($this)));
        }

        $orderingField       = $this->getColumnAlias('ordering');
        $quotedOrderingField = $this->_db->quoteName($orderingField);

        // If the change is none, do nothing.
        if (empty($delta)) {
            return true;
        }

        $row   = null;
        $query = $this->_db->getQuery(true);

        // Select the primary key and ordering values from the table.
        $query->select(implode(',', $this->_tbl_keys) . ', ' . $quotedOrderingField)
            ->from($this->_db->quoteName($this->_tbl));

        // If the movement delta is negative move the row up.
        if ($delta < 0) {
            $query->where($quotedOrderingField . ' < ' . (int) $this->$orderingField)
                ->order($quotedOrderingField . ' DESC');
        } elseif ($delta > 0) {
            // If the movement delta is positive move the row down.
            $query->where($quotedOrderingField . ' > ' . (int) $this->$orderingField)
                ->order($quotedOrderingField . ' ASC');
        }

        // Add the custom WHERE clause if set.
        if ($where) {
            $query->where($where);
        }

        // Pre-processing by observers
        $event = AbstractEvent::create(
            'onTableBeforeMove',
            [
                'subject' => $this,
                'query'   => $query,
                'delta'   => $delta,
                'where'   => $where,
            ]
        );
        $this->getDispatcher()->dispatch('onTableBeforeMove', $event);

        // Select the first row with the criteria.
        $query->setLimit(1);
        $this->_db->setQuery($query);
        $row = $this->_db->loadObject();

        // If a row is found, move the item.
        if (!empty($row)) {
            // Update the ordering field for this instance to the row's ordering value.
            $query->clear()
                ->update($this->_db->quoteName($this->_tbl))
                ->set($quotedOrderingField . ' = ' . (int) $row->$orderingField);
            $this->appendPrimaryKeys($query);
            $this->_db->setQuery($query);
            $this->_db->execute();

            // Update the ordering field for the row to this instance's ordering value.
            $query->clear()
                ->update($this->_db->quoteName($this->_tbl))
                ->set($quotedOrderingField . ' = ' . (int) $this->$orderingField);
            $this->appendPrimaryKeys($query, $row);
            $this->_db->setQuery($query);
            $this->_db->execute();

            // Update the instance value.
            $this->$orderingField = $row->$orderingField;
        } else {
            // Update the ordering field for this instance.
            $query->clear()
                ->update($this->_db->quoteName($this->_tbl))
                ->set($quotedOrderingField . ' = ' . (int) $this->$orderingField);
            $this->appendPrimaryKeys($query);
            $this->_db->setQuery($query);
            $this->_db->execute();
        }

        // Post-processing by observers
        $event = AbstractEvent::create(
            'onTableAfterMove',
            [
                'subject' => $this,
                'row'     => $row,
                'delta'   => $delta,
                'where'   => $where,
            ]
        );
        $this->getDispatcher()->dispatch('onTableAfterMove', $event);

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
     * @since   1.7.0
     */
    public function publish($pks = null, $state = 1, $userId = 0)
    {
        // Sanitize input
        $userId = (int) $userId;
        $state  = (int) $state;

        // Pre-processing by observers
        $event = AbstractEvent::create(
            'onTableBeforePublish',
            [
                'subject' => $this,
                'pks'     => $pks,
                'state'   => $state,
                'userId'  => $userId,
            ]
        );
        $this->getDispatcher()->dispatch('onTableBeforePublish', $event);

        if (!\is_null($pks)) {
            if (!\is_array($pks)) {
                $pks = [$pks];
            }

            foreach ($pks as $key => $pk) {
                if (!\is_array($pk)) {
                    $pks[$key] = [$this->_tbl_key => $pk];
                }
            }
        }

        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks)) {
            $pk = [];

            foreach ($this->_tbl_keys as $key) {
                if ($this->$key) {
                    $pk[$key] = $this->$key;
                } else {
                    // We don't have a full primary key - return false
                    $this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

                    return false;
                }
            }

            $pks = [$pk];
        }

        $publishedField  = $this->getColumnAlias('published');
        $checkedOutField = $this->getColumnAlias('checked_out');

        foreach ($pks as $pk) {
            // Update the publishing state for rows with the given primary keys.
            $query = $this->_db->getQuery(true)
                ->update($this->_db->quoteName($this->_tbl))
                ->set($this->_db->quoteName($publishedField) . ' = ' . (int) $state);

            // If publishing, set published date/time if not previously set
            if ($state && $this->hasField('publish_up') && (int) $this->publish_up == 0) {
                $nowDate = $this->_db->quote(Factory::getDate()->toSql());
                $query->set($this->_db->quoteName($this->getColumnAlias('publish_up')) . ' = ' . $nowDate);
            }

            // Determine if there is checkin support for the table.
            if ($this->hasField('checked_out') || $this->hasField('checked_out_time')) {
                $query->where(
                    '('
                        . $this->_db->quoteName($checkedOutField) . ' = 0'
                        . ' OR ' . $this->_db->quoteName($checkedOutField) . ' = ' . (int) $userId
                        . ' OR ' . $this->_db->quoteName($checkedOutField) . ' IS NULL'
                        . ')'
                );
                $checkin = true;
            } else {
                $checkin = false;
            }

            // Build the WHERE clause for the primary keys.
            $this->appendPrimaryKeys($query, $pk);

            $this->_db->setQuery($query);

            try {
                $this->_db->execute();
            } catch (\RuntimeException $e) {
                $this->setError($e->getMessage());

                return false;
            }

            // If checkin is supported and all rows were adjusted, check them in.
            if ($checkin && (\count($pks) == $this->_db->getAffectedRows())) {
                $this->checkIn($pk);
            }

            // If the Table instance value is in the list of primary keys that were set, set the instance.
            $ours = true;

            foreach ($this->_tbl_keys as $key) {
                if ($this->$key != $pk[$key]) {
                    $ours = false;
                }
            }

            if ($ours) {
                $this->$publishedField = $state;
            }
        }

        $this->setError('');

        // Pre-processing by observers
        $event = AbstractEvent::create(
            'onTableAfterPublish',
            [
                'subject' => $this,
                'pks'     => $pks,
                'state'   => $state,
                'userId'  => $userId,
            ]
        );
        $this->getDispatcher()->dispatch('onTableAfterPublish', $event);

        return true;
    }

    /**
     * Method to lock the database table for writing.
     *
     * @return  boolean  True on success.
     *
     * @since   1.7.0
     * @throws  \RuntimeException
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
     * built in \Joomla functions.
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
        $return = $this->_columnAlias[$column] ?? $column;

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
        // Sanitize the column name alias
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
     * @since   1.7.0
     */
    protected function _unlock()
    {
        if ($this->_locked) {
            $this->_db->unlockTables();
            $this->_locked = false;
        }

        return true;
    }

    /**
     * Check if the record has a property (applying a column alias if it exists)
     *
     * @param   string  $key  key to be checked
     *
     * @return  boolean
     *
     * @since   3.9.11
     */
    public function hasField($key)
    {
        $key = $this->getColumnAlias($key);

        return property_exists($this, $key);
    }
}
