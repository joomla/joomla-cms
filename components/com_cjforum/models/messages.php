<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumModelMessages extends JModelList
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
					'parent_id', 'a.parent_id',
					'state', 'a.state',
					'access', 'a.access',
					'created', 'a.created',
					'created_by', 'a.created_by',
					'ordering', 'a.ordering',
					'publish_up', 'a.publish_up',
					'publish_down', 'a.publish_down'
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
		
		// Process show_noauth parameter
		if (! $params->get('show_noauth'))
		{
			$this->setState('filter.access', true);
		}
		else
		{
			$this->setState('filter.access', false);
		}
		
		$this->setState('filter.listtype', $app->input->get('filter_list_type', 1));
		$this->setState('layout', $app->input->getString('layout'));
	}

	protected function getStoreId ($id = '')
	{
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.published'));
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.featured');
		$id .= ':' . $this->getState('filter.topic_id');
		$id .= ':' . $this->getState('filter.topic_id.include');
		$id .= ':' . serialize($this->getState('filter.parent_id'));
		$id .= ':' . $this->getState('filter.category_id.include');
		$id .= ':' . serialize($this->getState('filter.author_id'));
		$id .= ':' . $this->getState('filter.author_id.include');
		$id .= ':' . serialize($this->getState('filter.author_alias'));
		$id .= ':' . $this->getState('filter.author_alias.include');
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
		$displayName = $params->get('display_name', 'name');
		
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		// Select the required fields from the table.
		$query->select(
				$this->getState('list.select', 
						'a.id, m.sender_id, m.receiver_id, m.sender_state, m.receiver_state,'.
						'CASE WHEN a.publish_up = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.publish_up END as publish_up, a.publish_down, a.access'));
		
		$query->from('#__cjforum_messages_map AS m');
		
		$query
			->select('a.title, a.alias, a.description, a.state, a.checked_out, a.checked_out_time, a.created, a.created_by, a.created_by_alias')
			->innerJoin('#__cjforum_messages as a on a.id = m.message_id');
		
// 		// Join over the users for the author and modified_by names.
// 		$query
// 			->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.name END AS author, ua.email AS author_email")
// 			->innerJoin('#__users AS ua ON ua.id = a.created_by');
// 		$query->where('m.parent_id = 0');
		
		$listType = $this->getState('layout');
		switch ($listType)
		{
			case 'sent': // sent items
				$query
					->select('u1.'.$displayName.' as receiver_name, u1.email as receiver_email')
					->join('LEFT', '#__users AS u1 ON u1.id = m.receiver_id');
				
				$query->where('m.sender_id = '.$user->id);
				break;
				
			case 'trash': // trash
				$query
					->select('u1.'.$displayName.' as receiver_name, u1.email as receiver_email')
					->join('LEFT', '#__users AS u1 ON u1.id = m.receiver_id');
				
				$query
					->select('u2.'.$displayName.' as sender_name, u2.email as sender_email')
					->join('LEFT', '#__users AS u2 ON u2.id = m.sender_id');
					
				$query->where('(m.sender_id = '.$user->id.' and m.sender_state = 3) or (m.receiver_id = '.$user->id.' and m.receiver_state = 3)');
				break;
				
			default: // inbox
				$query
					->select('u1.'.$displayName.' as sender_name, u1.email as sender_email')
					->join('LEFT', '#__users AS u1 ON u1.id = m.sender_id');
				
				$query->where('m.receiver_id = '.$user->id);
		}
		
		$query->where('m.parent_id = 0');
		
		// Filter by published state
		$published = $this->getState('filter.published');
		
		if (is_numeric($published))
		{
			// Use topic state if badcats.id is null, otherwise, force 0 for unpublished
			$query->where('a.state = ' . (int) $published);
		}
		elseif (is_array($published))
		{
			JArrayHelper::toInteger($published);
			$published = implode(',', $published);
			
			// Use topic state if badcats.id is null, otherwise, force 0 for
			// unpublished
			$query->where('a.state IN (' . $published . ')');
		}
		
		// Filter by author
		$authorId = $this->getState('filter.author_id');
		$authorWhere = '';
		
		if (is_numeric($authorId))
		{
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<> ';
			$authorWhere = 'a.created_by ' . $type . (int) $authorId;
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
		
		// Filter by author alias
		$authorAlias = $this->getState('filter.author_alias');
		$authorAliasWhere = '';
		
		if (is_string($authorAlias))
		{
			$type = $this->getState('filter.author_alias.include', true) ? '= ' : '<> ';
			$authorAliasWhere = 'a.created_by_alias ' . $type . $db->quote($authorAlias);
		}
		elseif (is_array($authorAlias))
		{
			$first = current($authorAlias);
			
			if (! empty($first))
			{
				JArrayHelper::toString($authorAlias);
				
				foreach ($authorAlias as $key => $alias)
				{
					$authorAlias[$key] = $db->quote($alias);
				}
				
				$authorAlias = implode(',', $authorAlias);
				
				if ($authorAlias)
				{
					$type = $this->getState('filter.author_alias.include', true) ? 'IN' : 'NOT IN';
					$authorAliasWhere = 'a.created_by_alias ' . $type . ' (' . $authorAlias . ')';
				}
			}
		}
		
		if (! empty($authorWhere) && ! empty($authorAliasWhere))
		{
			$query->where('(' . $authorWhere . ' OR ' . $authorAliasWhere . ')');
		}
		elseif (empty($authorWhere) && empty($authorAliasWhere))
		{
			// If both are empty we don't want to add to the query
		}
		else
		{
			// One of these is empty, the other is not so we just add both
			$query->where($authorWhere . $authorAliasWhere);
		}
		
		// Filter by start and end dates.
		if ((! $user->authorise('core.edit.state', 'com_cjforum')) && (! $user->authorise('core.edit', 'com_cjforum')))
		{
			$nullDate = $db->quote($db->getNullDate());
			$nowDate = $db->quote(JFactory::getDate()->toSql());
			
			$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')->where(
					'(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
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
					$query->where('LOWER( CASE WHEN a.created_by_alias > ' . $db->quote(' ') . ' THEN a.created_by_alias ELSE u1.'.$displayName.' END ) LIKE ' . $filter . ' ');
					break;
				
				case 'hits':
					$query->where('a.hits >= ' . $hitsFilter . ' ');
					break;
				
				case 'title':
				default:
					// Default to 'title' if parameter is not valid
					$query->where('LOWER( a.title ) LIKE ' . $filter);
					break;
			}
		}
		
		$query->group('m.message_id');
		
		// Add the list ordering clause.
		$query->order($this->getState('list.ordering', 'a.created') . ' ' . $this->getState('list.direction', 'DESC'));
// 		echo $query->dump();
		return $query;
	}

	public function getItems ()
	{
		$items = parent::getItems();
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$guest = $user->get('guest');
		$groups = $user->getAuthorisedViewLevels();
		$input = JFactory::getApplication()->input;
		
		// Get the global params
		$params = JComponentHelper::getParams('com_cjforum');
		$messageIds = array();
		
		// Convert the parameter fields into objects.
		foreach ($items as &$item)
		{
			$messageIds[] = $item->id;
			$item->receivers = array();
			$item->params = clone $this->getState('params');
			
			// Get display date
			switch ($item->params->get('list_show_date'))
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
			
			// Compute the asset access permissions.
			// Technically guest could edit an topic, but lets not check that
			// to improve performance a little.
			if (! $guest)
			{
				$asset = 'com_cjforum';
				
				// Check general edit permission first.
				if ($user->authorise('core.edit', $asset))
				{
					$item->params->set('access-edit', true);
				}
				
				// Now check if edit.own is available.
				elseif (! empty($userId) && $user->authorise('core.edit.own', $asset))
				{
					// Check for a valid user and that they are the owner.
					if ($userId == $item->created_by && $item->receiver_state == 0)
					{
						$item->params->set('access-edit', true);
					}
				}
			}
			
			$access = $this->getState('filter.access');
			
			if ($access)
			{
				// If the access filter has been set, we already have only the topics this user can view.
				$item->params->set('access-view', true);
			}
			else
			{
				$item->params->set('access-view', in_array($item->access, $groups));
			}
		}
		
		$listType = $this->getState('layout');
		// get the target users list.
		if (in_array($listType, array('sent', 'trash')) && !empty($messageIds))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.message_id, a.receiver_id')
				->select('u.'.$params->get('display_name', 'name').' as receiver_name')
				->from('#__cjforum_messages_map AS a')
				->join('left', '#__users AS u on a.receiver_id = u.id')
				->where('a.message_id in ('.implode(',', $messageIds).')')
				->where('parent_id = 0');
			
			try 
			{
				$db->setQuery($query);
				$receivers = $db->loadObjectList();
			}
			catch (Exception $e)
			{
				// nothing to do
			}
			
			if(!empty($receivers))
			{
				$receiverIds = array();
				foreach ($receivers as $receiver)
				{
					foreach ($items as &$item)
					{
						if($item->id == $receiver->message_id)
						{
							$item->receivers[] = $receiver;
							$receiverIds[] = $receiver->receiver_id;
						}
					}
				}
				
				// prefetch all profiles.
				$api = new CjLibApi();
				$avatar = $params->get('user_avatar', 'cjforum');
				$profile = $params->get('avatar_component', 'cjforum');
					
				$api->prefetchUserProfiles($avatar, $receiverIds);
					
				if($avatar != $profile)
				{
					$api->prefetchUserProfiles($profile, $receiverIds);
				}
			}
		}
		
		return $items;
	}

	public function getStart ()
	{
		return $this->getState('list.start');
	}
}
