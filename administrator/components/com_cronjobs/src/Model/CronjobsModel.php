<?php
/**
 * Declares the CronjobsModel MVC Model.
 * TODO: Implement query filters in $GetListQuery()
 * ~~TODO: Complete $config['filter_fields']~~
 *
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 */

namespace Joomla\Component\Cronjobs\Administrator\Model;

// Restrict direct access
defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;

use function defined;

/**
 * MVC Model to deal with operations concerning multiple 'Cronjob' entries.
 *
 * @since __DEPLOY_VERSION__
 */
class CronjobsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array                     $config   An optional associative array of configuration settings.
	 *
	 * @param   MVCFactoryInterface|null  $factory  The factory.
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 * @see    \JControllerLegacy
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			/*
			 * TODO : Works right? Need to implement filtering to check.
			 * TODO: Might want to remove unnecessary fields
			*/
			$config['filter_fields']
				= array(
				'id',
				'a.id',

				'asset_id',
				'a.asset_id',

				'title',
				'a.title',

				'type',
				'a.type',

				'type_title',
				'j.type_title',

				'trigger',
				'a.trigger',

				'state',
				'a.state',

				'last_exit_code',
				'a.last_exit_code',

				'last_execution',
				'a.last_execution',

				'next_execution',
				'a.next_execution',

				'times_executed',
				'a.times_executed',

				'times_failed',
				'a.times_failed',

				'note',
				'a.note',

				'created',
				'a.created',

				'created_by',
				'a.created_by'
				);
		}

		parent::__construct($config, $factory);
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return array|boolean  An array of data items on success, false on failure.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getItems()
	{
		return parent::getItems();
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * ? What does this do internally ?
	 * TODO:
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return string  A store id.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function getStoreId($id = ''): string
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.enabled');
		$id .= ':' . $this->getState('filter.job_type');

		return parent::getStoreId($id);
	}

	/**
	 * Method to create a query for a list of items.
	 * ! No filters at the moment ⚠
	 *
	 * @return QueryInterface
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getListQuery(): QueryInterface
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = Factory::getApplication()->getIdentity();

		/*
		 * Select the required fields from the table.
		 * ? Do we need all these defaults ?
		 */
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.asset_id, a.title, a.type, a.trigger, a.execution_rules, a.state, a.last_exit_code' .
				', a.last_execution, a.next_execution, a.times_executed, a.times_failed'
			)
			// ? Does 'list.select' exist ?
		);

		$query->from($db->quoteName('#__cronjobs', 'a'));

		// TODO : Implement filters and sorting here

		return $query;
	}

	/**
	 * Overloads the parent _getList() method.
	 * Takes care of attaching CronOption objects and sorting by type titles.
	 *
	 * @param   DatabaseQuery  $query       The database query to get the list with
	 * @param   int            $limitstart  The list offset
	 * @param   int            $limit       Number of list items to fetch
	 *
	 * @return object[]
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 * @codingStandardsIgnoreStart
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0): array
	{
		/** @codingStandardsIgnoreEnd */

		// Get stuff from the model state
		$listOrder = $this->getState('list.ordering', 'a.title');
		$listDirectionN = strtolower($this->getState('list.direction', 'desc')) == 'desc' ? -1 : 1;

		// Set limit parameters and get object list
		$query->setLimit($limit, $limitstart);
		$this->getDbo()->setQuery($query);
		$responseList = $this->getDbo()->loadObjectList();

		// Attach CronOptions objects and a safe type title
		$this->attachCronOptions($responseList);

		// If ordering by non-db fields, we need to sort here in code
		if ($listOrder == 'j.type_title')
		{
			$responseList = ArrayHelper::sortObjects($responseList, 'safeTypeTitle', $listDirectionN, true, false);
		}

		return $responseList;
	}

	/**
	 * For an array of items, attaches CronOption objects and (safe) type titles to each.
	 *
	 * @param   array  $items  Array of items, passed by reference
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	private function attachCronOptions(array &$items): void
	{
		$cronOptions = CronjobsHelper::getCronOptions();

		foreach ($items as &$item)
		{
			// $jobType = $item->job_type;
			$item->cronOption = $cronOptions->findOption($item->type);
			$item->safeTypeTitle = $item->cronOption->title ?? 'N/A';
		}
	}

	/**
	 * Overload the parent populateState() method.
	 * ! Does nothing at the moment.
	 * TODO : Remove if no special handling needed.
	 *
	 * @param   ?string  $ordering   Field to order/sort list by
	 * @param   ?string  $direction  Direction in which to sort list
	 *
	 * @return void
	 * @since __DEPLOY_VERSION__
	 */
	protected function populateState($ordering = null, $direction = null): void
	{
		// TODO: Change the autogenerated stub
		parent::populateState($ordering, $direction);
	}

}
