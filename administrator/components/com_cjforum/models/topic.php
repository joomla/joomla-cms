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

class CjForumModelTopic extends JModelAdmin
{
	protected $text_prefix = 'COM_CJFORUM';

	public $typeAlias = 'com_cjforum.topic';
	
	protected $_item = null;
	
	public function __construct($config)
	{
		$config['event_after_delete'] = 'onTopicAfterDelete';
		$config['event_after_save'] = 'onTopicAfterSave';
		$config['event_before_delete'] = 'onTopicBeforeDelete';
		$config['event_before_save'] = 'onTopicBeforeSave';
		$config['event_change_state'] = 'onTopicChangeState';
		$config['event_change_lock'] = 'onTopicChangeLock';
		
		$config['events_map'] = array('delete'=>'cjforum', 'change_state'=>'cjforum');

		parent::__construct($config);
	}

	protected function batchCopy ($value, $pks, $contexts)
	{
		$categoryId = (int) $value;
		
		$i = 0;
		
		if (! parent::checkCategoryId($categoryId))
		{
			return false;
		}
		
		// Parent exists so we let's proceed
		while (! empty($pks))
		{
			// Pop the first ID off the stack
			$pk = array_shift($pks);
			
			$this->table->reset();
			
			// Check that the row actually exists
			if (! $this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);
					
					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}
			
			// Alter the title & alias
			$data = $this->generateNewTitle($categoryId, $this->table->alias, $this->table->title);
			$this->table->title = $data['0'];
			$this->table->alias = $data['1'];
			
			// Reset the ID because we are making a copy
			$this->table->id = 0;
			
			// New category ID
			$this->table->catid = $categoryId;
			
			// TODO: Deal with ordering?
			// $table->ordering = 1;
			
			// Get the featured state
			$featured = $this->table->featured;
			
			// Check the row.
			if (! $this->table->check())
			{
				$this->setError($table->getError());
				return false;
			}
			
			parent::createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);
			
			// Store the row.
			if (! $this->table->store())
			{
				$this->setError($table->getError());
				return false;
			}
			
			// Get the new item ID
			$newId = $this->table->get('id');
			
			// Add the new ID to the array
			$newIds[$i] = $newId;
			$i ++;
			
			// // Check if the topic was featured and update the
			// #__content_frontpage table
			// if ($featured == 1)
			// {
			// $db = $this->getDbo();
			// $query = $db->getQuery(true)
			// ->insert($db->quoteName('#__content_frontpage'))
			// ->values($newId . ', 0');
			// $db->setQuery($query);
			// $db->execute();
			// }
		}
		
		// Clean the cache
		$this->cleanCache();
		
		return $newIds;
	}

	protected function canDelete ($record)
	{
		if (! empty($record->id))
		{
			if ($record->state != - 2)
			{
				return;
			}
			$user = JFactory::getUser();
			return $user->authorise('core.delete', 'com_cjforum.topic.' . (int) $record->id);
		}
	}

	protected function canEditState ($record)
	{
		$user = JFactory::getUser();
		
		// Check for existing topic.
		if (! empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_cjforum.topic.' . (int) $record->id) || 
				(!$user->guest && $user->id == $record->created_by && $user->authorise('core.edit.state.own', 'com_cjforum.topic.' . (int) $record->id));
		}
		// New topic, so check against the category.
		elseif (! empty($record->catid))
		{
			return $user->authorise('core.edit.state', 'com_cjforum.category.' . (int) $record->catid) ||
				(!$user->guest && $user->id == $record->created_by && $user->authorise('core.edit.state.own', 'com_cjforum.category.' . (int) $record->catid));;
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
		
		// Increment the content version number.
		$table->version ++;
		
		// Reorder the topics within the category so the new topic is first
		if (empty($table->id))
		{
			$table->reorder('catid = ' . (int) $table->catid . ' AND state >= 0');
		}
	}

	public function getTable ($type = 'Topic', $prefix = 'CjForumTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getItem ($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Convert the params field to an array.
			$registry = new JRegistry();
			$registry->loadString($item->attribs);
			$item->attribs = $registry->toArray();
			
			// Convert the metadata field to an array.
			$registry = new JRegistry();
			$registry->loadString($item->metadata);
			$item->metadata = $registry->toArray();
			
			// Convert the images field to an array.
			$registry = new JRegistry();
			$registry->loadString($item->images);
			$item->images = $registry->toArray();
			
			// Convert the urls field to an array.
			$registry = new JRegistry();
			$registry->loadString($item->urls);
			$item->urls = $registry->toArray();
			
			$item->topictext = trim($item->fulltext) != '' ? $item->introtext . "<hr id=\"system-readmore\" />" . $item->fulltext : $item->introtext;
			
			if (! empty($item->id))
			{
				$item->tags = new JHelperTags();
				$item->tags->getTagIds($item->id, 'com_cjforum.topic');
			}
		}
		
		// Load associated content items
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();
		
		if ($assoc)
		{
			$item->associations = array();
			
			if ($item->id != null)
			{
				$associations = JLanguageAssociations::getAssociations('com_cjforum', '#__cjforum_topics', 'com_cjforum.item', $item->id);
				
				foreach ($associations as $tag => $association)
				{
					$item->associations[$tag] = $association->id;
				}
			}
		}
		
		return $item;
	}

	public function getForm ($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_cjforum.topic', 'topic', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		$jinput = JFactory::getApplication()->input;
		
		// The front end calls this model and uses t_id to avoid id clashes so
		// we need to check for that first.
		if ($jinput->get('t_id'))
		{
			$id = $jinput->get('t_id', 0);
		}
		// The back end uses id so we use that the rest of the time and set it
		// to 0 by default.
		else
		{
			$id = $jinput->get('id', 0);
		}
		// Determine correct permissions to check.
		if ($this->getState('topic.id'))
		{
			$id = $this->getState('topic.id');
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');
			// Existing record. Can only edit own topics in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit.own');
		}
		else
		{
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}
		
		$user = JFactory::getUser();
		
		// Check for existing topic.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (! $user->authorise('core.edit.state', 'com_cjforum.topic.' . (int) $id)) ||
				 ($id == 0 && ! $user->authorise('core.edit.state', 'com_cjforum')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('featured', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');
			$form->setFieldAttribute('access', 'disabled', 'true');
			
			// Disable fields while saving.
			// The controller has already verified this is an topic you can edit.
			$form->setFieldAttribute('featured', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
			$form->setFieldAttribute('access', 'filter', 'unset');
		}
		
		// Prevent messing with topic language and category when editing
		// existing topic with associations
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();
		
		if ($app->isSite() && $assoc && $this->getState('topic.id'))
		{
			$form->setFieldAttribute('language', 'readonly', 'true');
			$form->setFieldAttribute('catid', 'readonly', 'true');
			$form->setFieldAttribute('language', 'filter', 'unset');
			$form->setFieldAttribute('catid', 'filter', 'unset');
		}
		
		return $form;
	}

	protected function loadFormData ()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_cjforum.edit.topic.data', array());
		
		if (empty($data))
		{
			$data = $this->getItem();
			
			// Prime some default values.
			if ($this->getState('topic.id') == 0)
			{
				$filters = (array) $app->getUserState('com_cjforum.topics.filter');
				$filterCatId = isset($filters['category_id']) ? $filters['category_id'] : null;
				
				$data->set('catid', $app->input->getInt('catid', $filterCatId));
			}
		}
		
		$this->preprocessData('com_cjforum.topic', $data);
		
		return $data;
	}

	public function save ($data)
	{
		$app = JFactory::getApplication();
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		
		if (isset($data['id']) && $data['id'])
		{
			// Existing item
			$data['modified'] = $date->toSql();
			$data['modified_by'] = $user->get('id');
		}
		else
		{
			// New topic. A topic created and created_by field can be set
			// by the user, so we don't touch either of these if they are set.
			if (empty($data['created']))
			{
				// Hack, set replied to current date as well to sort recent topics correctly
				$data['created'] = $data['replied'] = $date->toSql();
			}
				
			if (empty($data['created_by']))
			{
				$data['created_by'] = $user->get('id');
			}
		}
		
		if (isset($data['images']) && is_array($data['images']))
		{
			$registry = new JRegistry();
			$registry->loadArray($data['images']);
			$data['images'] = (string) $registry;
		}
		
		if (isset($data['urls']) && is_array($data['urls']))
		{
			
			foreach ($data['urls'] as $i => $url)
			{
				if ($url != false && ($i == 'urla' || $i == 'urlb' || $i == 'urlc'))
				{
					$data['urls'][$i] = JStringPunycode::urlToPunycode($url);
				}
			}
			$registry = new JRegistry();
			$registry->loadArray($data['urls']);
			$data['urls'] = (string) $registry;
		}
		
		// Alter the title for save as copy
		if ($app->input->get('task') == 'save2copy')
		{
			list ($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
			$data['title'] = $title;
			$data['alias'] = $alias;
			$data['state'] = 0;
		}
		
		if(empty($data['id']))
		{
			$data['state'] = ($user->authorise('core.moderate.topic', 'com_cjforum.category.'.$data['catid'])  && ! $user->authorise('core.admin', 'com_cjforum.category.'.$data['catid'])) ? 0 : 1;
		}
		
		$data['ip_address'] = CjLibUtils::getUserIpAddress();
		JPluginHelper::importPlugin('cjforum');
		
		if (parent::save($data))
		{
			$db = JFactory::getDbo();
			$id = (int) $this->getState($this->getName() . '.id');
			
			if (isset($data['featured']))
			{
				$this->featured($id, $data['featured']);
			}
			
			$assoc = JLanguageAssociations::isEnabled();
			if ($assoc)
			{
				$item = $this->getItem($id);
				
				// Adding self to the association
				$associations = $data['associations'];
				
				foreach ($associations as $tag => $id)
				{
					if (empty($id))
					{
						unset($associations[$tag]);
					}
				}
				
				// Detecting all item menus
				$all_language = $item->language == '*';
				
				if ($all_language && ! empty($associations))
				{
					JError::raiseNotice(403, JText::_('COM_CJFORUM_ERROR_ALL_LANGUAGE_ASSOCIATED'));
				}
				
				$associations[$item->language] = $item->id;
				
				// Deleting old association for these items
				$query = $db->getQuery(true)
					->delete('#__associations')
					->where('context=' . $db->quote('com_cjforum.item'))
					->where('id IN (' . implode(',', $associations) . ')');
				$db->setQuery($query);
				$db->execute();
				
				if ($error = $db->getErrorMsg())
				{
					$this->setError($error);
					return false;
				}
				
				if (! $all_language && count($associations))
				{
					// Adding new association for these items
					$key = md5(json_encode($associations));
					$query->clear()->insert('#__associations');
					
					foreach ($associations as $id)
					{
						$query->values($id . ',' . $db->quote('com_cjforum.item') . ',' . $db->quote($key));
					}
					
					try 
					{
						$db->setQuery($query);
						$db->execute();
					}
					catch (Exception $e)
					{}
					
					if ($error = $db->getErrorMsg())
					{
						$this->setError($error);
						return false;
					}
				}
			}

			// Upload attachments if any.
			CjForumHelper::uploadFiles($id, 1);
			
			$query = $db->getQuery(true)
				->update('#__cjforum_users AS a')
				->set('a.topics = (select count(*) from #__cjforum_topics AS b where b.created_by = a.id)')
				->set('a.last_post_time = '.$db->q($date->toSql()))
				->where('a.id = '.$data['created_by']);
			
			try 
			{
				$db->setQuery($query);
				$db->execute();
			}
			catch (Exception $e){}
			
			if(empty($data['id']))
			{
				// update tracking information
				$tracking = new stdClass();
				$location = CJFunctions::get_user_location($data['ip_address']);
				$browser = CJFunctions::get_browser();
				
				$tracking->post_id = $id;
				$tracking->post_type = 1;
				$tracking->ip_address = $data['ip_address'];
				$tracking->country = $location['country_code'];
				$tracking->city = $location['city'];
				$tracking->os = $browser['platform'];
				$tracking->browser_name = $browser['name'];
				$tracking->browser_version = $browser['version'];
				$tracking->browser_info = $browser['userAgent'];
				
				try 
				{
					$db->insertObject('#__cjforum_tracking', $tracking);
				}
				catch (Exception $e){} 
			}
			
			return true;
		}
		
		return false;
	}

	public function featured ($pks, $value = 0)
	{
		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);
		
		if (empty($pks))
		{
			$this->setError(JText::_('COM_CJFORUM_NO_ITEM_SELECTED'));
			return false;
		}
		
		$table = $this->getTable('Featured', 'CjForumTable');
		
		try
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->update($db->quoteName('#__cjforum_topics'))
				->set('featured = ' . (int) $value)
				->where('id IN (' . implode(',', $pks) . ')');
			$db->setQuery($query);
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		
// 		$table->reorder(); //this is causing performance degradation
		$this->cleanCache();
		
		return true;
	}
	
	protected function getReorderConditions ($table)
	{
		$condition = array();
		$condition[] = 'catid = ' . (int) $table->catid;
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
	
	public function favorite($id, $state)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		
		$created = JFactory::getDate()->toSql();
		$id = (int) $id;
		
		try
		{
			if($state)
			{
				$query = $db->getQuery(true)
					->insert('#__cjforum_favorites')
					->columns('user_id, item_id, item_type, created, state')
					->values($user->id.','.$id.','.ITEM_TYPE_TOPIC.','.$db->q($created).','.$state);
				
				$db->setQuery($query);
				if($db->execute())
				{
					$topic = new stdClass();
					$topic->id = $id;
					
					JPluginHelper::importPlugin('cjforum');
					$dispatcher = JEventDispatcher::getInstance();
					$dispatcher->trigger('onTopicAfterFavored', array($this->option . '.' . $this->name, $topic));
					
					return true;
				}
			}
			else
			{
				$query = $db->getQuery(true)
					->delete('#__cjforum_favorites')
					->where('item_id = '.$id)
					->where('user_id = '.$user->id)
					->where('item_type = '.ITEM_TYPE_TOPIC);
				
				$db->setQuery($query);
				if($db->execute())
				{
					return true;
				}
			}
		}
		catch (Exception $e)
		{
			return false;
		}
		
		return false;
	}
	
	public function delete(&$pks)
	{
		try
		{
			$db = JFactory::getDbo();
			JArrayHelper::toInteger($pks);
			$query = $db->getQuery(true)
				->select('distinct created_by')
				->from('#__cjforum_topics')
				->where('id in ('.implode(',', $pks).')');

			$db->setQuery($query);
			$userIds = $db->loadColumn();
			JPluginHelper::importPlugin('cjforum');
			
			if(parent::delete($pks))
			{
				if(!empty($userIds))
				{
					// update user replies count
					$query = 'update #__cjforum_users AS a set a.topics = (select count(*) from #__cjforum_topics AS b where b.created_by = a.id) where a.id in ('.implode(',', $userIds).')';
					$db->setQuery($query);
					$db->execute();
					
					// delete tracking information
					$query = $db->getQuery(true)
						->delete('#__cjforum_tracking')
						->where('post_id in ('.implode(',', $pks).') and post_type = 1');
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage());
			// nothing
		}
	}
	
	public function lock(&$pks, $value = 1)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$user = JFactory::getUser();
		$table = $this->getTable();
		$pks = (array) $pks;

		// Include the content plugins for the change of state event.
		JPluginHelper::importPlugin('content');

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			$table->reset();

			if ($table->load($pk))
			{
				if (!$this->canEditState($table))
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');

					return false;
				}
			}
		}

		// Attempt to change the state of the records.
		if (!$table->lock($pks, $value, $user->get('id')))
		{
			$this->setError($table->getError());

			return false;
		}

		$context = $this->option . '.' . $this->name;

		// Trigger the onContentChangeState event.
		$result = $dispatcher->trigger($this->event_change_lock, array($context, $pks, $value));

		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
}