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

use Joomla\CMS\MVC\Model\ListModel;


// Todo: menu param for selecting groups to display
// Todo: param to show only users that have written published articles
// Todo: UserModel to show user + list of articles


/**
 * This models supports retrieving lists of users.
 *
 * @since  4.0
 */
class UsersModel extends ListModel
{
	/**
	 * Category items data
	 *
	 * @var array
	 * @since   4.0
	 */
	protected $item = null;

	/**
	 * The category that applies.
	 *
	 * @var        object
	 * @access    protected
	 * @since     4.0
	 */
	protected $category = null;

	/**
	 * The list of other contact categories.
	 *
	 * @var       array
	 * @access    protected
	 * @since     4.0
	 */
	protected $categories = null;

	/**
	 * Constructor
	 *
	 * @param array $config An optional associative array of configuration settings.
	 *
	 * @throws \Exception
	 * @since   4.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'name', 'a.name',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to get a list of items.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 * @since   4.0
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
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$groupIds = array(1, 2, 3, 4, 5, 6, 7, 8);

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
					)
				)
			)
		)
			->from($db->quoteName('#__users', 'users'))
			->leftJoin(
				$db->quoteName('#__user_usergroup_map', 'usergroupmap')
				. ' ON ' . $db->quoteName('usergroupmap.user_id') . ' = ' . $db->quoteName('users.id')
			)
			->where($db->quoteName('usergroupmap.group_id') . ' IN (' . implode(',', $groupIds) . ')')
			->where($db->quoteName('users.block') . ' = 0')
			->group($db->quoteName('users.id'))
			->order(
				$this->getState('list.ordering', 'users.name') . ' ' .
				$this->getState('list.direction', 'ASC')
			);

		echo $query->dump();

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param string $ordering  An optional ordering field.
	 * @param string $direction An optional direction (asc|desc).
	 *
	 * @return  void
	 * @throws \Exception
	 * @since   1.6
	 */
	/*protected function populateState($ordering = null, $direction = null)
	{
		$app    = Factory::getApplication();
		$params = ComponentHelper::getParams('com_contact');

		// Get list ordering default from the parameters
		$menuParams = new Registry;

		if ($menu = $app->getMenu()->getActive())
		{
			$menuParams->loadString($menu->params);
		}

		$mergedParams = clone $params;
		$mergedParams->merge($menuParams);

		// List state information
		$format = $app->input->getWord('format');

		$numberOfContactsToDisplay = $mergedParams->get('contacts_display_num');

		if ($format === 'feed')
		{
			$limit = $app->get('feed_limit');
		}
		elseif (isset($numberOfContactsToDisplay))
		{
			$limit = $numberOfContactsToDisplay;
		}
		else
		{
			$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'uint');
		}

		$this->setState('list.limit', $limit);

		$limitstart = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $limitstart);

		// Optional filter text
		$itemid = $app->input->get('Itemid', 0, 'int');
		$search = $app->getUserStateFromRequest('com_contact.category.list.' . $itemid . '.filter-search', 'filter-search', '', 'string');
		$this->setState('list.filter', $search);

		$orderCol = $app->input->get('filter_order', $mergedParams->get('initial_sort', 'name'));

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'name';
		}

		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->input->get('filter_order_Dir', 'ASC');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);

		$id = $app->input->get('id', 0, 'int');
		$this->setState('category.id', $id);

		$user = Factory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_contact')) && (!$user->authorise('core.edit', 'com_contact')))
		{
			// Limit to published for people who can't edit or edit.state.
			$this->setState('filter.published', 1);

			// Filter by start and end dates.
			$this->setState('filter.publish_date', true);
		}

		$this->setState('filter.language', Multilanguage::isEnabled());

		// Load the parameters.
		$this->setState('params', $params);
	}*/
}
