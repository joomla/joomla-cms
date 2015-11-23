<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumModelReplies extends JModelList
{

	public function __construct ($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
					'id', 'a.id',
					'checked_out', 'a.checked_out',
					'checked_out_time', 'a.checked_out_time',
					'topic_id', 'a.topic_id',
					'state', 'a.state',
					'access', 'a.access',
					'access_level',
					'created', 'a.created',
					'created_by', 'a.created_by',
					'ordering', 'a.ordering',
					'featured', 'a.featured',
					'publish_up', 'a.publish_up',
					'publish_down',	'a.publish_down'
			);
		}
		
		parent::__construct($config);
	}

	protected function populateState ($ordering = 'ordering', $direction = 'ASC')
	{
		$app = JFactory::getApplication();
		$params = $app->getParams();
		
		// List state information
		$value = $app->input->get('limit', $params->get('replies_limit', 10), 'uint');
		$this->setState('list.limit', $value);
		
		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);
		
		$orderCol = $app->input->get('filter_order', 'a.ordering');
		
		if (! in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.ordering';
		}
		
		$this->setState('list.ordering', $orderCol);
		
		$listOrder = $app->input->get('filter_order_Dir', 'ASC');
		
		if (! in_array(strtoupper($listOrder), array(
				'ASC',
				'DESC',
				''
		)))
		{
			$listOrder = 'ASC';
		}
		
		$this->setState('list.direction', $listOrder);
		$this->setState('params', $params);
		$user = JFactory::getUser();
		
		if ((! $user->authorise('core.edit.state', 'com_cjforum')) && (! $user->authorise('core.edit', 'com_cjforum')))
		{
			// Filter on published for those who do not have edit or edit.state
			// rights.
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
		
		$this->setState('layout', $app->input->getString('layout'));
	}

	protected function getStoreId ($id = '')
	{
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.published'));
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.topic_id');
		$id .= ':' . $this->getState('filter.topic_id.include');
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
		
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		// Select the required fields from the table.
		$query->select(
				$this->getState('list.select', 
						'a.id, a.description, a.checked_out, a.checked_out_time, a.topic_id, a.created, a.created_by, a.created_by_alias, a.likes, a.dislikes,' .
						// Use created if modified is 0
						'CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END as modified, ' .
						'a.modified_by, uam.'.$params->get('display_name', 'name').' as modified_by_name,' .
						// Use created if publish_up is 0
						'CASE WHEN a.publish_up = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.publish_up END as publish_up,' .
						'a.publish_down, a.images, a.attribs, a.access, a.featured, a.state'));
		
		$query->from('#__cjforum_replies AS a');
		
		// Join over the users for the author and modified_by names.
		$query->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.".$params->get('display_name', 'name')." END AS author")
			->select("ua.email AS author_email")
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by')
			->join('LEFT', '#__users AS uam ON uam.id = a.modified_by');
		
		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}
		
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
		$topicId = $this->getState('filter.topic_id');
		
		if (is_numeric($topicId))
		{
			$type = $this->getState('filter.topic_id.include', true) ? '= ' : '<> ';
			$query->where('a.topic_id ' . $type . (int) $topicId);
		}
		elseif (is_array($topicId))
		{
			JArrayHelper::toInteger($topicId);
			$topicId = implode(',', $topicId);
			$type = $this->getState('filter.topic_id.include', true) ? 'IN' : 'NOT IN';
			$query->where('a.topic_id ' . $type . ' (' . $topicId . ')');
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
		
		// Add the list ordering clause.
		$query->order($this->getState('list.ordering', 'a.created') . ' ' . $this->getState('list.direction', 'ASC'));
// echo $query->dump();
		return $query;
	}

	public function getItems ()
	{
		$query = $this->_getListQuery();
		
		$items = parent::getItems();
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$guest = $user->get('guest');
		$groups = $user->getAuthorisedViewLevels();
		$input = JFactory::getApplication()->input;
		
		// Get the global params
		$globalParams = JComponentHelper::getParams('com_cjforum', true);
		
		if($items)
		{
			$itemIds = array();
			$attachments = array();
			
			foreach ($items as $item)
			{
				$itemIds[] = $item->id;
			}
			
			try 
			{
				$db = JFactory::getDbo();
				$query  = $db->getQuery(true)
					->select('a.id, a.post_id, a.post_type, a.created_by, a.hash, a.filesize, a.folder, a.filetype, a.filename')
					->from('#__cjforum_attachments AS a')
					->where('a.post_id in ('.implode(',', $itemIds).') and a.post_type = 2');
// echo $query->dump();
				$db->setQuery($query);
				$attachments = $db->loadObjectList();
			}
			catch (Exception $e)
			{
				// nothiing
			}
			
			reset($items);
			
			// Convert the parameter fields into objects.
			foreach ($items as &$item)
			{
				$replyParams = new JRegistry();
				$replyParams->loadString($item->attribs);
				
				// Unpack readmore and layout params
				$item->alternative_readmore = $replyParams->get('alternative_readmore');
				$item->layout = $replyParams->get('layout');
				
				$item->params = clone $this->getState('params');
				$item->params->merge($replyParams);
				
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
					$asset = 'com_cjforum.topic.' . $item->topic_id;
					
					// Check general edit permission first.
					if ($user->authorise('core.edit', $asset))
					{
						$item->params->set('access-edit', true);
					}
					// Now check if edit.own is available.
					elseif (! empty($userId) && $user->authorise('core.edit.own', $asset))
					{
						// Check for a valid user and that they are the owner.
						if ($userId == $item->created_by)
						{
							$item->params->set('access-edit', true);
						}
					}
					
					
					if ($user->authorise('core.edit.state', $asset))
					{
						$item->params->set('access-edit-state', true);
					}
					// Now check if edit.state.own is available.
					elseif (! empty($userId) && $user->authorise('core.edit.state.own', $asset))
					{
						// Check for a valid user and that they are the owner.
						if ($userId == $item->created_by)
						{
							$item->params->set('access-edit-state', true);
						}
					}
				}
				
				$access = $this->getState('filter.access');
				$item->params->set('access-view', $access);
				
				$item->attachments = array();
				if(!empty($attachments))
				{
					foreach ($attachments as $attachment)
					{
						if($attachment->post_id == $item->id)
						{
							$item->attachments[] = $attachment;
						}
					}
					
					reset($itemIds);
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
