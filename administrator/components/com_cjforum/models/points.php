<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumModelPoints extends JModelList
{
	public function __construct ($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
					'id', 'a.id',
					'title', 'a.title',
					'checked_out', 'a.checked_out',
					'checked_out_time', 'a.checked_out_time',
					'published', 'a.published',
					'access_level',
					'created', 'a.created',
					'created_by', 'a.created_by',
					'points', 'a.points',
					'publish_up', 'a.publish_up',
					'publish_down', 'a.publish_down',
					'author_id'
			);
			
			if (JLanguageAssociations::isEnabled())
			{
				$config['filter_fields'][] = 'association';
			}
		}
		
		parent::__construct($config);
	}

	protected function populateState ($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		
		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}
		
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		
		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);
		
		$authorId = $app->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
		$this->setState('filter.author_id', $authorId);
		
		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);
		
		// List state information.
		parent::populateState('a.created', 'desc');
	}

	protected function getStoreId ($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.author_id');
		
		return parent::getStoreId($id);
	}

	protected function getListQuery ()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		
		// Select the required fields from the table.
		$query->select(
				$this->getState('list.select', 
						'a.id, a.title, a.checked_out, a.checked_out_time, a.points, a.published, a.created, a.created_by, a.publish_up, a.publish_down,'.
						'a.user_id, a.rule_id, a.ref_id, a.checked_out, a.checked_out_time'));
		
		$query->from('#__cjforum_points AS a');
		
		// Join over points rules table
		$query->select('pr.title as rule_title, pr.rule_name, pr.description as rule_description')->join('LEFT', '#__cjforum_points_rules AS pr on a.rule_id = pr.id');
		
		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
		
		// Join over the users for the author.
		$query->select('ua.name AS author_name')->join('LEFT', '#__users AS ua ON ua.id = a.user_id');
		
		// Join over the users for replied by username
		$query->select('ur.name AS created_by_name')->join('LEFT', '#__users AS ur on ur.id = a.created_by');
		
		// Filter by published state
		$published = $this->getState('filter.published');
		
		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published = 0 OR a.published = 1)');
		}
		
		// Filter by author
		$authorId = $this->getState('filter.author_id');
		
		if (is_numeric($authorId))
		{
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
			$query->where('a.user_id ' . $type . (int) $authorId);
		}
		
		// Filter by search in title.
		$search = $this->getState('filter.search');
		
		if (! empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0)
			{
				$search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
				$query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(a.title LIKE ' . $search . ' OR ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
			}
		}
		
		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.created');
		$orderDirn = $this->state->get('list.direction', 'desc');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));
// 		echo $query->dump();
		return $query;
	}

	public function getAuthors ()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		// Construct the query
		$query->select('u.id AS value, u.name AS text')
			->from('#__users AS u')
			->join('INNER', '#__cjforum_points AS c ON c.user_id = u.id')
			->group('u.id, u.name')
			->order('u.name');
		
		// Setup the query
		$db->setQuery($query);
		
		// Return the result
		return $db->loadObjectList();
	}
}