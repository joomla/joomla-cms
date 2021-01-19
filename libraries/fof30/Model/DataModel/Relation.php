<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model\DataModel;

defined('_JEXEC') || die;

use FOF30\Container\Container;
use FOF30\Model\DataModel;
use JDatabaseQuery;

abstract class Relation
{
	/** @var   DataModel  The data model we are attached to */
	protected $parentModel = null;

	/** @var   string  The class name of the foreign key's model */
	protected $foreignModelClass = null;

	/** @var   string  The application name of the foreign model */
	protected $foreignModelComponent = null;

	/** @var   string  The bade name of the foreign model */
	protected $foreignModelName = null;

	/** @var   string   The local table key for this relation */
	protected $localKey = null;

	/** @var   string   The foreign table key for this relation */
	protected $foreignKey = null;

	/** @var   null  For many-to-many relations, the pivot (glue) table */
	protected $pivotTable = null;

	/** @var   null  For many-to-many relations, the pivot table's column storing the local key */
	protected $pivotLocalKey = null;

	/** @var   null  For many-to-many relations, the pivot table's column storing the foreign key */
	protected $pivotForeignKey = null;

	/** @var   Collection  The data loaded by this relation */
	protected $data = null;

	/** @var  array  Maps each local table key to an array of foreign table keys, used in many-to-many relations */
	protected $foreignKeyMap = [];

	/** @var  Container  The component container for this relation */
	protected $container = null;

	/**
	 * Public constructor. Initialises the relation.
	 *
	 * @param   DataModel  $parentModel       The data model we are attached to
	 * @param   string     $foreignModelName  The name of the foreign key's model in the format
	 *                                        "modelName@com_something"
	 * @param   string     $localKey          The local table key for this relation
	 * @param   string     $foreignKey        The foreign key for this relation
	 * @param   string     $pivotTable        For many-to-many relations, the pivot (glue) table
	 * @param   string     $pivotLocalKey     For many-to-many relations, the pivot table's column storing the local
	 *                                        key
	 * @param   string     $pivotForeignKey   For many-to-many relations, the pivot table's column storing the foreign
	 *                                        key
	 */
	public function __construct(DataModel $parentModel, $foreignModelName, $localKey = null, $foreignKey = null, $pivotTable = null, $pivotLocalKey = null, $pivotForeignKey = null)
	{
		$this->parentModel       = $parentModel;
		$this->foreignModelClass = $foreignModelName;
		$this->localKey          = $localKey;
		$this->foreignKey        = $foreignKey;
		$this->pivotTable        = $pivotTable;
		$this->pivotLocalKey     = $pivotLocalKey;
		$this->pivotForeignKey   = $pivotForeignKey;

		$this->container = $parentModel->getContainer();

		$class = $foreignModelName;

		if (strpos($class, '@') === false)
		{
			$this->foreignModelComponent = null;
			$this->foreignModelName      = $class;
		}
		else
		{
			$foreignParts                = explode('@', $class, 2);
			$this->foreignModelComponent = $foreignParts[1];
			$this->foreignModelName      = $foreignParts[0];
		}
	}

	/**
	 * Reset the relation data
	 *
	 * @return $this For chaining
	 */
	public function reset()
	{
		$this->data          = null;
		$this->foreignKeyMap = [];

		return $this;
	}

	/**
	 * Rebase the relation to a different model
	 *
	 * @param   DataModel  $model
	 *
	 * @return $this For chaining
	 */
	public function rebase(DataModel $model)
	{
		$this->parentModel = $model;

		return $this->reset();
	}

	/**
	 * Get the relation data.
	 *
	 * If you want to apply additional filtering to the foreign model, use the $callback. It can be any function,
	 * static method, public method or closure with an interface of function(DataModel $foreignModel). You are not
	 * supposed to return anything, just modify $foreignModel's state directly. For example, you may want to do:
	 * $foreignModel->setState('foo', 'bar')
	 *
	 * @param   callable    $callback  The callback to run on the remote model.
	 * @param   Collection  $dataCollection
	 *
	 * @return Collection|DataModel
	 */
	public function getData($callback = null, Collection $dataCollection = null)
	{
		if (is_null($this->data))
		{
			// Initialise
			$this->data = new Collection();

			// Get a model instance
			$foreignModel = $this->getForeignModel();
			$foreignModel->setIgnoreRequest(true);

			$filtered = $this->filterForeignModel($foreignModel, $dataCollection);

			if (!$filtered)
			{
				return $this->data;
			}

			// Apply the callback, if applicable
			if (!is_null($callback) && is_callable($callback))
			{
				call_user_func($callback, $foreignModel);
			}

			// Get the list of items from the foreign model and cache in $this->data
			$this->data = $foreignModel->get(true);
		}

		return $this->data;
	}

	/**
	 * Populates the internal $this->data collection from the contents of the provided collection. This is used by
	 * DataModel to push the eager loaded data into each item's relation.
	 *
	 * @param   Collection  $data    The relation data to push into this relation
	 * @param   mixed       $keyMap  Used by many-to-many relations to pass around the local to foreign key map
	 *
	 * @return void
	 */
	public function setDataFromCollection(Collection &$data, $keyMap = null)
	{
		$this->data = new Collection();

		if (!empty($data))
		{
			$localKeyValue = $this->parentModel->getFieldValue($this->localKey);

			/** @var DataModel $item */
			foreach ($data as $key => $item)
			{
				if ($item->getFieldValue($this->foreignKey) == $localKeyValue)
				{
					$this->data->add($item);
				}
			}
		}
	}

	/**
	 * Returns the count sub-query for DataModel's has() and whereHas() methods.
	 *
	 * @return JDatabaseQuery
	 */
	abstract public function getCountSubquery();

	/**
	 * Returns a new item of the foreignModel type, pre-initialised to fulfil this relation
	 *
	 * @return DataModel
	 *
	 * @throws DataModel\Relation\Exception\NewNotSupported when it's not supported
	 */
	abstract public function getNew();

	/**
	 * Saves all related items. You can use it to touch items as well: every item being saved causes the modified_by and
	 * modified_on fields to be changed automatically, thanks to the DataModel's magic.
	 */
	public function saveAll()
	{
		if ($this->data instanceof Collection)
		{
			foreach ($this->data as $item)
			{
				if ($item instanceof DataModel)
				{
					$item->save();
				}
			}
		}
	}

	/**
	 * Returns the foreign key map of a many-to-many relation, used for eager loading many-to-many relations
	 *
	 * @return array
	 */
	public function &getForeignKeyMap()
	{
		return $this->foreignKeyMap;
	}

	/**
	 * Gets an object instance of the foreign model
	 *
	 * @param   array  $config  Optional configuration information for the Model
	 *
	 * @return DataModel
	 */
	public function &getForeignModel(array $config = [])
	{
		// If the model comes from this component go through our Factory
		if (is_null($this->foreignModelComponent))
		{
			$model = $this->container->factory->model($this->foreignModelName, $config)->tmpInstance();

			return $model;
		}

		// The model comes from another component. Create a container and go through its factory.
		$foreignContainer = Container::getInstance($this->foreignModelComponent, ['tempInstance' => true]);
		$model            = $foreignContainer->factory->model($this->foreignModelName, $config)->tmpInstance();

		return $model;
	}

	/**
	 * Returns the name of the local key of the relation
	 *
	 * @return  string
	 */
	public function getLocalKey()
	{
		return $this->localKey;
	}

	/**
	 * Applies the relation filters to the foreign model when getData is called
	 *
	 * @param   DataModel   $foreignModel    The foreign model you're operating on
	 * @param   Collection  $dataCollection  If it's an eager loaded relation, the collection of loaded parent records
	 *
	 * @return boolean Return false to force an empty data collection
	 */
	abstract protected function filterForeignModel(DataModel $foreignModel, Collection $dataCollection = null);
}
