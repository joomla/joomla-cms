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

	public $typeAlias = 'com_cjforum.reply';
	
	protected $_item = null;

	public function __construct($config)
	{
		$config['event_after_delete'] = 'onReplyAfterDelete';
		$config['event_after_save'] = 'onReplyAfterSave';
		$config['event_before_delete'] = 'onReplyBeforeDelete';
		$config['event_before_save'] = 'onReplyBeforeSave';
		$config['event_change_state'] = 'onReplyChangeState';
		
		$config['events_map'] = array('delete'=>'cjforum', 'change_state'=>'cjforum');
	
		parent::__construct($config);
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
			return $user->authorise('core.delete', 'com_cjforum.topic.' . (int) $record->topic_id);
		}
	}

	protected function canEditState ($record)
	{
		$user = JFactory::getUser();
		
		// Check for existing topic.
		if (! empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_cjforum.topic.' . (int) $record->topic_id);
		}
		// Default to component settings if neither reply nor topic known.
		else
		{
			return parent::canEditState('com_cjforum');
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
			$table->reorder('topic_id = ' . (int) $table->topic_id . ' AND state >= 0');
		}
	}

	public function getTable ($type = 'Reply', $prefix = 'CjForumTable', $config = array())
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
			
			// Convert the images field to an array.
			$registry = new JRegistry();
			$registry->loadString($item->images);
			$item->images = $registry->toArray();
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
		if ($jinput->get('r_id'))
		{
			$id = $jinput->get('r_id', 0);
		}
		// The back end uses id so we use that the rest of the time and set it
		// to 0 by default.
		else
		{
			$id = $jinput->get('id', 0);
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
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}
		
		$user = JFactory::getUser();
		
		// Check for existing topic.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (! $user->authorise('core.edit.state', 'com_cjforum.topic.' . (int) $id)) || ($id == 0 && ! $user->authorise('core.edit.state', 'com_cjforum')))
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
		
		if (empty($data))
		{
			$data = $this->getItem();
			$data->set('topic_id', $app->input->getInt('topic_id'));
		}
		
		$this->preprocessData('com_cjforum.reply', $data);
		
		return $data;
	}

	public function save ($data)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$created = JFactory::getDate()->toSql();

		if (isset($data['id']) && $data['id'])
		{
			// Existing item
			$data['modified'] = $created;
			$data['modified_by'] = $user->get('id');
		}
		else
		{
			// New topic. A topic created and created_by field can be set
			// by the user, so we don't touch either of these if they are set.
			if (empty($data['created']))
			{
				// Hack, set replied to current date as well to sort recent topics correctly
				$data['created'] = $created;
			}
		
			if (empty($data['created_by']))
			{
				$data['created_by'] = (int) $user->get('id');
			}
			
			$data['state'] = ($user->authorise('core.moderate.reply', 'com_cjforum.topic.'.$data['topic_id']) && ! $user->authorise('core.admin', 'com_cjforum')) ? 0 : 1;
		}
		
		if (isset($data['images']) && is_array($data['images']))
		{
			$registry = new JRegistry();
			$registry->loadArray($data['images']);
			$data['images'] = (string) $registry;
		}

		$data['ip_address'] = CjLibUtils::getUserIpAddress();
		JPluginHelper::importPlugin('cjforum');
		
		if (parent::save($data))
		{
			$id = $this->getState($this->getName() . '.id');

			// Upload attachments if any.
			CjForumHelper::uploadFiles($id, 2);

			try
			{
				// update replies count for the topic.
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->update('#__cjforum_topics')
					->set('replies = (select count(*) from #__cjforum_replies where topic_id = '.$data['topic_id'].' and state = 1)')
					->set('replied = '.$db->q($created))
					->set('replied_by = '.(int) $data['created_by'])
					->set('last_reply = '.$id)
					->where('id = '.$data['topic_id']);
					
				$db->setQuery($query);
				$db->execute();
					
				if ($error = $db->getErrorMsg())
				{
					$this->setError($error);
					return false;
				}

				if (isset($data['featured']))
				{
					$this->featured($id, $data['featured']);
				}
				
				if(empty($data['id']))
				{
					// update tracking information
					$tracking = new stdClass();
					$location = CJFunctions::get_user_location($data['ip_address']);
					$browser = CJFunctions::get_browser();

					$tracking->post_id = $id;
					$tracking->post_type = 2;
					$tracking->ip_address = $data['ip_address'];
					$tracking->country = $location['country_code'];
					$tracking->city = $location['city'];
					$tracking->os = $browser['platform'];
					$tracking->browser_name = $browser['name'];
					$tracking->browser_version = $browser['version'];
					$tracking->browser_info = $browser['userAgent'];

					$db->insertObject('#__cjforum_tracking', $tracking);
				}

				$query = $db->getQuery(true)
					->update('#__cjforum_users AS a')
					->set('a.replies = (select count(*) from #__cjforum_replies AS b where b.created_by = a.id)')
					->set('a.last_post_time = '.$db->q($created))
					->where('a.id = '.$data['created_by']);
					
				try
				{
					$db->setQuery($query);
					$db->execute();
				}
				catch (Exception $e){}
			}
			catch (Exception $e)
			{
				// return
			}
			
			return true;
		}
		
		return false;
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
		
		JPluginHelper::importPlugin('cjforum');
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
					$query = 'update #__cjforum_users as u set replies = (select count(*) from #__cjforum_replies r where r.created_by = u.id and r.state = 1) where u.id in ('.implode(',', $user_ids).')';
					$db->setQuery($query);
					$db->execute();
				}
				
				if(!empty($topic_ids))
				{
					$query = 'update #__cjforum_topics as a set replies = (select count(*) from #__cjforum_replies r where r.topic_id = a.id and r.state = 1) where a.id in ('.implode(',', $topic_ids).')';
					$db->setQuery($query);
					$db->execute();
				}
			}
			catch (Exception $e){}
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
				->update($db->quoteName('#__cjforum_replies'))
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
		
		$table->reorder();
		
		$this->cleanCache();
		
		return true;
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
			$fieldset = $fields->addChild('fieldset');
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
}