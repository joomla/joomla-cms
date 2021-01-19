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
 * FOF model behavior class to filter access to items based on the viewing access levels.
 *
 * @since    2.1
 */
class Access extends Observer
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
		if (!$model->hasField('access'))
		{
			return;
		}

		$model->applyAccessFiltering(null);
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
		if (!$model->hasField('access'))
		{
			return;
		}

		// Get the user
		$user              = $model->getContainer()->platform->getUser();
		$recordAccessLevel = $model->getFieldValue('access', null);

		// Filter by authorised access levels
		if (!in_array($recordAccessLevel, $user->getAuthorisedViewLevels()))
		{
			$model->reset(true);
		}
	}
}
