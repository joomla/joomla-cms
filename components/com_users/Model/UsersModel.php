<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\Model;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * This models supports retrieving a list of users.
 *
 * @since   __deploy_version__
 */
class UsersModel extends ListModel
{
	/**
	 * User items data
	 *
	 * @var     array
	 * @since   __deploy_version__
	 */
	protected $item = null;

	/**
	 * Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 * @since   __deploy_version__
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'users.id',
				'name', 'users.name',
				'username', 'users.username',
				'email', 'users.email',
				'registerDate', 'users.registerDate',
				'lastvisitDate', 'users.lastvisitDate',
				'session.time'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to get a list of items.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 * @since   __deploy_version__
	 */
	public function getItems()
	{
		// Invoke the parent getItems method to get the main list
		$items = parent::getItems();

		return $items;
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return  string    An SQL query
	 * @throws  Exception
	 * @since   __deploy_version__
	 */
	protected function getListQuery()
	{
		$app        = Factory::getApplication();
		$menu       = $app->getMenu();
		$active     = $menu->getActive();
		$itemId     = $active->id;
		$menuParams = $menu->getParams($itemId);
		$groupIds   = $menuParams->get('groups', 0, 'array');

		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				$db->quoteName(
					array(
						'users.id',
						'users.name',
						'users.username',
						'users.email',
						'users.registerDate',
						'users.lastvisitDate'
					)
				)
			)
		)
			->from($db->quoteName('#__users', 'users'))
			->leftJoin(
				$db->quoteName('#__user_usergroup_map', 'usergroupmap')
				. ' ON ' . $db->quoteName('usergroupmap.user_id') . ' = ' . $db->quoteName('users.id')
			)
			->leftJoin(
				$db->quoteName('#__session', 'session')
				. ' ON ' . $db->quoteName('session.userid') . ' = ' . $db->quoteName('users.id')
			)
			->select($db->quoteName('session.time'))
			->where($db->quoteName('users.block') . ' = 0')
			->group($db->quoteName('users.id'))
			->order(
				$this->getState('list.ordering', 'users.name') . ' ' .
				$this->getState('list.direction', 'ASC')
			);

		if (is_numeric($groupIds))
		{
			$query->where($db->quoteName('usergroupmap.group_id') . ' = :group_id')
				->bind(':group_id', $groupIds, ParameterType::INTEGER);
		}
		elseif (is_array($groupIds) && (count($groupIds) > 0))
		{
			$groupIds = ArrayHelper::toInteger($groupIds);

			$query->whereIn(
				$db->quoteName('usergroupmap.group_id'), $groupIds
			);
		}

		// Add the list ordering clause
		$query->order(
			$this->getState('list.ordering', 'users.name') . ' '
			. $this->getState('list.direction', 'ASC')
		);

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   __deploy_version__
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app    = Factory::getApplication();
		$params = ComponentHelper::getParams('com_users');

		// List state information
		$value = $app->input->get('limit', $app->get('list_limit', 0), 'uint');
		$this->setState('list.limit', $value);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$orderCol = $app->input->get('filter_order', 'users.name');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'users.id';
		}

		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->input->get('filter_order_Dir', 'ASC');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);

		// Load the parameters.
		$this->setState('params', $params);
	}
}
