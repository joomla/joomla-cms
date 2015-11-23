<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR . '/components/com_cjforum/models/reply.php';

class CjForumModelReplyForm extends CjForumModelReply
{
	protected $text_prefix = 'COM_CJFORUM';
	public $typeAlias = 'com_cjforum.reply';
	protected $_item = null;
	
	public function __construct($config)
	{
		parent::__construct($config);
		$this->populateState();
	}
	
	protected function populateState ()
	{
		$app = JFactory::getApplication();
	
		// Load state from the request.
		$pk = $app->input->getInt('r_id');
		$topicid = $app->input->getInt('t_id');
		$catid = $app->input->getInt('catid');
		$quote = $app->input->getInt('quote');
		
		$this->setState('replyform.id', $pk);
		$this->setState('replyform.topic_id', $topicid);
		$this->setState('replyform.catid', $catid);
		$this->setState('replyform.quote', $quote);
		
		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));
	
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
	
		$this->setState('layout', $app->input->getString('layout'));
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
		// Default to component settings if neither topic nor category known.
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
		
		// Reorder the topics within the topic so the new reply is first
		if (empty($table->id))
		{
			$table->reorder('topic_id = ' . (int) $table->topic_id . ' AND state >= 0');
		}
	}

	public function getTable ($type = 'Reply', $prefix = 'CjForumTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getItem($itemId = null)
	{
		$itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('replyform.id');
	
		// Get a row instance.
		$table = $this->getTable();
	
		// Attempt to load the row.
		$return = $table->load($itemId);
	
		// Check for a table object error.
		if ($return === false && $table->getError())
		{
			$this->setError($table->getError());
	
			return false;
		}
	
		$properties = $table->getProperties(1);
		$value = JArrayHelper::toObject($properties, 'JObject');

		// Convert attrib field to Registry.
		$value->params = new JRegistry;
		$value->params->loadString($value->attribs);
	
		// Compute selected asset permissions.
		$user	= JFactory::getUser();
		$userId	= $user->get('id');
		$asset	= 'com_cjform.topic.' . $value->topic_id;
		$value->catid = (int) $this->getState('replyform.catid');
	
		// Check general edit permission first.
		if ($user->authorise('core.edit', $asset))
		{
			$value->params->set('access-edit', true);
		}
		// Now check if edit.own is available.
		elseif (!empty($userId) && ($userId == $value->created_by) && $user->authorise('core.edit.own', 'com_cjforum.category.'.$value->catid))
		{
			$value->params->set('access-edit', true);
		}
		
		if(empty($value->topic_id))
		{
			$value->topic_id = (int) $this->getState('replyform.topic_id');
		}
		
		// Check edit state permission.
		if ($itemId)
		{
			// Existing item
			$value->params->set('access-change', $user->authorise('core.edit.state', $asset));
		}
		else
		{
			// New item.
			if ($value->catid)
			{
				$value->params->set('access-change', $user->authorise('core.edit.state', 'com_cjform.category.' . $value->catid));
				
			}
			else
			{
				$value->params->set('access-change', $user->authorise('core.edit.state', 'com_cjform'));
			}
		}
		
		return $value;
	}
	
	public function getForm ($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_cjforum.reply', 'reply', array('control' => 'jform', 'load_data' => $loadData));
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
		// The back end uses id so we use that the rest of the time and set it
		// to 0 by default.
		else
		{
			$id = $jinput->get('id', 0);
		}
		// Determine correct permissions to check.
		if ($this->getState('replyform.id'))
		{
			$id = $this->getState('replyform.id');
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('topic', 'action', 'core.edit');
			// Existing record. Can only edit own topics in selected categories.
			$form->setFieldAttribute('topic', 'action', 'core.edit.own');
		}
		else
		{
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('topic', 'action', 'core.create');
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
			$form->setFieldAttribute('language', 'filter', 'unset');
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
			$quote = (int) $this->getState('replyform.quote');
			if ($quote)
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('a.description, u.name AS author')
					->from('#__cjforum_replies AS a')
					->join('inner', '#__users AS u on u.id = a.created_by')
					->where('a.id = '.$quote);
				
				try 
				{
					$db->setQuery($query);
					$reply = $db->loadObject();
					$quotation = JText::sprintf('COM_CJFORUM_REPLY_QUOTATION_TEXT', CjLibUtils::escape($reply->author));
					$data->set('description', '<blockquote cite="'.$quote.'"><cite>'.$quotation.'</cite>'.$reply->description.'</blockquote><p></p>');
				}
				catch (Exception $e)
				{
					$data->set('description', '');
				}
			}
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
		
		if(parent::save($data))
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
			$this->setError(JText::_('COM_CONTENT_NO_ITEM_SELECTED'));
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
		
		$table->reorder();
		
		$this->cleanCache();
		
		return true;
	}

	protected function getReorderConditions ($table)
	{
		$condition = array();
		$condition[] = 'catid = ' . (int) $table->catid;
		return $condition;
	}

	public function getReturnPage ()
	{
		return base64_encode($this->getState('return_page'));
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
}