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
 * HasMany (1-to-many) relation: this model is a parent which has zero or more children in the foreign table
 *
 * For example, parentModel is Users and foreignModel is Articles. Each user has zero or more articles.
 */
class HasMany extends Relation
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
	 * @param   string     $pivotTable        IGNORED
	 * @param   string     $pivotLocalKey     IGNORED
	 * @param   string     $pivotForeignKey   IGNORED
	 */
	public function __construct(DataModel $parentModel, $foreignModelName, $localKey = null, $foreignKey = null, $pivotTable = null, $pivotLocalKey = null, $pivotForeignKey = null)
	{
		parent::__construct($parentModel, $foreignModelName, $localKey, $foreignKey, $pivotTable, $pivotLocalKey, $pivotForeignKey);

		if (empty($this->localKey))
		{
			$this->localKey = $parentModel->getIdFieldName();
		}

		if (empty($this->foreignKey))
		{
			$this->foreignKey = $this->localKey;
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
		// Get a model instance
		$foreignModel = $this->getForeignModel();
		$foreignModel->setIgnoreRequest(true);

		$db = $foreignModel->getDbo();

		if (empty($tableAlias))
		{
			$tableAlias = $this->parentModel->getTableName();
		}

		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn($foreignModel->getTableName(), 'reltbl'))
			->where($db->qn('reltbl') . '.' . $db->qn($foreignModel->getFieldAlias($this->foreignKey)) . ' = '
				. $db->qn($tableAlias) . '.'
				. $db->qn($this->parentModel->getFieldAlias($this->localKey)));

		return $query;
	}

	/**
	 * Returns a new item of the foreignModel type, pre-initialised to fulfil this relation
	 *
	 * @return DataModel
	 *
	 * @throws DataModel\Relation\Exception\NewNotSupported when it's not supported
	 */
	public function getNew()
	{
		// Get a model instance
		$foreignModel = $this->getForeignModel();
		$foreignModel->setIgnoreRequest(true);

		// Prime the model
		$foreignModel->setFieldValue($this->foreignKey, $this->parentModel->getFieldValue($this->localKey));

		// Make sure we do have a data list
		if (!($this->data instanceof DataModel\Collection))
		{
			$this->getData();
		}

		// Add the model to the data list
		$this->data->add($foreignModel);

		return $this->data->last();
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

				// Keep only unique values. This double step is required to re-index the array and avoid issues with
				// Joomla Registry class. See issue #681
				$values = array_values(array_unique($values));

				// Apply the filter
				if (!empty($values))
				{
					$foreignModel->where($this->foreignKey, 'in', $values);
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

			$foreignModel->where($this->foreignKey, '==', $localKey);
		}

		return true;
	}
}
