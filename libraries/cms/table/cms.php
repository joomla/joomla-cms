<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Class JTableCms
 * This is a simplifed table class
 */
abstract class JTableCms implements JTableCmsInterface
{
	/**
	 * Name of the database tableName.
	 *
	 * @var    string
	 */
	protected $table;

	/**
	 * Name of the primary key field.
	 *
	 * @var    string
	 */
	protected $primaryKey;

	/**
	 * Name of the asset id field
	 *
	 * @var string
	 */
	protected $assetField = 'asset_id';

	/**
	 * Name of the state field
	 *
	 * @var string
	 */
	protected $stateField = 'state';

	/**
	 * Name of the ordering field
	 *
	 * @var string
	 */
	protected $orderingField = 'ordering';

	/**
	 * JDatabaseDriver object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $dbo;

	/**
	 * Configuration Array
	 *
	 * @var array
	 */
	protected $config;


	public function __construct($config = array())
	{
		//make sure we have some defaults
		if (!isset($config['table']['name']))
		{
			$prefix  = '#__' . substr($config['option'], 4);
			$postfix = '_' . $config['subject'];

			$config['table']['name'] = strtolower($prefix.$postfix);
		}

		if (!isset($config['table']['key']))
		{
			$config['table']['key'] = $config['subject'] . '_id';
		}

		if (!isset($config['dbo']))
		{
			$config['dbo'] = $this->getDbo();
		}

		//set properties
		$this->table = $config['table']['name'];
		$this->primaryKey = $config['table']['key'];
		$this->config = $config;
	}

	public function getDbo()
	{
		if(!($this->dbo instanceof JDatabaseDriver))
		{
			$this->dbo = JFactory::getDbo();
		}

		return $this->dbo;
	}

	/**
	 * Get the columns from database table.
	 *
	 * @return  array  An array of the field names.
	 *
	 * @throws  UnexpectedValueException
	 */
	public function getFields()
	{
		static $cache = null;

		if ($cache === null)
		{
			$dbo = $this->getDbo();
			// Lookup the fields for this table only once.
			$fields = $dbo->getTableColumns($this->table, false);

			if (empty($fields))
			{
				$msg = JText::_('BABELU_LIB_TABLE_ERROR_NO_COLUMNS_FOUND_FOR_THIS_TABLE');
				throw new UnexpectedValueException($msg.':'.$this->table);
			}

			$cache = $fields;
		}

		return $cache;
	}

	/**
	 * Method to get the name of the field to store the asset id
	 *
	 * @return string
	 */
	public function getAssetField()
	{
		return $this->assetField;
	}

	/**
	 * Method to return the title to use for the asset table.
	 * By default the asset name is used.
	 * A title is kept for each asset so that in the future there is some
	 * context available in a unified access manager.
	 *
	 * @return  string  The string to use as the title in the asset table.
	 */
	public function getAssetTitle()
	{
		return $this->getAssetName();
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form option.subject.primaryKeyValue
	 *
	 * @param bool $withKeys should the name be returned with primary key value
	 *
	 * @return  string
	 */
	public function getAssetName($withKeys = true)
	{
		$config = $this->config;

		$assetName = $config['option'] . '.' . $config['subject'];

		if($withKeys)
		{
			$key = $this->getKeyName();
			$assetName .= '.'. (int) $this->$key;
		}

		return $assetName;
	}

	/**
	 * Method to get the primary key field name for the table.
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return  string  The name of the primary key for the table.
	 */
	public function getKeyName()
	{
		if(empty($this->primaryKey))
		{
			$msg = JText::_('BABELU_LIB_TABLE_ERROR_EMPTY_PRIMARY_KEY');
			throw new InvalidArgumentException($msg);
		}

		return $this->primaryKey;
	}

	/**
	 * Method to get the name of field used for state
	 * @return string
	 */
	public function getStateField()
	{
		return $this->stateField;
	}

	/**
	 * Method to get the name fo the field used for ordering
	 * @return string
	 */
	public function getOrderingField()
	{
		return $this->orderingField;
	}

	public function create($src, $ignore = array())
	{
		$this->reset();
		$this->bind($src, $ignore);
		$this->check();
		$this->prepareForCreate();

		$dbo = $this->getDbo();
		$query = $dbo->getQuery(true);

		$query->insert($this->table);

		$fields = $this->getFields();

		foreach($fields AS $field => $definition )
		{
			$this->setField($query,$field);
		}

		$dbo->setQuery($query);

		if(!$dbo->execute())
		{
			$msg = JText::_('BABELU_LIB_TABLE_ERROR_CREATE_FAILED');
			throw new ErrorException($msg);
		}

		$key = $this->getKeyName();
		//set the primary key
		$insertId = $dbo->insertid();
		if(!empty($insertId))
		{
			$this->$key = $insertId;
		}

		return true;
	}

	public function bind($src, $ignore = array())
	{
		$isObject = is_object($src);
		$isArray = is_array($src);
		// If the source value is not an array or object return false.
		if (!$isObject && !$isArray)
		{
			$msg = JText::_('BABELU_LIB_TABLE_ERROR_INVALID_SRC_DATATYPE').' : '.gettype($src);
			throw new InvalidArgumentException($msg);
		}

		// If the source value is an object, get its accessible properties.
		if ($isObject)
		{
			$src = get_object_vars($src);
		}

		//encode the params
		$hasParams = (isset($src['params']) && is_array($src['params']));
		if($hasParams)
		{
			$src['params'] = json_encode($src['params']);
		}

		$hasMetadata = (isset($src['metadata']) && is_array($src['metadata']));

		if($hasMetadata)
		{
			$src['metadata'] = json_encode($src['metadata']);
		}

		// If the ignore value is not an array throw error
		if (!is_array($ignore))
		{
			$msg = JText::_('BABELU_LIB_TABLE_ERROR_IGNORE_MUST_BE_AN_ARRAY');
			throw new InvalidArgumentException($msg);
		}

		$properties = $this->getFields();
		foreach($properties AS $property => $value)
		{
			$shouldBind = (!in_array($property, $ignore) && isset($src[$property]));
			if($shouldBind)
			{
				$this->$property = $src[$property];
			}
		}

		return true;
	}


	public function check()
	{
		return true;
	}

	/**
	 * Method to prep the table before executing the create method
	 * @return bool
	 */
	protected function prepareForCreate()
	{
		//Make sure there is no primary key set
		$key = $this->getKeyName();

		$this->$key = "NULL";

		$orderField = $this->getOrderingField();
		if($this->supportsOrdering() && empty($this->$orderField))
		{
			$dbo = $this->getDbo();
			$query = $dbo->getQuery(true);
			$query->select('MAX('.$orderField.')');
			$query->from($dbo->quoteName($this->table));

			$where = $this->getReorderConditions();
			if(!empty($where))
			{
				$query->where($where);
			}

			$dbo->setQuery($query);
			$max = $dbo->loadResult();

			$this->$orderField = $max + 1;
		}

		return true;

	}

	public function load($pk, $ignore = array(), $reset = true)
	{
		if($reset)
		{
			$this->reset();
		}

		if($pk != 0)
		{
			$dbo = $this->getDbo();
			$query = $dbo->getQuery(true);

			$query->select('*');
			$query->from($this->table);

			$primaryKey = $this->getKeyName();
			$query->where($dbo->quoteName($primaryKey) .' = '. $dbo->quote($pk));

			$dbo->setQuery($query);

			$src = $dbo->loadAssoc();

			if(!empty($src))
			{
				$this->bind($src, $ignore);
			}
		}

		return true;
	}

	public function reset()
	{
		$fields = $this->getFields();

		foreach($fields AS $field => $definition)
		{
			$this->$field = null;
		}
	}

	/**
	 * Method to set the fields to the query.
	 *
	 * @param JDatabaseQuery  $query
	 * @param string          $name name of the field
	 * @param boolean         $updateNulls
	 *
	 * @return $this
	 */
	protected function setField($query, $name, $updateNulls = false)
	{
		$dbo = $this->getDbo();
		if(is_numeric($this->$name) && $this->$name == 0)
		{
			$this->$name = 'NULL';
		}

		if(!empty($this->$name) || $updateNulls)
		{
			$query->set($dbo->quoteName($name). ' = '. $dbo->quote($this->$name));
		}

		return $this;
	}

	public function update($src, $ignore = array(), $updateNulls = false, $loadFirst = false)
	{
		if($loadFirst)
		{
			$key = $this->getKeyName();
			$this->load($src[$key],$ignore, true);
		}
		else
		{
			$this->reset();
		}

		$this->bind($src, $ignore);
		$this->check();

		$dbo = $this->getDbo();
		$query = $dbo->getQuery(true);

		$query->update($this->table);

		$fields = $this->getFields();

		foreach($fields AS $field => $definition)
		{
			// This can probably be moved over to the setField method
			if(is_null($this->$field) && $updateNulls)
			{
				$this->$field = 'NULL';
			}

			$this->setField($query, $field, $updateNulls);
		}

		$key = $this->getKeyName();
		$query->where($dbo->quoteName($key).' = '. (int)$this->$key);


		$dbo->setQuery($query);

		if(!$dbo->execute())
		{
			$msg = JText::_('BABELU_LIB_TABLE_ERROR_UPDATE_FAILED');
			throw new ErrorException($msg);
		}
	}

	public function delete($pk)
	{
		if(empty($pk))
		{
			$msg = JText::_('BABELU_LIB_TABLE_DELETE_ERROR_INVALID_PRIMARY_KEY');
			throw new InvalidArgumentException($msg);
		}

		$dbo = $this->getDbo();
		$query = $dbo->getQuery(true);
		$query->delete($this->table);

		$primaryKey = $this->getKeyName();
		$query->where($dbo->quoteName($primaryKey) .' = '. $dbo->quote($pk));

		$dbo->setQuery($query);

		if(!$dbo->execute())
		{
			$msg = JText::_('BABELU_LIB_TABLE_ERROR_DELETE_FAILED') .' : '.$pk;
			throw new ErrorException($msg);
		}

		return true;
	}

	/**
	 * Method to check if the current table supports the ordering field
	 *
	 * @return bool
	 */
	public function supportsOrdering()
	{
		if(!property_exists($this, $this->getKeyName()))
		{
			// Reset the table, so the fields are set to the object
			$this->reset();
		}

		if (property_exists($this, $this->orderingField))
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to get an SQL WHERE clause to order by
	 * This method is intended to be overridden by children classes if needed
	 *
	 * @return string
	 */
	public function getReorderConditions()
	{
		return '';
	}
}