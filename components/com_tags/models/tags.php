<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * This models supports retrieving a list of tags.
 *
 * @since  3.1
 */
class TagsModelTags extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $_context = 'com_tags.tags';

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @note Calling getState in this method will result in recursion.
	 *
	 * @since   3.1
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pid = $app->input->getInt('parent_id');
		$this->setState('tag.parent_id', $pid);

		$language = $app->input->getString('tag_list_language_filter');
		$this->setState('tag.language', $language);

		$offset = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.offset', $offset);
		$app = JFactory::getApplication();

		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('list.limit', $params->get('maximum', 200));

		$this->setState('filter.published', 1);
		$this->setState('filter.access', true);

		$user = JFactory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_tags')) &&  (!$user->authorise('core.edit', 'com_tags')))
		{
			$this->setState('filter.published', 1);
		}

		// Optional filter text
		$itemid = $pid . ':' . $app->input->getInt('Itemid', 0);
		$filterSearch = $app->getUserStateFromRequest('com_tags.tags.list.' . $itemid . '.filter_search', 'filter-search', '', 'string');
		$this->setState('list.filter', $filterSearch);
	}

	/**
	 * Redefine the function and add some properties to make the styling more easy
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   3.1
	 */
	public function getItems()
	{
		// Invoke the parent getItems method to get the main list
		$items = parent::getItems();

		if (!count($items))
		{
			$app = JFactory::getApplication();
			$menu = $app->getMenu();
			$active = $menu->getActive();
			$params = new Registry;

			if ($active)
			{
				$params->loadString($active->params);
			}
		}

		return $items;
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return  string  An SQL query
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$app            = JFactory::getApplication('site');
		$user           = JFactory::getUser();
		$groups         = implode(',', $user->getAuthorisedViewLevels());
		$pid            = $this->getState('tag.parent_id');
		$orderby        = $this->state->params->get('all_tags_orderby', 'title');
		$published      = $this->state->params->get('published', 1);
		$orderDirection = $this->state->params->get('all_tags_orderby_direction', 'ASC');
		$language       = $this->getState('tag.language');

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select required fields from the tags.
		$query->select('a.*, u.name as created_by_user_name, u.email')
			->from($db->quoteName('#__tags') . ' AS a')
			->join('LEFT', '#__users AS u ON a.created_user_id = u.id')
			->where($db->quoteName('a.access') . ' IN (' . $groups . ')');

		if (!empty($pid))
		{
			$query->where($db->quoteName('a.parent_id') . ' = ' . $pid);
		}

		// Exclude the root.
		$query->where($db->quoteName('a.parent_id') . ' <> 0');

		// Optionally filter on language
		if (empty($language))
		{
			$language = JComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');
		}

		if ($language !== 'all')
		{
			if ($language === 'current_language')
			{
				$language = JHelperContent::getCurrentLanguage();
			}

			$query->where($db->quoteName('language') . ' IN (' . $db->quote($language) . ', ' . $db->quote('*') . ')');
		}

		// List state information
		$format = $app->input->getWord('format');

		if ($format === 'feed')
		{
			$limit = $app->get('feed_limit');
		}
		else
		{
			if ($this->state->params->get('show_pagination_limit'))
			{
				$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'uint');
			}
			else
			{
				$limit = $this->state->params->get('maximum', 20);
			}
		}

		$this->setState('list.limit', $limit);

		$offset = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $offset);

		// Optionally filter on entered value
		if ($this->state->get('list.filter'))
		{
			$query->where($db->quoteName('a.title') . ' LIKE ' . $db->quote('%' . $this->state->get('list.filter') . '%'));
		}

		$query->where($db->quoteName('a.published') . ' = ' . $published);

		$query->order($db->quoteName($orderby) . ' ' . $orderDirection . ', a.title ASC');

		return $query;
	}
}
