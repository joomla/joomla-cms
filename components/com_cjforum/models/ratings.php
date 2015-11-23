<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2015 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumModelRatings extends JModelItem
{
	protected $_context = 'com_cjforum.ratings';

	public function like($pk, $topicId, $state = 0, $type = null)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$field = $state ? 'likes' : 'dislikes';
	
		try
		{
			$rating = new stdClass();
			$rating->item_id = $pk;
			$rating->user_id = $user->id;
			$rating->item_type = $type;
			$rating->action_value = $state ? 1 : 2;
			$tableName = ($type == ITEM_TYPE_TOPIC) ? 'topics' : 'replies';
			
			$query = $db->getQuery(true)
				->select('count(*)')
				->from('#__cjforum_user_ratings')
				->where('item_id = '.$pk.' and user_id = '.$user->id.' and item_type = '.$type);
				
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
						#__cjforum_'.$tableName.'
					set 
						likes = 
							(
								select 
									count(*) 
								from 
									#__cjforum_user_ratings 
								where 
									item_id = '.$pk.' and 
									item_type = '.$type.' and 
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
									item_type = '.$type.' and 
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
			$typeName = ($type == ITEM_TYPE_TOPIC) ? 'Topic' : 'Reply';
			$rating->topic_id = $topicId;
			$dispatcher->trigger('on'.$typeName.'AfterLike', array($this->option . '.' . strtolower($typeName), $rating));

			// return the final karma
			$query = $db->getQuery(true)
				->select('likes, dislikes')
				->from('#__cjforum_'.$tableName)
				->where('id = '.$pk);
			$db->setQuery($query);
			$result = $db->loadObject();
				
			return !empty($result) ? ($state ? $result->likes : $result->dislikes) : 0;
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
}
