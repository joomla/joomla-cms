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
 * FOF model behavior class to filter access to items owned by the currently logged in user only
 *
 * @since    2.1
 */
class Own extends Observer
{
	/**
	 * This event runs after we have built the query used to fetch a record
	 * list in a model. It is used to apply automatic query filters.
	 *
	 * @param   DataModel      &$model  The model which calls this event
	 * @param   JDatabaseQuery &$query  The query we are manipulating
	 *
	 * @return  void
	 */
	public function onAfterBuildQuery(&$model, &$query)
	{
		// Make sure the field actually exists
		if (!$model->hasField('created_by'))
		{
			return;
		}

		// Get the current user's id
		$user_id = $model->getContainer()->platform->getUser()->id;

		// And filter the query output by the user id
		$db = $model->getContainer()->platform->getDbo();

		$query->where($db->qn($model->getFieldAlias('created_by')) . ' = ' . $db->q($user_id));
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
		if (!$model->hasField('created_by'))
		{
			return;
		}

		// Get the user
		$user_id    = $model->getContainer()->platform->getUser()->id;
		$recordUser = $model->getFieldValue('created_by', null);

		// Filter by authorised access levels
		if ($recordUser != $user_id)
		{
			$model->reset(true);
		}
	}
}
