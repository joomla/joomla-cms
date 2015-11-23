<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumModelTopics extends JModelList
{
	public function __construct ($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
					'id', 'a.id',
					'title', 'a.title',
					'alias', 'a.alias',
					'checked_out', 'a.checked_out',
					'checked_out_time', 'a.checked_out_time',
					'catid', 'a.catid',
					'category_title',
					'state', 'a.state',
					'access', 'a.access',
					'access_level',
					'created', 'a.created',
					'created_by', 'a.created_by',
					'created_by_alias', 'a.created_by_alias',
					'replies', 'a.replies',
					'replied', 'a.replied',
					'replied_by', 'a.replied_by',
					'ordering', 'a.ordering',
					'featured', 'a.featured',
					'language', 'a.language',
					'hits', 'a.hits',
					'publish_up', 'a.publish_up',
					'publish_down', 'a.publish_down',
					'published', 'a.published',
					'author_id', 
					'category_id',
					'level',
					'tag'
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
		
		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
		$this->setState('filter.category_id', $categoryId);
		
		$level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level', 0, 'int');
		$this->setState('filter.level', $level);
		
		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);
		
		$tag = $this->getUserStateFromRequest($this->context . '.filter.tag', 'filter_tag', '');
		$this->setState('filter.tag', $tag);
		
		// List state information.
		parent::populateState('a.created', 'desc');
		
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
		$id .= ':' . $this->getState('filter.category_id');
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
		$query->select(
				$this->getState('list.select', 
						'a.id, a.title, a.alias, a.checked_out, a.checked_out_time, a.catid, a.replied, a.replied_by, a.replies' .
								 ', a.state, a.access, a.created, a.created_by, a.created_by_alias, a.ordering, a.featured, a.language, a.hits' .
								 ', a.publish_up, a.publish_down'));
		
		$query->from('#__cjforum_topics AS a');
		
		// Join over the language
		$query->select('l.title AS language_title')->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');
		
		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
		
		// Join over the asset groups.
		$query->select('ag.title AS access_level')->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
		
		// Join over the categories.
		$query->select('c.title AS category_title')->join('LEFT', '#__categories AS c ON c.id = a.catid');
		
		// Join over the users for the author.
		$query->select('ua.name AS author_name')->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
		
		// Join over the users for replied by username
		$query->select('ur.name AS replied_by_name')->join('LEFT', '#__users AS ur on ur.id = a.replied_by');
		
		// Join over the associations.
		if (JLanguageAssociations::isEnabled())
		{
			$query->select('COUNT(asso2.id)>1 as association')
				->join('LEFT', '#__associations AS asso ON asso.id = a.id AND asso.context=' . $db->quote('com_cjforum.item'))
				->join('LEFT', '#__associations AS asso2 ON asso2.key = asso.key')
				->group('a.id');
		}
		
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
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state = 0 OR a.state = 1)');
		}
		
		// Filter by a single or group of categories.
		$baselevel = 1;
		$categoryId = $this->getState('filter.category_id');
		
		if (is_numeric($categoryId))
		{
			$cat_tbl = JTable::getInstance('Category', 'JTable');
			$cat_tbl->load($categoryId);
			$rgt = $cat_tbl->rgt;
			$lft = $cat_tbl->lft;
			$baselevel = (int) $cat_tbl->level;
			$query->where('c.lft >= ' . (int) $lft)->where('c.rgt <= ' . (int) $rgt);
		}
		elseif (is_array($categoryId))
		{
			JArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);
			$query->where('a.catid IN (' . $categoryId . ')');
		}
		
		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$query->where('c.level <= ' . ((int) $level + (int) $baselevel - 1));
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
		
		// Filter by a single tag.
		$tagId = $this->getState('filter.tag');
		
		if (is_numeric($tagId))
		{
			$query->where($db->quoteName('tagmap.tag_id') . ' = ' . (int) $tagId)
				->join('LEFT', 
					$db->quoteName('#__contentitem_tag_map', 'tagmap') . ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' .
							 $db->quoteName('a.id') . ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_cjforum.topic'));
		}
		
		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.created');
		$orderDirn = $this->state->get('list.direction', 'desc');
		
		if ($orderCol == 'a.ordering' || $orderCol == 'category_title')
		{
			$orderCol = 'c.title ' . $orderDirn . ', a.ordering';
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

	public function getAuthors ()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		// Construct the query
		$query->select('u.id AS value, u.name AS text')
			->from('#__users AS u')
			->join('INNER', '#__cjforum_topics AS c ON c.created_by = u.id')
			->group('u.id, u.name')
			->order('u.name');
		
		// Setup the query
		$db->setQuery($query);
		
		// Return the result
		return $db->loadObjectList();
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