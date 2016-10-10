<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  table
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

class FOFTableRelations
{
	/**
	 * Holds all known relation definitions
	 *
	 * @var   array
	 */
	protected $relations = array(
		'child'		=> array(),
		'parent'	=> array(),
		'children'	=> array(),
		'multiple'	=> array(),
	);

	/**
	 * Holds the default relations' keys
	 *
	 * @var  array
	 */
	protected $defaultRelation = array(
		'child'		=> null,
		'parent'	=> null,
		'children'	=> null,
		'multiple'	=> null,
	);

	/**
	 * The table these relations are attached to
	 *
	 * @var   FOFTable
	 */
	protected $table = null;

	/**
	 * The name of the component used by our attached table
	 *
	 * @var   string
	 */
	protected $componentName = 'joomla';

	/**
	 * The type (table name without prefix and component name) of our attached table
	 *
	 * @var   string
	 */
	protected $tableType = '';


	/**
	 * Create a relations object based on the provided FOFTable instance
	 *
	 * @param   FOFTable   $table  The table instance used to initialise the relations
	 */
	public function __construct(FOFTable $table)
	{
		// Store the table
		$this->table = $table;

		// Get the table's type from its name
		$tableName = $table->getTableName();
		$tableName = str_replace('#__', '', $tableName);
		$type = explode("_", $tableName);

		if (count($type) == 1)
		{
			$this->tableType = array_pop($type);
		}
		else
		{
			$this->componentName = array_shift($type);
			$this->tableType = array_pop($type);
		}

		$this->tableType = FOFInflector::singularize($this->tableType);

		$tableKey = $table->getKeyName();

		unset($type);

		// Scan all table keys and look for foo_bar_id fields. These fields are used to populate parent relations.
		foreach ($table->getKnownFields() as $field)
		{
			// Skip the table key name
			if ($field == $tableKey)
			{
				continue;
			}

			if (substr($field, -3) != '_id')
			{
				continue;
			}

			$parts = explode('_', $field);

			// If the component type of the field is not set assume 'joomla'
			if (count($parts) == 2)
			{
				array_unshift($parts, 'joomla');
			}

			// Sanity check
			if (count($parts) != 3)
			{
				continue;
			}

			// Make sure we skip any references back to ourselves (should be redundant, due to key field check above)
			if ($parts[1] == $this->tableType)
			{
				continue;
			}

			// Default item name: the name of the table, singular
			$itemName = FOFInflector::singularize($parts[1]);

			// Prefix the item name with the component name if we refer to a different component
			if ($parts[0] != $this->componentName)
			{
				$itemName = $parts[0] . '_' . $itemName;
			}

			// Figure out the table class
			$tableClass = ucfirst($parts[0]) . 'Table' . ucfirst($parts[1]);

			$default = empty($this->relations['parent']);

			$this->addParentRelation($itemName, $tableClass, $field, $field, $default);
		}

		// Get the relations from the configuration provider
		$key = $table->getConfigProviderKey() . '.relations';
		$configRelations = $table->getConfigProvider()->get($key, array());

		if (!empty($configRelations))
		{
			foreach ($configRelations as $relation)
			{
				if (empty($relation['type']))
				{
					continue;
				}

				if (isset($relation['pivotTable']))
				{
					$this->addMultipleRelation($relation['itemName'], $relation['tableClass'],
						$relation['localKey'], $relation['ourPivotKey'], $relation['theirPivotKey'],
						$relation['remoteKey'], $relation['pivotTable'], $relation['default']);
				}
				else
				{
					$method = 'add' . ucfirst($relation['type']). 'Relation';

					if (!method_exists($this, $method))
					{
						continue;
					}

					$this->$method($relation['itemName'], $relation['tableClass'],
						$relation['localKey'], $relation['remoteKey'], $relation['default']);
				}
			}
		}

	}

	/**
	 * Add a 1:1 forward (child) relation. This adds relations for the getChild() method.
	 *
	 * In other words: does a table HAVE ONE child
	 *
	 * Parent and child relations works the same way. We have them separated as it makes more sense for us humans to
	 * read code like $item->getParent() and $item->getChild() than $item->getRelatedObject('someRandomKeyName')
	 *
	 * @param   string   $itemName    is how it will be known locally to the getRelatedItem method (singular)
	 * @param   string   $tableClass  if skipped it is defined automatically as ComponentnameTableItemname
	 * @param   string   $localKey    is the column containing our side of the FK relation, default: our primary key
	 * @param   string   $remoteKey   is the remote table's FK column, default: componentname_itemname_id
	 * @param   boolean  $default     add as the default child relation?
	 *
	 * @return  void
	 */
	public function addChildRelation($itemName, $tableClass = null, $localKey = null, $remoteKey = null, $default = true)
	{
		$itemName = $this->normaliseItemName($itemName, false);

		if (empty($localKey))
		{
			$localKey = $this->table->getKeyName();
		}

		$this->addBespokeSimpleRelation('child', $itemName, $tableClass, $localKey, $remoteKey, $default);
	}

	/**
	 * Defining an inverse 1:1 (parent) relation. You must specify at least the $tableClass or the $localKey.
	 * This adds relations for the getParent() method.
	 *
	 * In other words: does a table BELONG TO ONE parent
	 *
	 * Parent and child relations works the same way. We have them separated as it makes more sense for us humans to
	 * read code like $item->getParent() and $item->getChild() than $item->getRelatedObject('someRandomKeyName')
	 *
	 * @param   string   $itemName    is how it will be known locally to the getRelatedItem method (singular)
	 * @param   string   $tableClass  if skipped it is defined automatically as ComponentnameTableItemname
	 * @param   string   $localKey    is the column containing our side of the FK relation, default: componentname_itemname_id
	 * @param   string   $remoteKey   is the remote table's FK column, default: componentname_itemname_id
	 * @param   boolean  $default     Is this the default parent relationship?
	 *
	 * @return  void
	 */
	public function addParentRelation($itemName, $tableClass = null, $localKey = null, $remoteKey = null, $default = true)
	{
		$itemName = $this->normaliseItemName($itemName, false);

		$this->addBespokeSimpleRelation('parent', $itemName, $tableClass, $localKey, $remoteKey, $default);
	}

	/**
	 * Defining a forward 1:∞ (children) relation. This adds relations to the getChildren() method.
	 *
	 * In other words: does a table HAVE MANY children?
	 *
	 * The children relation works very much the same as the parent and child relation. The difference is that the
	 * parent and child relations return a single table object, whereas the children relation returns an iterator to
	 * many objects.
	 *
	 * @param   string   $itemName    is how it will be known locally to the getRelatedItems method (plural)
	 * @param   string   $tableClass  if skipped it is defined automatically as ComponentnameTableItemname
	 * @param   string   $localKey    is the column containing our side of the FK relation, default: our primary key
	 * @param   string   $remoteKey   is the remote table's FK column, default: componentname_itemname_id
	 * @param   boolean  $default     is this the default children relationship?
	 *
	 * @return  void
	 */
	public function addChildrenRelation($itemName, $tableClass = null, $localKey = null, $remoteKey = null, $default = true)
	{
		$itemName = $this->normaliseItemName($itemName, true);

		if (empty($localKey))
		{
			$localKey = $this->table->getKeyName();
		}

		$this->addBespokeSimpleRelation('children', $itemName, $tableClass, $localKey, $remoteKey, $default);
	}

	/**
	 * Defining a ∞:∞ (multiple) relation. This adds relations to the getMultiple() method.
	 *
	 * In other words: is a table RELATED TO MANY other records?
	 *
	 * @param   string   $itemName       is how it will be known locally to the getRelatedItems method (plural)
	 * @param   string   $tableClass     if skipped it is defined automatically as ComponentnameTableItemname
	 * @param   string   $localKey       is the column containing our side of the FK relation, default: our primary key field name
	 * @param   string   $ourPivotKey    is the column containing our side of the FK relation in the pivot table, default: $localKey
	 * @param   string   $theirPivotKey  is the column containing the other table's side of the FK relation in the pivot table, default $remoteKey
	 * @param   string   $remoteKey      is the remote table's FK column, default: componentname_itemname_id
	 * @param   string   $glueTable      is the name of the glue (pivot) table, default: #__componentname_thisclassname_itemname with plural items (e.g. #__foobar_users_roles)
	 * @param   boolean  $default        is this the default multiple relation?
	 */
	public function addMultipleRelation($itemName, $tableClass = null, $localKey = null, $ourPivotKey = null, $theirPivotKey = null, $remoteKey = null, $glueTable = null, $default = true)
	{
		$itemName = $this->normaliseItemName($itemName, true);

		if (empty($localKey))
		{
			$localKey = $this->table->getKeyName();
		}

		$this->addBespokePivotRelation('multiple', $itemName, $tableClass, $localKey, $remoteKey, $ourPivotKey, $theirPivotKey, $glueTable, $default);
	}

	/**
	 * Removes a previously defined relation by name. You can optionally specify the relation type.
	 *
	 * @param   string  $itemName  The name of the relation to remove
	 * @param   string  $type      [optional] The relation type (child, parent, children, ...)
	 *
	 * @return  void
	 */
	public function removeRelation($itemName, $type = null)
	{
		$types = array_keys($this->relations);

		if (in_array($type, $types))
		{
			$types = array($type);
		}

		foreach ($types as $type)
		{
			foreach ($this->relations[$type] as $key => $relations)
			{
				if ($itemName == $key)
				{
					unset ($this->relations[$type][$itemName]);

                    // If it's the default one, remove it from the default array, too
                    if($this->defaultRelation[$type] == $itemName)
                    {
                        $this->defaultRelation[$type] = null;
                    }

					return;
				}
			}
		}
	}

	/**
	 * Removes all existing relations
	 *
	 * @param   string  $type  The type or relations to remove, omit to remove all relation types
	 *
	 * @return  void
	 */
	public function clearRelations($type = null)
	{
		$types = array_keys($this->relations);

		if (in_array($type, $types))
		{
			$types = array($type);
		}

		foreach ($types as $type)
		{
			$this->relations[$type] = array();

            // Remove the relation from the default stack, too
            $this->defaultRelation[$type] = null;
		}
	}

	/**
	 * Does the named relation exist? You can optionally specify the type.
	 *
	 * @param   string  $itemName  The name of the relation to check
	 * @param   string  $type      [optional] The relation type (child, parent, children, ...)
	 *
	 * @return  boolean
	 */
	public function hasRelation($itemName, $type = null)
	{
		$types = array_keys($this->relations);

		if (in_array($type, $types))
		{
			$types = array($type);
		}

		foreach ($types as $type)
		{
			foreach ($this->relations[$type] as $key => $relations)
			{
				if ($itemName == $key)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get the definition of a relation
	 *
	 * @param   string  $itemName  The name of the relation to check
	 * @param   string  $type      [optional] The relation type (child, parent, children, ...)
	 *
	 * @return  array
	 *
	 * @throws  RuntimeException  When the relation is not found
	 */
	public function getRelation($itemName, $type)
	{
		$types = array_keys($this->relations);

		if (in_array($type, $types))
		{
			$types = array($type);
		}

		foreach ($types as $type)
		{
			foreach ($this->relations[$type] as $key => $relations)
			{
				if ($itemName == $key)
				{
					$temp         = $relations;
					$temp['type'] = $type;

					return $temp;
				}
			}
		}

		throw new RuntimeException("Relation $itemName not found in table {$this->tableType}", 500);
	}

	/**
	 * Gets the item referenced by a named relation. You can optionally specify the type. Only single item relation
	 * types will be searched.
	 *
	 * @param   string  $itemName  The name of the relation to use
	 * @param   string  $type      [optional] The relation type (child, parent)
	 *
	 * @return  FOFTable
	 *
	 * @throws  RuntimeException  If the named relation doesn't exist or isn't supposed to return single items
	 */
	public function getRelatedItem($itemName, $type = null)
	{
		if (empty($type))
		{
			$relation = $this->getRelation($itemName, $type);
			$type = $relation['type'];
		}

		switch ($type)
		{
			case 'parent':
				return $this->getParent($itemName);
				break;

			case 'child':
				return $this->getChild($itemName);
				break;

			default:
				throw new RuntimeException("Invalid relation type $type for returning a single related item", 500);
				break;
		}
	}

	/**
	 * Gets the iterator for the items referenced by a named relation. You can optionally specify the type. Only
	 * multiple item relation types will be searched.
	 *
	 * @param   string  $itemName  The name of the relation to use
	 * @param   string  $type      [optional] The relation type (children, multiple)
	 *
	 * @return  FOFDatabaseIterator
	 *
	 * @throws  RuntimeException  If the named relation doesn't exist or isn't supposed to return single items
	 */
	public function getRelatedItems($itemName, $type = null)
	{
		if (empty($type))
		{
			$relation = $this->getRelation($itemName, $type);
			$type = $relation['type'];
		}

		switch ($type)
		{
			case 'children':
				return $this->getChildren($itemName);
				break;

			case 'multiple':
				return $this->getMultiple($itemName);
				break;

			case 'siblings':
				return $this->getSiblings($itemName);
				break;

			default:
				throw new RuntimeException("Invalid relation type $type for returning a collection of related items", 500);
				break;
		}
	}

	/**
	 * Gets a parent item
	 *
	 * @param   string  $itemName  [optional] The name of the relation to use, skip to use the default parent relation
	 *
	 * @return  FOFTable
	 *
	 * @throws  RuntimeException  When the relation is not found
	 */
	public function getParent($itemName = null)
	{
		if (empty($itemName))
		{
			$itemName = $this->defaultRelation['parent'];
		}

		if (empty($itemName))
		{
			throw new RuntimeException(sprintf('Default parent relation for %s not found', $this->table->getTableName()), 500);
		}

		if (!isset($this->relations['parent'][$itemName]))
		{
			throw new RuntimeException(sprintf('Parent relation %s for %s not found', $itemName, $this->table->getTableName()), 500);
		}

		return $this->getTableFromRelation($this->relations['parent'][$itemName]);
	}

	/**
	 * Gets a child item
	 *
	 * @param   string  $itemName  [optional] The name of the relation to use, skip to use the default child relation
	 *
	 * @return  FOFTable
	 *
	 * @throws  RuntimeException  When the relation is not found
	 */
	public function getChild($itemName = null)
	{
		if (empty($itemName))
		{
			$itemName = $this->defaultRelation['child'];
		}

		if (empty($itemName))
		{
			throw new RuntimeException(sprintf('Default child relation for %s not found', $this->table->getTableName()), 500);
		}

		if (!isset($this->relations['child'][$itemName]))
		{
			throw new RuntimeException(sprintf('Child relation %s for %s not found', $itemName, $this->table->getTableName()), 500);
		}

		return $this->getTableFromRelation($this->relations['child'][$itemName]);
	}

	/**
	 * Gets an iterator for the children items
	 *
	 * @param   string  $itemName  [optional] The name of the relation to use, skip to use the default children relation
	 *
	 * @return  FOFDatabaseIterator
	 *
	 * @throws  RuntimeException  When the relation is not found
	 */
	public function getChildren($itemName = null)
	{
		if (empty($itemName))
		{
			$itemName = $this->defaultRelation['children'];
		}
		if (empty($itemName))
		{
			throw new RuntimeException(sprintf('Default children relation for %s not found', $this->table->getTableName()), 500);
		}

		if (!isset($this->relations['children'][$itemName]))
		{
			throw new RuntimeException(sprintf('Children relation %s for %s not found', $itemName, $this->table->getTableName()), 500);
		}

		return $this->getIteratorFromRelation($this->relations['children'][$itemName]);
	}

	/**
	 * Gets an iterator for the sibling items. This relation is inferred from the parent relation. It returns all
	 * elements on the same table which have the same parent.
	 *
	 * @param   string  $itemName  [optional] The name of the relation to use, skip to use the default children relation
	 *
	 * @return  FOFDatabaseIterator
	 *
	 * @throws  RuntimeException  When the relation is not found
	 */
	public function getSiblings($itemName = null)
	{
		if (empty($itemName))
		{
			$itemName = $this->defaultRelation['parent'];
		}
		if (empty($itemName))
		{
			throw new RuntimeException(sprintf('Default siblings relation for %s not found', $this->table->getTableName()), 500);
		}

		if (!isset($this->relations['parent'][$itemName]))
		{
			throw new RuntimeException(sprintf('Sibling relation %s for %s not found', $itemName, $this->table->getTableName()), 500);
		}

		// Get my table class
		$tableName = $this->table->getTableName();
		$tableName = str_replace('#__', '', $tableName);
		$tableNameParts = explode('_', $tableName, 2);
		$tableClass = ucfirst($tableNameParts[0]) . 'Table' . ucfirst(FOFInflector::singularize($tableNameParts[1]));

		$parentRelation = $this->relations['parent'][$itemName];
		$relation = array(
			'tableClass'	=> $tableClass,
			'localKey'		=> $parentRelation['localKey'],
			'remoteKey'		=> $parentRelation['localKey'],
		);

		return $this->getIteratorFromRelation($relation);
	}

	/**
	 * Gets an iterator for the multiple items
	 *
	 * @param   string  $itemName  [optional] The name of the relation to use, skip to use the default multiple relation
	 *
	 * @return  FOFDatabaseIterator
	 *
	 * @throws  RuntimeException  When the relation is not found
	 */
	public function getMultiple($itemName = null)
	{
		if (empty($itemName))
		{
			$itemName = $this->defaultRelation['multiple'];
		}

		if (empty($itemName))
		{
			throw new RuntimeException(sprintf('Default multiple relation for %s not found', $this->table->getTableName()), 500);
		}

		if (!isset($this->relations['multiple'][$itemName]))
		{
			throw new RuntimeException(sprintf('Multiple relation %s for %s not found', $itemName, $this->table->getTableName()), 500);
		}

		return $this->getIteratorFromRelation($this->relations['multiple'][$itemName]);
	}

	/**
	 * Returns a FOFTable object based on a given relation
	 *
	 * @param   array    $relation   Indexed array holding relation definition.
     *                                  tableClass => name of the related table class
     *                                  localKey   => name of the local key
     *                                  remoteKey  => name of the remote key
	 *
	 * @return FOFTable
	 *
	 * @throws RuntimeException
     * @throws InvalidArgumentException
	 */
	protected function getTableFromRelation($relation)
	{
        // Sanity checks
        if(
            !isset($relation['tableClass']) || !isset($relation['remoteKey']) || !isset($relation['localKey']) ||
            !$relation['tableClass'] || !$relation['remoteKey'] || !$relation['localKey']
        )
        {
            throw new InvalidArgumentException('Missing array index for the '.__METHOD__.' method. Please check method signature', 500);
        }

		// Get a table object from the table class name
		$tableClass      = $relation['tableClass'];
		$tableClassParts = FOFInflector::explode($tableClass);

        if(count($tableClassParts) < 3)
        {
            throw new InvalidArgumentException('Invalid table class named. It should be something like FooTableBar');
        }

		$table = FOFTable::getInstance($tableClassParts[2], ucfirst($tableClassParts[0]) . ucfirst($tableClassParts[1]));

		// Get the table name
		$tableName = $table->getTableName();

		// Get the remote and local key names
		$remoteKey = $relation['remoteKey'];
		$localKey  = $relation['localKey'];

		// Get the local key's value
		$value = $this->table->$localKey;

        // If there's no value for the primary key, let's stop here
        if(!$value)
        {
            throw new RuntimeException('Missing value for the primary key of the table '.$this->table->getTableName(), 500);
        }

		// This is required to prevent one relation from killing the db cursor used in a different relation...
		$oldDb = $this->table->getDbo();
		$oldDb->disconnect(); // YES, WE DO NEED TO DISCONNECT BEFORE WE CLONE THE DB OBJECT. ARGH!
		$db = clone $oldDb;

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn($tableName))
			->where($db->qn($remoteKey) . ' = ' . $db->q($value));
		$db->setQuery($query, 0, 1);

		$data = $db->loadObject();

		if (!is_object($data))
		{
			throw new RuntimeException(sprintf('Cannot load item from relation against table %s column %s', $tableName, $remoteKey), 500);
		}

		$table->bind($data);

		return $table;
	}

	/**
	 * Returns a FOFDatabaseIterator based on a given relation
	 *
	 * @param   array    $relation   Indexed array holding relation definition.
     *                                  tableClass => name of the related table class
     *                                  localKey   => name of the local key
     *                                  remoteKey  => name of the remote key
     *                                  pivotTable    => name of the pivot table (optional)
     *                                  theirPivotKey => name of the remote key in the pivot table (mandatory if pivotTable is set)
     *                                  ourPivotKey   => name of our key in the pivot table (mandatory if pivotTable is set)
	 *
	 * @return FOFDatabaseIterator
	 *
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 */
	protected function getIteratorFromRelation($relation)
	{
        // Sanity checks
        if(
            !isset($relation['tableClass']) || !isset($relation['remoteKey']) || !isset($relation['localKey']) ||
            !$relation['tableClass'] || !$relation['remoteKey'] || !$relation['localKey']
        )
        {
            throw new InvalidArgumentException('Missing array index for the '.__METHOD__.' method. Please check method signature', 500);
        }

        if(array_key_exists('pivotTable', $relation))
        {
            if(
                !isset($relation['theirPivotKey']) || !isset($relation['ourPivotKey']) ||
                !$relation['pivotTable'] || !$relation['theirPivotKey'] || !$relation['ourPivotKey']
            )
            {
                throw new InvalidArgumentException('Missing array index for the '.__METHOD__.' method. Please check method signature', 500);
            }
        }

		// Get a table object from the table class name
		$tableClass      = $relation['tableClass'];
		$tableClassParts = FOFInflector::explode($tableClass);

        if(count($tableClassParts) < 3)
        {
            throw new InvalidArgumentException('Invalid table class named. It should be something like FooTableBar');
        }

		$table = FOFTable::getInstance($tableClassParts[2], ucfirst($tableClassParts[0]) . ucfirst($tableClassParts[1]));

		// Get the table name
		$tableName = $table->getTableName();

		// Get the remote and local key names
		$remoteKey = $relation['remoteKey'];
		$localKey  = $relation['localKey'];

		// Get the local key's value
		$value = $this->table->$localKey;

        // If there's no value for the primary key, let's stop here
        if(!$value)
        {
            throw new RuntimeException('Missing value for the primary key of the table '.$this->table->getTableName(), 500);
        }

		// This is required to prevent one relation from killing the db cursor used in a different relation...
		$oldDb = $this->table->getDbo();
		$oldDb->disconnect(); // YES, WE DO NEED TO DISCONNECT BEFORE WE CLONE THE DB OBJECT. ARGH!
		$db = clone $oldDb;

		// Begin the query
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn($tableName));

		// Do we have a pivot table?
		$hasPivot = array_key_exists('pivotTable', $relation);

		// If we don't have pivot it's a straightforward query
		if (!$hasPivot)
		{
			$query->where($db->qn($remoteKey) . ' = ' . $db->q($value));
		}
		// If we have a pivot table we have to do a subquery
		else
		{
			$subQuery = $db->getQuery(true)
				->select($db->qn($relation['theirPivotKey']))
				->from($db->qn($relation['pivotTable']))
				->where($db->qn($relation['ourPivotKey']) . ' = ' . $db->q($value));
			$query->where($db->qn($remoteKey) . ' IN (' . $subQuery . ')');
		}

		$db->setQuery($query);

		$cursor = $db->execute();

		$iterator = FOFDatabaseIterator::getIterator($db->name, $cursor, null, $tableClass);

		return $iterator;
	}

	/**
	 * Add any bespoke relation which doesn't involve a pivot table.
	 *
	 * @param   string   $relationType  The type of the relationship (parent, child, children)
	 * @param   string   $itemName      is how it will be known locally to the getRelatedItems method
	 * @param   string   $tableClass    if skipped it is defined automatically as ComponentnameTableItemname
	 * @param   string   $localKey      is the column containing our side of the FK relation, default: componentname_itemname_id
	 * @param   string   $remoteKey     is the remote table's FK column, default: componentname_itemname_id
	 * @param   boolean  $default       is this the default children relationship?
	 *
	 * @return  void
	 */
	protected function addBespokeSimpleRelation($relationType, $itemName, $tableClass, $localKey, $remoteKey, $default)
	{
		$ourPivotKey   = null;
		$theirPivotKey = null;
		$pivotTable    = null;

		$this->normaliseParameters(false, $itemName, $tableClass, $localKey, $remoteKey, $ourPivotKey, $theirPivotKey, $pivotTable);

		$this->relations[$relationType][$itemName] = array(
			'tableClass'	=> $tableClass,
			'localKey'		=> $localKey,
			'remoteKey'		=> $remoteKey,
		);

		if ($default)
		{
			$this->defaultRelation[$relationType] = $itemName;
		}
	}

	/**
	 * Add any bespoke relation which involves a pivot table.
	 *
	 * @param   string   $relationType   The type of the relationship (multiple)
	 * @param   string   $itemName       is how it will be known locally to the getRelatedItems method
	 * @param   string   $tableClass     if skipped it is defined automatically as ComponentnameTableItemname
	 * @param   string   $localKey       is the column containing our side of the FK relation, default: componentname_itemname_id
	 * @param   string   $remoteKey      is the remote table's FK column, default: componentname_itemname_id
	 * @param   string   $ourPivotKey    is the column containing our side of the FK relation in the pivot table, default: $localKey
	 * @param   string   $theirPivotKey  is the column containing the other table's side of the FK relation in the pivot table, default $remoteKey
	 * @param   string   $pivotTable     is the name of the glue (pivot) table, default: #__componentname_thisclassname_itemname with plural items (e.g. #__foobar_users_roles)
	 * @param   boolean  $default        is this the default children relationship?
	 *
	 * @return  void
	 */
	protected function addBespokePivotRelation($relationType, $itemName, $tableClass, $localKey, $remoteKey, $ourPivotKey, $theirPivotKey, $pivotTable, $default)
	{
		$this->normaliseParameters(true, $itemName, $tableClass, $localKey, $remoteKey, $ourPivotKey, $theirPivotKey, $pivotTable);

		$this->relations[$relationType][$itemName] = array(
			'tableClass'	=> $tableClass,
			'localKey'		=> $localKey,
			'remoteKey'		=> $remoteKey,
			'ourPivotKey'	=> $ourPivotKey,
			'theirPivotKey'	=> $theirPivotKey,
			'pivotTable'	=> $pivotTable,
		);

		if ($default)
		{
			$this->defaultRelation[$relationType] = $itemName;
		}
	}

	/**
	 * Normalise the parameters of a relation, guessing missing values
	 *
	 * @param   boolean  $pivot          Is this a many to many relation involving a pivot table?
	 * @param   string   $itemName       is how it will be known locally to the getRelatedItems method (plural)
	 * @param   string   $tableClass     if skipped it is defined automatically as ComponentnameTableItemname
	 * @param   string   $localKey       is the column containing our side of the FK relation, default: componentname_itemname_id
	 * @param   string   $remoteKey      is the remote table's FK column, default: componentname_itemname_id
	 * @param   string   $ourPivotKey    is the column containing our side of the FK relation in the pivot table, default: $localKey
	 * @param   string   $theirPivotKey  is the column containing the other table's side of the FK relation in the pivot table, default $remoteKey
	 * @param   string   $pivotTable     is the name of the glue (pivot) table, default: #__componentname_thisclassname_itemname with plural items (e.g. #__foobar_users_roles)
	 *
	 * @return  void
	 */
	protected function normaliseParameters($pivot = false, &$itemName, &$tableClass, &$localKey, &$remoteKey, &$ourPivotKey, &$theirPivotKey, &$pivotTable)
	{
		// Get a default table class if none is provided
		if (empty($tableClass))
		{
			$tableClassParts = explode('_', $itemName, 3);

			if (count($tableClassParts) == 1)
			{
				array_unshift($tableClassParts, $this->componentName);
			}

			if ($tableClassParts[0] == 'joomla')
			{
				$tableClassParts[0] = 'J';
			}

			$tableClass = ucfirst($tableClassParts[0]) . 'Table' . ucfirst(FOFInflector::singularize($tableClassParts[1]));
		}

		// Make sure we have both a local and remote key
		if (empty($localKey) && empty($remoteKey))
		{
            // WARNING! If we have a pivot table, this behavior is wrong!
            // Infact if we have `parts` and `groups` the local key should be foobar_part_id and the remote one foobar_group_id.
            // However, this isn't a real issue because:
            // 1. we have no way to detect the local key of a multiple relation
            // 2. this scenario never happens, since, in this class, if we're adding a multiple relation we always supply the local key
			$tableClassParts = FOFInflector::explode($tableClass);
			$localKey  = $tableClassParts[0] . '_' . $tableClassParts[2] . '_id';
			$remoteKey = $localKey;
		}
		elseif (empty($localKey) && !empty($remoteKey))
		{
			$localKey = $remoteKey;
		}
		elseif (!empty($localKey) && empty($remoteKey))
		{
            if($pivot)
            {
                $tableClassParts = FOFInflector::explode($tableClass);
                $remoteKey = $tableClassParts[0] . '_' . $tableClassParts[2] . '_id';
            }
            else
            {
                $remoteKey = $localKey;
            }
		}

		// If we don't have a pivot table nullify the relevant variables and return
		if (!$pivot)
		{
			$ourPivotKey   = null;
			$theirPivotKey = null;
			$pivotTable    = null;

			return;
		}

		if (empty($ourPivotKey))
		{
			$ourPivotKey = $localKey;
		}

		if (empty($theirPivotKey))
		{
			$theirPivotKey = $remoteKey;
		}

		if (empty($pivotTable))
		{
			$pivotTable = '#__' . strtolower($this->componentName) . '_' .
							strtolower(FOFInflector::pluralize($this->tableType)) . '_';

			$itemNameParts = explode('_', $itemName);
			$lastPart = array_pop($itemNameParts);
			$pivotTable .= strtolower($lastPart);
		}
	}

	/**
	 * Normalises the format of a relation name
	 *
	 * @param   string   $itemName   The raw relation name
	 * @param   boolean  $pluralise  Should I pluralise the name? If not, I will singularise it
	 *
	 * @return  string  The normalised relation key name
	 */
	protected function normaliseItemName($itemName, $pluralise = false)
	{
		// Explode the item name
		$itemNameParts = explode('_', $itemName);

		// If we have multiple parts the first part is considered to be the component name
		if (count($itemNameParts) > 1)
		{
			$prefix = array_shift($itemNameParts);
		}
		else
		{
			$prefix = null;
		}

		// If we still have multiple parts we need to pluralise/singularise the last part and join everything in
		// CamelCase format
		if (count($itemNameParts) > 1)
		{
			$name = array_pop($itemNameParts);
			$name = $pluralise ? FOFInflector::pluralize($name) : FOFInflector::singularize($name);
			$itemNameParts[] = $name;

			$itemName = FOFInflector::implode($itemNameParts);
		}
		// Otherwise we singularise/pluralise the remaining part
		else
		{
			$name = array_pop($itemNameParts);
			$itemName = $pluralise ? FOFInflector::pluralize($name) : FOFInflector::singularize($name);
		}

		if (!empty($prefix))
		{
			$itemName = $prefix . '_' . $itemName;
		}

		return $itemName;
	}
}