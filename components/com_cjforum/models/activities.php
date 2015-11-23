<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumModelActivities extends JModelList
{
	public function __construct ($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
					'id', 'a.id',
					'activity_type', 'a.activity_type',
					'item_id', 'a.item_id',
					'published', 'a.published',
					'parent_id', 'a.parent_id',
					'created', 'a.created',
					'created_by', 'a.created_by',
					'featured', 'a.featured',
					'language', 'a.language',
					'likes', 'a.likes',
					'likes', 'a.dislikes',
					'comments', 'a.comments'
			);
		}
		
		parent::__construct($config);
	}

	protected function populateState ($ordering = 'ordering', $direction = 'ASC')
	{
		$app = JFactory::getApplication();
		
		// List state information
		$value = $app->input->get('limit', $app->getCfg('list_limit', 0), 'uint');
		$this->setState('list.limit', $value);
		
		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);
		
		$featured = $app->input->get('filter_featured', '', 'cmd');
		$this->setState('filter.featured', $featured);

		$authorId = $app->input->get('filter_author_id', 0, 'uint');
		$this->setState('filter.author_id', $authorId);

		$favored = $app->input->get('filter_favored', 0, 'uint');
		$this->setState('filter.favored', $favored);
		
		$orderCol = $app->input->get('filter_order', 'a.created');
		
		if (! in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.created';
		}
		
		$this->setState('list.ordering', $orderCol);
		
		$listOrder = $app->input->get('filter_order_Dir', 'DESC');
		
		if (! in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'DESC';
		}
		
		$this->setState('list.direction', $listOrder);
		
		$params = $app->getParams();
		$this->setState('params', $params);
		$user = JFactory::getUser();
		
		if (! $user->authorise('core.edit.state', 'com_cjforum') && ! $user->authorise('core.edit', 'com_cjforum'))
		{
			// Filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.published', 1);
		}
		
		$this->setState('filter.language', JLanguageMultilang::isEnabled());
		$this->setState('layout', $app->input->getString('layout'));
	}

	protected function getStoreId ($id = '')
	{
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.published'));
		$id .= ':' . $this->getState('filter.featured');
		$id .= ':' . $this->getState('filter.item_id');
		$id .= ':' . $this->getState('filter.item_id.include');
		$id .= ':' . serialize($this->getState('filter.activity_id'));
		$id .= ':' . $this->getState('filter.activity_id.include');
		$id .= ':' . serialize($this->getState('filter.author_id'));
		$id .= ':' . $this->getState('filter.author_id.include');
		$id .= ':' . $this->getState('filter.date_filtering');
		$id .= ':' . $this->getState('filter.date_field');
		$id .= ':' . $this->getState('filter.start_date_range');
		$id .= ':' . $this->getState('filter.end_date_range');
		$id .= ':' . $this->getState('filter.relative_date');
		
		return parent::getStoreId($id);
	}

	protected function getListQuery ()
	{
		// Get the current user for authorisation checks
		$user = JFactory::getUser();
		$params = JComponentHelper::getParams('com_cjforum');
		
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		// Select the required fields from the table.
		$query->select(
				$this->getState('list.select', 
						'a.id, a.title, a.description, a.activity_type, a.item_id, a.parent_id, a.language,'.
						'a.created_by, a.created, a.published, a.featured, a.language, a.likes, a.dislikes'));
		
		$query->from('#__cjforum_activity AS a');
		
		// Join over the activity types.
		$query->select('c.app_name, c.activity_name, c.published as activity_type_state')
			->join('LEFT', '#__cjforum_activity_types AS c ON c.id = a.activity_type');
		
		// Join over the users for the author and modified_by names.
		$query
			->select('ua.'.$params->get('display_name', 'name').' AS author, ua.email AS author_email')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
		
		// Filter by published state
		$published = $this->getState('filter.published');
		
		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif (is_array($published))
		{
			JArrayHelper::toInteger($published);
			$published = implode(',', $published);
			
			$query->where('a.published IN (' . $published . ')');
		}
		
		// Filter by featured state
		$featured = $this->getState('filter.featured');
		
		switch ($featured)
		{
			case 'hide':
				$query->where('a.featured = 0');
				break;
			
			case 'only':
				$query->where('a.featured = 1');
				break;
			
			case 'show':
			default:
				// Normally we do not discriminate
				// between featured/unfeatured items.
				break;
		}
		
		// Filter by a single or group of topics.
		$topicId = $this->getState('filter.activity_id');
		
		if (is_numeric($topicId))
		{
			$type = $this->getState('filter.activity_id.include', true) ? '= ' : '<> ';
			$query->where('a.id ' . $type . (int) $topicId);
		}
		elseif (is_array($topicId))
		{
			JArrayHelper::toInteger($topicId);
			$topicId = implode(',', $topicId);
			$type = $this->getState('filter.activity_id.include', true) ? 'IN' : 'NOT IN';
			$query->where('a.id ' . $type . ' (' . $topicId . ')');
		}
		
		// Filter by a single or group of categories
		$activityType = $this->getState('filter.activity_type');
		
		if (is_numeric($activityType))
		{
			$type = $this->getState('filter.activity_type.include', true) ? '= ' : '<> ';
			
			// Add subcategory check
			$includeSubcategories = $this->getState('filter.subcategories', false);
			$query->where('a.activity_type ' . $type . (int) $activityType);
		}
		elseif (is_array($activityType) && (count($activityType) > 0))
		{
			JArrayHelper::toInteger($activityType);
			$activityType = implode(',', $activityType);
			
			if (! empty($activityType))
			{
				$type = $this->getState('filter.activity_type.include', true) ? 'IN' : 'NOT IN';
				$query->where('a.activity_type ' . $type . ' (' . $activityType . ')');
			}
		}
		
		// Filter by author
		$authorId = $this->getState('filter.author_id');
		$authorWhere = '';
		
		if (is_numeric($authorId) && $authorId)
		{
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<> ';
			$authorWhere = 'a.created_by ' . $type . (int) $authorId;
			
			$favored = $this->getState('filter.favored', 0);
			if($favored)
			{
				$authorWhere = $authorWhere.' and a.id in (select item_id from #__cjforum_favorites where user_id = '.$authorId.' and item_type = 2)';
			}
		}
		elseif (is_array($authorId))
		{
			JArrayHelper::toInteger($authorId);
			$authorId = implode(',', $authorId);
			
			if ($authorId)
			{
				$type = $this->getState('filter.author_id.include', true) ? 'IN' : 'NOT IN';
				$authorWhere = 'a.created_by ' . $type . ' (' . $authorId . ')';
			}
		}
		
		if (! empty($authorWhere) )
		{
			$query->where($authorWhere);
		}
		
		// Filter by Date Range or Relative Date
		$dateFiltering = $this->getState('filter.date_filtering', 'off');
		$dateField = $this->getState('filter.date_field', 'a.created');
		
		switch ($dateFiltering)
		{
			case 'range':
				$startDateRange = $db->quote($this->getState('filter.start_date_range', $nullDate));
				$endDateRange = $db->quote($this->getState('filter.end_date_range', $nullDate));
				$query->where('(' . $dateField . ' >= ' . $startDateRange . ' AND ' . $dateField . ' <= ' . $endDateRange . ')');
				break;
			
			case 'relative':
				$relativeDate = (int) $this->getState('filter.relative_date', 0);
				$query->where($dateField . ' >= DATE_SUB(' . $nowDate . ', INTERVAL ' . $relativeDate . ' DAY)');
				break;
			
			case 'off':
			default:
				break;
		}
		
		// Process the filter for list views with user-entered filters
		$params = $this->getState('params');
		
		if ((is_object($params)) && ($params->get('filter_field') != 'hide') && ($filter = $this->getState('list.filter')))
		{
			// Clean filter variable
			$filter = JString::strtolower($filter);
			$hitsFilter = (int) $filter;
			$filter = $db->quote('%' . $db->escape($filter, true) . '%', false);
			
			switch ($params->get('filter_field'))
			{
				case 'author':
					$query->where('LOWER(ua.name) LIKE ' . $filter . ' ');
					break;
				
				case 'votes':
					$query->where('a.likes >= ' . $hitsFilter . ' ');
					break;
				
				case 'comments':
				default:
					// Default to 'title' if parameter is not valid
					$query->where('a.comments >= ' . $hitsFilter . ' ');
					break;
			}
		}
		
		// Filter by language
		if ($this->getState('filter.language'))
		{
			$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}
		
		// Add the list ordering clause.
		$query->order($this->getState('list.ordering', 'a.id') . ' ' . $this->getState('list.direction', 'ASC'));
// echo $query->dump();
		return $query;
	}

	public function getStart ()
	{
		return $this->getState('list.start');
	}
	
	public function getItems()
	{
		$items = parent::getItems();
		
		if(empty($items))
		{
			return false;
		}
		
		$activityIds = array();
		
		foreach ($items as $item)
		{
			$activityIds[] = $item->id;
		}
		
		$comments = array();
		$userIds = array();

		try
		{
			$db = JFactory::getDbo();
			
			$query = $db->getQuery(true)
				->select('a.id, a.parent_id, a.description, a.created_by, a.created, a.published, a.likes, a.dislikes')
				->select('ua.name AS author, ua.email AS author_email')
				->from('#__cjforum_activity_comments AS a')
				->join('LEFT OUTER', '#__cjforum_activity_comments AS b on (a.parent_id = b.parent_id and a.created < b.created)')
				->join('LEFT', '#__users AS ua ON ua.id = a.created_by')
				->where('a.published = 1 and a.parent_id in ('.implode(',', $activityIds).')')
				->group('a.id')
				->having('count(*) < 3')
				->order('a.created desc');
				
			$db->setQuery($query);
			$comments = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			return array();
		}
		
		foreach ($items as &$item)
		{
			$item->comments = array();
			$userIds[] = $item->created_by;
			
			if(!empty($comments))
			{
				foreach ($comments as $comment)
				{
					if($comment->parent_id == $item->id)
					{
						$item->comments[] = $comment;
						$userIds[] = $comment->created_by;
					}
				}
			}
		}
		
		// load user profiles at a go
		$params = $this->getState('params');
		$system = $params->get('profile_component', 'cjforum');
		
		$api = new CjLibApi();
		$api->prefetchUserProfiles($system, $userIds);
		
		return $items;
	}
}
