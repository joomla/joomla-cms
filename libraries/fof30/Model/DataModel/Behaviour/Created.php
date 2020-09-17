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
 * FOF model behavior class to updated the created_by and created_on fields on newly created records.
 *
 * This behaviour is added to DataModel by default. If you want to remove it you need to do
 * $this->behavioursDispatcher->removeBehaviour('Created');
 *
 * @since  3.0
 */
class Created extends Observer
{
	/**
	 * Add the created_on and created_by fields in the fieldsSkipChecks list of the model. We expect them to be empty
	 * so that we can fill them in through this behaviour.
	 *
	 * @param   DataModel  $model
	 */
	public function onBeforeCheck(&$model)
	{
		$model->addSkipCheckField('created_on');
		$model->addSkipCheckField('created_by');
	}

	/**
	 * @param   DataModel  $model
	 * @param   stdClass  $dataObject
	 */
	public function onBeforeCreate(&$model, &$dataObject)
	{
		// Handle the created_on field
		if ($model->hasField('created_on'))
		{
			$nullDate   = $model->getDbo()->getNullDate();
			$created_on = $model->getFieldValue('created_on');

			if (empty($created_on) || ($created_on == $nullDate))
			{
				$model->setFieldValue('created_on', $model->getContainer()->platform->getDate()->toSql(false, $model->getDbo()));

				$createdOnField              = $model->getFieldAlias('created_on');
				$dataObject->$createdOnField = $model->getFieldValue('created_on');
			}
		}

		// Handle the created_by field
		if ($model->hasField('created_by'))
		{
			$created_by = $model->getFieldValue('created_by');

			if (empty($created_by))
			{
				$model->setFieldValue('created_by', $model->getContainer()->platform->getUser()->id);

				$createdByField              = $model->getFieldAlias('created_by');
				$dataObject->$createdByField = $model->getFieldValue('created_by');
			}
		}
	}
}
