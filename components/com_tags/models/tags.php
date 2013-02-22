<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * This models supports retrieving a list of tags.
 *
 * @package     Joomla.Site
 * @subpackage  com_tags
 * @since       3.1
 */
class TagsModelTags extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	public $_context = 'com_tags.tags';

	private $_items = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since  3.1
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app    = JFactory::getApplication('site');

		// Load state from the request.
		$pid = $app->input->getInt('parent_id');
		$this->setState('tag.parent_id', $pid);

		$offset = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.offset', $offset);
		$app = JFactory::getApplication();

		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('filter.published', 1);
		$this->setState('filter.access', true);

		$user = JFactory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_tags')) &&  (!$user->authorise('core.edit', 'com_tags')))
		{
			$this->setState('filter.published', 1);
		}
	}

	/**
	 * Redefine the function and add some properties to make the styling more easy
	 *
	 * @return mixed An array of data items on success, false on failure.
	 *
	 * @since   3.1
	 */
	public function getItems()
	{
		// Invoke the parent getItems method to get the main list
		$items = parent::getItems();

		if(!count($items))
		{
			$app = JFactory::getApplication();
			$menu = $app->getMenu();
			$active = $menu->getActive();
			$params = new JRegistry;
			if($active)
			{
				$params->loadString($active->params);
			}

			$options = array();
		}

		return $items;
	}
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return  string	An SQL query
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$user	= JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$pid = $this->getState('tag.parent_id');
		$orderby = $this->state->params->get('all_tags_orderby', 'title');
		$orderDirection = $this->state->params->get('all_tags_orderby_direction', 'ASC');
		$limit = ' LIMIT 0,' . $this->state->params->get('maximum', 200);

		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select required fields from the tags.
		$query->select( 'a.*');


		$query->from($db->quoteName('#__tags').' AS a');
		$query->where($db->quoteName('a.access') . ' IN (' . $groups . ')');

		if (!empty($pid))
		{
			$query->where($db->quoteName('a.parent_id') . ' = ' . $pid );
		}

		$query->order($db->quoteName($orderby) . ' ' . $orderDirection . ' ' . $limit);

		return $query;
	}
}
