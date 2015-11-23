<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2015 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class CjForumModelTopic extends JModelItem
{
	protected $_context = 'com_cjforum.topic';
	
	public function __construct($config = array())
	{
		parent::__construct($config = array());
		$this->populateState();
	}
	
	protected function populateState ()
	{
		$app = JFactory::getApplication('site');
		
		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('topic.id', $pk);
		
		$offset = $app->input->getUint('limitstart');
		$this->setState('list.offset', $offset);
		
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		
		// TODO: Tune these values based on other permissions.
		$user = JFactory::getUser();
		
		if ((! $user->authorise('core.edit.state', 'com_cjforum')) && (! $user->authorise('core.edit', 'com_cjforum')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
		
		$this->setState('filter.language', JLanguageMultilang::isEnabled());
	}

	public function getItem ($pk = null)
	{
		$user = JFactory::getUser();
		
		$pk = (! empty($pk)) ? $pk : (int) $this->getState('topic.id');
		
		if ($this->_item === null)
		{
			$this->_item = array();
		}
		
		if (! isset($this->_item[$pk]))
		{
			try
			{
				$db = $this->getDbo();
				$params = JComponentHelper::getParams('com_cjforum');
				
				$query = $db->getQuery(true)->select(
						$this->getState('item.select', 
								'a.id, a.asset_id, a.title, a.alias, a.introtext, a.fulltext, a.likes, a.dislikes, a.locked, a.replies, a.replied, a.replied_by, a.last_reply,' .
								// If badcats is not null, this means that the topic is inside an unpublished category
								// In this case, the state is set to 0 to indicate Unpublished (even if the topic state is Published)
								'CASE WHEN badcats.id is null THEN a.state ELSE 0 END AS state, a.catid, a.created, a.created_by, a.created_by_alias, ' . 
								// Use created if modified is 0
								'CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) .' THEN a.created ELSE a.modified END as modified, ' .
								'a.modified_by, a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.images, a.urls, a.attribs, a.version, a.ordering, ' .
								'a.metakey, a.metadesc, a.access, a.hits, a.metadata, a.featured, a.language, a.xreference'));
				$query->from('#__cjforum_topics AS a');
				
				// Join on category table.
				$query
					->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access')
					->join('LEFT', '#__categories AS c on c.id = a.catid');
				
				// Join on user table.
				$query
					->select('u.'.$params->get('display_name', 'name').' AS author, u.email as author_email')
					->join('LEFT', '#__users AS u on u.id = a.created_by');
				
				// Filter by language
				if ($this->getState('filter.language'))
				{
					$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
				}
				
				// get last page number
				$repliesLimit = $this->getState('list.replies_limit', 10);
				if($repliesLimit > 0)
				{
					$query->select('(ceil(a.replies / '.$repliesLimit.') - 1) * '.$repliesLimit.' AS page_start');
				}
				else
				{
					$query->select('floor(a.replies / 5) * 5 AS page_start');
				}
				
				// Join over the categories to get parent category titles
				$query
					->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias')
					->join('LEFT', '#__categories as parent ON parent.id = c.parent_id')
					->where('a.id = ' . (int) $pk);
				
				if ((! $user->authorise('core.edit.state', 'com_cjforum')) && (! $user->authorise('core.edit', 'com_cjforum')))
				{
					// Filter by start and end dates.
					$nullDate = $db->quote($db->getNullDate());
					$date = JFactory::getDate();
					
					$nowDate = $db->quote($date->toSql());
					
					$query
						->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
						->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
				}
				
				// Join to check for category published state in parent
				// categories up the tree
				// If all categories are published, badcats.id will be null, and
				// we just use the topic state
				$subquery = ' (SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ';
				$subquery .= 'ON cat.lft BETWEEN parent.lft AND parent.rgt ';
				$subquery .= 'WHERE parent.extension = ' . $db->quote('com_cjforum');
				$subquery .= ' AND parent.published <= 0 GROUP BY cat.id)';
				$query->join('LEFT OUTER', $subquery . ' AS badcats ON badcats.id = c.id');
				
				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived = $this->getState('filter.archived');
				
				if (is_numeric($published))
				{
					$query->where('(a.state = ' . (int) $published . ' OR a.state =' . (int) $archived . ')');
				}
				
				$db->setQuery($query);
				
				$data = $db->loadObject();
				
				if (empty($data))
				{
					return JError::raiseError(404, JText::_('COM_CJFORUM_ERROR_TOPIC_NOT_FOUND'));
				}
				
				// Check for published state if filter set.
				if (((is_numeric($published)) || (is_numeric($archived))) && (($data->state != $published) && ($data->state != $archived)))
				{
					return JError::raiseError(404, JText::_('COM_CJFORUM_ERROR_TOPIC_NOT_FOUND'));
				}
				
				// Convert parameter fields to objects.
				$registry = new JRegistry();
				$registry->loadString($data->attribs);
				
				$data->params = clone $this->getState('params');
				$data->params->merge($registry);
				
				$registry = new JRegistry();
				$registry->loadString($data->metadata);
				$data->metadata = $registry;
				
				// Technically guest could edit an topic, but lets not check
				// that to improve performance a little.
				if (! $user->get('guest'))
				{
					$userId = $user->get('id');
					$asset = 'com_cjforum.topic.' . $data->id;
					
					// Check general edit permission first.
					if ($user->authorise('core.edit', $asset))
					{
						$data->params->set('access-edit', true);
					}
					// Now check if edit.own is available.
					elseif (! empty($userId) && $user->authorise('core.edit.own', $asset))
					{
						// Check for a valid user and that they are the owner.
						if ($userId == $data->created_by)
						{
							$data->params->set('access-edit', true);
						}
					}
					
					if ($user->authorise('core.reply', $asset))
					{
						$allowed = $this->isAllowedToCreateReply($data->id);
						$data->params->set('max_replies_limit_reached', !$allowed);
						$data->params->set('access-reply', $allowed);
					}
					
					// Check general edit state permission first.
					if ($user->authorise('core.edit.state', $asset))
					{
						$data->params->set('access-edit-state', true);
					}
					// Now check if edit.state.own is available.
					elseif (! empty($userId) && $user->authorise('core.edit.state.own', $asset))
					{
						// Check for a valid user and that they are the owner.
						if ($userId == $data->created_by)
						{
							$data->params->set('access-edit-state', true);
						}
					}
				}
				
				// Compute view access permissions.
				if ($access = $this->getState('filter.access'))
				{
					// If the access filter has been set, we already know this
					// user can view.
					$data->params->set('access-view', true);
				}
				else
				{
					// If no access filter is set, the layout takes some
					// responsibility for display of limited information.
					$user = JFactory::getUser();
					$groups = $user->getAuthorisedViewLevels();
					
					if ($data->catid == 0 || $data->category_access === null)
					{
						$data->params->set('access-view', in_array($data->access, $groups));
					}
					else
					{
						$data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
					}
				}
				
				if($user->authorise('core.download', 'com_cjforum.category.'.$data->catid))
				{
					$data->params->set('access-download', true);
				}
				
				$data->favorite = 0;
				if(!$user->guest)
				{
					// get favorite status
					$query = $db->getQuery(true)
						->select('count(*)')
						->from('#__cjforum_favorites')
						->where('item_id = '.$data->id)
						->where('user_id = '.$user->id)
						->where('item_type = '.ITEM_TYPE_TOPIC);
					
					$db->setQuery($query);
					$data->favorite = (int) $db->loadResult();
				}
				
				$query = $db->getQuery(true)
					->select('a.id, a.post_id, a.post_type, a.created_by, a.hash, a.filesize, a.folder, a.filetype, a.filename')
					->from('#__cjforum_attachments AS a')
					->where('a.post_id = '.$pk.' and a.post_type = 1');
				
				$db->setQuery($query);
				$data->attachments = $db->loadObjectList();
				
				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go thru the error handler to allow Redirect to
					// work.
					JError::raiseError(404, $e->getMessage());
				}
				else
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}
		
		return $this->_item[$pk];
	}

	public function hit ($pk = 0)
	{
		$input = JFactory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);
		
		if ($hitcount)
		{
			$pk = (! empty($pk)) ? $pk : (int) $this->getState('topic.id');
			
			$table = JTable::getInstance('Topic', 'CjForumTable');
			$table->load($pk);
			$table->hit($pk);
		}
		
		return true;
	}

	public function storeVote ($pk = 0, $rate = 0)
	{
		if ($rate >= 1 && $rate <= 5 && $pk > 0)
		{
			$userIP = $_SERVER['REMOTE_ADDR'];
			
			// Initialize variables.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			// Create the base select statement.
			$query->select('*')
				->from($db->quoteName('#__cjforum_rating'))
				->where($db->quoteName('topic_id') . ' = ' . (int) $pk);
			
			// Set the query and load the result.
			$db->setQuery($query);
			$rating = $db->loadObject();
			
			// Check for a database error.
			if ($db->getErrorNum())
			{
				JError::raiseWarning(500, $db->getErrorMsg());
				
				return false;
			}
			
			// There are no ratings yet, so lets insert our rating
			if (! $rating)
			{
				$query = $db->getQuery(true);
				
				// Create the base insert statement.
				$query->insert($db->quoteName('#__cjforum_rating'))
					->columns(
						array(
								$db->quoteName('topic_id'),
								$db->quoteName('lastip'),
								$db->quoteName('rating_sum'),
								$db->quoteName('rating_count')
						))
					->values((int) $pk . ', ' . $db->quote($userIP) . ',' . (int) $rate . ', 1');
				
				// Set the query and execute the insert.
				$db->setQuery($query);
				
				try
				{
					$db->execute();
				}
				catch (RuntimeException $e)
				{
					JError::raiseWarning(500, $e->getMessage());
					
					return false;
				}
			}
			else
			{
				if ($userIP != ($rating->lastip))
				{
					$query = $db->getQuery(true);
					
					// Create the base update statement.
					$query->update($db->quoteName('#__cjforum_rating'))
						->set($db->quoteName('rating_count') . ' = rating_count + 1')
						->set($db->quoteName('rating_sum') . ' = rating_sum + ' . (int) $rate)
						->set($db->quoteName('lastip') . ' = ' . $db->quote($userIP))
						->where($db->quoteName('cjforum_id') . ' = ' . (int) $pk);
					
					// Set the query and execute the update.
					$db->setQuery($query);
					
					try
					{
						$db->execute();
					}
					catch (RuntimeException $e)
					{
						JError::raiseWarning(500, $e->getMessage());
						
						return false;
					}
				}
				else
				{
					return false;
				}
			}
			
			return true;
		}
		
		JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('COM_CJFORUM_INVALID_RATING', $rate), "JModelTopic::storeVote($rate)");
		
		return false;
	}
	
	public function like($pk, $state = 0)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$field = $state ? 'likes' : 'dislikes';
		$actionValue = $state ? '1' : '2';
	
		try
		{
			$rating = new stdClass();
			$rating->item_id = $pk;
			$rating->user_id = $user->id;
			$rating->item_type = ITEM_TYPE_TOPIC;
			$rating->action_value = $state ? 1 : 2;
			
			$query = $db->getQuery(true)
				->select('count(*)')
				->from('#__cjforum_user_ratings')
				->where('item_id = '.$pk.' and user_id = '.$user->id.' and item_type = '.ITEM_TYPE_TOPIC);
				
			$db->setQuery($query);
			$count = (int) $db->loadResult();
				
			if($count > 0)
			{
				$rating->modified = JFactory::getDate()->toSql();
				if(!$db->updateObject('#__cjforum_user_ratings', $rating, array('item_id', 'user_id', 'item_type')))
				{
					return false;
				}
			}
			else 
			{
				$rating->created = JFactory::getDate()->toSql();
				if(!$db->insertObject('#__cjforum_user_ratings', $rating))
				{
					return false;
				}
			}
			
			$query = '
					update 
						#__cjforum_topics
					set 
						likes = 
							(
								select 
									count(*) 
								from 
									#__cjforum_user_ratings 
								where 
									item_id = '.$pk.' and 
									item_type = '.ITEM_TYPE_TOPIC.' and 
									action_value = 1
							),
						dislikes = 
							(
								select 
									count(*) 
								from 
									#__cjforum_user_ratings 
								where 
									item_id = '.$pk.' and 
									item_type = '.ITEM_TYPE_TOPIC.' and 
									action_value = 2
							)
					where
						id = '.$pk;
			
			$db->setQuery($query);
			
			if(!$db->execute())
			{
				return false;
			}
			
			JPluginHelper::importPlugin('cjforum');
			$dispatcher = JEventDispatcher::getInstance();
			$dispatcher->trigger('onTopicAfterLike', array($this->option . '.' . $this->name, $rating));
			
			return true;
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
			return false;
		}
	
		return false;
	}
	
	public function getLikes($items = null, $userId = 0)
	{
		$db = JFactory::getDbo();
		$userId = $userId ? $userId : JFactory::getUser()->id;
		
		try 
		{
			$query = $db->getQuery(true)
				->select('item_id, item_type, action_value, created')
				->from('#__cjforum_user_ratings')
				->where('user_id = '.$userId);
			
			if(!empty($items) && is_array($items))
			{
				$wheres = array();
				
				foreach ($items as $item)
				{
					$wheres[] = '(item_id = '.$item['id'].' and item_type = '.$item['type'].')';
				}
				
				$query->where('('.implode(' OR ', $wheres).')');
			}
// 			echo $query->dump();
			
			$db->setQuery($query);
			$likes = $db->loadObjectList();
			
			return !empty($likes) ? $likes : array();
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
			return false;
		}
		
		return false;
	}
	
	public function getThankyou($items = null)
	{
		$db = JFactory::getDbo();
		
		try
		{
			$query = $db->getQuery(true)
				->select('a.item_id, a.item_type, a.created_by, a.created, a.assigned_to, a.published')
				->select('u1.name AS created_by_name, u2.name AS assigned_to_name')
				->select('u1.email AS created_by_email, u2.email AS assigned_to_email')
				->from('#__cjforum_thankyou AS a')
				->join('left', '#__users AS u1 ON u1.id = a.created_by')
				->join('left', '#__users AS u2 ON u2.id = a.assigned_to')
				->where('a.published = 1');
			
			if(!empty($items) && is_array($items))
			{
				$wheres = array();
			
				foreach ($items as $item)
				{
					$wheres[] = '(item_id = '.$item['id'].' and item_type = '.$item['type'].')';
				}
			
				$query->where('('.implode(' OR ', $wheres).')');
			}
			
			$db->setQuery($query);
			$thankyou = $db->loadObjectList();
			
			return !empty($thankyou) ? $thankyou : array();;
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
			return false;
		}
		
		return false;
	}
	
	public function getAttachmentDetails($id)
	{
		$db = JFactory::getDbo();
		
		try 
		{
			$query = $db->getQuery(true)
				->select('id, post_id, post_type, created_by, hash, filesize, folder, filetype, filename')
				->from('#__cjforum_attachments')
				->where('id = '.$id);
			
			$db->setQuery($query);
			$attachment = $db->loadObject();
			
			return $attachment;
		}
		catch (Exception $e)
		{
			return false;
		}
		
		return false;
	}
	
	public function isAllowedToCreateReply($pk)
	{
		$user 				= JFactory::getUser();
		$db 				= JFactory::getDbo();
		$pk 				= (! empty($pk)) ? $pk : (int) $this->getState('topic.id');
		$params 			= $this->getState('params');
		$newUserDays		= (int) $params->get('new_user_status_days', 0);
		$maxReplies			= (int) $params->get('max_replies_per_day', 0);
		$maxRepliesPerTopic	= (int) $params->get('max_replies_per_topic', 0);
		$allowed			= true;
		
		if(strtotime($user->registerDate) >= strtotime('-'.$newUserDays.' days'))
		{
			$maxReplies			= (int) $params->get('max_replies_per_new_user', 0);
			$maxRepliesPerTopic	= (int) $params->get('max_replies_per_topic_new_user', 0);
		}
		
		if($maxRepliesPerTopic > 0)
		{
			$query = $db->getQuery(true)
				->select('count(*)')
				->from('#__cjforum_replies')
				->where('topic_id = '.$pk)
				->where('created_by = '.$user->id);
			
			$db->setQuery($query);
			$count = (int) $db->loadResult();
			$allowed = $count < $maxRepliesPerTopic;
		}
		
		if($allowed && $maxReplies > 0)
		{
			$query = $db->getQuery(true)
				->select('count(*)')
				->from('#__cjforum_replies')
				->where('created_by = '.$user->id)
				->where('created > DATE_SUB(NOW(), INTERVAL 1 DAY)');
			
			$db->setQuery($query);
			$count = (int) $db->loadResult();
				
			return $count < $maxReplies;
		}
		
		return $allowed;
	}
}
