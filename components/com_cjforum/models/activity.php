<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumModelActivity extends JModelItem
{
	public function __construct ($config = array())
	{
		parent::__construct($config);
	}
	protected $_context = 'com_cjforum.topic';
	
	protected function populateState ()
	{
		$app = JFactory::getApplication('site');
	
		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('activity.id', $pk);
	
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
		$pk = (! empty($pk)) ? $pk : (int) $this->getState('activity.id');
	
		if ($this->_item === null)
		{
			$this->_item = array();
		}
	
		if (! isset($this->_item[$pk]))
		{
			try
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true);
				
				$query->select(
					$this->getState('list.select', 
						'a.id, a.title, a.description, a.activity_type, a.item_id, a.parent_id, a.language,'.
						'a.created_by, a.created, a.published, a.featured, a.language, a.likes, a.dislikes'));
				
				$query
					->from('#__cjforum_activity AS a')
					->where('a.id = ' . (int) $pk);
	
				// Join on user table.
				$query
					->select('u.name AS author, u.email as author_email')
					->join('LEFT', '#__users AS u on u.id = a.created_by');
	
				// Filter by language
				if ($this->getState('filter.language'))
				{
					$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
				}
						
				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived = $this->getState('filter.archived');
	
				if (is_numeric($published))
				{
					$query->where('(a.published = ' . (int) $published . ' OR a.published =' . (int) $archived . ')');
				}
				
				$db->setQuery($query);
	
				$data = $db->loadObject();
	
				if (empty($data))
				{
					return JError::raiseError(404, JText::_('COM_CJFORUM_ERROR_ACTIVITY_NOT_FOUND'));
				}
	
				// Check for published state if filter set.
				if (((is_numeric($published)) || (is_numeric($archived))) && (($data->published != $published) && ($data->published != $archived)))
				{
					return JError::raiseError(404, JText::_('COM_CJFORUM_ERROR_ACTIVITY_NOT_FOUND'));
				}
				
				$data->comments = $this->getActivityComments($pk);
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
	
	public function getActivityComments($pk, $start = 0, $limit = 10)
	{
		$db = JFactory::getDbo();
		if(!$pk) return array();
		
		try 
		{
			$params = JComponentHelper::getParams('com_cjforum');
			$query = $db->getQuery(true)
				->select('a.id, a.parent_id, a.description, a.created_by, a.created, a.published, a.likes, a.dislikes')
				->select('ua.'.$params->get('display_name', 'name').' AS author, ua.email AS author_email')
				->from('#__cjforum_activity_comments AS a')
				->join('LEFT', '#__users AS ua ON ua.id = a.created_by')
				->where('a.published = 1 and a.parent_id = '.$pk )
				->order('a.created desc');
			
			$db->setQuery($query, $start, $limit);
			$comments = $db->loadObjectList();
			
			return $comments;
		}
		catch (Exception $e)
		{
			throw new Exception(JText::_('COM_CJFORUM_DATABASE_ERROR'), 500);
		}
	}
	
	public function saveComment(&$comment)
	{
		$db = JFactory::getDbo();
		
		try 
		{
			if($comment->id > 0)
			{
				if($db->updateObject('#__cjforum_activity_comments', $comment, 'id'))
				{
					return true;	
				}
			}
			else
			{
				if($db->insertObject('#__cjforum_activity_comments', $comment))
				{
					$comment->id = $db->insertid();
					return true;
				}
			}
		}
		catch (Exception $e)
		{
			throw new Exception(JText::_('COM_CJFORUM_DATABASE_ERROR'), 500);
		}
		
		return false;
	}
}
