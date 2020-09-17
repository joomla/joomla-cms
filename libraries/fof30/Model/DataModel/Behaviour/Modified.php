<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model\DataModel\Behaviour;

defined('_JEXEC') || die;

use FOF30\Event\Observer;
use FOF30\Model\DataModel;
use stdClass;

/**
 * FOF model behavior class to updated the modified_by and modified_on fields on newly created records.
 *
 * This behaviour is added to DataModel by default. If you want to remove it you need to do
 * $this->behavioursDispatcher->removeBehaviour('Modified');
 *
 * @since  3.0
 */
class Modified extends Observer
{
	/**
	 * Add the modified_on and modified_by fields in the fieldsSkipChecks list of the model. We expect them to be empty
	 * so that we can fill them in through this behaviour.
	 *
	 * @param   DataModel  $model
	 */
	public function onBeforeCheck(&$model)
	{
		$model->addSkipCheckField('modified_on');
		$model->addSkipCheckField('modified_by');
	}

	/**
	 * @param   DataModel  $model
	 * @param   stdClass  $dataObject
	 */
	public function onBeforeUpdate(&$model, &$dataObject)
	{
		// Make sure we're not modifying a locked record
		$userId   = $model->getContainer()->platform->getUser()->id;
		$isLocked = $model->isLocked($userId);

		if ($isLocked)
		{
			return;
		}

		// Handle the modified_on field
		if ($model->hasField('modified_on'))
		{
			$model->setFieldValue('modified_on', $model->getContainer()->platform->getDate()->toSql(false, $model->getDbo()));

			$modifiedOnField              = $model->getFieldAlias('modified_on');
			$dataObject->$modifiedOnField = $model->getFieldValue('modified_on');
		}

		// Handle the modified_by field
		if ($model->hasField('modified_by'))
		{
			$model->setFieldValue('modified_by', $userId);

			$modifiedByField              = $model->getFieldAlias('modified_by');
			$dataObject->$modifiedByField = $model->getFieldValue('modified_by');
		}
	}
}
