<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumModelMessage extends JModelItem
{
	protected $_context = 'com_cjforum.message';

	protected function populateState ()
	{
		$app = JFactory::getApplication('site');
		
		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('message.id', $pk);

		$offset = $app->input->getUInt('limitstart');
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
		
		$pk = (! empty($pk)) ? $pk : (int) $this->getState('message.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}
		
		if (! isset($this->_item[$pk]))
		{
			try
			{
				$db = $this->getDbo();
				$query =  $db->getQuery(true)
					->select('m.message_id, m.sender_id, m.receiver_id, m.sender_state, m.receiver_state')
					->from('#__cjforum_messages_map AS m');
				
				// Join messages table.
				$query->select(
						$this->getState('item.select', 
								'a.id, a.title, a.alias, a.description, a.state, a.created, a.created_by, a.created_by_alias, ' . 
								'CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) .' THEN a.created ELSE a.modified END as modified, ' .
								'a.modified_by, a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.version, a.ordering, a.access'));
				$query->join('LEFT', '#__cjforum_messages AS a on a.id = m.message_id');
				
				// Join on user table.
				$query
					->select('u1.name AS sender_name, u1.email as sender_email')
					->join('LEFT', '#__users AS u1 on u1.id = m.sender_id');
				
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
				
				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived = $this->getState('filter.archived');
				
				if (is_numeric($published))
				{
					$query->where('(a.state = ' . (int) $published . ' OR a.state =' . (int) $archived . ')');
				}
				
				$query->where('a.id = '.$pk)->where('m.parent_id = 0');
				
				$db->setQuery($query);
// 				echo $query->dump();
// 				jexit();
				
				$data = $db->loadObject();
				
				if (empty($data))
				{
					return JError::raiseError(404, JText::_('COM_CJFORUM_PMS_ERROR_MESSAGE_NOT_FOUND'));
				}
				
				// Check for published state if filter set.
				if (((is_numeric($published)) || (is_numeric($archived))) && (($data->state != $published) && ($data->state != $archived)))
				{
					return JError::raiseError(404, JText::_('COM_CJFORUM_PMS_ERROR_MESSAGE_NOT_FOUND'));
				}
				
				// Convert parameter fields to objects.
				$data->params = clone $this->getState('params');
				
				// Technically guest could edit an message, but lets not check
				// that to improve performance a little.
				if (! $user->get('guest'))
				{
					$userId = $user->get('id');
					$asset = 'com_cjforum';
					
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
				
				// Get the replies
				$query = $db->getQuery(true)
					->select('a.id, a.title, a.alias, a.description, a.state, a.created, a.created_by, a.created_by_alias,'.
							'CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) .' THEN a.created ELSE a.modified END as modified, ' .
							'a.modified_by, a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.version, a.ordering, a.access')
					->select('u.name as author, u.email as author_email')
					->from('#__cjforum_messages AS a')
					->join('inner', '#__cjforum_messages_map AS m ON a.id = m.message_id')
					->join('inner', '#__users AS u on u.id = a.created_by')
					->where('m.parent_id = '.$data->message_id)
					->where('(m.sender_id = '.$user->id.' or m.receiver_id = '.$user->id.')')
					->order('a.created asc')
					->group('a.id');
// 				echo $query->dump();
				
				$db->setQuery($query);
				$data->replies = $db->loadObjectList();

				$query = $db->getQuery(true)
					->select('distinct a.receiver_id')
					->select('u.name as receiver_name')
					->from('#__cjforum_messages_map AS a')
					->join('left', '#__users AS u on a.receiver_id = u.id')
					->where('a.message_id = '.$pk. ' or a.parent_id = '.$pk);

				$db->setQuery($query);
				$data->participants = $db->loadObjectList();
				
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
	
	public function hit($pk = null)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		
		$pk = (! empty($pk)) ? $pk : (int) $this->getState('message.id');
		$query = $db->getQuery(true)
			->update('#__cjforum_messages_map')
			->set('receiver_state = 1')
			->where('receiver_id = '.$user->id.' AND (message_id = '.$pk.' OR parent_id = '.$pk.')');
		
		try
		{
			$db->setQuery($query);
			if($db->execute())
			{
				return true;
			}
		}
		catch (Exception $e)
		{
			return false;
		}
		 
		return false;
	}
}