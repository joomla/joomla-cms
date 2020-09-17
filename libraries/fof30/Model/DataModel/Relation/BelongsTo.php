<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model\DataModel\Relation;

defined('_JEXEC') || die;

use FOF30\Model\DataModel;

/**
 * BelongsTo (reverse 1-to-1 or 1-to-many) relation: this model is a child which belongs to the foreign table
 *
 * For example, parentModel is Articles and foreignModel is Users. Each article belongs to one user. One user can have
 * one or more article.
 *
 * Example #2: parentModel is Phones and foreignModel is Users. Each phone belongs to one user. One user can have zero
 * or one phones.
 */
class BelongsTo extends HasOne
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

		if (empty($localKey))
		{
			/** @var DataModel $foreignModel */
			$foreignModel = $this->getForeignModel();
			$foreignModel->setIgnoreRequest(true);

			$this->localKey = $foreignModel->getIdFieldName();
		}

		if (empty($foreignKey))
		{
			if (!isset($foreignModel))
			{
				/** @var DataModel $foreignModel */
				$foreignModel = $this->getForeignModel();
				$foreignModel->setIgnoreRequest(true);
			}

			$this->foreignKey = $foreignModel->getIdFieldName();
		}
	}

	/**
	 * This is not supported by the belongsTo relation
	 *
	 * @throws DataModel\Relation\Exception\NewNotSupported when it's not supported
	 */
	public function getNew()
	{
		throw new DataModel\Relation\Exception\NewNotSupported("getNew() is not supported by the belongsTo relation type");
	}

}
