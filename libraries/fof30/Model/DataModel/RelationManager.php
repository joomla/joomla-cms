<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model\DataModel;

defined('_JEXEC') || die;

use DirectoryIterator;
use FOF30\Model\DataModel;
use InvalidArgumentException;
use JDatabaseQuery;

class RelationManager
{
	/** @var array The known relation types */
	protected static $relationTypes = [];
	/** @var DataModel The data model we are attached to */
	protected $parentModel = null;
	/** @var Relation[] The relations known to us */
	protected $relations = [];
	/** @var array A list of the names of eager loaded relations */
	protected $eager = [];

	/**
	 * Creates a new relation manager for the defined parent model
	 *
	 * @param   DataModel  $parentModel  The model we are attached to
	 */
	public function __construct(DataModel $parentModel)
	{
		// Set the parent model
		$this->parentModel = $parentModel;

		// Make sure the relation types are initialised
		static::getRelationTypes();

		// @todo Maybe set up a few relations automatically?
	}

	/**
	 * Populates the static map of relation type methods and relation handling classes
	 *
	 * @return array Key = method name, Value = relation handling class
	 */
	public static function getRelationTypes()
	{
		if (empty(static::$relationTypes))
		{
			$relationTypeDirectory = __DIR__ . '/Relation';
			$fs                    = new DirectoryIterator($relationTypeDirectory);

			/** @var $file DirectoryIterator */
			foreach ($fs as $file)
			{
				if ($file->isDir())
				{
					continue;
				}

				if ($file->getExtension() != 'php')
				{
					continue;
				}

				$baseName   = ucfirst($file->getBasename('.php'));
				$methodName = strtolower($baseName[0]) . substr($baseName, 1);
				$className  = '\\FOF30\\Model\\DataModel\\Relation\\' . $baseName;

				if (!class_exists($className, true))
				{
					continue;
				}

				static::$relationTypes[$methodName] = $className;
			}
		}

		return static::$relationTypes;
	}

	/**
	 * Implements deep cloning of the relation object
	 */
	function __clone()
	{
		$relations = [];

		if (!empty($this->relations))
		{
			/** @var Relation[] $relations */
			foreach ($this->relations as $key => $relation)
			{
				$relations[$key] = clone($relation);
				$relations[$key]->reset();
			}
		}

		$this->relations = $relations;
	}

	/**
	 * Rebase a relation manager
	 *
	 * @param   DataModel  $parentModel
	 */
	public function rebase(DataModel $parentModel)
	{
		$this->parentModel = $parentModel;

		if (count($this->relations))
		{
			foreach ($this->relations as $name => $relation)
			{
				/** @var Relation $relation */
				$relation->rebase($parentModel);
			}
		}
	}

	/**
	 * Populates the internal $this->data collection of a relation from the contents of the provided collection. This is
	 * used by DataModel to push the eager loaded data into each item's relation.
	 *
	 * @param   string      $name    Relation name
	 * @param   Collection  $data    The relation data to push into this relation
	 * @param   mixed       $keyMap  Used by many-to-many relations to pass around the local to foreign key map
	 *
	 * @return void
	 *
	 * @throws Relation\Exception\RelationNotFound
	 */
	public function setDataFromCollection($name, Collection &$data, $keyMap = null)
	{
		if (!isset($this->relations[$name]))
		{
			throw new DataModel\Relation\Exception\RelationNotFound("Relation '$name' not found");
		}

		$this->relations[$name]->setDataFromCollection($data, $keyMap);
	}

	/**
	 * Adds a relation to the relation manager
	 *
	 * @param   string  $name              The name of the relation as known to this relation manager, e.g. 'phone'
	 * @param   string  $type              The relation type, e.g. 'hasOne'
	 * @param   string  $foreignModelName  The name of the foreign key's model in the format "modelName@com_something"
	 * @param   string  $localKey          The local table key for this relation
	 * @param   string  $foreignKey        The foreign key for this relation
	 * @param   string  $pivotTable        For many-to-many relations, the pivot (glue) table
	 * @param   string  $pivotLocalKey     For many-to-many relations, the pivot table's column storing the local key
	 * @param   string  $pivotForeignKey   For many-to-many relations, the pivot table's column storing the foreign key
	 *
	 * @return DataModel The parent model, for chaining
	 *
	 * @throws Relation\Exception\RelationTypeNotFound when $type is not known
	 * @throws Relation\Exception\ForeignModelNotFound when $foreignModelClass doesn't exist
	 */
	public function addRelation($name, $type, $foreignModelName = null, $localKey = null, $foreignKey = null, $pivotTable = null, $pivotLocalKey = null, $pivotForeignKey = null)
	{
		if (!isset(static::$relationTypes[$type]))
		{
			throw new DataModel\Relation\Exception\RelationTypeNotFound("Relation type '$type' not found");
		}

		// Guess the foreign model class if necessary
		if (empty($foreignModelName))
		{
			$foreignModelName = ucfirst($name);
		}

		$className = static::$relationTypes[$type];

		/** @var Relation $relation */
		$relation = new $className($this->parentModel, $foreignModelName, $localKey, $foreignKey,
			$pivotTable, $pivotLocalKey, $pivotForeignKey);

		$this->relations[$name] = $relation;

		return $this->parentModel;
	}

	/**
	 * Removes a known relation
	 *
	 * @param   string  $name  The name of the relation to remove
	 *
	 * @return DataModel The parent model, for chaining
	 */
	public function removeRelation($name)
	{
		if (isset($this->relations[$name]))
		{
			unset ($this->relations[$name]);
		}

		return $this->parentModel;
	}

	/**
	 * Removes all known relations
	 */
	public function resetRelations()
	{
		$this->relations = [];
	}

	/**
	 * Resets the data of all relations in this manager. This doesn't remove relations, just their data so that they
	 * get loaded again.
	 *
	 * @param   array  $relationsToReset  The names of the relations to reset. Pass an empty array (default) to reset
	 *                                    all relations.
	 */
	public function resetRelationData(array $relationsToReset = [])
	{
		/** @var Relation $relation */
		foreach ($this->relations as $name => $relation)
		{
			if (!empty($relationsToReset) && !in_array($name, $relationsToReset))
			{
				continue;
			}

			$relation->reset();
		}
	}

	/**
	 * Returns a list of all known relations' names
	 *
	 * @return array
	 */
	public function getRelationNames()
	{
		return array_keys($this->relations);
	}

	/**
	 * Gets the related items of a relation
	 *
	 * @param   string  $name  The name of the relation to return data for
	 *
	 * @return Relation
	 *
	 * @throws Relation\Exception\RelationNotFound
	 */
	public function &getRelation($name)
	{
		if (!isset($this->relations[$name]))
		{
			throw new DataModel\Relation\Exception\RelationNotFound("Relation '$name' not found");
		}

		return $this->relations[$name];
	}


	/**
	 * Get a new related item which satisfies relation $name and adds it to this relation's data list.
	 *
	 * @param   string  $name  The relation based on which a new item is returned
	 *
	 * @return DataModel
	 *
	 * @throws Relation\Exception\RelationNotFound
	 */
	public function getNew($name)
	{
		if (!isset($this->relations[$name]))
		{
			throw new DataModel\Relation\Exception\RelationNotFound("Relation '$name' not found");
		}

		return $this->relations[$name]->getNew();
	}

	/**
	 * Saves all related items belonging to the specified relation or, if $name is null, all known relations which
	 * support saving.
	 *
	 * @param   null|string  $name  The relation to save, or null to save all known relations
	 *
	 * @return DataModel The parent model, for chaining
	 *
	 * @throws Relation\Exception\RelationNotFound
	 */
	public function save($name = null)
	{
		if (is_null($name))
		{
			foreach ($this->relations as $name => $relation)
			{
				try
				{
					$relation->saveAll();
				}
				catch (DataModel\Relation\Exception\SaveNotSupported $e)
				{
					// We don't care if a relation doesn't support saving
				}
			}
		}
		else
		{
			if (!isset($this->relations[$name]))
			{
				throw new DataModel\Relation\Exception\RelationNotFound("Relation '$name' not found");
			}

			$this->relations[$name]->saveAll();
		}

		return $this->parentModel;
	}

	/**
	 * Gets the related items of a relation
	 *
	 * @param   string                   $name            The name of the relation to return data for
	 * @param   callable                 $callback        A callback to customise the returned data
	 * @param   \FOF30\Utils\Collection  $dataCollection  Used when fetching the data of an eager loaded relation
	 *
	 * @return Collection|DataModel
	 *
	 * @throws Relation\Exception\RelationNotFound
	 * @see Relation::getData()
	 *
	 */
	public function getData($name, $callback = null, \FOF30\Utils\Collection $dataCollection = null)
	{
		if (!isset($this->relations[$name]))
		{
			throw new DataModel\Relation\Exception\RelationNotFound("Relation '$name' not found");
		}

		return $this->relations[$name]->getData($callback, $dataCollection);
	}

	/**
	 * Gets the foreign key map of a many-to-many relation
	 *
	 * @param   string  $name  The name of the relation to return data for
	 *
	 * @return array
	 *
	 * @throws Relation\Exception\RelationNotFound
	 */
	public function &getForeignKeyMap($name)
	{
		if (!isset($this->relations[$name]))
		{
			throw new DataModel\Relation\Exception\RelationNotFound("Relation '$name' not found");
		}

		return $this->relations[$name]->getForeignKeyMap();
	}

	/**
	 * Returns the count sub-query for a relation, used for relation filters (whereHas in the DataModel).
	 *
	 * @param   string  $name        The relation to get the sub-query for
	 * @param   string  $tableAlias  The alias to use for the local table
	 *
	 * @return JDatabaseQuery
	 * @throws Relation\Exception\RelationNotFound
	 */
	public function getCountSubquery($name, $tableAlias = null)
	{
		if (!isset($this->relations[$name]))
		{
			throw new DataModel\Relation\Exception\RelationNotFound("Relation '$name' not found");
		}

		return $this->relations[$name]->getCountSubquery($tableAlias);
	}

	/**
	 * A magic method which allows us to define relations using shorthand notation, e.g. $manager->hasOne('phone')
	 * instead of $manager->addRelation('phone', 'hasOne')
	 *
	 * You can also use it to get data of a relation using shorthand notation, e.g. $manager->getPhone($callback)
	 * instead of $manager->getData('phone', $callback);
	 *
	 * @param   string  $name       The magic method to call
	 * @param   array   $arguments  The arguments to the magic method
	 *
	 * @return DataModel The parent model, for chaining
	 *
	 * @throws InvalidArgumentException
	 * @throws DataModel\Relation\Exception\RelationTypeNotFound
	 */
	function __call($name, $arguments)
	{
		$numberOfArguments = count($arguments);

		if (isset(static::$relationTypes[$name]))
		{
			if ($numberOfArguments == 1)
			{
				return $this->addRelation($arguments[0], $name);
			}
			elseif ($numberOfArguments == 2)
			{
				return $this->addRelation($arguments[0], $name, $arguments[1]);
			}
			elseif ($numberOfArguments == 3)
			{
				return $this->addRelation($arguments[0], $name, $arguments[1], $arguments[2]);
			}
			elseif ($numberOfArguments == 4)
			{
				return $this->addRelation($arguments[0], $name, $arguments[1], $arguments[2], $arguments[3]);
			}
			elseif ($numberOfArguments == 5)
			{
				return $this->addRelation($arguments[0], $name, $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
			}
			elseif ($numberOfArguments == 6)
			{
				return $this->addRelation($arguments[0], $name, $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5]);
			}
			elseif ($numberOfArguments >= 7)
			{
				return $this->addRelation($arguments[0], $name, $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5], $arguments[6]);
			}
			else
			{
				throw new InvalidArgumentException("You can not create an unnamed '$name' relation");
			}
		}
		elseif (substr($name, 0, 3) == 'get')
		{
			$relationName = substr($name, 3);
			$relationName = strtolower($relationName[0]) . substr($relationName, 1);

			if ($numberOfArguments == 0)
			{
				return $this->getData($relationName);
			}
			elseif ($numberOfArguments == 1)
			{
				return $this->getData($relationName, $arguments[0]);
			}
			elseif ($numberOfArguments == 2)
			{
				return $this->getData($relationName, $arguments[0], $arguments[1]);
			}
			else
			{
				throw new InvalidArgumentException("Invalid number of arguments getting data for the '$relationName' relation");
			}
		}

		// Throw an exception otherwise
		throw new DataModel\Relation\Exception\RelationTypeNotFound("Relation type '$name' not known to relation manager");
	}

	/**
	 * Is $name a magic-callable method?
	 *
	 * @param   string  $name  The name of a potential magic-callable method
	 *
	 * @return bool
	 */
	public function isMagicMethod($name)
	{
		if (isset(static::$relationTypes[$name]))
		{
			return true;
		}
		elseif (substr($name, 0, 3) == 'get')
		{
			$relationName = substr($name, 3);
			$relationName = strtolower($relationName[0]) . substr($relationName, 1);

			if (isset($this->relations[$relationName]))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Is $name a magic property? Corollary: returns true if a relation of this name is known to the relation manager.
	 *
	 * @param   string  $name  The name of a potential magic property
	 *
	 * @return bool
	 */
	public function isMagicProperty($name)
	{
		return isset($this->relations[$name]);
	}

	/**
	 * Magic method to get the data of a relation using shorthand notation, e.g. $manager->phone instead of
	 * $manager->getData('phone')
	 *
	 * @param $name
	 *
	 * @return Collection
	 */
	function __get($name)
	{
		return $this->getData($name);
	}
}
