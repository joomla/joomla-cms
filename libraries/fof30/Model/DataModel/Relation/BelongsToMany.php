<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model\DataModel\Relation;

defined('_JEXEC') || die;

use FOF30\Model\DataModel;
use FOF30\Model\DataModel\Relation;
use JDatabaseQuery;

/**
 * BelongsToMany (many-to-many) relation: one or more records of this model are related to one or more records in the
 * foreign model.
 *
 * For example, parentModel is Users and foreignModel is Groups. Each user can be assigned to many groups. Each group
 * can be assigned to many users.
 */
class BelongsToMany extends Relation
{
	/**
	 * Public constructor. Initialises the relation.
	 *
	 * @param   DataModel  $parentModel       The data model we are attached to
	 * @param   string     $foreignModelName  The name of the foreign key's model in the format
	 *                                        "modelName@com_something"
	 * @param   string     $localKey          The local table key for this relation, default: parentModel's ID field
	 *                                        name
	 * @param   string     $foreignKey        The foreign key for this relation, default: parentModel's ID field name
	 * @param   string     $pivotTable        For many-to-many relations, the pivot (glue) table
	 * @param   string     $pivotLocalKey     For many-to-many relations, the pivot table's column storing the local
	 *                                        key
	 * @param   string     $pivotForeignKey   For many-to-many relations, the pivot table's column storing the foreign
	 *                                        key
	 *
	 * @throws  DataModel\Relation\Exception\PivotTableNotFound
	 */
	public function __construct(DataModel $parentModel, $foreignModelName, $localKey = null, $foreignKey = null, $pivotTable = null, $pivotLocalKey = null, $pivotForeignKey = null)
	{
		parent::__construct($parentModel, $foreignModelName, $localKey, $foreignKey, $pivotTable, $pivotLocalKey, $pivotForeignKey);

		if (empty($localKey))
		{
			$this->localKey = $parentModel->getIdFieldName();
		}

		if (empty($pivotLocalKey))
		{
			$this->pivotLocalKey = $this->localKey;
		}

		if (empty($foreignKey))
		{
			/** @var DataModel $foreignModel */
			$foreignModel = $this->getForeignModel();
			$foreignModel->setIgnoreRequest(true);

			$this->foreignKey = $foreignModel->getIdFieldName();
		}

		if (empty($pivotForeignKey))
		{
			$this->pivotForeignKey = $this->foreignKey;
		}

		if (empty($pivotTable))
		{
			// Get the local model's name (e.g. "users")
			$localName = $parentModel->getName();
			$localName = strtolower($localName);

			// Get the foreign model's name (e.g. "groups")
			if (!isset($foreignModel))
			{
				/** @var DataModel $foreignModel */
				$foreignModel = $this->getForeignModel();
				$foreignModel->setIgnoreRequest(true);
			}

			$foreignName = $foreignModel->getName();
			$foreignName = strtolower($foreignName);

			// Get the local model's app name
			$parentModelBareComponent  = $parentModel->getContainer()->bareComponentName;
			$foreignModelBareComponent = $foreignModel->getContainer()->bareComponentName;

			// There are two possibilities for the table name: #__component_local_foreign or #__component_foreign_local.
			// There are also two possibilities for a component name (local or foreign model's)
			$db     = $parentModel->getDbo();
			$prefix = $db->getPrefix();

			$tableNames = [
				'#__' . strtolower($parentModelBareComponent) . '_' . $localName . '_' . $foreignName,
				'#__' . strtolower($parentModelBareComponent) . '_' . $foreignName . '_' . $localName,
				'#__' . strtolower($foreignModelBareComponent) . '_' . $localName . '_' . $foreignName,
				'#__' . strtolower($foreignModelBareComponent) . '_' . $foreignName . '_' . $localName,
			];

			$allTables = $db->getTableList();

			$this->pivotTable = null;

			foreach ($tableNames as $tableName)
			{
				$checkName = $prefix . substr($tableName, 3);

				if (in_array($checkName, $allTables))
				{
					$this->pivotTable = $tableName;
				}
			}

			if (empty($this->pivotTable))
			{
				throw new DataModel\Relation\Exception\PivotTableNotFound("Pivot table for many-to-many relation between '$localName and '$foreignName' not found'");
			}
		}
	}

	/**
	 * Populates the internal $this->data collection from the contents of the provided collection. This is used by
	 * DataModel to push the eager loaded data into each item's relation.
	 *
	 * @param   DataModel\Collection  $data    The relation data to push into this relation
	 * @param   mixed                 $keyMap  Passes around the local to foreign key map
	 *
	 * @return void
	 */
	public function setDataFromCollection(DataModel\Collection &$data, $keyMap = null)
	{
		$this->data = new DataModel\Collection();

		if (!is_array($keyMap))
		{
			return;
		}

		if (!empty($data))
		{
			// Get the local key value
			$localKeyValue = $this->parentModel->getFieldValue($this->localKey);

			// Make sure this local key exists in the (cached) pivot table
			if (!isset($keyMap[$localKeyValue]))
			{
				return;
			}

			/** @var DataModel $item */
			foreach ($data as $key => $item)
			{
				// Only accept foreign items whose key is associated in the pivot table with our local key
				if (in_array($item->getFieldValue($this->foreignKey), $keyMap[$localKeyValue]))
				{
					$this->data->add($item);
				}
			}
		}
	}

	/**
	 * Returns the count sub-query for DataModel's has() and whereHas() methods.
	 *
	 * @param   string  $tableAlias  The alias of the local table in the query. Leave blank to use the table's name.
	 *
	 * @return JDatabaseQuery
	 */
	public function getCountSubquery($tableAlias = null)
	{
		/** @var DataModel $foreignModel */
		$foreignModel = $this->getForeignModel();
		$foreignModel->setIgnoreRequest(true);

		$db = $foreignModel->getDbo();

		if (empty($tableAlias))
		{
			$tableAlias = $this->parentModel->getTableName();
		}

		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn($foreignModel->getTableName()) . ' AS ' . $db->qn('reltbl'))
			->innerJoin(
				$db->qn($this->pivotTable) . ' AS ' . $db->qn('pivotTable') . ' ON('
				. $db->qn('pivotTable') . '.' . $db->qn($this->pivotForeignKey) . ' = '
				. $db->qn('reltbl') . '.' . $db->qn($foreignModel->getFieldAlias($this->foreignKey))
				. ')'
			)
			->where(
				$db->qn('pivotTable') . '.' . $db->qn($this->pivotLocalKey) . ' ='
				. $db->qn($tableAlias) . '.'
				. $db->qn($this->parentModel->getFieldAlias($this->localKey))
			);

		return $query;
	}

	/**
	 * Saves all related items. For many-to-many relations there are two things we have to do:
	 * 1. Save all related items; and
	 * 2. Overwrite the pivot table data with the new associations
	 */
	public function saveAll()
	{
		// Save all related items
		parent::saveAll();

		$this->saveRelations();
	}

	/**
	 * Overwrite the pivot table data with the new associations
	 */
	public function saveRelations()
	{
		// Get all the new keys
		$newKeys = [];

		if ($this->data instanceof DataModel\Collection)
		{
			foreach ($this->data as $item)
			{
				if ($item instanceof DataModel)
				{
					$newKeys[] = $item->getId();
				}
				elseif (!is_object($item))
				{
					$newKeys[] = $item;
				}
			}
		}

		$newKeys = array_unique($newKeys);

		$db            = $this->parentModel->getDbo();
		$localKeyValue = $this->parentModel->getFieldValue($this->localKey);

		// Kill all existing relations in the pivot table
		$query = $db->getQuery(true)
			->delete($db->qn($this->pivotTable))
			->where($db->qn($this->pivotLocalKey) . ' = ' . $db->q($localKeyValue));
		$db->setQuery($query);
		$db->execute();

		// Write the new relations to the database
		$protoQuery = $db->getQuery(true)
			->insert($db->qn($this->pivotTable))
			->columns([$db->qn($this->pivotLocalKey), $db->qn($this->pivotForeignKey)]);

		$i     = 0;
		$query = null;

		foreach ($newKeys as $key)
		{
			$i++;

			if (is_null($query))
			{
				$query = clone $protoQuery;
			}

			$query->values($db->q($localKeyValue) . ', ' . $db->q($key));

			if (($i % 50) == 0)
			{
				$db->setQuery($query);
				$db->execute();
				$query = null;
			}
		}

		if (!is_null($query))
		{
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * This is not supported by the belongsTo relation
	 *
	 * @throws DataModel\Relation\Exception\NewNotSupported when it's not supported
	 */
	public function getNew()
	{
		throw new DataModel\Relation\Exception\NewNotSupported("getNew() is not supported for many-to-may relations. Please add/remove items from the relation data and use push() to effect changes.");
	}

	/**
	 * Applies the relation filters to the foreign model when getData is called
	 *
	 * @param   DataModel             $foreignModel    The foreign model you're operating on
	 * @param   DataModel\Collection  $dataCollection  If it's an eager loaded relation, the collection of loaded
	 *                                                 parent records
	 *
	 * @return boolean Return false to force an empty data collection
	 */
	protected function filterForeignModel(DataModel $foreignModel, DataModel\Collection $dataCollection = null)
	{
		$db = $this->parentModel->getDbo();

		// Decide how to proceed, based on eager or lazy loading
		if (is_object($dataCollection))
		{
			// Eager loaded relation
			if (!empty($dataCollection))
			{
				// Get a list of local keys from the collection
				$values = [];

				/** @var $item DataModel */
				foreach ($dataCollection as $item)
				{
					$v = $item->getFieldValue($this->localKey, null);

					if (!is_null($v))
					{
						$values[] = $v;
					}
				}

				// Keep only unique values
				$values = array_unique($values);
				$values = array_map(function ($x) use (&$db) {
					return $db->q($x);
				}, $values);

				// Get the foreign keys from the glue table
				$query = $db->getQuery(true)
					->select([$db->qn($this->pivotLocalKey), $db->qn($this->pivotForeignKey)])
					->from($db->qn($this->pivotTable))
					->where($db->qn($this->pivotLocalKey) . ' IN(' . implode(',', $values) . ')');
				$db->setQuery($query);
				$foreignKeysUnmapped = $db->loadRowList();

				$this->foreignKeyMap = [];
				$foreignKeys         = [];

				foreach ($foreignKeysUnmapped as $unmapped)
				{
					$local   = $unmapped[0];
					$foreign = $unmapped[1];

					if (!isset($this->foreignKeyMap[$local]))
					{
						$this->foreignKeyMap[$local] = [];
					}

					$this->foreignKeyMap[$local][] = $foreign;

					$foreignKeys[] = $foreign;
				}

				// Keep only unique values. However, the array keys are all screwed up. See below.
				$foreignKeys = array_unique($foreignKeys);

				// This looks stupid, but it's required to reset the array keys. Without it where() below fails.
				$foreignKeys = array_merge($foreignKeys);

				// Apply the filter
				if (!empty($foreignKeys))
				{
					$foreignModel->where($this->foreignKey, 'in', $foreignKeys);
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			// Lazy loaded relation; get the single local key
			$localKey = $this->parentModel->getFieldValue($this->localKey, null);

			if (is_null($localKey) || ($localKey === ''))
			{
				return false;
			}

			$query = $db->getQuery(true)
				->select($db->qn($this->pivotForeignKey))
				->from($db->qn($this->pivotTable))
				->where($db->qn($this->pivotLocalKey) . ' = ' . $db->q($localKey));
			$db->setQuery($query);
			$foreignKeys = $db->loadColumn();

			$this->foreignKeyMap[$localKey] = $foreignKeys;

			// If there are no foreign keys (no foreign items assigned to our item) we return false which then causes
			// the relation to return null, marking the lack of data.
			if (empty($foreignKeys))
			{
				return false;
			}

			$foreignModel->where($this->foreignKey, 'in', $this->foreignKeyMap[$localKey]);
		}

		return true;
	}
}
