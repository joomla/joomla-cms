<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumModelActivitytypes extends JModelList
{
	public function __construct ($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
					'id', 'a.id',
					'title', 'a.title',
					'ordering', 'a.ordering',
					'app_name', 'a.app_name',
					'activity_name', 'a.activity_name',
					'checked_out', 'a.checked_out',
					'checked_out_time', 'a.checked_out_time',
					'published', 'a.published',
					'access', 'a.access',
					'created', 'a.created',
					'created_by', 'a.created_by',
					'language', 'a.language',
			        'app_name', 'a.app_name'
			);
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
		
		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$appName = $this->getUserStateFromRequest($this->context . '.filter.app_name', 'filter_app_name', '', 'cmd');
		$this->setState('filter.app_name', $appName);
		
		// List state information.
		parent::populateState('a.ordering', 'asc');
		
		// Force a language
		$forcedLanguage = $app->input->get('forcedLanguage');
		
		if (! empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}
	}

	protected function getStoreId ($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.author_id');
		$id .= ':' . $this->getState('filter.language');
		
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
		$query
			->select(
					$this->getState(
							'list.select', 
							'a.id, a.title, a.description, a.app_name, a.activity_name, a.created_by, a.created, a.access,'.
							'a.published, a.checked_out, a.checked_out_time, a.language, a.ordering')
					)
			->from('#__cjforum_activity_types AS a');
		
		// Join over the language
		$query
			->select('l.title AS language_title')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');
		
		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
		
		// Join over the users for the author.
		$query->select('ua.name AS author_name')->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
		
		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where('a.access = ' . (int) $access);
		}
		
		// Implement View Level Access
		if (! $user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}
		
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
			$query->where('a.created_by ' . $type . (int) $authorId);
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
				$query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
			}
		}
		
		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where('a.language = ' . $db->quote($language));
		}

		// Filter by app name
		$appName = $this->getState('filter.app_name');
		if (!empty($appName))
		{
		    $query->where('a.app_name = ' . $db->q($appName));
		}
		
		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.ordering');
		$orderDirn = $this->state->get('list.direction', 'asc');
		
		if ($orderCol == 'a.activity_type' || $orderCol == 'activity_type')
		{
			$orderCol = 'at.title ' . $orderDirn;
		}
		
		// SQL server change
		if ($orderCol == 'language')
		{
			$orderCol = 'l.title';
		}
		
		if ($orderCol == 'access_level')
		{
			$orderCol = 'ag.title';
		}
		
		$query->order($db->escape($orderCol . ' ' . $orderDirn));
// 		echo $query->dump();
		return $query;
	}

	public function getItems ()
	{
		$items = parent::getItems();
		
		if (JFactory::getApplication()->isSite())
		{
			$user = JFactory::getUser();
			$groups = $user->getAuthorisedViewLevels();
			
			for ($x = 0, $count = count($items); $x < $count; $x ++)
			{
				// Check the access level. Remove topics the user shouldn't see
				if (! in_array($items[$x]->access, $groups))
				{
					unset($items[$x]);
				}
			}
		}
		
		return $items;
	}
}