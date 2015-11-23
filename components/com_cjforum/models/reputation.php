<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumModelReputation extends JModelList
{
	public function __construct ($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
					'id', 'a.id',
					'alias', 'r.title',
					'points', 'a.points'
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
		
		$authorId = $app->input->get('filter_author_id', 0, 'uint');
		$this->setState('filter.author_id', $authorId);

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
		
		$this->setState('layout', $app->input->getString('layout'));
	}

	protected function getStoreId ($id = '')
	{
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.published'));
		$id .= ':' . serialize($this->getState('filter.author_id'));
		$id .= ':' . $this->getState('filter.author_id.include');
		$id .= ':' . serialize($this->getState('filter.rule_id'));
		$id .= ':' . $this->getState('filter.rule_id.include');
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
		$query->select($this->getState('list.select', 'a.title, a.description, a.points, a.created'));
		$query->from('#__cjforum_points AS a');
		
		// Join over the rules.
		$query
			->select('r.title as rule_title')
			->join('LEFT', '#__cjforum_points_rules AS r ON r.id = a.rule_id');
		
		// Join over the users for the author and modified_by names.
		$query
			->select('ua.'.$params->get('display_name', 'name').' AS author, ua.email AS author_email')
			->join('LEFT', '#__users AS ua ON ua.id = a.user_id');
		
		// Filter by published state
		$published = $this->getState('filter.published');
		
		if (is_numeric($published))
		{
			// Use topic state if badcats.id is null, otherwise, force 0 for unpublished
			$query->where('a.published = ' . (int) $published);
		}
		elseif (is_array($published))
		{
			JArrayHelper::toInteger($published);
			$published = implode(',', $published);
			
			// Use topic state if badcats.id is null, otherwise, force 0 for
			// unpublished
			$query->where('a.published IN (' . $published . ')');
		}
		
		// Filter by a single or group of categories
		$ruleId = $this->getState('filter.rule_id');
		
		if (is_numeric($ruleId))
		{
			$type = $this->getState('filter.rule_id.include', true) ? '= ' : '<> ';
			$query->where('a.rule_id ' . $type . (int) $ruleId);
		}
		elseif (is_array($ruleId) && (count($ruleId) > 0))
		{
			JArrayHelper::toInteger($ruleId);
			$ruleId = implode(',', $ruleId);
			
			if (! empty($ruleId))
			{
				$type = $this->getState('filter.rule_id.include', true) ? 'IN' : 'NOT IN';
				$query->where('a.rule_id ' . $type . ' (' . $ruleId . ')');
			}
		}
		
		// Filter by author
		$authorId = $this->getState('filter.author_id');
		$authorWhere = '';
		
		if (is_numeric($authorId))
		{
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<> ';
			$authorWhere = 'a.user_id ' . $type . (int) $authorId;
		}
		elseif (is_array($authorId))
		{
			JArrayHelper::toInteger($authorId);
			$authorId = implode(',', $authorId);
			
			if ($authorId)
			{
				$type = $this->getState('filter.author_id.include', true) ? 'IN' : 'NOT IN';
				$authorWhere = 'a.user_id ' . $type . ' (' . $authorId . ')';
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
				
				case 'points':
					$query->where('a.points >= ' . $hitsFilter . ' ');
					break;
				
				case 'description':
				default:
					// Default to 'title' if parameter is not valid
					$query->where('LOWER( a.description ) LIKE ' . $filter);
					break;
			}
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
}
