<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Users\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;

/**
 * Model class for list of users.
 *
 * @since  4.0
 */
class UsersModel extends ListModel
{

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState($ordering = 'ordering', $direction = 'ASC')
	{
		$app = \JFactory::getApplication();

		// List state information
		$groupId = $app->input->get('id');
		$this->setState('user.group', $groupId);

		parent::populateState($ordering = 'ordering', $direction = 'ASC');
	}

	/**
	 * Get the list of items.
	 *
	 * @return \JDatabaseQuery|\Joomla\Database\DatabaseQuery
	 *
	 * @throws \Exception
	 */
	protected function getListQuery()
	{
		$app = Factory::getApplication();
		$user = Factory::getUser();

		// $u = Access::getUsersByGroup((int)$this->getState('user.group'));

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			' a.id, a.name, a.username, a.email, a.access, map.group_id'
		);

		$query->from($db->quoteName('#__users') . ' AS a')
			->leftJoin($db->quoteName('#__user_usergroup_map') . ' AS map ON a.id = map.user_id')
			->where('map.group_id = (' . $this->getState('user.group') . ')');

		// Filter by access level.
		if ($this->getState('filter.access', true))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}

		$params = $app->getParams();
		$this->setState('params', $params);

		return $query;
	}

}
