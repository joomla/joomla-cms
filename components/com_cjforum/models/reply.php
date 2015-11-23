<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JLoader::register('CjForumHelper', JPATH_ADMINISTRATOR . '/components/com_cjforum/helpers/cjforum.php');

class CjForumModelReply extends JModelAdmin
{
	protected $text_prefix = 'COM_CJFORUM';
	public $typeAlias = 'com_cjforum.topic';
	protected $_item = null;
	protected $_context = 'com_cjforum.reply';

	public function __construct($config)
	{
		$config['event_after_delete'] = 'onReplyAfterDelete';
		$config['event_before_delete'] = 'onReplyBeforeDelete';
		$config['event_change_state'] = 'onReplyChangeState';
	
		$config['events_map'] = array('delete'=>'cjforum', 'change_state'=>'cjforum');
	
		parent::__construct($config);
	}
	
	protected function populateState ()
	{
		$app = JFactory::getApplication('site');
	
		// Load state from the request.
		$pk = $app->input->getInt('r_id');
		$this->setState('reply.id', $pk);
	
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
	
	protected function canDelete ($record)
	{
		if (! empty($record->topic_id))
		{
			if ($record->state != - 2)
			{
				return;
			}
			$user = JFactory::getUser();
			return $user->authorise('core.delete', 'com_cjforum.topic.' . (int) $record->topic_id);
		}
	}

	protected function canEditState ($record)
	{
		$user = JFactory::getUser();
		
		// Check for existing topic.
		if (! empty($record->topic_id))
		{
			return $user->authorise('core.edit.state', 'com_cjforum.topic.' . (int) $record->topic_id) || 
				(!$user->guest && $user->id == $record->created_by && $user->authorise('core.edit.state.own', 'com_cjforum.topic.' . (int) $record->topic_id));
		}
		// Default to component settings if neither topic nor category known.
		else
		{
			return ($user->authorise('core.edit.state', 'com_cjforum') || $user->authorise('core.edit.state.own', 'com_cjforum'));
		}
	}

	protected function prepareTable ($table)
	{
		// Set the publish date to now
		$db = $this->getDbo();
		if ($table->state == 1 && (int) $table->publish_up == 0)
		{
			$table->publish_up = JFactory::getDate()->toSql();
		}
		
		if ($table->state == 1 && intval($table->publish_down) == 0)
		{
			$table->publish_down = $db->getNullDate();
		}
		
		// Reorder the replies within the topic so the new topic is first
		if (empty($table->id))
		{
			$table->reorder('topic_id = ' . (int) $table->topic_id . ' AND state >= 0');
		}
	}

	public function getTable ($type = 'Reply', $prefix = 'CjForumTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getItem ($pk = null)
	{
		$user = JFactory::getUser();
		
		$pk = (! empty($pk)) ? $pk : (int) $this->getState('reply.id');
		
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
								'a.id, a.description, a.topic_id,'.
								'CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) .' THEN a.created ELSE a.modified END as modified, ' .
								'a.modified_by, a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, ' .
								'a.images, a.attribs, a.ordering, a.access, a.featured'));
				$query->from('#__cjforum_replies AS a');
				
				// Join on topics table.
				$query
					->select('t.catid, t.title AS topic_title, t.alias AS topic_alias, t.access AS topic_access')
					->join('LEFT', '#__cjforum_topics AS t on t.id = a.topic_id');
				
				// Join on user table.
				$query
					->select('u.'.$params->get('display_name', 'name').' AS author, u.email as author_email')
					->join('LEFT', '#__users AS u on u.id = a.created_by');
				
				// where
				$query->where('a.id = ' . (int) $pk);
				
				if ((! $user->authorise('core.edit.state', 'com_cjforum')) && (! $user->authorise('core.edit', 'com_cjforum')))
				{
					// Filter by start and end dates.
					$nullDate = $db->quote($db->getNullDate());
					$date = JFactory::getDate();
					
					$nowDate = $db->quote($date->toSql());
					
					$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')->where(
							'(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
				}
				
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
					return JError::raiseError(404, JText::_('COM_CJFORUM_ERROR_REPLY_NOT_FOUND'));
				}
				
				// Check for published state if filter set.
				if (((is_numeric($published)) || (is_numeric($archived))) && (($data->state != $published) && ($data->state != $archived)))
				{
					return JError::raiseError(404, JText::_('COM_CJFORUM_ERROR_REPLY_NOT_FOUND'));
				}
				
				// Convert parameter fields to objects.
				$registry = new JRegistry();
				$registry->loadString($data->attribs);
				
				$data->params = clone $this->getState('params');
				$data->params->merge($registry);
				
				// Technically guest could edit an reply, but lets not check
				// that to improve performance a little.
				if (! $user->get('guest'))
				{
					$userId = $user->get('id');
					$asset = 'com_cjforum.topic.' . $data->topic_id;
					
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

	public function getForm ($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_cjforum.reply', 'reply', array(
				'control' => 'jform',
				'load_data' => $loadData
		));
		if (empty($form))
		{
			return false;
		}
		$jinput = JFactory::getApplication()->input;
		
		// The front end calls this model and uses r_id to avoid id clashes so
		// we need to check for that first.
		if ($jinput->get('r_id'))
		{
			$id = $jinput->get('r_id', 0);
		}
		
		// Determine correct permissions to check.
		if ($this->getState('reply.id'))
		{
			$id = $this->getState('reply.id');
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('topic_id', 'action', 'core.edit');
			// Existing record. Can only edit own topics in selected categories.
			$form->setFieldAttribute('topic_id', 'action', 'core.edit.own');
		}
		else
		{
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('topic_id', 'action', 'core.create');
		}
		
		$user = JFactory::getUser();
		
		// Check for existing topic.
		$topic_id = $jinput->get('topic_id', 0);
		
		// Modify the form based on Edit State access controls.
		if ($topic_id != 0 && (! $user->authorise('core.edit.state', 'com_cjforum.topic.' . (int) $topic_id)) ||
				 ($topic_id == 0 && ! $user->authorise('core.edit.state', 'com_cjforum')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('featured', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');
			
			// Disable fields while saving.
			// The controller has already verified this is an topic you can
			// edit.
			$form->setFieldAttribute('featured', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}
		
		// Prevent messing with topic language and category when editing
		// existing topic with associations
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();
		
		if ($app->isSite() && $assoc && $this->getState('topic.id'))
		{
			$form->setFieldAttribute('language', 'readonly', 'true');
			$form->setFieldAttribute('topic_id', 'readonly', 'true');
			$form->setFieldAttribute('language', 'filter', 'unset');
			$form->setFieldAttribute('topic_id', 'filter', 'unset');
		}
		
		return $form;
	}

	protected function loadFormData ()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_cjforum.edit.reply.data', array());
		
		if (empty($data) && $this->getState('reply.id') > 0)
		{
			$data = $this->getItem();
		}
		
		$this->preprocessData('com_cjforum.reply', $data);
		
		return $data;
	}

	public function save ($data)
	{
		$app = JFactory::getApplication();
		
		if (isset($data['images']) && is_array($data['images']))
		{
			$registry = new JRegistry();
			$registry->loadArray($data['images']);
			$data['images'] = (string) $registry;
		}
		
		if (parent::save($data))
		{
			// add subscription
			$user = JFactory::getUser();
			$db = JFactory::getDbo();
			
			$subscribe = $app->input->getInt('subscribe');
			$id = (int) $this->getState($this->getName() . '.id');
			
			if($id && $subscribe && !$user->guest)
			{
				$query = $db->getQuery(true)
					->insert('#__cjforum_subscribes')
					->columns('subscription_type, subscription_id, subscriber_id')
					->values('1, '.$data['topic_id'].','.$user->id);
				
				try 
				{
					$db->setQuery($query);
					$db->execute();
				}
				catch (Exception $e)
				{
// 					return false;
				}
			}
			
			return $this->updateCounts(array($id));
		}
		
		return false;
	}

	protected function getReorderConditions ($table)
	{
		$condition = array();
		$condition[] = 'topic_id = ' . (int) $table->topic_id;
		return $condition;
	}

	protected function preprocessForm (JForm $form, $data, $group = 'content')
	{
		// Association content items
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();
		if ($assoc)
		{
			$languages = JLanguageHelper::getLanguages('lang_code');
			
			// force to array (perhaps move to $this->loadFormData())
			$data = (array) $data;
			
			$addform = new SimpleXMLElement('<form />');
			$fields = $addform->addChild('fields');
			$fields->addAttribute('name', 'associations');
			$fieldset = $fields->addChild('fieldset');
			$fieldset->addAttribute('name', 'item_associations');
			$fieldset->addAttribute('description', 'COM_CJFORUM_ITEM_ASSOCIATIONS_FIELDSET_DESC');
			$add = false;
			foreach ($languages as $tag => $language)
			{
				if (empty($data['language']) || $tag != $data['language'])
				{
					$add = true;
					$field = $fieldset->addChild('field');
					$field->addAttribute('name', $tag);
					$field->addAttribute('type', 'modal_topic');
					$field->addAttribute('language', $tag);
					$field->addAttribute('label', $language->title);
					$field->addAttribute('translate_label', 'false');
					$field->addAttribute('edit', 'true');
					$field->addAttribute('clear', 'true');
				}
			}
			if ($add)
			{
				$form->load($addform, false);
			}
		}
		
		parent::preprocessForm($form, $data, $group);
	}

	protected function cleanCache ($group = null, $client_id = 0)
	{
		parent::cleanCache('com_cjforum');
	}
	
	public function publish(&$pks, $value = 1)
	{
		if (parent::publish($pks, $value))
		{
			return $this->updateCounts($pks);
		}
	}
	
	public function delete(&$pks)
	{
		$pks = (array) $pks;
		$db = JFactory::getDbo();
		
		try 
		{
			$query = $db->getQuery(true)->select('distinct(created_by)')->from('#__cjforum_replies')->where('id in ('.implode(',', $pks).')');
			$db->setQuery($query);
			$user_ids = $db->loadColumn();

			$query = $db->getQuery(true)->select('distinct(topic_id)')->from('#__cjforum_replies')->where('id in ('.implode(',', $pks).')');
			$db->setQuery($query);
			$topic_ids = $db->loadColumn();
		}
		catch (Exception $e){}
		
		if(parent::delete($pks))
		{
			try
			{
				// delete tracking information
				$query = $db->getQuery(true)
					->delete('#__cjforum_tracking')
					->where('post_id in ('.implode(',', $pks).') and post_type = 2');
				$db->setQuery($query);
				$db->execute();
			
				if(!empty($user_ids))
				{
					$query = 'update #__cjforum_users as u set replies = (select count(*) from #__cjforum_replies r where r.state = 1 and r.created_by = u.id) where u.id in ('.implode(',', $user_ids).')';
					$db->setQuery($query);
					$db->execute();
				}
				
				if(!empty($topic_ids))
				{
					$query = 'update #__cjforum_topics as a set replies = (select count(*) from #__cjforum_replies r where r.state = 1 and r.topic_id = a.id) where a.id in ('.implode(',', $topic_ids).')';
					$db->setQuery($query);
					$db->execute();
				}
			}
			catch (Exception $e){}
			return true;
		}
		
		return false;
	}
	
	private function updateCounts($pks)
	{
		if(!empty($pks))
		{
			$db = JFactory::getDbo();

			$query = $db->getQuery(true)->select('topic_id')->from('#__cjforum_replies')->where('id in ('.implode(',', $pks).')');
			$db->setQuery($query);
			$topic_ids = $db->loadColumn();
	
			if(!empty($topic_ids))
			{
				$query = 'update #__cjforum_topics as a set replies = (select count(*) from #__cjforum_replies r where r.state = 1 and r.topic_id = a.id) where a.id in ('.implode(',', $topic_ids).')';
				$db->setQuery($query);
				$db->execute();
			}
		}
	
		return true;
	}
	
	public function like($pk, $topicId, $state = 0)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$field = $state ? 'likes' : 'dislikes';
	
		try
		{
			$rating = new stdClass();
			$rating->item_id = $pk;
			$rating->user_id = $user->id;
			$rating->item_type = ITEM_TYPE_REPLY;
			$rating->action_value = $state ? 1 : 2;
			
			$query = $db->getQuery(true)
				->select('count(*)')
				->from('#__cjforum_user_ratings')
				->where('item_id = '.$pk.' and user_id = '.$user->id.' and item_type = '.ITEM_TYPE_REPLY);
				
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
						#__cjforum_replies 
					set 
						likes = 
							(
								select 
									count(*) 
								from 
									#__cjforum_user_ratings 
								where 
									item_id = '.$pk.' and 
									item_type = '.ITEM_TYPE_REPLY.' and 
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
									item_type = '.ITEM_TYPE_REPLY.' and 
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
			$rating->topic_id = $topicId;
			$dispatcher->trigger('onReplyAfterLike', array($this->option . '.' . $this->name, $rating));
			
			return true;
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
			return false;
		}
	
		return false;
	}
	
	public function addOrRemoveThankYou($itemId, $topicId, $state)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		
		if($state == 1)
		{
			$thankyou = new stdClass();
			$thankyou->item_id = $itemId;
			$thankyou->item_type = 2;
			$thankyou->created = JFactory::getDate()->toSql();
			$thankyou->created_by = $user->id;
			
			$query = $db->getQuery(true)
				->select('created_by, topic_id, description')
				->from('#__cjforum_replies')
				->where('id = '.$itemId);
			$db->setQuery($query);
			
			try 
			{
				$result = $db->loadObject();
				$thankyou->assigned_to = (int) $result->created_by;
				
				if($thankyou->assigned_to && $thankyou->assigned_to != $user->id)
				{
					if($db->insertObject('#__cjforum_thankyou', $thankyou))
					{
						$thankyou->topic_id = (int) $result->topic_id;
						$thankyou->description = $result->description;
						
						JPluginHelper::importPlugin('cjforum');
						$dispatcher = JEventDispatcher::getInstance();
						$dispatcher->trigger('onReplyAfterThankyou', array($this->option . '.' . $this->name, $thankyou));
						
						return true;
					}
				}
			}
			catch (Exception $e)
			{
				return false;
			}
		}
		else
		{
			$query = $db->getQuery(true)
				->delete('#__cjforum_thankyou')
				->where('item_id = '.$itemId)
				->where('item_type = 2')
				->where('created_by = '.$user->id);
			
			$db->setQuery($query);
			
			try 
			{
				if($db->execute())
				{
					return true;
				}
			}
			catch (Exception $e)
			{
				return false;
			}
		}
		
		return false;
	}
}