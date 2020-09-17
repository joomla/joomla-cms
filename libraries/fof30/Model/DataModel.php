<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model;

defined('_JEXEC') || die;

use Exception;
use FOF30\Container\Container;
use FOF30\Controller\Exception\LockedRecord;
use FOF30\Date\Date;
use FOF30\Event\Dispatcher;
use FOF30\Event\Observer;
use FOF30\Model\DataModel\Collection as DataCollection;
use FOF30\Model\DataModel\Exception\BaseException;
use FOF30\Model\DataModel\Exception\CannotLockNotLoadedRecord;
use FOF30\Model\DataModel\Exception\InvalidSearchMethod;
use FOF30\Model\DataModel\Exception\NoAssetKey;
use FOF30\Model\DataModel\Exception\NoContentType;
use FOF30\Model\DataModel\Exception\NoItemsFound;
use FOF30\Model\DataModel\Exception\NoTableColumns;
use FOF30\Model\DataModel\Exception\RecordNotLoaded;
use FOF30\Model\DataModel\Exception\SpecialColumnMissing;
use FOF30\Model\DataModel\Relation\Exception\RelationNotFound;
use FOF30\Model\DataModel\RelationManager;
use FOF30\Utils\ArrayHelper;
use InvalidArgumentException;
use JDatabaseDriver;
use JDatabaseQuery;
use JLoader;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Asset;
use Joomla\CMS\Table\ContentType;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\UCM\UCMContent;
use RuntimeException;
use UnexpectedValueException;

/**
 * Data-aware model, implementing a convenient ORM
 *
 * Type hinting -- start
 *
 * @method $this hasOne() hasOne(string $name, string $foreignModelClass = null, string $localKey = null, string $foreignKey = null)
 * @method $this belongsTo() belongsTo(string $name, string $foreignModelClass = null, string $localKey = null, string $foreignKey = null)
 * @method $this hasMany() hasMany(string $name, string $foreignModelClass = null, string $localKey = null, string $foreignKey = null)
 * @method $this belongsToMany() belongsToMany(string $name, string $foreignModelClass = null, string $localKey = null, string $foreignKey = null, string $pivotTable = null, string $pivotLocalKey = null, string $pivotForeignKey = null)
 *
 * @method $this filter_order() filter_order(string $orderingField)
 * @method $this filter_order_Dir() filter_order_Dir(string $direction)
 * @method $this limit() limit(int $limit)
 * @method $this limitstart() limitstart(int $limitStart)
 * @method $this enabled() enabled(int $enabled)
 * @method DataModel getNew() getNew(string $relationName)
 *
 * @property  int    $enabled      Publish status of this record
 * @property  int    $ordering     Sort ordering of this record
 * @property  int    $created_by   ID of the user who created this record
 * @property  string $created_on   Date/time stamp of record creation
 * @property  int    $modified_by  ID of the user who modified this record
 * @property  string $modified_on  Date/time stamp of record modification
 * @property  int    $locked_by    ID of the user who locked this record
 * @property  string $locked_on    Date/time stamp of record locking
 *
 * Type hinting -- end
 */
class DataModel extends Model implements TableInterface
{
	/** @var   array  A list of tables in the database */
	protected static $tableCache = [];

	/** @var   array  A list of table fields, keyed per table */
	protected static $tableFieldCache = [];

	/** @var   array  A list of permutations of the prefix with upper/lowercase letters */
	protected static $prefixCasePermutations = [];

	/** @var   array  Table field name aliases, defined as aliasFieldName => actualFieldName */
	protected $aliasFields = [];

	/** @var   boolean  Should I run automatic checks on the table data? */
	protected $autoChecks = true;

	/** @var   boolean  Should I auto-fill the fields of the model object when constructing it? */
	protected $autoFill = false;

	/** @var   Dispatcher  An event dispatcher for model behaviours */
	protected $behavioursDispatcher = null;

	/** @var   JDatabaseDriver  The database driver for this model */
	protected $dbo = null;

	/** @var   array  Which fields should be exempt from automatic checks when autoChecks is enabled */
	protected $fieldsSkipChecks = [];

	/** @var   array  Which fields should be auto-filled from the model state (by extent, the request)? */
	protected $fillable = [];

	/** @var   array  Which fields should never be auto-filled from the model state (by extent, the request)? */
	protected $guarded = [];

	/** @var   string  The identity field's name */
	protected $idFieldName = '';

	/** @var   array  A hash array with the table fields we know about and their information. Each key is the field name, the value is the field information */
	protected $knownFields = [];

	/** @var   array  The data of the current record */
	protected $recordData = [];

	/** @var   boolean  What will delete() do? True: trash (enabled set to -2); false: hard delete (remove from database) */
	protected $softDelete = false;

	/** @var   string  The name of the database table we connect to */
	protected $tableName = '';

	/** @var   array  A collection of custom, additional where clauses to apply during buildQuery */
	protected $whereClauses = [];

	/** @var   RelationManager  The relation manager of this model */
	protected $relationManager = null;

	/** @var   array  A list of all eager loaded relations and their attached callbacks */
	protected $eagerRelations = [];

	/** @var   array  A list of the relation filter definitions for this model */
	protected $relationFilters = [];

	/** @var   array  A list of the relations which will be auto-touched by save() and touch() methods */
	protected $touches = [];

	/** @var bool Should rows be tracked as ACL assets? */
	protected $_trackAssets = false;

	/** @var bool Does the resource support joomla tags? */
	protected $_has_tags = false;

	/** @var  Rules  The rules associated with this record. */
	protected $_rules;

	/** @var  string  The UCM content type (typically: com_something.viewname, e.g. com_foobar.items) */
	protected $contentType = null;

	/** @var  array  Shared parameters for behaviors */
	protected $_behaviorParams = [];

	/**
	 * The asset key for items in this table. It's usually something in the
	 * com_example.viewname format. They asset name will be this key appended
	 * with the item's ID, e.g. com_example.viewname.123
	 *
	 * @var    string
	 */
	protected $_assetKey = '';

	/**
	 * Public constructor. Overrides the parent constructor, adding support for database-aware models.
	 *
	 * You can use the $config array to pass some configuration values to the object:
	 *
	 * tableName             String   The name of the database table to use. Default: #__appName_viewNamePlural (Ruby
	 * on Rails convention) idFieldName           String   The table key field name. Default:
	 * appName_viewNameSingular_id (Ruby on Rails convention) knownFields           Array    The known fields in the
	 * table. Default: read from the table itself autoChecks            Boolean  Should I turn on automatic data
	 * validation checks? fieldsSkipChecks      Array    List of fields which should not participate in automatic data
	 * validation checks. aliasFields           Array    Associative array of "magic" field aliases.
	 * behavioursDispatcher  EventDispatcher  The model behaviours event dispatcher. behaviourObservers    Array    The
	 * model behaviour observers to attach to the behavioursDispatcher. behaviours            Array    A list of
	 * behaviour names to instantiate and attach to the behavioursDispatcher. fillable_fields       Array    Which
	 * fields should be auto-filled from the model state (by extent, the request)? guarded_fields        Array    Which
	 * fields should never be auto-filled from the model state (by extent, the request)? relations             Array    (hashed)  The relations to autoload on model creation. contentType           String   The UCM content type, e.g. "com_foobar.items"
	 *
	 * Setting either fillable_fields or guarded_fields turns on automatic filling of fields in the constructor. If
	 * both
	 * are set only guarded_fields is taken into account. Fields are not filled automatically outside the constructor.
	 *
	 * @param   Container  $container  The configuration variables to this model
	 * @param   array      $config     Configuration values for this model
	 *
	 * @throws NoTableColumns
	 * @see Model::__construct()
	 *
	 */
	public function __construct(Container $container, array $config = [])
	{
		// First call the parent constructor.
		parent::__construct($container, $config);

		// Should I use a different database object?
		$this->dbo = $container->db;

		// Do I have a table name?
		if (isset($config['tableName']))
		{
			$this->tableName = $config['tableName'];
		}
		elseif (empty($this->tableName))
		{
			// The table name is by default: #__appName_viewNamePlural (Ruby on Rails convention)
			$viewPlural      = $container->inflector->pluralize($this->getName());
			$this->tableName = '#__' . strtolower($this->container->bareComponentName) . '_' . strtolower($viewPlural);
		}

		// Do I have a table key name?
		if (isset($config['idFieldName']))
		{
			$this->idFieldName = $config['idFieldName'];
		}
		elseif (empty($this->idFieldName))
		{
			// The default ID field is: appName_viewNameSingular_id (Ruby on Rails convention)
			$viewSingular      = $container->inflector->singularize($this->getName());
			$this->idFieldName = strtolower($this->container->bareComponentName) . '_' . strtolower($viewSingular) . '_id';
		}

		// Do I have a list of known fields?
		if (isset($config['knownFields']) && !empty($config['knownFields']))
		{
			if (!is_array($config['knownFields']))
			{
				$config['knownFields'] = explode(',', $config['knownFields']);
			}

			$this->knownFields = $config['knownFields'];
		}
		else
		{
			// By default the known fields are fetched from the table itself (slow!)
			$this->knownFields = $this->getTableFields();
		}

		if (empty($this->knownFields))
		{
			throw new NoTableColumns(sprintf('Model %s could not fetch column list for the table %s', $this->getName(), $this->tableName));
		}

		// Should I turn on autoChecks?
		if (isset($config['autoChecks']))
		{
			if (!is_bool($config['autoChecks']))
			{
				$config['autoChecks'] = strtolower($config['autoChecks']);
				$config['autoChecks'] = in_array($config['autoChecks'], ['yes', 'true', 'on', 1]);
			}

			$this->autoChecks = $config['autoChecks'];
		}

		// Should I exempt fields from autoChecks?
		if (isset($config['fieldsSkipChecks']))
		{
			if (!is_array($config['fieldsSkipChecks']))
			{
				$config['fieldsSkipChecks'] = explode(',', $config['fieldsSkipChecks']);
				$config['fieldsSkipChecks'] = array_map(function ($x) {
					return trim($x);
				}, $config['fieldsSkipChecks']);
			}

			$this->fieldsSkipChecks = $config['fieldsSkipChecks'];
		}

		// Do I have alias fields?
		if (isset($config['aliasFields']))
		{
			$this->aliasFields = $config['aliasFields'];
		}

		// Do I have a behaviours dispatcher?
		if (isset($config['behavioursDispatcher']) && ($config['behavioursDispatcher'] instanceof Dispatcher))
		{
			$this->behavioursDispatcher = $config['behavioursDispatcher'];
		}
		// Otherwise create the model behaviours dispatcher
		else
		{
			$this->behavioursDispatcher = new Dispatcher($this->container);
		}

		// Do I have an array of behaviour observers
		if (isset($config['behaviourObservers']) && is_array($config['behaviourObservers']))
		{
			foreach ($config['behaviourObservers'] as $observer)
			{
				$this->behavioursDispatcher->attach($observer);
			}
		}

		// Do I have a list of behaviours?
		if (isset($config['behaviours']) && is_array($config['behaviours']))
		{
			foreach ($config['behaviours'] as $behaviour)
			{
				$this->addBehaviour($behaviour);
			}
		}

		// Add extra behaviours
		foreach (['Created', 'Modified'] as $behaviour)
		{
			$this->addBehaviour($behaviour);
		}

		// Do I have a list of fillable fields?
		if (isset($config['fillable_fields']) && !empty($config['fillable_fields']))
		{
			if (!is_array($config['fillable_fields']))
			{
				$config['fillable_fields'] = explode(',', $config['fillable_fields']);
				$config['fillable_fields'] = array_map(function ($x) {
					return trim($x);
				}, $config['fillable_fields']);
			}

			$this->fillable = [];
			$this->autoFill = true;

			foreach ($config['fillable_fields'] as $field)
			{
				if (array_key_exists($field, $this->knownFields))
				{
					$this->fillable[] = $field;
				}
				elseif (isset($this->aliasFields[$field]))
				{
					$this->fillable[] = $this->aliasFields[$field];
				}
			}
		}

		// Do I have a list of guarded fields?
		if (isset($config['guarded_fields']) && !empty($config['guarded_fields']))
		{
			if (!is_array($config['guarded_fields']))
			{
				$config['guarded_fields'] = explode(',', $config['guarded_fields']);
				$config['guarded_fields'] = array_map(function ($x) {
					return trim($x);
				}, $config['guarded_fields']);
			}

			$this->guarded  = [];
			$this->autoFill = true;

			foreach ($config['guarded_fields'] as $field)
			{
				if (array_key_exists($field, $this->knownFields))
				{
					$this->guarded[] = $field;
				}
				elseif (isset($this->aliasFields[$field]))
				{
					$this->guarded[] = $this->aliasFields[$field];
				}
			}
		}

		// If we are tracking assets, make sure an access field exists and initially set the default.
		$asset_id_field = $this->getFieldAlias('asset_id');
		$access_field   = $this->getFieldAlias('access');

		if (array_key_exists($asset_id_field, $this->knownFields))
		{
			$this->_trackAssets = true;
		}

		/**
		 * if ($this->_trackAssets && array_key_exists($access_field, $this->knownFields) && !($this->getState($access_field, null)))
		 * {
		 * $this->$access_field = (int) $this->container->platform->getConfig()->get('access');
		 * }
		 **/

		$assetKey = $this->container->componentName . '.' . strtolower($container->inflector->singularize($this->getName()));
		$this->setAssetKey($assetKey);

		// Set the UCM content type if applicable
		if (isset($config['contentType']))
		{
			$this->contentType = $config['contentType'];
		}

		// Do I have to auto-fill the fields?
		if ($this->autoFill)
		{
			// If I have guarded fields, I'll try to fill everything, using such fields as a "blacklist"
			if (!empty($this->guarded))
			{
				$fields = array_keys($this->knownFields);
			}
			else
			{
				// Otherwise I'll fill only the fillable ones (act like having a "whitelist")
				$fields = $this->fillable;
			}

			foreach ($fields as $field)
			{
				if (in_array($field, $this->guarded))
				{
					// Do not set guarded fields
					continue;
				}

				$stateValue = $this->getState($field, null);

				if (!is_null($stateValue))
				{
					$this->setFieldValue($field, $stateValue);
				}
			}
		}

		// Create a relation manager
		$this->relationManager = new RelationManager($this);

		// Do I have a list of relations?
		if (isset($config['relations']) && is_array($config['relations']))
		{
			foreach ($config['relations'] as $relConfig)
			{
				if (!is_array($relConfig))
				{
					continue;
				}

				$defaultRelConfig = [
					'type'              => 'hasOne',
					'foreignModelClass' => null,
					'localKey'          => null,
					'foreignKey'        => null,
					'pivotTable'        => null,
					'pivotLocalKey'     => null,
					'pivotForeignKey'   => null,
				];

				$relConfig = array_merge($defaultRelConfig, $relConfig);

				$this->relationManager->addRelation($relConfig['itemName'], $relConfig['type'], $relConfig['foreignModelClass'],
					$relConfig['localKey'], $relConfig['foreignKey'], $relConfig['pivotTable'],
					$relConfig['pivotLocalKey'], $relConfig['pivotForeignKey']);
			}
		}

		// Initialise the data model
		foreach ($this->knownFields as $fieldName => $information)
		{
			// Initialize only the null or not yet set records
			if (!isset($this->recordData[$fieldName]))
			{
				$this->recordData[$fieldName] = $information->Default;
			}
		}

		// Trigger the onAfterConstruct event. This allows you to set up model state etc.
		$this->triggerEvent('onAfterConstruct');
	}

	/**
	 * Magic caller. It works like the magic setter and returns ourselves for chaining. If no arguments are passed we'll
	 * only look for a scope filter.
	 *
	 * @param   string  $name
	 * @param   array   $arguments
	 *
	 * @return  static
	 */
	public function __call($name, $arguments)
	{
		// If no arguments are provided try mapping to the scopeSomething() method
		if (empty($arguments))
		{
			$methodName = 'scope' . ucfirst($name);
			if (method_exists($this, $methodName))
			{
				$this->{$methodName}();

				return $this;
			}
		}

		// Implements getNew($relationName)
		if (($name == 'getNew') && count($arguments))
		{
			return $this->relationManager->getNew($arguments[0]);
		}

		// Magically map relations to methods, e.g. $this->foobar will return the "foobar" relations' contents
		if ($this->relationManager->isMagicMethod($name))
		{
			return call_user_func_array([$this->relationManager, $name], $arguments);
		}

		// Otherwise call the parent
		return parent::__call($name, $arguments);
	}

	/**
	 * Magic checker on a property. It follows the same logic of the __get magic method, however, if nothing is found,
	 * it won't return the state of a variable (we are checking if a property is set)
	 *
	 * @param   string  $name  The name of the field to check
	 *
	 * @return  bool    Is the field set?
	 */
	public function __isset($name)
	{
		$value   = null;
		$isState = false;

		if (substr($name, 0, 3) == 'flt')
		{
			$isState = true;
			$name    = strtolower(substr($name, 3, 1)) . substr($name, 4);
		}

		// If $name is a field name, get its value
		if (!$isState && array_key_exists($name, $this->recordData))
		{
			$value = $this->getFieldValue($name);
		}
		elseif (!$isState && array_key_exists($name, $this->aliasFields) && array_key_exists($this->aliasFields[$name], $this->recordData))
		{
			$name = $this->aliasFields[$name];

			$value = $this->getFieldValue($name);
		}
		elseif ($this->relationManager->isMagicProperty($name))
		{
			$value = $this->relationManager->$name;
		}

		// As the core function isset, the property must exists AND must be NOT null
		return ($value !== null);
	}

	/**
	 * Magic getter. It will return the value of a field or, if no such field is found, the value of the relevant state
	 * variable.
	 *
	 * Tip: Trying to get fltSomething will always return the value of the state variable "something"
	 *
	 * Tip: You can define custom field getter methods as getFieldNameAttribute, where FieldName is your field's name,
	 *      in CamelCase (even if the field name itself is in snake_case).
	 *
	 * @param   string  $name  The name of the field / state variable to retrieve
	 *
	 * @return  static|mixed
	 */
	public function __get($name)
	{
		// Handle $this->input
		if ($name == 'input')
		{
			return $this->container->input;
		}

		$isState = false;

		if (substr($name, 0, 3) == 'flt')
		{
			$isState = true;
			$name    = strtolower(substr($name, 3, 1)) . substr($name, 4);
		}

		// If $name is a field name, get its value
		if (!$isState && array_key_exists($name, $this->recordData))
		{
			return $this->getFieldValue($name);
		}
		elseif (!$isState && array_key_exists($name, $this->aliasFields) && array_key_exists($this->aliasFields[$name], $this->recordData))
		{
			$name = $this->aliasFields[$name];

			return $this->getFieldValue($name);
		}
		elseif ($this->relationManager->isMagicProperty($name))
		{
			return $this->relationManager->$name;
		}
		// If $name is not a field name, get the value of a state variable
		else
		{
			return $this->getState($name);
		}
	}

	/**
	 * Magic setter. It will set the value of a field or the value of a dynamic scope filter, or the value of the
	 * relevant state variable.
	 *
	 * Tip: Trying to set fltSomething will always return the value of the state variable "something"
	 *
	 * Tip: Trying to set scopeSomething will always return the value of the dynamic scope filter "something"
	 *
	 * Tip: You can define custom field setter methods as setFieldNameAttribute, where FieldName is your field's name,
	 *      in CamelCase (even if the field name itself is in snake_case).
	 *
	 * @param   string  $name   The name of the field / scope / state variable to set
	 * @param   mixed   $value  The value to set
	 *
	 * @return  void
	 */
	public function __set($name, $value)
	{
		$isState = false;
		$isScope = false;

		if (substr($name, 0, 3) == 'flt')
		{
			$isState = true;
			$name    = strtolower(substr($name, 3, 1)) . substr($name, 4);
		}
		elseif (substr($name, 0, 5) == 'scope')
		{
			$isScope = true;
			$name    = strtolower(substr($name, 5, 1)) . substr($name, 5);
		}

		// If $name is a field name, set its value
		if (!$isState && !$isScope && array_key_exists($name, $this->recordData))
		{
			$this->setFieldValue($name, $value);
		}
		elseif (!$isState && !$isScope && array_key_exists($name, $this->aliasFields) && array_key_exists($this->aliasFields[$name], $this->recordData))
		{
			$name = $this->aliasFields[$name];
			$this->setFieldValue($name, $value);
		}
		// If $name is a dynamic scope filter, set its value
		elseif ($isScope || method_exists($this, 'scope' . ucfirst($name)))
		{
			$method = 'scope' . ucfirst($name);
			$this->{$method}($value);
		}
		// If $name is not a field name, set the value of a state variable
		else
		{
			$this->setState($name, $value);
		}
	}

	/**
	 * Returns a temporary instance of the model. Please note that this returns a _clone_ of the model object, not the
	 * original object. The new object is set up to not save its stats, ignore the request when getting state variables
	 * and comes with an empty state. The temporary object instance has its data reset as well.
	 *
	 * @return  static
	 */
	public function tmpInstance()
	{
		return parent::tmpInstance()->reset(true, true);
	}

	/**
	 * Adds a known field to the DataModel. This is only necessary if you are using a custom buildQuery with JOINs or
	 * field aliases. Please note that you need to make further modifications for bind() and save() to work in this
	 * case. Please refer to the documentation blocks of these methods for more information. It is generally considered
	 * a very BAD idea using JOINs instead of relations. It complicates your life and is bound to cause bugs that are
	 * very hard to track back.
	 *
	 * Basically, if you find yourself using this method you are probably doing something very wrong or very advanced.
	 * If you do not feel confident with debugging FOF code STOP WHATEVER YOU'RE DOING and rethink your Model. Why are
	 * you using a JOIN? If you want to filter the records by a field found in another table you can still use
	 * relations and whereHas with a callback.
	 *
	 * @param   string  $fieldName  The name of the field
	 * @param   mixed   $default    Default value, used by reset() (default: null)
	 * @param   string  $type       Database type for the field. If unsure use 'integer', 'float' or 'text'.
	 * @param   bool    $replace    Should we replace an existing known field definition?
	 *
	 * @return  static
	 */
	public function addKnownField($fieldName, $default = null, $type = 'integer', $replace = false)
	{
		if (array_key_exists($fieldName, $this->knownFields) && !$replace)
		{
			return $this;
		}

		$info = (object) [
			'Default' => $default,
			'Type'    => $type,
			'Null'    => 'YES',
		];

		$this->knownFields[$fieldName] = $info;

		// Initialize only the null or not yet set records
		if (!isset($this->recordData[$fieldName]))
		{
			$this->recordData[$fieldName] = $default;
		}

		return $this;
	}

	/**
	 * Get the columns from database table. For JTableInterface compatibility.
	 *
	 * @return  mixed  An array of the field names, or false if an error occurs.
	 */
	public function getFields()
	{
		return $this->getTableFields();
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
		// Make sure we have a list of tables in this db
		if (empty(static::$tableCache))
		{
			static::$tableCache = $this->getDbo()->getTableList();
		}

		if (!$tableName)
		{
			$tableName = $this->tableName;
		}

		// Try to load again column specifications if the table is not loaded OR if it's loaded and
		// the previous call returned an error
		if (!array_key_exists($tableName, static::$tableFieldCache) ||
			(isset(static::$tableFieldCache[$tableName]) && !static::$tableFieldCache[$tableName])
		)
		{
			// Lookup the fields for this table only once.
			$name = $tableName;

			$prefix = $this->getDbo()->getPrefix();

			if (substr($name, 0, 3) == '#__')
			{
				$checkName = $prefix . substr($name, 3);
			}
			else
			{
				$checkName = $name;
			}

			// Iterate through all lower/uppercase permutations of the prefix if we have a prefix with at least one uppercase letter
			if (!in_array($checkName, static::$tableCache) && preg_match('/[A-Z]/', $prefix) && (substr($name, 0, 3) == '#__'))
			{
				$prefixPermutations = $this->getPrefixCasePermutations();
				$partialCheckName   = substr($name, 3);

				foreach ($prefixPermutations as $permutatedPrefix)
				{
					$checkName = $permutatedPrefix . $partialCheckName;

					if (in_array($checkName, static::$tableCache))
					{
						break;
					}
				}
			}

			if (!in_array($checkName, static::$tableCache))
			{
				// The table doesn't exist. Return false.
				static::$tableFieldCache[$tableName] = false;
			}
			else
			{
				$fields = $this->getDbo()->getTableColumns($name, false);

				if (empty($fields))
				{
					$fields = false;
				}

				static::$tableFieldCache[$tableName] = $fields;
			}

			// PostgreSQL date type compatibility
			if (($this->getDbo()->name == 'postgresql') && (static::$tableFieldCache[$tableName] != false))
			{
				foreach (static::$tableFieldCache[$tableName] as $field)
				{
					if (strtolower($field->type) == 'timestamp without time zone')
					{
						if (stristr($field->Default, '\'::timestamp without time zone'))
						{
							[$date, ] = explode('::', $field->Default, 2);
							$field->Default = trim($date, "'");
						}
					}
				}
			}
		}

		return static::$tableFieldCache[$tableName];
	}

	/**
	 * Get the database connection associated with this data Model
	 *
	 * @return  JDatabaseDriver
	 */
	public function getDbo()
	{
		if (!is_object($this->dbo))
		{
			$this->dbo = $this->container->db;
		}

		return $this->dbo;
	}

	/**
	 * Returns the data currently bound to the model in an array format. Similar to toArray() but returns a copy instead
	 * of the internal table itself.
	 *
	 * @return array
	 */
	public function getData()
	{
		$ret = [];

		foreach ($this->knownFields as $field => $info)
		{
			$ret[$field] = $this->getFieldValue($field);
		}

		return $ret;
	}

	/**
	 * Return the value of the identity column of the currently loaded record
	 *
	 * @return   mixed
	 */
	public function getId()
	{
		return $this->{$this->idFieldName};
	}

	/**
	 * Returns the name of the table's id field (primary key) name
	 *
	 * @return  string
	 */
	public function getIdFieldName()
	{
		return $this->idFieldName;
	}

	/**
	 * Alias of getIdFieldName. Used for JTableInterface compatibility.
	 *
	 * @return  string  The name of the primary key for the table.
	 *
	 * @codeCoverageIgnore
	 */
	public function getKeyName()
	{
		return $this->getIdFieldName();
	}

	/**
	 * Returns the database table name this model talks to
	 *
	 * @return  string
	 */
	public function getTableName()
	{
		return $this->tableName;
	}

	/**
	 * Returns the value of a field. If a field is not set it uses the $default value. Automatically uses magic
	 * getter variables if required.
	 *
	 * @param   string  $name     The name of the field to retrieve
	 * @param   mixed   $default  Default value, if the field is not set and doesn't have a getter method
	 *
	 * @return  mixed  The value of the field
	 */
	public function getFieldValue($name, $default = null)
	{
		if (array_key_exists($name, $this->aliasFields))
		{
			$name = $this->aliasFields[$name];
		}

		if (!array_key_exists($name, $this->knownFields))
		{
			return $default;
		}

		if (!isset($this->recordData[$name]))
		{
			$this->recordData[$name] = $default;
		}

		return $this->recordData[$name];
	}

	/**
	 * Sets the value of a field.
	 *
	 * @param   string  $name   The name of the field to set
	 * @param   mixed   $value  The value to set it to
	 *
	 * @return  void
	 */
	public function setFieldValue($name, $value = null)
	{
		if (array_key_exists($name, $this->aliasFields))
		{
			$name = $this->aliasFields[$name];
		}

		if (array_key_exists($name, $this->knownFields))
		{
			$this->recordData[$name] = $value;
		}
	}

	/**
	 * Applies the getSomethingAttribute methods to $this->recordData, converting the database representation of the
	 * data to the record representation. $this->recordData is directly modified.
	 *
	 * @return  void
	 */
	public function databaseDataToRecordData()
	{
		foreach ($this->recordData as $name => $value)
		{
			$method = $this->container->inflector->camelize('get_' . $name . '_attribute');

			if (method_exists($this, $method))
			{
				$this->recordData[$name] = $this->{$method}($value);
			}
		}
	}

	/**
	 * Applies the setSomethingAttribute methods to $this->recordData, converting the record representation to database
	 * representation. It does not modify $this->recordData, it returns a copy of the data array.
	 *
	 * If you are using custom knownFields to cater for table JOINs you need to override this method and _remove_ the
	 * fields which do not belong to the table you are saving to. It's generally a bad idea using JOINs instead of
	 * relations. You have been warned!
	 *
	 * @return  array
	 */
	public function recordDataToDatabaseData()
	{
		$copy = array_merge($this->recordData);

		foreach ($copy as $name => $value)
		{
			$method = $this->container->inflector->camelize('set_' . $name . '_attribute');

			if (method_exists($this, $method))
			{
				$copy[$name] = $this->{$method}($value);
			}
		}

		return $copy;
	}

	/**
	 * Does this model know about a field called $fieldName? Automatically uses aliases when necessary.
	 *
	 * @param   string  $fieldName  Field name to check
	 *
	 * @return  boolean  True if the field exists
	 */
	public function hasField($fieldName)
	{
		$realFieldName = $this->getFieldAlias($fieldName);

		return array_key_exists($realFieldName, $this->knownFields);
	}

	/**
	 * Get the real name of a field name based on its alias. If the field is not aliased $alias is returned
	 *
	 * @param   string  $alias  The field to get an alias for
	 *
	 * @return  string  The real name of the field
	 */
	public function getFieldAlias($alias)
	{
		if (array_key_exists($alias, $this->aliasFields))
		{
			return $this->aliasFields[$alias];
		}
		else
		{
			return $alias;
		}
	}

	/**
	 * Returns an array mapping relation names to their local key field names.
	 *
	 * For example, given a relation "foobar" with local key name "example_item_id" it will return:
	 * ["foobar" => "example_item_id"]
	 *
	 * @return  array  Array of [relationName => fieldName] arrays
	 *
	 * @throws  RelationNotFound
	 */
	public function getRelationFields()
	{
		$fields = [];

		$relationNames = $this->relationManager->getRelationNames();

		if (empty($relationNames))
		{
			return $fields;
		}

		foreach ($relationNames as $name)
		{
			$fields[$name] = $this->relationManager->getRelation($name)->getLocalKey();
		}

		return $fields;
	}

	/**
	 * Returns the qualified foreign model name, in the format "componentName.modelName", for the specified model
	 * field. First it checks the relations you have defined. If none is found it will try to parse the field name as
	 * following the componentName_modelName_id naming convention (FOF best practice and recommendation).
	 *
	 * This feature is used by the Blade compiler.
	 *
	 * @param   string  $fieldName  The field name for which we'll get a foreign model name
	 *
	 * @return  string
	 */
	public function getForeignModelNameFor($fieldName)
	{
		// First look for a local field mapped in a relationship
		try
		{
			$relationMap  = $this->getRelationFields();
			$relationName = array_search($fieldName, $relationMap);

			if ($relationName !== false)
			{
				$model     = $this->relationManager->getRelation($relationName)->getForeignModel();
				$component = $model->getContainer()->componentName;
				$modelName = $model->getName();

				return "$component.$modelName";
			}
		}
		catch (RelationNotFound $e)
		{
			// Bummer. The relation cannot be found. I will fall back to parsing the field name.
		}

		// Do I have a field following the componentName_modelName_id format?
		$parts = explode('_', $fieldName);
		if ((substr($fieldName, -3) != '_id') || (count($parts) < 3))
		{
			throw new RuntimeException("Cannot determine the foreign model for local field '$fieldName'; it does not follow the expected component_model_id convention.");
		}

		$fieldName = substr($fieldName, 0, -3);
		[$component, $modelName] = explode('_', $fieldName, 2);
		$modelName = $this->container->inflector->camelize($modelName);

		return "$component.$modelName";
	}

	/**
	 * Save a record, creating it if it doesn't exist or updating it if it exists. By default it uses the currently set
	 * data, unless you provide a $data array.
	 *
	 * Special note if you are using a custom buildQuery with JOINs or field aliases:
	 * You will need to override the recordDataToDatabaseData method. Make sure that you _remove_ or rename any fields
	 * which do not exist in the table defined in $this->tableName. Otherwise Joomla! will not know how to insert /
	 * update the data on the table and will throw an Exception denoting a database error. It is generally a BAD idea
	 * using JOINs instead of relations. If unsure, use relations.
	 *
	 * @param   null|array  $data            [Optional] Data to bind
	 * @param   string      $orderingFilter  A WHERE clause used to apply table item reordering
	 * @param   array       $ignore          A list of fields to ignore when binding $data
	 *
	 * @para    boolean     $resetRelations  Should I automatically reset relations if relation-important fields are
	 *          changed?
	 *
	 * @return   static  Self, for chaining
	 */
	public function save($data = null, $orderingFilter = '', $ignore = null, $resetRelations = true)
	{
		// Stash the primary key
		$oldPKValue = $this->getId();

		// Call the onBeforeSave event
		$this->triggerEvent('onBeforeSave', [&$data]);

		// Get the relation to local field map and initialise the relationsAffected array
		$relationImportantFields = $this->getRelationFields();
		$dataBeforeBind          = [];

		// If we have relations we keep a copy of the data before bind.
		if (count($relationImportantFields))
		{
			$dataBeforeBind = array_merge($this->recordData);
		}

		// Bind any (optional) data. If no data is provided, the current record data is used
		if (!is_null($data))
		{
			$this->bind($data, $ignore);
		}

		// Is this a new record?
		if (empty($oldPKValue))
		{
			$isNewRecord = true;
		}
		else
		{
			$isNewRecord = $oldPKValue != $this->getId();
		}

		// Check the validity of the data
		$this->check();

		// Get the database object
		$db = $this->getDbo();

		// Insert or update the record. Note that the object we use for insertion / update is the a copy holding
		// the transformed data.
		$dataObject = $this->recordDataToDatabaseData();
		$dataObject = (object) $dataObject;

		if ($isNewRecord)
		{
			$this->triggerEvent('onBeforeCreate', [&$dataObject]);

			// Insert the new record
			$db->insertObject($this->tableName, $dataObject, $this->idFieldName);

			// Update ourselves with the new ID field's value
			$this->{$this->idFieldName} = $db->insertid();

			// Rebase the relations with the newly created model
			if ($resetRelations)
			{
				$this->relationManager->rebase($this);
			}

			$this->triggerEvent('onAfterCreate');
		}
		else
		{
			$this->triggerEvent('onBeforeUpdate', [&$dataObject]);

			$db->updateObject($this->tableName, $dataObject, $this->idFieldName, true);

			$this->triggerEvent('onAfterUpdate');
		}

		// If an ordering filter is set, attempt reorder the rows in the table based on the filter and value.
		if ($orderingFilter)
		{
			$filterValue = $this->$orderingFilter;
			$this->reorder($orderingFilter ? $db->qn($orderingFilter) . ' = ' . $db->q($filterValue) : '');
		}

		// One more thing... Touch all relations in the $touches array
		if (!empty($this->touches))
		{
			foreach ($this->touches as $relation)
			{
				$records = $this->getRelations()->getData($relation);

				if (!empty($records))
				{
					if ($records instanceof DataModel)
					{
						$records = [$records];
					}

					/** @var DataModel $record */
					foreach ($records as $record)
					{
						$record->touch();
					}
				}
			}
		}

		// If we have relations we compare the data to the copy of the data before bind.
		if (count($relationImportantFields) && $resetRelations)
		{
			// Since array_diff_assoc doesn't work recursively we have to do it the EXCRUCIATINGLY SLOW WAY. Sad panda :(
			$keysRecord     = (is_array($this->recordData) && !empty($this->recordData)) ? array_keys($this->recordData) : [];
			$keysBefore     = (is_array($dataBeforeBind) && !empty($dataBeforeBind)) ? array_keys($dataBeforeBind) : [];
			$keysAll        = array_merge($keysRecord, $keysBefore);
			$keysAll        = array_unique($keysAll);
			$modifiedFields = [];

			foreach ($keysAll as $key)
			{
				if (!isset($dataBeforeBind[$key]) || !isset($this->recordData[$key]))
				{
					$modifiedFields[] = $key;
				}
				elseif ($dataBeforeBind[$key] != $this->recordData[$key])
				{
					$modifiedFields[] = $key;
				}
			}

			unset ($dataBeforeBind);

			if (count($modifiedFields))
			{
				$relationsAffected = [];

				unset($modifiedData);

				foreach ($relationImportantFields as $relationName => $fieldName)
				{
					if (in_array($fieldName, $modifiedFields))
					{
						$relationsAffected[] = $relationName;
					}
				}

				// Reset the relations which are affected by the save. This will force-reload the relations when you try to
				// access them again.
				$this->relationManager->resetRelationData($relationsAffected);
			}
		}

		// Finally, call the onAfterSave event
		$this->triggerEvent('onAfterSave');

		return $this;
	}

	/**
	 * Alias of save. For JTableInterface compatibility.
	 *
	 * @param   boolean  $updateNulls  Blatantly ignored.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		try
		{
			$this->save();
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Save a record, creating it if it doesn't exist or updating it if it exists. By default it uses the currently set
	 * data, unless you provide a $data array. On top of that, it also saves all specified relations. If $relations is
	 * null it will save all relations known to this model.
	 *
	 * @param   null|array  $data            [Optional] Data to bind
	 * @param   string      $orderingFilter  A WHERE clause used to apply table item reordering
	 * @param   array       $ignore          A list of fields to ignore when binding $data
	 * @param   array       $relations       Which relations to save with the model's record. Leave null for all
	 *                                       relations
	 *
	 * @return static Self, for chaining
	 */
	public function push($data = null, $orderingFilter = '', $ignore = null, array $relations = null)
	{
		// Store the model's $touches definition
		$touches = $this->touches;

		// If $relations is non-null, remove $relations from $this->touches. Since $relations will be saved, they are
		// implicitly touched. We don't want to double-touch those records, do we?
		if (is_array($relations))
		{
			$this->touches = array_diff($this->touches, $relations);
		}
		// Otherwise empty $this->touches completely as we'll be pushing all relations
		else
		{
			$this->touches = [];
		}

		// Save this record
		$this->save($data, $orderingFilter, $ignore, false);

		// Push all relations specified (or all relations if $relations is null)
		$relManager   = $this->getRelations();
		$allRelations = $relManager->getRelationNames();

		if (!empty($allRelations))
		{
			foreach ($allRelations as $relationName)
			{
				if (!is_null($relations) && !in_array($relationName, $relations))
				{
					continue;
				}

				$relManager->save($relationName);
			}
		}

		// Restore the model's $touches definition
		$this->touches = $touches;

		// Return self for chaining
		return $this;
	}

	/**
	 * Method to bind an associative array or object to the DataModel instance. This method optionally takes an array of
	 * properties to ignore when binding.
	 *
	 * Special note if you are using a custom buildQuery with JOINs or field aliases:
	 * You will need to use addKnownField to let FOF know that the fields from your JOINs and the aliased fields should
	 * be bound to the record data. If you are using aliased fields you may also want to override the
	 * databaseDataToRecordData method. Generally, it is a BAD idea using JOINs instead of relations.
	 *
	 * @param   mixed  $data    An associative array or object to bind to the DataModel instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  static  Self, for chaining
	 *
	 * @throws  InvalidArgumentException
	 * @throws    Exception
	 */
	public function bind($data, $ignore = [])
	{
		$this->triggerEvent('onBeforeBind', [&$data]);

		// If the source value is not an array or object return false.
		if (!is_object($data) && !is_array($data))
		{
			throw new InvalidArgumentException(Text::sprintf('LIB_FOF_MODEL_ERR_BIND', get_class($this), gettype($data)));
		}

		// If the ignore value is a string, explode it over spaces.
		if (!is_array($ignore))
		{
			$ignore = explode(' ', $ignore);
		}

		// Bind the source value, excluding the ignored fields.
		foreach ($this->recordData as $k => $currentValue)
		{
			// Only process fields not in the ignore array.
			if (!in_array($k, $ignore))
			{
				if (is_array($data) && isset($data[$k]))
				{
					$this->setFieldValue($k, $data[$k]);
				}
				elseif (is_object($data) && isset($data->$k))
				{
					$this->setFieldValue($k, $data->$k);
				}
			}
		}

		// Perform data transformation
		$this->databaseDataToRecordData();

		$this->triggerEvent('onAfterBind', [$data]);

		return $this;
	}

	/**
	 * Check the data for validity. By default it only checks for fields declared as NOT NULL
	 *
	 * @return  static  Self, for chaining
	 *
	 * @throws RuntimeException  When the data bound to this record is invalid
	 */
	public function check()
	{
		if (!$this->autoChecks)
		{
			return $this;
		}

		// Run a custom event
		$this->triggerEvent('onBeforeCheck');

		// Create a slug if there is a title and an empty slug
		$slugField  = $this->getFieldAlias('slug');
		$titleField = $this->getFieldAlias('title');

		if ($this->hasField('title') && $this->hasField('slug') && !$this->$slugField)
		{
			$this->$slugField = ApplicationHelper::stringURLSafe($this->$titleField);
		}

		// Special handling of the ordering field
		if ($this->hasField('ordering') && is_null($this->getFieldValue('ordering')))
		{
			$this->setFieldValue('ordering', 0);
		}

		foreach ($this->knownFields as $fieldName => $field)
		{
			// Never check the key if it's empty; an empty key is normal for new records
			if ($fieldName == $this->idFieldName)
			{
				continue;
			}

			$value = $this->$fieldName;

			if (isset($field->Null) && ($field->Null == 'NO') && empty($value) && !is_numeric($value) && !in_array($fieldName, $this->fieldsSkipChecks))
			{
				if (!is_null($field->Default))
				{
					$this->$fieldName = $field->Default;

					continue;
				}

				$text = $this->container->componentName . '_' . $this->container->inflector->singularize($this->getName()) . '_ERR_'
					. $fieldName . '_EMPTY';

				throw new RuntimeException(Text::_(strtoupper($text)), 500);
			}
		}

		return $this;
	}

	/**
	 * Change the ordering of the records of the table
	 *
	 * @param   string  $where  The WHERE clause of the SQL used to fetch the order
	 *
	 * @return  static  Self, for chaining
	 *
	 * @throws  UnexpectedValueException
	 */
	public function reorder($where = '')
	{
		// If there is no ordering field set an error and return false.
		if (!$this->hasField('ordering'))
		{
			throw new SpecialColumnMissing(sprintf('%s does not support ordering.', $this->tableName));
		}

		$this->triggerEvent('onBeforeReorder', [&$where]);

		$order_field = $this->getFieldAlias('ordering');
		$k           = $this->getIdFieldName();
		$db          = $this->getDbo();

		// Get the primary keys and ordering values for the selection.
		$query = $db->getQuery(true)
			->select($db->qn($k) . ', ' . $db->qn($order_field))
			->from($db->qn($this->getTableName()))
			->where($db->qn($order_field) . ' >= ' . $db->q(0))
			->order($db->qn($order_field) . 'ASC, ' . $db->qn($k) . 'ASC');

		// Setup the extra where and ordering clause data.
		if ($where)
		{
			$query->where($where);
		}

		$rows = $db->setQuery($query)->loadObjectList();

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
					$query = $db->getQuery(true)
						->update($db->qn($this->getTableName()))
						->set($db->qn($order_field) . ' = ' . $db->q($i + 1))
						->where($db->qn($k) . ' = ' . $db->q($row->$k));
					$db->setQuery($query)->execute();
				}
			}
		}

		$this->triggerEvent('onAfterReorder');

		return $this;
	}

	/**
	 * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
	 * Negative numbers move the row up in the sequence and positive numbers move it down.
	 *
	 * @param   integer  $delta  The direction and magnitude to move the row in the ordering sequence.
	 * @param   string   $where  WHERE clause to use for limiting the selection of rows to compact the
	 *                           ordering values.
	 *
	 * @return  static  Self, for chaining
	 *
	 * @throws  UnexpectedValueException  If the table does not support reordering
	 * @throws  RuntimeException  If the record is not loaded
	 */
	public function move($delta, $where = '')
	{
		if (!$this->hasField('ordering'))
		{
			throw new SpecialColumnMissing(sprintf('%s does not support ordering.', $this->tableName));
		}

		$this->triggerEvent('onBeforeMove', [&$delta, &$where]);

		$ordering_field = $this->getFieldAlias('ordering');

		// If the change is none, do nothing.
		if (empty($delta))
		{
			$this->triggerEvent('onAfterMove');

			return $this;
		}

		$k     = $this->idFieldName;
		$row   = null;
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// If the table is not loaded, return false
		if (empty($this->$k))
		{
			throw new RecordNotLoaded(sprintf("Model %s does not have a loaded record", $this->getName()));
		}

		// Select the primary key and ordering values from the table.
		$query->select([
				$db->qn($this->idFieldName), $db->qn($ordering_field),
			]
		)->from($db->qn($this->tableName));

		// If the movement delta is negative move the row up.
		if ($delta < 0)
		{
			$query->where($db->qn($ordering_field) . ' < ' . $db->q((int) $this->$ordering_field));
			$query->order($db->qn($ordering_field) . ' DESC');
		}
		// If the movement delta is positive move the row down.
		elseif ($delta > 0)
		{
			$query->where($db->qn($ordering_field) . ' > ' . $db->q((int) $this->$ordering_field));
			$query->order($db->qn($ordering_field) . ' ASC');
		}

		// Add the custom WHERE clause if set.
		if ($where)
		{
			$query->where($where);
		}

		// Select the first row with the criteria.
		$row = $db->setQuery($query, 0, 1)->loadObject();

		// If a row is found, move the item.
		if (!empty($row))
		{
			// Update the ordering field for this instance to the row's ordering value.
			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($db->qn($ordering_field) . ' = ' . $db->q((int) $row->$ordering_field))
				->where($db->qn($k) . ' = ' . $db->q($this->$k));
			$db->setQuery($query)->execute();

			// Update the ordering field for the row to this instance's ordering value.
			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($db->qn($ordering_field) . ' = ' . $db->q((int) $this->$ordering_field))
				->where($db->qn($k) . ' = ' . $db->q($row->$k));
			$db->setQuery($query)->execute();

			// Update the instance value.
			$this->$ordering_field = $row->$ordering_field;
		}

		$this->triggerEvent('onAfterMove');

		return $this;
	}

	/**
	 * Process a large collection of records a few at a time.
	 *
	 * @param   integer   $chunkSize  How many records to process at once
	 * @param   callable  $callback   A callable to process each record
	 *
	 * @return  static  Self, for chaining
	 */
	public function chunk($chunkSize, $callback)
	{
		$totalItems = $this->count();

		if (!$totalItems)
		{
			return $this;
		}

		$start = 0;

		while ($start < ($totalItems - 1))
		{
			$this->get(true, $start, $chunkSize)->transform($callback);

			$start += $chunkSize;
		}

		return $this;
	}

	/**
	 * Get the number of all items
	 *
	 * @return  integer
	 */
	public function count()
	{
		// Get a "count all" query
		$db    = $this->getDbo();
		$query = $this->buildQuery(true);
		$query->clear('select')->clear('order')->select('COUNT(*)');

		// Run the "before build query" hook and behaviours
		$this->triggerEvent('onBuildCountQuery', [&$query]);

		$total = $db->setQuery($query)->loadResult();

		return $total;
	}

	/**
	 * Build the query to fetch data from the database
	 *
	 * @param   boolean  $overrideLimits  Should I override limits
	 *
	 * @return  JDatabaseQuery  The database query to use
	 */
	public function buildQuery($overrideLimits = false)
	{
		// Get a "select all" query
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($this->getTableName());

		// Run the "before build query" hook and behaviours
		$this->triggerEvent('onBeforeBuildQuery', [&$query, $overrideLimits]);

		// Apply custom WHERE clauses
		if (count($this->whereClauses))
		{
			foreach ($this->whereClauses as $clause)
			{
				$query->where($clause);
			}
		}

		$order = $this->getState('filter_order', null, 'cmd');

		if (!array_key_exists($order, $this->knownFields))
		{
			$order = $this->getIdFieldName();
			$this->setState('filter_order', $order);
		}

		$order = $db->qn($order);

		$dir = strtoupper($this->getState('filter_order_Dir', null, 'cmd'));

		if (!in_array($dir, ['ASC', 'DESC']))
		{
			$dir = 'ASC';
			$this->setState('filter_order_Dir', $dir);
		}

		$query->order($order . ' ' . $dir);

		// Run the "before after query" hook and behaviours
		$this->triggerEvent('onAfterBuildQuery', [&$query, $overrideLimits]);

		return $query;
	}

	/**
	 * Returns a DataCollection iterator based on your currently set Model state
	 *
	 * @param   boolean  $overrideLimits  Should I ignore limits set in the Model?
	 * @param   integer  $limitstart      How many items to skip from the start, only when $overrideLimits = true
	 * @param   integer  $limit           How many items to return, only when $overrideLimits = true
	 *
	 * @return  DataCollection  The data collection
	 */
	public function get($overrideLimits = false, $limitstart = 0, $limit = 0)
	{
		if (!$overrideLimits)
		{
			$limitstart = $this->getState('limitstart', 0);
			$limit      = $this->getState('limit', 0);
		}

		$dataCollection = DataCollection::make($this->getItemsArray($limitstart, $limit, $overrideLimits));

		$this->eagerLoad($dataCollection, null);

		return $dataCollection;
	}

	/**
	 * Returns a raw array of DataModel instances based on your currently set Model state
	 *
	 * @param   integer  $limitstart      How many items from the start to skip (0 = do not skip)
	 * @param   integer  $limit           How many items to return (0 = all)
	 * @param   bool     $overrideLimits  Set to true to override limitstart, limit and ordering
	 *
	 * @return  array  Array of DataModel objects
	 */
	public function &getItemsArray($limitstart = 0, $limit = 0, $overrideLimits = false)
	{
		$itemsTemp = $this->getRawDataArray($limitstart, $limit, $overrideLimits);
		$items     = [];

		while (!empty($itemsTemp))
		{
			$data = array_shift($itemsTemp);
			/** @var DataModel $item */
			$item = clone $this;
			$item->clearState()->reset(true);
			$item->bind($data);
			$items[$item->getId()] = $item;
			$item->relationManager = clone $this->relationManager;
			$item->relationManager->rebase($item);
		}

		$this->triggerEvent('onAfterGetItemsArray', [&$items]);

		return $items;
	}

	/**
	 * Returns the raw data array, as fetched from the database, based on your currently set Model state
	 *
	 * @param   integer  $limitstart      How many items from the start to skip (0 = do not skip)
	 * @param   integer  $limit           How many items to return (0 = all)
	 * @param   bool     $overrideLimits  Set to true to override limitstart, limit and ordering
	 *
	 * @return  array  Array of hashed arrays
	 */
	public function &getRawDataArray($limitstart = 0, $limit = 0, $overrideLimits = false)
	{
		$limitstart = max($limitstart, 0);
		$limit      = max($limit, 0);

		$query = $this->buildQuery($overrideLimits);

		$db = $this->getDbo();
		$db->setQuery($query, $limitstart, $limit);
		$rawData = $db->loadAssocList();

		return $rawData;
	}

	/**
	 * Eager loads the provided relations and assigns their data to a data collection
	 *
	 * @param   DataCollection  $dataCollection  The data collection on which the eager loaded relations will be
	 *                                           applied
	 * @param   array|null      $relations       The relations to eager load. Leave empty to use the already defined
	 *                                           relations
	 *
	 * @return static for chaining
	 */
	public function eagerLoad(DataCollection &$dataCollection, array $relations = null)
	{
		if (empty($relations))
		{
			$relations = $this->eagerRelations;
		}

		// Apply eager loaded relations
		if ($dataCollection->count() && !empty($relations))
		{
			$relationManager = $this->getRelations();

			foreach ($relations as $relation => $callback)
			{
				// Did they give us a relation name without a callback?
				if (!is_callable($callback) && is_string($callback) && !empty($callback))
				{
					$relation = $callback;
					$callback = null;
				}

				$relationData  = $relationManager->getData($relation, $callback, $dataCollection);
				$foreignKeyMap = $relationManager->getForeignKeyMap($relation);

				/** @var DataModel $item */
				foreach ($dataCollection as $item)
				{
					$item->getRelations()->setDataFromCollection($relation, $relationData, $foreignKeyMap);
				}
			}
		}

		return $this;
	}

	/**
	 * Archive the record, i.e. set enabled to 2
	 *
	 * @return   static  For chaining
	 */
	public function archive()
	{
		if (!$this->getId())
		{
			throw new RecordNotLoaded("Can't archive a not loaded DataModel");
		}

		if (!$this->hasField('enabled'))
		{
			return $this;
		}

		$this->triggerEvent('onBeforeArchive', []);

		$enabled = $this->getFieldAlias('enabled');

		$this->$enabled = 2;
		$this->save();

		$this->triggerEvent('onAfterArchive');

		return $this;
	}

	/**
	 * Trashes a record, either the currently loaded one or the one specified in $id. If an $id is specified that record
	 * is loaded before trying to trash it. Unlike a hard delete, trashing is a "soft delete", only setting the enabled
	 * field to -2.
	 *
	 * @param   mixed  $id  Primary key (id field) value
	 *
	 * @return  static  for chaining
	 */
	public function trash($id = null)
	{
		if (!empty($id))
		{
			$this->findOrFail($id);
		}

		$id = $this->getId();

		if (!$id)
		{
			throw new RecordNotLoaded("Can't trash a not loaded DataModel");
		}

		if (!$this->hasField('enabled'))
		{
			throw new SpecialColumnMissing("DataModel::trash method needs an 'enabled' field");
		}

		$this->triggerEvent('onBeforeTrash', [&$id]);

		$enabled        = $this->getFieldAlias('enabled');
		$this->$enabled = -2;
		$this->save();

		$this->triggerEvent('onAfterTrash', [&$id]);

		return $this;
	}

	/**
	 * Change the publish state of a record. By default it will set it to 1 (published) unless you specify a different
	 * value.
	 *
	 * @param   int  $state  The publish state. Default: 1 (published).
	 *
	 * @return   static  For chaining
	 */
	public function publish($state = 1)
	{
		if (!$this->getId())
		{
			throw new RecordNotLoaded("Can't change the state of a not loaded DataModel");
		}

		if (!$this->hasField('enabled'))
		{
			return $this;
		}

		$this->triggerEvent('onBeforePublish', []);

		$enabled = $this->getFieldAlias('enabled');

		$this->$enabled = $state;
		$this->save();

		$this->triggerEvent('onAfterPublish');

		return $this;
	}

	/**
	 * Unpublish the record, i.e. set enabled to 0
	 *
	 * @return   static  For chaining
	 */
	public function unpublish()
	{
		if (!$this->getId())
		{
			throw new RecordNotLoaded("Can't unpublish a not loaded DataModel");
		}

		if (!$this->hasField('enabled'))
		{
			return $this;
		}

		$this->triggerEvent('onBeforeUnpublish', []);

		$enabled = $this->getFieldAlias('enabled');

		$this->$enabled = 0;
		$this->save();

		$this->triggerEvent('onAfterUnpublish');

		return $this;
	}

	/**
	 * Untrashes a record, either the currently loaded one or the one specified in $id. If an $id is specified that
	 * record is loaded before trying to untrash it. Please note that enabled is set to 0 (unpublished) when you untrash
	 * an item.
	 *
	 * @param   mixed  $id  Primary key (id field) value
	 *
	 * @return  static  for chaining
	 */
	public function restore($id = null)
	{
		if (!$this->hasField('enabled'))
		{
			return $this;
		}

		if (!empty($id))
		{
			$this->findOrFail($id);
		}

		$id = $this->getId();

		if (!$id)
		{
			throw new RecordNotLoaded("Can't change the state of a not loaded DataModel");
		}

		$this->triggerEvent('onBeforeRestore', [&$id]);

		$enabled = $this->getFieldAlias('enabled');

		$this->$enabled = 0;
		$this->save();

		$this->triggerEvent('onAfterRestore', [&$id]);

		return $this;
	}

	/**
	 * Creates a copy of the current record. After the copy is performed, the data model contains the data of the new
	 * record.
	 *
	 * @param   array|DataModel  An associative array or object to bind to the DataModel instance. Allows you to
	 *                              override values on the copied object.
	 *
	 * @return   static
	 */
	public function copy($data = null)
	{
		$this->triggerEvent('onBeforeCopy');

		$this->{$this->idFieldName} = null;

		if ($this->hasField('created_by'))
		{
			$this->setFieldValue('created_by', null);
		}

		if ($this->hasField('modified_by'))
		{
			$this->setFieldValue('modified_by', null);
		}

		if ($this->hasField('locked_by'))
		{
			$this->setFieldValue('locked_by', null);
		}

		if ($this->hasField('created_on'))
		{
			$this->setFieldValue('created_on', null);
		}

		if ($this->hasField('modified_on'))
		{
			$this->setFieldValue('modified_on', null);
		}

		if ($this->hasField('locked_on'))
		{
			$this->setFieldValue('locked_on', null);
		}

		$result = $this->save($data);

		$this->triggerEvent('onAfterCopy', [&$result]);

		return $result;
	}

	/**
	 * Check-in an item. This works similar to unlock() but performs additional checks. If the item is locked by another
	 * user you need to have adequate ACL privileges to unlock it, i.e. core.admin or core.manage component-wide
	 * privileges; core.edit.state privileges component-wide or per asset; or be the creator of the item and have
	 * core.edit.own privileges component-wide or per asset.
	 *
	 * @return  static
	 *
	 * @throws  LockedRecord  If you don't have the privilege to check in this item
	 */
	public function checkIn($userId = null)
	{
		// If there is no loaded record we can't do much, I'm afraid
		if (!$this->getId())
		{
			throw new RecordNotLoaded("Can't checkin a not loaded DataModel");
		}

		// If the lock fields are missing we have nothing to do
		if (!$this->hasField('locked_by') && !$this->hasField('locked_on'))
		{
			return $this;
		}

		// If there's no locked_by field we just unlock and return
		if (!$this->hasField('locked_by'))
		{
			return $this->unlock();
		}

		// If the current user and the user who locked the record are the same, unlock it.
		if (empty($userId))
		{
			$userId = $this->container->platform->getUser()->id;
		}

		$lockedBy = $this->getFieldValue('locked_by');

		if (empty($lockedBy) || ($lockedBy == $userId))
		{
			return $this->unlock();
		}

		// Get the component privileges
		$platform  = $this->container->platform;
		$component = $this->container->componentName;

		$privileges = [
			'editown'   => $platform->authorise('core.edit.own', $component),
			'editstate' => $platform->authorise('core.edit.state', $component),
			'admin'     => $platform->authorise('core.admin', $component),
			'manage'    => $platform->authorise('core.manage', $component),
		];

		// If we are trackign assets get the item's privileges
		if ($this->isAssetsTracked())
		{
			$assetKey        = $this->getAssetKey();
			$assetPrivileges = [
				'editown'   => $platform->authorise('core.edit.own', $assetKey),
				'editstate' => $platform->authorise('core.edit.state', $assetKey),
			];

			foreach ($assetPrivileges as $k => $v)
			{
				$privileges[$k] = $privileges[$k] || $v;
			}
		}

		// If you are a Super User, component manager or allowed to edit the state of records we unlock it
		if ($privileges['admin'] || $privileges['manage'] || $privileges['editstate'])
		{
			return $this->unlock();
		}

		// If you are the owner of the record and have core.edit.own privilege we will unlock it.
		$owner = 0;

		if ($this->hasField('created_by'))
		{
			$owner = $this->getFieldValue('created_by');
		}

		if ($privileges['editown'] && ($owner == $userId))
		{
			return $this->unlock();
		}

		// All else failed, you don't have the privilege to unlock this item.
		throw new LockedRecord;
	}

	/**
	 * Reset the record data
	 *
	 * @param   boolean  $useDefaults     Should I use the default values? Default: yes
	 * @param   boolean  $resetRelations  Should I reset the relations too? Default: no
	 *
	 * @return  static  Self, for chaining
	 */
	public function reset($useDefaults = true, $resetRelations = false)
	{
		$this->recordData   = [];
		$this->whereClauses = [];

		foreach ($this->knownFields as $fieldName => $information)
		{
			if ($useDefaults)
			{
				$this->recordData[$fieldName] = $information->Default;
			}
			else
			{
				$this->recordData[$fieldName] = null;
			}
		}

		if ($resetRelations)
		{
			$this->relationManager->resetRelationData();
			$this->eagerRelations = [];
		}

		$this->relationFilters = [];

		$this->triggerEvent('onAfterReset', [$useDefaults, $resetRelations]);

		return $this;
	}

	/**
	 * Automatically performs a hard or soft delete, based on the value of $this->softDelete. A soft delete simply sets
	 * enabled to -2 whereas a hard delete removes the data from the database. If you want to force a specific behaviour
	 * directly call trash() for a soft delete or forceDelete() for a hard delete.
	 *
	 * @param   mixed  $id  Primary key (id field) value
	 *
	 * @return  static  for chaining
	 */
	public function delete($id = null)
	{
		if ($this->softDelete)
		{
			return $this->trash($id);
		}
		else
		{
			return $this->forceDelete($id);
		}
	}

	/**
	 * Delete a record, either the currently loaded one or the one specified in $id. If an $id is specified that record
	 * is loaded before trying to delete it. In the end the data model is reset.
	 *
	 * @param   mixed  $id  Primary key (id field) value
	 *
	 * @return  static  for chaining
	 */
	public function forceDelete($id = null)
	{
		if (!empty($id))
		{
			$this->findOrFail($id);
		}

		$id = $this->getId();

		if (!$id)
		{
			throw new RecordNotLoaded("Can't delete a not loaded DataModel object");
		}

		$this->triggerEvent('onBeforeDelete', [&$id]);

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->delete()
			->from($this->tableName)
			->where($db->qn($this->idFieldName) . ' = ' . $db->q($id));
		$db->setQuery($query)->execute();

		$this->triggerEvent('onAfterDelete', [&$id]);

		$this->reset();

		return $this;
	}

	/**
	 * Generic check for whether dependencies exist for this object in the db schema. This method is NOT used by
	 * default. If you want to use it you will have to override your delete(), trash() or forceDelete() method,
	 * or create an onBeforeDelete and/or onBeforeTrash event handler.
	 *
	 * @param   integer  $oid    The primary key of the record to delete
	 * @param   array    $joins  Any joins to foreign table, used to determine if dependent records exist
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException  If you should not delete the record (the message tells you why)
	 */
	public function canDelete($oid = null, $joins = null)
	{
		$pkField = $this->getKeyName();

		if ($oid)
		{
			$this->$pkField = intval($oid);
		}

		if (!$this->$pkField)
		{
			throw new InvalidArgumentException('Master table should be loaded or an ID should be passed');
		}

		if (is_array($joins))
		{
			$db      = $this->getDbo();
			$query   = $db->getQuery(true)
				->select($db->qn('master') . '.' . $db->qn($pkField))
				->from($db->qn($this->tableName) . ' AS ' . $db->qn('master'));
			$tableNo = 0;

			foreach ($joins as $table)
			{
				// Sanity check on passed array
				$check  = ['idfield', 'idalias', 'name', 'joinfield', 'label'];
				$result = array_intersect($check, array_keys($table));

				if (count($result) != count($check))
				{
					throw new InvalidArgumentException('Join array missing some keys, please check the documentation');
				}

				$tableNo++;
				$query->select(
					[
						'COUNT(DISTINCT ' . $db->qn('t' . $tableNo) .
						'.' . $db->qn($table['idfield']) . ') AS ' . $db->qn($table['idalias']),
					]
				);
				$query->join('LEFT', $db->qn($table['name']) .
					' AS ' . $db->qn('t' . $tableNo) .
					' ON ' . $db->qn('t' . $tableNo) . '.' . $db->qn($table['joinfield']) .
					' = ' . $db->qn('master') . '.' . $db->qn($pkField)
				);
			}

			$query->where($db->qn('master') . '.' . $db->qn($pkField) . ' = ' . $db->q($this->$pkField));
			$query->group($db->qn('master') . '.' . $db->qn($pkField));
			$this->getDbo()->setQuery((string) $query);

			$obj = $this->getDbo()->loadObject();

			$msg = [];
			$i   = 0;

			foreach ($joins as $table)
			{
				$pkField = $table['idalias'];

				if ($obj->$pkField > 0)
				{
					$msg[] = Text::_($table['label']);
				}

				$i++;
			}

			if (count($msg))
			{
				$option  = $this->container->componentName;
				$comName = $this->container->bareComponentName;
				$tbl     = $this->getTableName();
				$tview   = str_replace('#__' . $comName . '_', '', $tbl);
				$prefix  = $option . '_' . $tview . '_NODELETE_';

				$message = '<ul>';

				foreach ($msg as $key)
				{
					$message .= '<li>' . Text::_(strtoupper($prefix . $key)) . '</li>';
				}

				$message .= '</ul>';

				throw new RuntimeException($message);
			}
		}
	}

	/**
	 * Find and load a single record based on the provided key values. If the record is not found an exception is thrown
	 *
	 * @param   array|mixed  $keys  An optional primary key value to load the row by, or an array of fields to match.
	 *                              If not set the "id" state variable or, if empty, the identity column's value is used
	 *
	 * @return  static  Self, for chaining
	 *
	 * @throws  RuntimeException  When the row is not found
	 */
	public function findOrFail($keys = null)
	{
		$this->find($keys);

		// We have to assign the value, since empty() is not triggering the __get magic method
		// http://stackoverflow.com/questions/2045791/php-empty-on-get-accessor
		$value = $this->getId();

		if (empty($value))
		{
			throw new RecordNotLoaded;
		}

		return $this;
	}

	/**
	 * Method to load a row from the database by primary key. Used for JTableInterface compatibility.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If
	 *                           not set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 *
	 * @throws  RuntimeException
	 * @throws  UnexpectedValueException
	 * @link    http://docs.joomla.org/JTable/load
	 * @since   3.2
	 */
	public function load($keys = null, $reset = true)
	{
		if ($reset)
		{
			$this->reset();
		}

		try
		{
			$this->findOrFail($keys);
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Find and load a single record based on the provided key values
	 *
	 * @param   array|mixed  $keys  An optional primary key value to load the row by, or an array of fields to match.
	 *                              If not set the "id" state variable or, if empty, the identity column's value is used
	 *
	 * @return  static  Self, for chaining
	 */
	public function find($keys = null)
	{
		// Execute the onBeforeLoad event
		$this->triggerEvent('onBeforeLoad', [&$keys]);

		// If we are not given any keys, try to get the ID from the state or the table data
		if (empty($keys))
		{
			$id = $this->getState('id', 0);

			if (empty($id))
			{
				$id = $this->getId();
			}

			if (empty($id))
			{
				$this->triggerEvent('onAfterLoad', [false, &$keys]);

				$this->reset();

				return $this;
			}

			$keys = [$this->idFieldName => $id];
		}
		elseif (!is_array($keys))
		{
			if (empty($keys))
			{
				$this->triggerEvent('onAfterLoad', [false, &$keys]);

				$this->reset();

				return $this;
			}

			$keys = [$this->idFieldName => $keys];
		}

		// Reset the table
		$this->reset();

		// Get the query
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn($this->tableName));

		// Apply key filters
		foreach ($keys as $filterKey => $filterValue)
		{
			if ($filterKey == 'id')
			{
				$filterKey = $this->getIdFieldName();
			}

			if (array_key_exists($filterKey, $this->recordData))
			{
				$query->where($db->qn($filterKey) . ' = ' . $db->q($filterValue));
			}
		}

		// Get the row
		$db->setQuery($query);

		try
		{
			$row = $db->loadAssoc();
		}
		catch (Exception $e)
		{
			$row = null;
		}

		if (empty($row))
		{
			$this->triggerEvent('onAfterLoad', [false, &$keys]);

			return $this;
		}

		// Bind the data
		$this->bind($row);

		$this->relationManager->rebase($this);

		// Execute the onAfterLoad event
		$this->triggerEvent('onAfterLoad', [true, &$keys]);

		return $this;
	}

	/**
	 * Create a new record with the provided data
	 *
	 * @param   array  $data  The data to use in the new record
	 *
	 * @return  static  Self, for chaining
	 */
	public function create($data)
	{
		return $this->reset()->bind($data)->save();
	}

	/**
	 * Return the first item found or create a new one based on the provided $data
	 *
	 * @param   array  $data  Data for the newly created item
	 *
	 * @return  static
	 */
	public function firstOrCreate($data)
	{
		$item = $this->get(true, 0, 1)->first();

		if (is_null($item))
		{
			$item = clone $this;
			$item->create($data);
		}

		return $item;
	}

	/**
	 * Return the first item found or throw a \RuntimeException
	 *
	 * @return  static
	 *
	 * @throws  RuntimeException
	 */
	public function firstOrFail()
	{
		$item = $this->get(true, 0, 1)->first();

		if (is_null($item))
		{
			throw new NoItemsFound(get_class($this));
		}

		return $item;
	}

	/**
	 * Return the first item found or create a new, blank one
	 *
	 * @return  static
	 */
	public function firstOrNew()
	{
		$item = $this->get(true, 0, 1)->first();

		if (is_null($item))
		{
			$item = clone $this;
			$item->reset();
		}

		return $item;
	}

	/**
	 * Adds a behaviour by its name. It will search the following classes, in this order:
	 * \component_namespace\Model\modelName\Behaviour\behaviourName
	 * \component_namespace\Model\Behaviour\behaviourName
	 * \FOF30\Model\DataModel\Behaviour\behaviourName
	 * where:
	 * component_namespace  is the namespace of the component as defined in the container
	 * modelName            is the model's name, first character uppercase, e.g. Baz
	 * behaviourName        is the $behaviour parameter, first character uppercase, e.g. Something
	 *
	 * @param   string  $behaviour  The behaviour's name
	 *
	 * @return  static  Self, for chaining
	 */
	public function addBehaviour($behaviour)
	{
		$prefixes = [
			$this->container->getNamespacePrefix() . 'Model\\Behaviour\\' . ucfirst($this->getName()),
			$this->container->getNamespacePrefix() . 'Model\\Behaviour',
			'\\FOF30\\Model\\DataModel\\Behaviour',
		];

		foreach ($prefixes as $prefix)
		{
			$className = $prefix . '\\' . ucfirst($behaviour);

			if (class_exists($className, true) && !$this->behavioursDispatcher->hasObserverClass($className))
			{
				/** @var Observer $o */
				$observer = new $className($this->behavioursDispatcher);
				$this->behavioursDispatcher->attach($observer);

				return $this;
			}
		}

		return $this;
	}

	/**
	 * Removes a behaviour by its name. It will search the following classes, in this order:
	 * \component_namespace\Model\modelName\Behaviour\behaviourName
	 * \component_namespace\Model\DataModel\Behaviour\behaviourName
	 * \FOF30\Model\DataModel\Behaviour\behaviourName
	 * where:
	 * component_namespace  is the namespace of the component as defined in the container
	 * modelName            is the model's name, first character uppercase, e.g. Baz
	 * behaviourName        is the $behaviour parameter, first character uppercase, e.g. Something
	 *
	 * @param   string  $behaviour  The behaviour's name
	 *
	 * @return  static  Self, for chaining
	 */
	public function removeBehaviour($behaviour)
	{
		$prefixes = [
			$this->container->getNamespacePrefix() . 'Model\\Behaviour\\' . ucfirst($this->getName()),
			$this->container->getNamespacePrefix() . 'Model\\Behaviour',
			'\\FOF30\\Model\\DataModel\\Behaviour',
		];

		foreach ($prefixes as $prefix)
		{
			$className = ltrim($prefix . '\\' . ucfirst($behaviour), '\\');

			$observer = $this->behavioursDispatcher->getObserverByClass($className);

			if (is_null($observer))
			{
				continue;
			}

			$this->behavioursDispatcher->detach($observer);

			return $this;
		}

		return $this;
	}

	/**
	 * Gives you access to the behaviours dispatcher, allowing to attach/detach behaviour observers
	 *
	 * @return Dispatcher
	 */
	public function &getBehavioursDispatcher()
	{
		return $this->behavioursDispatcher;
	}

	/**
	 * Set the field and direction of ordering for the query returned by buildQuery.
	 * Alias of $this->setState('filter_order', $fieldName) and $this->setState('filter_order_Dir', $direction)
	 *
	 * @param   string  $fieldName  The field name to order by
	 * @param   string  $direction  The direction to order by (ASC for ascending or DESC for descending)
	 *
	 * @return  static  For chaining
	 */
	public function orderBy($fieldName, $direction = 'ASC')
	{
		$direction = strtoupper($direction);

		if (!in_array($direction, ['ASC', 'DESC']))
		{
			$direction = 'ASC';
		}

		$this->setState('filter_order', $fieldName);
		$this->setState('filter_order_Dir', $direction);

		return $this;
	}

	/**
	 * Set the limitStart for the query, i.e. how many records to skip.
	 * Alias of $this->setState('limitstart', $limitStart);
	 *
	 * @param   integer  $limitStart  Records to skip from the start
	 *
	 * @return  static  For chaining
	 */
	public function skip($limitStart = null)
	{
		// Only positive integers are allowed
		if (!is_int($limitStart) || $limitStart < 0 || !$limitStart)
		{
			$limitStart = 0;
		}

		$this->setState('limitstart', $limitStart);

		return $this;
	}

	/**
	 * Set the limit for the query, i.e. how many records to return.
	 * Alias of $this->setState('limit', $limit);
	 *
	 * @param   integer  $limit  Maximum number of records to return
	 *
	 * @return  static  For chaining
	 */
	public function take($limit = null)
	{
		// Only positive integers are allowed
		if (!is_int($limit) || $limit < 0 || !$limit)
		{
			$limit = 0;
		}

		$this->setState('limit', $limit);

		return $this;
	}

	/**
	 * Return the record's data as an array
	 *
	 * @return  array
	 */
	public function toArray()
	{
		return $this->recordData;
	}

	/**
	 * Returns the record's data as a JSON string
	 *
	 * @param   boolean  $prettyPrint  Should I format the JSON for pretty printing
	 *
	 * @return  string
	 */
	public function toJson($prettyPrint = false)
	{
		if (defined('JSON_PRETTY_PRINT'))
		{
			$options = $prettyPrint ? JSON_PRETTY_PRINT : 0;
		}
		else
		{
			$options = 0;
		}

		return json_encode($this->recordData, $options);
	}

	/**
	 * Touch a record, updating its modified_on and/or modified_by columns
	 *
	 * @param   integer  $userId  Optional user ID of the user touching the record
	 *
	 * @return  static  Self, for chaining
	 */
	public function touch($userId = null)
	{
		if (!$this->getId())
		{
			throw new RecordNotLoaded("Can't touch a not loaded DataModel");
		}

		if (!$this->hasField('modified_on') && !$this->hasField('modified_by'))
		{
			return $this;
		}

		$db   = $this->getDbo();
		$date = new Date();

		// Update the created_on / modified_on
		if ($this->hasField('modified_on'))
		{
			$modified_on        = $this->getFieldAlias('modified_on');
			$this->$modified_on = $date->toSql(false, $db);
		}

		// Update the created_by / modified_by values if necessary
		if ($this->hasField('modified_by'))
		{
			if (empty($userId))
			{
				$userId = $this->container->platform->getUser()->id;
			}

			$modified_by        = $this->getFieldAlias('modified_by');
			$this->$modified_by = $userId;
		}

		$this->save();

		return $this;
	}

	/**
	 * Lock a record by setting its locked_on and/or locked_by columns
	 *
	 * @param   integer  $userId
	 *
	 * @return  static  Self, for chaining
	 */
	public function lock($userId = null)
	{
		if (!$this->getId())
		{
			throw new CannotLockNotLoadedRecord;
		}

		if (!$this->hasField('locked_on') && !$this->hasField('locked_by'))
		{
			return $this;
		}

		$this->triggerEvent('onBeforeLock', []);

		$db = $this->getDbo();

		if ($this->hasField('locked_on'))
		{
			$date             = new Date();
			$locked_on        = $this->getFieldAlias('locked_on');
			$this->$locked_on = $date->toSql(false, $db);
		}

		if ($this->hasField('locked_by'))
		{
			if (empty($userId))
			{
				$userId = $this->container->platform->getUser()->id;
			}

			$locked_by        = $this->getFieldAlias('locked_by');
			$this->$locked_by = $userId;
		}

		$this->save();

		$this->triggerEvent('onAfterLock');

		return $this;
	}

	/**
	 * Unlock a record by resetting its locked_on and/or locked_by columns
	 *
	 * @return  static  Self, for chaining
	 */
	public function unlock()
	{
		if (!$this->getId())
		{
			throw new RecordNotLoaded("Can't unlock a not loaded DataModel");
		}

		if (!$this->hasField('locked_on') && !$this->hasField('locked_by'))
		{
			return $this;
		}

		$this->triggerEvent('onBeforeUnlock', []);

		$db = $this->getDbo();

		if ($this->hasField('locked_on'))
		{
			$locked_on        = $this->getFieldAlias('locked_on');
			$this->$locked_on = $db->getNullDate();
		}

		if ($this->hasField('locked_by'))
		{
			$locked_by        = $this->getFieldAlias('locked_by');
			$this->$locked_by = 0;
		}

		$this->save();

		$this->triggerEvent('onAfterUnlock');

		return $this;
	}

	/**
	 * Is this record locked by a different user than $userId?
	 *
	 * @param   integer  $userId
	 *
	 * @return  bool  True if the record is locked
	 */
	public function isLocked($userId = null)
	{
		if (!$this->hasField('locked_on') && !$this->hasField('locked_by'))
		{
			return false;
		}

		$nullDate = $this->getDbo()->getNullDate();

		// Get the locked_by / locked_on
		$locked_on = $nullDate;
		$locked_by = 0;

		if ($this->hasField('locked_on'))
		{
			$locked_on = $this->getFieldValue('locked_on', $nullDate);

			if (empty($locked_on))
			{
				$locked_on = $nullDate;
			}
		}

		if ($this->hasField('locked_by'))
		{
			$locked_by = $this->getFieldValue('locked_by', 0);

			if (empty($locked_by))
			{
				$locked_by = 0;
			}
		}

		$allowedUsers = [0];

		if (!empty($userId))
		{
			$allowedUsers[] = $userId;
		}

		if (in_array($locked_by, $allowedUsers))
		{
			return false;
		}

		return $locked_on != $nullDate;
	}

	/**
	 * Automatically uses the Filters behaviour to filter records in the model based on your criteria.
	 *
	 * @param   string  $fieldName  The field name to filter on
	 * @param   string  $method     The filtering method, e.g. <>, =, != and so on
	 * @param   mixed   $values     The value you're filtering on. Some filters (e.g. interval or between) require an
	 *                              array of values
	 *
	 * @return  static  For chaining
	 */
	public function where($fieldName, $method = '=', $values = null)
	{
		// Make sure the Filters behaviour is added to the model
		if (!$this->behavioursDispatcher->hasObserverClass('FOF30\\Model\\DataModel\\Behaviour\\Filters'))
		{
			$this->addBehaviour('filters');
		}

		// If we are dealing with the primary key, let's set the field name to "id". This is a convention and it will
		// be used inside the Filters behaviour
		// -- Let's not do this. The Filters behaviour works just fine with the regular field name!
		/**
		 * if ($fieldName == $this->getIdFieldName())
		 * {
		 * $fieldName = 'id';
		 * }
		 **/

		$options = [
			'method' => $method,
			'value'  => $values,
		];

		// Handle method aliases
		switch ($method)
		{
			case '<>':
				$options['method']   = 'search';
				$options['operator'] = '!=';
				break;

			case 'lt':
				$options['method']   = 'search';
				$options['operator'] = '<';
				break;

			case 'le':
				$options['method']   = 'search';
				$options['operator'] = '<=';
				break;

			case 'gt':
				$options['method']   = 'search';
				$options['operator'] = '>';
				break;

			case 'ge':
				$options['method']   = 'search';
				$options['operator'] = '>=';
				break;

			case 'eq':
				$options['method']   = 'search';
				$options['operator'] = '=';
				break;

			case 'neq':
			case 'ne':
				$options['method']   = 'search';
				$options['operator'] = '!=';
				break;

			case '<':
			case '!<':
			case '<=':
			case '!<=':
			case '>':
			case '!>':
			case '>=':
			case '!>=':
			case '!=':
			case '=':
				$options['method']   = 'search';
				$options['operator'] = $method;
				break;

			case 'like':
			case '~':
			case '%':
				$options['method'] = 'partial';
				break;

			case '==':
			case '=[]':
			case '=()':
			case 'in':
				$options['method'] = 'exact';
				break;

			case '()':
			case '[]':
			case '[)':
			case '(]':
				$options['method'] = 'between';
				break;

			case ')(':
			case ')[':
			case '](':
			case '][':
				$options['method'] = 'outside';
				break;

			case '*=':
			case 'every':
				$options['method'] = 'interval';
				break;

			case '?=':
				$options['method'] = 'search';
				break;

			default:

				throw new InvalidSearchMethod('Method ' . $method . ' is unsupported');

				break;
		}

		// Handle real methods
		switch ($options['method'])
		{
			case 'between':
			case 'outside':
				if (is_array($values) && (count($values) > 1))
				{
					// Get the from and to values from the $values array
					if (isset($values['from']) && isset($values['to']))
					{
						$options['from'] = $values['from'];
						$options['to']   = $values['to'];
					}
					else
					{
						$options['from'] = array_shift($values);
						$options['to']   = array_shift($values);
					}

					unset($options['value']);
				}
				else
				{
					// $values is not a from/to array. Treat as = (between) or != (outside)
					if (is_array($values))
					{
						$values = array_shift($values);
					}

					$options['operator'] = ($options['method'] == 'between') ? '=' : '!=';
					$options['value']    = $values;
					$options['method']   = 'search';
				}

				break;

			case 'interval':
				if (is_array($values) && (count($values) > 1))
				{
					// Get the value and interval from the $values array
					if (isset($values['value']) && isset($values['interval']))
					{
						$options['value']    = $values['value'];
						$options['interval'] = $values['interval'];
					}
					else
					{
						$options['value']    = array_shift($values);
						$options['interval'] = array_shift($values);
					}
				}
				else
				{
					// $values is not a value/interval array. Treat as =
					if (is_array($values))
					{
						$values = array_shift($values);
					}

					$options['value']    = $values;
					$options['method']   = 'search';
					$options['operator'] = '=';
				}
				break;

			case 'search':
				// We don't have to do anything if the operator is already set
				if (isset($options['operator']))
				{
					break;
				}

				if (is_array($values) && (count($values) > 1))
				{
					// Get the operator and value from the $values array
					if (isset($values['operator']) && isset($values['value']))
					{
						$options['operator'] = $values['operator'];
						$options['value']    = $values['value'];
					}
					else
					{
						$options['operator'] = array_shift($values);
						$options['value']    = array_shift($values);
					}
				}
				break;
		}

		$this->setState($fieldName, $options);

		return $this;
	}

	/**
	 * Add custom, pre-compiled WHERE clauses for use in buildQuery. The raw WHERE clause you specify is added as is to
	 * the query generated by buildQuery. You are responsible for quoting and escaping the field names and data found
	 * inside the WHERE clause.
	 *
	 * Using this method is a generally bad idea. You are better off overriding buildQuery and using state variables to
	 * customise the query build built instead of using this method to push raw SQL to the query builder. Mixing your
	 * business logic with raw SQL makes your application harder to maintain and refactor as dependencies to your
	 * database schema creep in areas of your code that should have nothing to do with it.
	 *
	 * @param   string  $rawWhereClause  The raw WHERE clause to add
	 *
	 * @return  static  For chaining
	 */
	public function whereRaw($rawWhereClause)
	{
		$this->whereClauses[] = $rawWhereClause;

		return $this;
	}

	/**
	 * Instructs the model to eager load the specified relations. The $relations array can have the format:
	 *
	 * array('relation1', 'relation2')
	 *        Eager load relation1 and relation2 without any callbacks
	 * array('relation1' => $callable1, 'relation2' => $callable2)
	 *        Eager load relation1 with callback $callable1 etc
	 * array('relation1', 'relation2' => $callable2)
	 *        Eager load relation1 without a callback, relation2 with callback $callable2
	 *
	 * The callback must have the signature function(\JDatabaseQuery $query) and doesn't return a value. It is
	 * supposed to modify the query directly.
	 *
	 * Please note that eager loaded relations produce their queries without going through the respective model. Instead
	 * they generate a SQL query directly, then map the loaded results into a DataCollection.
	 *
	 * @param   array  $relations  The relations to eager load. See above for more information.
	 *
	 * @return static For chaining
	 */
	public function with(array $relations)
	{
		if (empty($relations))
		{
			$this->eagerRelations = [];

			return $this;
		}

		$knownRelations = $this->relationManager->getRelationNames();

		foreach ($relations as $k => $v)
		{
			if (is_callable($v))
			{
				$relName  = $k;
				$callback = $v;
			}
			else
			{
				$relName  = $v;
				$callback = null;
			}

			if (in_array($relName, $knownRelations))
			{
				$this->eagerRelations[$relName] = $callback;
			}
		}

		return $this;
	}

	/**
	 * Filter the model based on the fulfilment of relations. For example:
	 * $posts->has('comments', '>=', 10)->get();
	 * will return all posts with at least 10 comments.
	 *
	 * @param   string  $relation  The relation to query
	 * @param   string  $operator  The comparison operator. Same operators as the where() method.
	 * @param   mixed   $value     The value(s) to compare against.
	 * @param   bool    $replace   When true (default) any existing relation filters for the same relation will be
	 *                             replaced
	 *
	 * @return static
	 */
	public function has($relation, $operator = '>=', $value = 1, $replace = true)
	{
		// Make sure the Filters behaviour is added to the model
		if (!$this->behavioursDispatcher->hasObserverClass('FOF30\\Model\\DataModel\\Behaviour\\RelationFilters'))
		{
			$this->addBehaviour('relationFilters');
		}

		$filter = [
			'relation' => $relation,
			'method'   => $operator,
			'operator' => $operator,
			'value'    => $value,
		];

		// Handle method aliases
		switch ($operator)
		{
			case '<>':
				$filter['method']   = 'search';
				$filter['operator'] = '!=';
				break;

			case 'lt':
				$filter['method']   = 'search';
				$filter['operator'] = '<';
				break;

			case 'le':
				$filter['method']   = 'search';
				$filter['operator'] = '<=';
				break;

			case 'gt':
				$filter['method']   = 'search';
				$filter['operator'] = '>';
				break;

			case 'ge':
				$filter['method']   = 'search';
				$filter['operator'] = '>=';
				break;

			case 'eq':
				$filter['method']   = 'search';
				$filter['operator'] = '=';
				break;

			case 'neq':
			case 'ne':
				$filter['method']   = 'search';
				$filter['operator'] = '!=';
				break;

			case '<':
			case '!<':
			case '<=':
			case '!<=':
			case '>':
			case '!>':
			case '>=':
			case '!>=':
			case '!=':
			case '=':
				$filter['method']   = 'search';
				$filter['operator'] = $operator;
				break;

			case 'like':
			case '~':
			case '%':
				$filter['method'] = 'partial';
				break;

			case '==':
			case '=[]':
			case '=()':
			case 'in':
				$filter['method'] = 'exact';
				break;

			case '()':
			case '[]':
			case '[)':
			case '(]':
				$filter['method'] = 'between';
				break;

			case ')(':
			case ')[':
			case '](':
			case '][':
				$filter['method'] = 'outside';
				break;

			case '*=':
			case 'every':
				$filter['method'] = 'interval';
				break;

			case '?=':
				$filter['method'] = 'search';
				break;

			case 'callback':
				$filter['method']   = 'callback';
				$filter['operator'] = 'callback';
				break;

			default:
				throw new InvalidSearchMethod('Operator ' . $operator . ' is unsupported');
				break;
		}

		// Handle real methods
		switch ($filter['method'])
		{
			case 'between':
			case 'outside':
				if (is_array($value) && (count($value) > 1))
				{
					// Get the from and to values from the $value array
					if (isset($value['from']) && isset($value['to']))
					{
						$filter['from'] = $value['from'];
						$filter['to']   = $value['to'];
					}
					else
					{
						$filter['from'] = array_shift($value);
						$filter['to']   = array_shift($value);
					}

					unset($filter['value']);
				}
				else
				{
					// $value is not a from/to array. Treat as = (between) or != (outside)
					if (is_array($value))
					{
						$value = array_shift($value);
					}

					$filter['operator'] = ($filter['method'] == 'between') ? '=' : '!=';
					$filter['value']    = $value;
					$filter['method']   = 'search';
				}

				break;

			case 'interval':
				if (is_array($value) && (count($value) > 1))
				{
					// Get the value and interval from the $value array
					if (isset($value['value']) && isset($value['interval']))
					{
						$filter['value']    = $value['value'];
						$filter['interval'] = $value['interval'];
					}
					else
					{
						$filter['value']    = array_shift($value);
						$filter['interval'] = array_shift($value);
					}
				}
				else
				{
					// $value is not a value/interval array. Treat as =
					if (is_array($value))
					{
						$value = array_shift($value);
					}

					$filter['value']    = $value;
					$filter['method']   = 'search';
					$filter['operator'] = '=';
				}
				break;

			case 'search':
				// We don't have to do anything if the operator is already set
				if (isset($filter['operator']))
				{
					break;
				}

				if (is_array($value) && (count($value) > 1))
				{
					// Get the operator and value from the $value array
					if (isset($value['operator']) && isset($value['value']))
					{
						$filter['operator'] = $value['operator'];
						$filter['value']    = $value['value'];
					}
					else
					{
						$filter['operator'] = array_shift($value);
						$filter['value']    = array_shift($value);
					}
				}
				break;

			case 'callback':
				if (!is_callable($filter['value']))
				{
					$filter['method']   = 'search';
					$filter['operator'] = '=';
					$filter['value']    = 1;
				}
				break;
		}

		if ($replace && !empty($this->relationFilters))
		{
			foreach ($this->relationFilters as $k => $v)
			{
				if ($v['relation'] == $relation)
				{
					unset ($this->relationFilters[$k]);
				}
			}
		}

		$this->relationFilters[] = $filter;

		return $this;
	}

	/**
	 * Advanced model filtering on the fulfilment of relations. Unlike has() you can provide your own callback which
	 * modifies the COUNT subquery used to compare against the relation. The $callBack has the signature
	 * function(\JDatabaseQuery $query)
	 * and MUST return a string. The $query you are passed is the COUNT subquery of the relation, e.g.
	 * SELECT COUNT(*) FROM #__comments AS reltbl WHERE reltbl.user_id = user_id
	 * You have to return a WHERE clause for the model's query, e.g.
	 * (SELECT COUNT(*) FROM #__comments AS reltbl WHERE reltbl.user_id = user_id) BETWEEN 1 AND 20
	 *
	 * @param   string    $relation  The relation to query against
	 * @param   callable  $callBack  The callback to use for filtering
	 * @param   bool      $replace   When true (default) any existing relation filters for the same relation will be
	 *                               replaced
	 *
	 * @return static
	 */
	public function whereHas($relation, $callBack, $replace = true)
	{
		$this->has($relation, 'callback', $callBack, $replace);

		return $this;
	}

	/**
	 * Returns the relations manager of the model
	 *
	 * @return RelationManager
	 */
	public function &getRelations()
	{
		return $this->relationManager;
	}

	/**
	 * Gets the relation filter definitions, for use by the RelationFilters behaviour
	 *
	 * @return array
	 */
	public function getRelationFilters()
	{
		return $this->relationFilters;
	}

	/**
	 * Returns the list of relations which are touched by save() and touch()
	 *
	 * @return array
	 */
	public function &getTouches()
	{
		return $this->touches;
	}

	/**
	 * Method to get the rules for the record.
	 *
	 * @return  Rules object
	 */
	public function getRules()
	{
		return $this->_rules;
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
		if ($input instanceof Rules)
		{
			$this->_rules = $input;
		}
		else
		{
			$this->_rules = new Rules($input);
		}
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

		$this->_trackAssets = $state;
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
		$this->_has_tags = $newState;
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 * @throws  NoAssetKey
	 *
	 */
	public function getAssetName()
	{
		$k = $this->getKeyName();

		// If there is no assetKey defined, stop here, or we'll get a wrong name
		if (!$this->_assetKey || !$this->$k)
		{
			throw new NoAssetKey;
		}

		return $this->_assetKey . '.' . (int) $this->$k;
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 */
	public function getAssetKey()
	{
		return $this->_assetKey;
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
	 * Method to return the title to use for the asset table.  In
	 * tracking the assets a title is kept for each asset so that there is some
	 * context available in a unified access manager.  Usually this would just
	 * return $this->title or $this->name or whatever is being used for the
	 * primary name of the row. If this method is not overridden, the asset name is used.
	 *
	 * @return  string  The string to use as the title in the asset table.
	 *
	 * @codeCoverageIgnore
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
	 * @param   DataModel  $model  A model object for the asset parent.
	 * @param   integer    $id     Id to look up
	 *
	 * @return  integer
	 */
	public function getAssetParentId($model = null, $id = null)
	{
		if ($model)
		{
		} // Prevent phpStorm's inspections from freaking out
		if ($id)
		{
		} // Prevent phpStorm's inspections from freaking out

		// For simple cases, parent to the asset root.
		$assets = Table::getInstance('Asset', 'JTable', ['dbo' => $this->getDbo()]);
		$rootId = $assets->getRootId();

		if (!empty($rootId))
		{
			return $rootId;
		}

		return 1;
	}

	/**
	 * Method to load a row for editing from the version history table.
	 *
	 * @param   integer  $version_id  Key to the version history table.
	 * @param   string   $alias       The type_alias in #__content_types
	 *
	 * @return  boolean  True on success
	 *
	 * @throws  RecordNotLoaded
	 * @throws  BaseException
	 * @since   2.3
	 *
	 */
	public function loadhistory($version_id, $alias)
	{
		// Only attempt to check the row in if it exists.
		if (!$version_id)
		{
			throw new RecordNotLoaded;
		}

		// Get an instance of the row to checkout.
		$historyTable = Table::getInstance('Contenthistory');

		if (!$historyTable->load($version_id))
		{
			throw new BaseException($historyTable->getError());
		}

		$rowArray = ArrayHelper::fromObject(json_decode($historyTable->version_data));

		$typeId = Table::getInstance('Contenttype')->getTypeId($alias);

		if ($historyTable->ucm_type_id != $typeId)
		{
			$key = $this->getKeyName();

			if (isset($rowArray[$key]))
			{
				$this->{$this->idFieldName} = $rowArray[$key];
				$this->unlock();
			}

			throw new BaseException(Text::_('JLIB_APPLICATION_ERROR_HISTORY_ID_MISMATCH'));
		}

		$this->setState('save_date', $historyTable->save_date);
		$this->setState('version_note', $historyTable->version_note);

		$this->bind($rowArray);

		return true;
	}

	/**
	 * Applies view access level filtering for the specified user. Useful to
	 * filter a front-end items listing.
	 *
	 * @param   integer  $userID  The user ID to use. Skip it to use the currently logged in user.
	 *
	 * @return  static  Reference to self
	 */
	public function applyAccessFiltering($userID = null)
	{
		if (!$this->hasField('access'))
		{
			return $this;
		}

		$user = $this->container->platform->getUser($userID);

		$accessField = $this->getFieldAlias('access');

		$this->setState($accessField, $user->getAuthorisedViewLevels());

		return $this;
	}

	/**
	 * Get the content type for ucm
	 *
	 * @return   string  The content type alias
	 *
	 * @throws   Exception  If you have not set the contentType configuration variable
	 */
	public function getContentType()
	{
		if ($this->contentType)
		{
			return $this->contentType;
		}

		throw new NoContentType(get_class($this));
	}

	/**
	 * Check if a UCM content type exists for this resource, and
	 * create it if it does not
	 *
	 * @param   string  $alias  The content type alias (optional)
	 *
	 * @return  void
	 */
	public function checkContentType($alias = null)
	{
		$contentType = new ContentType($this->getDbo());

		if (!$alias)
		{
			$alias = $this->getContentType();
		}

		$aliasParts = explode('.', $alias);

		// Fetch the extension name
		$component = $aliasParts[0];
		$component = ComponentHelper::getComponent($component);

		// Fetch the name using the menu item
		$query = $this->getDbo()->getQuery(true);
		$query->select('title')->from('#__menu')->where('component_id = ' . (int) $component->id);
		$this->getDbo()->setQuery($query);
		$component_name = Text::_($this->getDbo()->loadResult());

		$name = $component_name . ' ' . ucfirst($aliasParts[1]);

		// Create a new content type for our resource
		if (!$contentType->load(['type_alias' => $alias]))
		{
			$contentType->type_title = $name;
			$contentType->type_alias = $alias;
			$contentType->table      = json_encode(
				[
					'special' => [
						'dbtable' => $this->getTableName(),
						'key'     => $this->getKeyName(),
						'type'    => $name,
						'prefix'  => $this->container->getNamespacePrefix() . '\\Model\\',
						'class'   => $this->getName(),
						'config'  => 'array()',
					],
					'common'  => [
						'dbtable' => '#__ucm_content',
						'key'     => 'ucm_id',
						'type'    => 'CoreContent',
						'prefix'  => 'JTable',
						'config'  => 'array()',
					],
				]
			);

			$contentType->field_mappings = json_encode(
				[
					'common'  => [
						0 => [
							"core_content_item_id" => $this->getKeyName(),
							"core_title"           => $this->getUcmCoreAlias('title'),
							"core_state"           => $this->getUcmCoreAlias('enabled'),
							"core_alias"           => $this->getUcmCoreAlias('alias'),
							"core_created_time"    => $this->getUcmCoreAlias('created_on'),
							"core_modified_time"   => $this->getUcmCoreAlias('created_by'),
							"core_body"            => $this->getUcmCoreAlias('body'),
							"core_hits"            => $this->getUcmCoreAlias('hits'),
							"core_publish_up"      => $this->getUcmCoreAlias('publish_up'),
							"core_publish_down"    => $this->getUcmCoreAlias('publish_down'),
							"core_access"          => $this->getUcmCoreAlias('access'),
							"core_params"          => $this->getUcmCoreAlias('params'),
							"core_featured"        => $this->getUcmCoreAlias('featured'),
							"core_metadata"        => $this->getUcmCoreAlias('metadata'),
							"core_language"        => $this->getUcmCoreAlias('language'),
							"core_images"          => $this->getUcmCoreAlias('images'),
							"core_urls"            => $this->getUcmCoreAlias('urls'),
							"core_version"         => $this->getUcmCoreAlias('version'),
							"core_ordering"        => $this->getUcmCoreAlias('ordering'),
							"core_metakey"         => $this->getUcmCoreAlias('metakey'),
							"core_metadesc"        => $this->getUcmCoreAlias('metadesc'),
							"core_catid"           => $this->getUcmCoreAlias('cat_id'),
							"core_xreference"      => $this->getUcmCoreAlias('xreference'),
							"asset_id"             => $this->getUcmCoreAlias('asset_id'),
						],
					],
					'special' => [
						0 => [
						],
					],
				]
			);

			$ignoreFields = [
				$this->getUcmCoreAlias('modified_on', null),
				$this->getUcmCoreAlias('modified_by', null),
				$this->getUcmCoreAlias('locked_by', null),
				$this->getUcmCoreAlias('locked_on', null),
				$this->getUcmCoreAlias('hits', null),
				$this->getUcmCoreAlias('version', null),
			];

			$contentType->content_history_options = json_encode(
				[
					"ignoreChanges" => array_filter($ignoreFields, 'strlen'),
				]
			);

			$contentType->router = '';

			$contentType->store();
		}
	}

	/**
	 * Set a behavior param
	 *
	 * @param   string  $name   The name of the param you want to set
	 * @param   mixed   $value  The value to set
	 *
	 * @return  static   Self, for chaining
	 */
	public function setBehaviorParam($name, $value)
	{
		$this->_behaviorParams[$name] = $value;

		return $this;
	}

	/**
	 * Get a behavior param
	 *
	 * @param   string  $name     The name of the param you want to get
	 * @param   mixed   $default  The default value returned if not set
	 *
	 * @return  mixed
	 */
	public function getBehaviorParam($name, $default = null)
	{
		return $this->_behaviorParams[$name] ?? $default;
	}

	/**
	 * Set or get the backlisted filters.
	 *
	 * Note: passing a null $list to get the filter blacklist is deprecated as of FOF 3.1. Pleas use getBlacklistFilters
	 *       instead.
	 *
	 * @param   mixed    $list   A filter or list of filters to backlist. If null return the list of backlisted filter
	 * @param   boolean  $reset  Reset the blacklist if true
	 *
	 * @return  null|array  Return an array of value if $list is null
	 */
	public function blacklistFilters($list = null, $reset = false)
	{
		if (!isset($list))
		{
			return $this->getBehaviorParam('blacklistFilters', []);
		}

		if (is_string($list))
		{
			$list = (array) $list;
		}

		if (!$reset)
		{
			$list = array_unique(array_merge($this->getBehaviorParam('blacklistFilters', []), $list));
		}

		$this->setBehaviorParam('blacklistFilters', $list);

		return null;
	}

	/**
	 * Get the blacklisted filters.
	 *
	 * @return  array
	 */
	public function getBlacklistFilters()
	{
		return $this->getBehaviorParam('blacklistFilters', []);
	}

	/**
	 * This method is called by Joomla! itself when it needs to update the UCM content
	 *
	 * @return  bool
	 */
	public function updateUcmContent()
	{
		// Process the tags
		$data            = $this->getData();
		$alias           = $this->getContentType();
		$ucmContentTable = Table::getInstance('Corecontent');

		$ucm     = new UCMContent($this, $alias);
		$ucmData = $data ? $ucm->mapData($data) : $ucm->ucmData;

		$primaryId = $ucm->getPrimaryKey($ucmData['common']['core_type_id'], $ucmData['common']['core_content_item_id']);
		$result    = $ucmContentTable->load($primaryId);
		$result    = $result && $ucmContentTable->bind($ucmData['common']);
		$result    = $result && $ucmContentTable->check();
		$result    = $result && $ucmContentTable->store();
		$ucmId     = $ucmContentTable->core_content_id;

		return $result;
	}

	/**
	 * Add a field to the list of fields to be ignored by the check() method
	 *
	 * @param   string  $fieldName  The field to add (can be a field alias)
	 *
	 * @return  void
	 */
	public function addSkipCheckField($fieldName)
	{
		if (!is_array($this->fieldsSkipChecks))
		{
			$this->fieldsSkipChecks = [];
		}

		if (!$this->hasField($fieldName))
		{
			return;
		}

		$fieldName = $this->getFieldAlias($fieldName);

		if (!in_array($fieldName, $this->fieldsSkipChecks))
		{
			$this->fieldsSkipChecks[] = $fieldName;
		}
	}

	/**
	 * Remove a field from the list of fields to be ignored by the check() method
	 *
	 * @param   string  $fieldName  The field to remove (can be a field alias)
	 *
	 * @return  void
	 */
	public function removeSkipCheckField($fieldName)
	{
		if (!is_array($this->fieldsSkipChecks))
		{
			$this->fieldsSkipChecks = [];

			return;
		}

		if (!$this->hasField($fieldName))
		{
			return;
		}

		$fieldName = $this->getFieldAlias($fieldName);

		if (in_array($fieldName, $this->fieldsSkipChecks))
		{
			$index = array_search($fieldName, $this->fieldsSkipChecks);
			unset($this->fieldsSkipChecks[$index]);
		}
	}

	/**
	 * Is a field present in the list of fields to be ignored by the check() method?
	 *
	 * @param   string  $fieldName  The field to check (can be a field alias)
	 *
	 * @return  bool  True if the field is skipped from checks, false if not or if the field doesn't exist.
	 */
	public function hasSkipCheckField($fieldName)
	{
		if (!is_array($this->fieldsSkipChecks))
		{
			$this->fieldsSkipChecks = [];

			return false;
		}

		if (!$this->hasField($fieldName))
		{
			return false;
		}

		$fieldName = $this->getFieldAlias($fieldName);

		return in_array($fieldName, $this->fieldsSkipChecks);
	}

	/**
	 * Loads the asset table related to this table.
	 * This will help tests, too, since we can mock this function.
	 *
	 * @return bool|Asset     False on failure, otherwise JTableAsset
	 */
	protected function getAsset()
	{
		$name = $this->getAssetName();

		// Do NOT touch JTable here -- we are loading the core asset table which is a JTable, not a F0FTable
		$asset = Table::getInstance('Asset');

		if (!$asset->loadByName($name))
		{
			return false;
		}

		return $asset;
	}

	/**
	 * Utility methods that fetches the column name for the field.
	 * If it does not exists, returns a "null" string
	 *
	 * @param   string  $alias  The alias for the column
	 * @param   string  $null   What to return if no column exists
	 *
	 * @return string The column name
	 */
	protected function getUcmCoreAlias($alias, $null = "null")
	{
		if (!$this->hasField($alias))
		{
			return $null;
		}

		return $this->getFieldAlias($alias);
	}

	/**
	 * Returns all lower and upper case permutations of the database prefix
	 *
	 * @return  array
	 */
	protected function getPrefixCasePermutations()
	{
		if (empty(self::$prefixCasePermutations))
		{
			$prefix = $this->getDbo()->getPrefix();
			$suffix = '';

			if (substr($prefix, -1) == '_')
			{
				$suffix = '_';
				$prefix = substr($prefix, 0, -1);
			}

			$letters      = str_split($prefix, 1);
			$permutations = [''];

			foreach ($letters as $nextLetter)
			{
				$lower = strtolower($nextLetter);
				$upper = strtoupper($nextLetter);
				$ret   = [];

				foreach ($permutations as $perm)
				{
					$ret[] = $perm . $lower;

					if ($lower != $upper)
					{
						$ret[] = $perm . $upper;
					}

					$permutations = $ret;
				}
			}

			$permutations = array_merge([
				strtolower($prefix),
				strtoupper($prefix),
			], $permutations);
			$permutations = array_map(function ($x) use ($suffix) {
				return $x . $suffix;
			}, $permutations);

			self::$prefixCasePermutations = array_unique($permutations);
		}

		return self::$prefixCasePermutations;
	}
}
