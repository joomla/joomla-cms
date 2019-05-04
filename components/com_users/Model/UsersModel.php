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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;


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
	 * User items data
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
	 * @param   array  $config  An optional associative array of configuration settings.
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
	 * @throws \Exception
	 * @since   4.0
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

			// Count Articles
			$subQueryCountArticles = $db->getQuery(true);
			$subQueryCountArticles
				->select('COUNT(' . $db->quoteName('articles.id') . ')')
				->from($db->quoteName('#__content', 'articles'))
				->where($db->quoteName('articles.created_by') . '= ' . $db->quoteName('users.id'))
				->where($db->quoteName('articles.state') . '= 1');
			$query->select('(' . (string) $subQueryCountArticles . ') AS articlesByUser');

		if (is_numeric($groupIds))
		{
			$query->where($db->quoteName('usergroupmap.group_id') . ' = ' . (int) $groupIds);
		}
		elseif (is_array($groupIds) && (count($groupIds) > 0))
		{
			$groupIds = ArrayHelper::toInteger($groupIds);
			$query->where(
				$db->quoteName('usergroupmap.group_id') . ' IN (' . implode(',', $groupIds) . ')'
			);
		}

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
	 * @throws \Exception
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
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

		$this->setState('filter.language', Multilanguage::isEnabled());

		// Load the parameters.
		$this->setState('params', $params);
	}
}
