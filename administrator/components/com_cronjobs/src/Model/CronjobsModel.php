<?php
/**
 * Declares the CronjobsModel MVC Model.
 * TODO: Implement query filters in $GetListQuery()
 * TODO: Complete $config['filter_fields']
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
			// TODO : Works right? Need to implement filtering to check.
			$config['filter_fields']
				= array(
					'job_id',
					'a.job_id',

					'name',
					'a.name',

					'type',
					'a.type',

					'trigger',
					'a.trigger',

					'execution_interval',
					'a.execution_interval',

					'enabled',
					'a.enabled',

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
					'a.note'
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

		// Select the required fields from the table.
		// TODO : Should add a "created_by" column to `#__cronjobs`
		$query->select(
			$this->getState(
				'list.select',
				'a.job_id, a.name, a.type, a.trigger, a.execution_interval, a.enabled, a.last_exit_code' .
				', a.next_execution, a.times_executed, a.times_failed'
			)
			// ? Does 'list.select' exist ?
		);

		$query->from($db->quoteName('#__cronjobs', 'a'));

		// TODO : Implement filters and sorting here

		return $query;
	}

}
