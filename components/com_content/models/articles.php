<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * This models supports retrieving lists of articles.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @since		1.6
 */
class ContentModelArticles extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	public $_context = 'com_content.articles';

	/**
	 * Method to auto-populate the model state.
	 *
	 * @since	1.6
	 */
	protected function _populateState()
	{
		$app = &JFactory::getApplication();

		// List state information
		//$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$limit = JRequest::getInt('limit', $app->getCfg('list_limit', 0));
		$this->setState('list.limit', $limit);

		//$limitstart = $app->getUserStateFromRequest($this->_context.'.limitstart', 'limitstart', 0);
		$limitstart = JRequest::getInt('limitstart', 0);
		$this->setState('list.start', $limitstart);

		//$orderCol = $app->getUserStateFromRequest($this->_context.'.ordercol', 'filter_order', 'a.lft');
		$orderCol = JRequest::getCmd('filter_order', 'a.ordering');
		$this->setState('list.ordering', $orderCol);

		//$orderDirn = $app->getUserStateFromRequest($this->_context.'.orderdirn', 'filter_order_Dir', 'asc');
		$orderDirn = JRequest::getWord('filter_order_Dir', 'asc');
		$this->setState('list.direction', $orderDirn);

		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('filter.published',	1);
		$this->setState('filter.access',	true);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 */
	protected function _getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.access');

		return parent::_getStoreId($id);
	}

	/**
	 * @param	boolean	True to join selected foreign information
	 *
	 * @return	string
	 */
	function _getListQuery()
	{
		// Create a new query object.
		$query = new JQuery;

		// Select the required fields from the table.
		$query->select($this->getState(
			'list.select',
			'a.id, a.title, a.alias, a.title_alias, a.introtext, a.state, a.catid, a.created, a.created_by, a.created_by_alias,' .
			' a.modified, a.modified_by,a.publish_up, a.publish_down, a.attribs, a.metadata, a.metakey, a.metadesc, a.access,' .
			' a.hits,' .
			' LENGTH(a.fulltext) AS readmore'
		));
		$query->from('#__content AS a');

		// Join over the categories.
		$query->select('c.title AS category_title, c.path AS category_route, c.access AS category_access, c.alias AS category_alias');
		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the users for the author.
		$query->select('ua.name AS author_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$user	= &JFactory::getUser();
			$groups	= implode(',', $user->authorisedLevels());
			$query->where('a.access IN ('.$groups.')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		}
		else if (is_array($published))
		{
			JArrayHelper::toInteger($published);
			$published = implode(',', $published);
			$query->where('a.state IN ('.$published.')');
		}

		// Filter by a single or group of categories.
		$categoryId = $this->getState('filter.category_id');
		if (is_numeric($categoryId))
		{
			$type = $this->getState('filter.category_id.include', true) ? '= ' : '<>';
			$query->where('a.catid '.$type.(int) $categoryId);
		}
		else if (is_array($categoryId))
		{
			JArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);
			$type = $this->getState('filter.category_id.include', true) ? 'IN' : 'NOT IN';
			$query->where('a.catid '.$type.' ('.$categoryId.')');
		}

		$authorId 	= $this->getState('filter.author_id');

		if (is_numeric($authorId))
		{
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
			$query->where('a.created_by '.$type.(int) $authorId);
		}
		else if (is_array($authorId))
		{
			JArrayHelper::toInteger($authorId);
			$authorId = implode(',', $authorId);
			$type = $this->getState('filter.author_id.include', true) ? 'IN' : 'NOT IN';
			$query->where('a.created '.$type.' ('.$authorId.')');
		}

		// Filter by start and end dates.
		$nullDate	= $this->_db->Quote($this->_db->getNullDate());
		$nowDate	= $this->_db->Quote(JFactory::getDate()->toMySQL());

		$query->where('(a.publish_up = '.$nullDate.' OR a.publish_up <= '.$nowDate.')');
		$query->where('(a.publish_down = '.$nullDate.' OR a.publish_down >= '.$nowDate.')');

		// Add the list ordering clause.
		$query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.ordering')));

		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}


	/**
	 * Method to get a list of articles.
	 *
	 * Overriden to inject convert the attribs field into a JParameter object.
	 *
	 * @return	mixed	An array of objects on success, false on failure.
	 */
	public function &getItems()
	{
		$items	= &parent::getItems();
		$user	= &JFactory::getUser();
		$groups	= $user->authorisedLevels();

		// Contvert the parameter fields into objects.
		foreach ($items as &$item)
		{
			$registry = new JRegistry;
			$registry->loadJSON($item->attribs);
			$item->params = clone $this->getState('params');
			$item->params->merge($registry);
			
			// get display date
			switch ($item->params->get('show_date'))
			{
				case 'modified':
					$item->displayDate = $item->modified;
					break;
				
				case 'published':
					$item->displayDate = ($item->publish_up == 0) ? $item->created : $item->publish_up;
					break;
				
				default:
				case 'created': 
					$item->displayDate = $item->created;
					break;
			}

			// TODO: Embed the access controls in here
			$item->params->set('access-edit', false);

			$access = $this->getState('filter.access');
			if ($access = $this->getState('filter.access'))
			{
				// If the access filter has been set, we already have only the articles this user can view.
				$item->params->set('access-view', true);
			}
			else
			{
				// If no access filter is set, the layout takes some responsibility for display of limited information.
				if ($item->catid == 0 || $item->category_access === null) {
					$item->params->set('access-view', in_array($item->access, $groups));
				}
				else {
					$item->params->set('access-view', in_array($item->access, $groups) && in_array($item->category_access, $groups));
				}
			}
		}

		return $items;
	}
}
