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
use JDatabaseQuery;

/**
 * FOF model behavior class to filter access to items based on the enabled status
 *
 * @since    2.1
 */
class Enabled extends Observer
{
	/**
	 * This event runs before we have built the query used to fetch a record
	 * list in a model. It is used to apply automatic query filters.
	 *
	 * @param   DataModel      &$model  The model which calls this event
	 * @param   JDatabaseQuery &$query  The query we are manipulating
	 *
	 * @return  void
	 */
	public function onBeforeBuildQuery(&$model, &$query)
	{
		// Make sure the field actually exists
		if (!$model->hasField('enabled'))
		{
			return;
		}

		$fieldName = $model->getFieldAlias('enabled');
		$db        = $model->getDbo();

		$model->whereRaw($db->qn($fieldName) . ' = ' . $db->q(1));
	}

	/**
	 * The event runs after DataModel has retrieved a single item from the database. It is used to apply automatic
	 * filters.
	 *
	 * @param   DataModel &$model  The model which was called
	 * @param   Array     &$keys   The keys used to locate the record which was loaded
	 *
	 * @return  void
	 */
	public function onAfterLoad(&$model, &$keys)
	{
		// Make sure we have a DataModel
		if (!($model instanceof DataModel))
		{
			return;
		}

		// Make sure the field actually exists
		if (!$model->hasField('enabled'))
		{
			return;
		}

		// Filter by enabled status
		if (!$model->getFieldValue('enabled', 0))
		{
			$model->reset(true);
		}
	}
}
