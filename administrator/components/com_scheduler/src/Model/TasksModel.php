<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Model;

// Restrict direct access
\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\Component\Scheduler\Administrator\Helper\SchedulerHelper;
use Joomla\Component\Scheduler\Administrator\Task\TaskOption;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Utilities\ArrayHelper;

/**
 * The MVC Model for TasksView.
 * Defines methods to deal with operations concerning multiple `#__scheduler_tasks` entries.
 *
 * @since  4.1.0
 */
class TasksModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array                     $config   An optional associative array of configuration settings.
	 *
	 * @param   MVCFactoryInterface|null  $factory  The factory.
	 *
	 * @since  4.1.0
	 * @throws \Exception
	 * @see    \JControllerLegacy
	 */
	public function __construct($config = [], MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'id', 'a.id',
				'asset_id', 'a.asset_id',
				'title', 'a.title',
				'type', 'a.type',
				'type_title', 'j.type_title',
				'state', 'a.state',
				'last_exit_code', 'a.last_exit_code',
				'last_execution', 'a.last_execution',
				'next_execution', 'a.next_execution',
				'times_executed', 'a.times_executed',
				'times_failed', 'a.times_failed',
				'ordering', 'a.ordering',
				'priority', 'a.priority',
				'note', 'a.note',
				'created', 'a.created',
				'created_by', 'a.created_by',
			];
		}

		parent::__construct($config, $factory);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return string  A store id.
	 *
	 * @since  4.1.0
	 */
	protected function getStoreId($id = ''): string
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.type');
		$id .= ':' . $this->getState('filter.orphaned');
		$id .= ':' . $this->getState('filter.due');
		$id .= ':' . $this->getState('filter.locked');
		$id .= ':' . $this->getState('filter.trigger');
		$id .= ':' . $this->getState('list.select');

		return parent::getStoreId($id);
	}

	/**
	 * Method to create a query for a list of items.
	 *
	 * @return QueryInterface
	 *
	 * @since  4.1.0
	 * @throws \Exception
	 */
	protected function getListQuery(): QueryInterface
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		/**
		 * Select the required fields from the table.
		 * ? Do we need all these defaults ?
		 * ? Does 'list.select' exist ?
		 */
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.asset_id, a.title, a.type, a.execution_rules, a.state, a.last_exit_code, a.locked' .
				', a.last_execution, a.next_execution, a.times_executed, a.times_failed, a.ordering, a.note'
			)
		);

		// From the #__scheduler_tasks table as 'a'
		$query->from($db->quoteName('#__scheduler_tasks', 'a'));

		// Filters go below
		$filterCount = 0;

		/**
		 * Extends query if already filtered.
		 *
		 * @param   string  $outerGlue
		 * @param   array   $conditions
		 * @param   string  $innerGlue
		 *
		 * @since  4.1.0
		 */
		$extendWhereIfFiltered = static function (
			string $outerGlue,
			array $conditions,
			string $innerGlue
		) use ($query, &$filterCount) {
			if ($filterCount++)
			{
				$query->extendWhere($outerGlue, $conditions, $innerGlue);
			}
			else
			{
				$query->where($conditions, $innerGlue);
			}

		};

		// Filter over ID, title (redundant to search, but) ---
		if (is_numeric($id = $this->getState('filter.id')))
		{
			$filterCount++;
			$id = (int) $id;
			$query->where($db->qn('a.id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER);
		}
		elseif ($title = $this->getState('filter.title'))
		{
			$filterCount++;
			$match = "%$title%";
			$query->where($db->qn('a.title') . ' LIKE :match')
				->bind(':match', $match);
		}

		// Filter orphaned (-1: exclude, 0: include, 1: only) ----
		$filterOrphaned = (int) $this->getState('filter.orphaned');

		if ($filterOrphaned !== 0)
		{
			$filterCount++;
			$taskOptions = SchedulerHelper::getTaskOptions();

			// Array of all active routine ids
			$activeRoutines = array_map(
				static function (TaskOption $taskOption): string
				{
					return $taskOption->type;
				},
				$taskOptions->options
			);

			if ($filterOrphaned === -1)
			{
				$query->whereIn($db->quoteName('type'), $activeRoutines, ParameterType::STRING);
			}
			else
			{
				$query->whereNotIn($db->quoteName('type'), $activeRoutines, ParameterType::STRING);
			}
		}

		// Filter over state ----
		$state = $this->getState('filter.state');

		if ($state !== '*')
		{
			$filterCount++;

			if (is_numeric($state))
			{
				$state = (int) $state;

				$query->where($db->quoteName('a.state') . ' = :state')
					->bind(':state', $state, ParameterType::INTEGER);
			}
			else
			{
				$query->whereIn($db->quoteName('a.state'), [0, 1]);
			}
		}

		// Filter over type ----
		$typeFilter = $this->getState('filter.type');

		if ($typeFilter)
		{
			$filterCount++;
			$query->where($db->quotename('a.type') . '= :type')
				->bind(':type', $typeFilter);
		}

		// Filter over exit code ----
		$exitCode = $this->getState('filter.last_exit_code');

		if (is_numeric($exitCode))
		{
			$filterCount++;
			$exitCode = (int) $exitCode;
			$query->where($db->quoteName('a.last_exit_code') . '= :last_exit_code')
				->bind(':last_exit_code', $exitCode, ParameterType::INTEGER);
		}

		// Filter due (-1: exclude, 0: include, 1: only) ----
		$due = $this->getState('filter.due');

		if (is_numeric($due) && $due != 0)
		{
			$now      = Factory::getDate('now', 'GMT')->toSql();
			$operator = $due == 1 ? ' <= ' : ' > ';
			$filterCount++;
			$query->where($db->qn('a.next_execution') . $operator . ':now')
				->bind(':now', $now);
		}

		/*
		 * Filter locked ---
		 * Locks can be either hard locks or soft locks. Locks that have expired (exceeded the task timeout) are soft
		 * locks. Hard-locked tasks are assumed to be running. Soft-locked tasks are assumed to have suffered a fatal
		 * failure.
		 * {-2: exclude-all, -1: exclude-hard-locked, 0: include, 1: include-only-locked, 2: include-only-soft-locked}
		 */
		$locked = $this->getState('filter.locked');

		if (is_numeric($locked) && $locked != 0)
		{
			$now              = Factory::getDate('now', 'GMT');
			$timeout          = ComponentHelper::getParams('com_scheduler')->get('timeout', 300);
			$timeout          = new \DateInterval(sprintf('PT%dS', $timeout));
			$timeoutThreshold = (clone $now)->sub($timeout)->toSql();
			$now              = $now->toSql();

			switch ($locked)
			{
				case -2:
					$query->where($db->qn('a.locked') . 'IS NULL');
					break;
				case -1:
					$extendWhereIfFiltered(
						'AND',
						[
							$db->qn('a.locked') . ' IS NULL',
							$db->qn('a.locked') . ' < :threshold',
						],
						'OR'
					);
					$query->bind(':threshold', $timeoutThreshold);
					break;
				case 1:
					$query->where($db->qn('a.locked') . ' IS NOT NULL');
					break;
				case 2:
					$query->where($db->qn('a.locked') . ' < :threshold')
						->bind(':threshold', $timeoutThreshold);
			}
		}

		// Filter over search string if set (title, type title, note, id) ----
		$searchStr = $this->getState('filter.search');

		if (!empty($searchStr))
		{
			// Allow search by ID
			if (stripos($searchStr, 'id:') === 0)
			{
				// Add array support [?]
				$id = (int) substr($searchStr, 3);
				$query->where($db->quoteName('a.id') . '= :id')
					->bind(':id', $id, ParameterType::INTEGER);
			}
			// Search by type is handled exceptionally in _getList() [@todo: remove refs]
			elseif (stripos($searchStr, 'type:') !== 0)
			{
				$searchStr = "%$searchStr%";

				// Bind keys to query
				$query->bind(':title', $searchStr)
					->bind(':note', $searchStr);
				$conditions = [
					$db->quoteName('a.title') . ' LIKE :title',
					$db->quoteName('a.note') . ' LIKE :note',
				];
				$extendWhereIfFiltered('AND', $conditions, 'OR');
			}
		}

		// Add list ordering clause. ----
		// @todo implement multi-column ordering someway
		$multiOrdering = $this->state->get('list.multi_ordering');

		if (!$multiOrdering || !\is_array($multiOrdering))
		{
			$orderCol = $this->state->get('list.ordering', 'a.title');
			$orderDir = $this->state->get('list.direction', 'desc');

			// Type title ordering is handled exceptionally in _getList()
			if ($orderCol !== 'j.type_title')
			{
				$query->order($db->quoteName($orderCol) . ' ' . $orderDir);

				// If ordering by type or state, also order by title.
				if (\in_array($orderCol, ['a.type', 'a.state', 'a.priority']))
				{
					// @todo : Test if things are working as expected
					$query->order($db->quoteName('a.title') . ' ' . $orderDir);
				}
			}
		}
		else
		{
			// @todo Should add quoting here
			$query->order($multiOrdering);
		}

		return $query;
	}

	/**
	 * Overloads the parent _getList() method.
	 * Takes care of attaching TaskOption objects and sorting by type titles.
	 *
	 * @param   DatabaseQuery  $query       The database query to get the list with
	 * @param   int            $limitstart  The list offset
	 * @param   int            $limit       Number of list items to fetch
	 *
	 * @return object[]
	 *
	 * @since  4.1.0
	 * @throws \Exception
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0): array
	{

		// Get stuff from the model state
		$listOrder      = $this->getState('list.ordering', 'a.title');
		$listDirectionN = strtolower($this->getState('list.direction', 'desc')) == 'desc' ? -1 : 1;

		// Set limit parameters and get object list
		$query->setLimit($limit, $limitstart);
		$this->getDbo()->setQuery($query);

		// Return optionally an extended class.
		// @todo: Use something other than CMSObject..
		if ($this->getState('list.customClass'))
		{
			$responseList = array_map(
				static function (array $arr) {
					$o = new CMSObject;

					foreach ($arr as $k => $v)
					{
						$o->{$k} = $v;
					}

					return $o;
				},
				$this->getDbo()->loadAssocList() ?: []
			);
		}
		else
		{
			$responseList = $this->getDbo()->loadObjectList();
		}

		// Attach TaskOptions objects and a safe type title
		$this->attachTaskOptions($responseList);

		// If ordering by non-db fields, we need to sort here in code
		if ($listOrder == 'j.type_title')
		{
			$responseList = ArrayHelper::sortObjects($responseList, 'safeTypeTitle', $listDirectionN, true, false);
		}

		return $responseList;
	}

	/**
	 * For an array of items, attaches TaskOption objects and (safe) type titles to each.
	 *
	 * @param   array  $items  Array of items, passed by reference
	 *
	 * @return void
	 *
	 * @since  4.1.0
	 * @throws \Exception
	 */
	private function attachTaskOptions(array $items): void
	{
		$taskOptions = SchedulerHelper::getTaskOptions();

		foreach ($items as $item)
		{
			$item->taskOption    = $taskOptions->findOption($item->type);
			$item->safeTypeTitle = $item->taskOption->title ?? Text::_('JGLOBAL_NONAPPLICABLE');
		}
	}

	/**
	 * Proxy for the parent method.
	 * Sets ordering defaults.
	 *
	 * @param   string  $ordering   Field to order/sort list by
	 * @param   string  $direction  Direction in which to sort list
	 *
	 * @return void
	 * @since  4.1.0
	 */
	protected function populateState($ordering = 'a.id', $direction = 'ASC'): void
	{
		// Call the parent method
		parent::populateState($ordering, $direction);
	}
}
