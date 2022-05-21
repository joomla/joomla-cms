<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  Task.Requests
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Task plugin with routines to make HTTP requests.
 * At the moment, offers a single routine for GET requests.
 *
 * @since  4.1.0
 */
class PlgTaskCheckin extends CMSPlugin implements SubscriberInterface
{
	use TaskPluginTrait;

	/**
	 * @var    \Joomla\Database\DatabaseDriver
	 *
	 * @since  3.3
	 */
	protected $db;

	/**
	 * @var string[]
	 * @since 4.1.0
	 */
	protected const TASKS_MAP = [
		'plg_task_checkin_task_get' => [
			'langConstPrefix' => 'PLG_TASK_CHECKIN',
			'method'          => 'makeCheckin',
		],
	];

	/**
	 * @var boolean
	 * @since 4.1.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * @inheritDoc
	 *
	 * @return string[]
	 *
	 * @since 4.1.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onTaskOptionsList'    => 'advertiseRoutines',
			'onExecuteTask'        => 'standardRoutineHandler'
		];
	}

	/**
	 * Standard routine method for the checkin routine.
	 *
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 *
	 * @since 4.1.0
	 * @throws Exception
	 */
	protected function makeCheckin(ExecuteTaskEvent $event): int
	{
		$tables = $this->db->getTableList();
		$prefix = Factory::getApplication()->get('dbprefix');

		$results = array();

		foreach ($tables as $tn)
		{
			// Make sure we get the right tables based on prefix.
			if (stripos($tn, $prefix) !== 0)
			{
				continue;
			}

			$fields = $this->db->getTableColumns($tn, false);

			if (!(isset($fields['checked_out']) && isset($fields['checked_out_time'])))
			{
				continue;
			}

			$query = $this->db->getQuery(true)
				->select('COUNT(*)')
				->from($this->db->quoteName($tn));

			if ($fields['checked_out']->Null === 'YES')
			{
				$query->where($this->db->quoteName('checked_out') . ' IS NOT NULL');
			}
			else
			{
				$query->where($this->db->quoteName('checked_out') . ' > 0');
			}

			$this->db->setQuery($query);
			$count = $this->db->loadResult();

			if ($count)
			{
				$results[] = $tn;
			}
		}

		if (empty($results))
		{
			return TaskStatus::OK;
		}

		// Get the model.
		/** @var \Joomla\Component\Checkin\Administrator\Model\CheckinModel $model */
		$model = Factory::getApplication()->bootComponent('checkin')
			->getMVCFactory()->createModel('checkin', 'Administrator', ['ignore_request' => true]);

		$model->checkin($results);

		return TaskStatus::OK;
	}
}
