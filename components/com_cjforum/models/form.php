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
require_once JPATH_ADMINISTRATOR . '/components/com_cjforum/models/topic.php';

class CjForumModelForm extends CjForumModelTopic
{
	public $typeAlias = 'com_cjforum.topic';
	
	public function __construct($config)
	{
		parent::__construct($config);
		$this->populateState();
	}

	public function populateState (	)
	{
		$app = JFactory::getApplication();
		
		// Load state from the request.
		$pk = $app->input->getInt('t_id');
		$this->setState('topic.id', $pk);
		
		$this->setState('topic.catid', $app->input->getInt('catid'));
		
		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));
		
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		
		$this->setState('layout', $app->input->getString('layout'));
	}

	public function getItem ($itemId = null)
	{
		$itemId = (int) (! empty($itemId)) ? $itemId : $this->getState('topic.id');
		
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
		$value->params = new JRegistry();
		$value->params->loadString($value->attribs);
		
		// Compute selected asset permissions.
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$asset = 'com_cjforum.topic.' . $value->id;
		
		// Check general edit permission first.
		if ($user->authorise('core.edit', $asset))
		{
			$value->params->set('access-edit', true);
		}
		
		// Now check if edit.own is available.
		elseif (! empty($userId) && $user->authorise('core.edit.own', $asset))
		{
			// Check for a valid user and that they are the owner.
			if ($userId == $value->created_by)
			{
				$value->params->set('access-edit', true);
			}
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
			$catId = (int) $this->getState('topic.catid');
			
			if ($catId)
			{
				$value->params->set('access-change', $user->authorise('core.edit.state', 'com_cjforum.category.' . $catId));
				$value->catid = $catId;
			}
			else
			{
				$value->params->set('access-change', $user->authorise('core.edit.state', 'com_cjforum'));
			}
		}
		
		$value->topictext = $value->introtext;
		
		if (! empty($value->fulltext))
		{
			$value->topictext .= '<hr id="system-readmore" />' . $value->fulltext;
		}
		
		// Convert the metadata field to an array.
		$registry = new JRegistry();
		$registry->loadString($value->metadata);
		$value->metadata = $registry->toArray();
		
		if ($itemId)
		{
			$value->tags = new JHelperTags();
			$value->tags->getTagIds($value->id, 'com_cjforum.topic');
			$value->metadata['tags'] = $value->tags;
			
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.id, a.post_id, a.post_type, a.created_by, a.hash, a.filesize, a.folder, a.filetype, a.filename')
				->from('#__cjforum_attachments AS a')
				->where('a.post_id = '.$itemId.' and a.post_type = 1');
			
			$db->setQuery($query);
			$value->attachments = $db->loadObjectList();
		}
		
		return $value;
	}

	public function getReturnPage ()
	{
		return base64_encode($this->getState('return_page'));
	}

	public function save ($data)
	{
		// Associations are not edited in frontend ATM so we have to inherit
		// them
		if (JLanguageAssociations::isEnabled() && ! empty($data['id']))
		{
			if ($associations = JLanguageAssociations::getAssociations('com_cjforum', '#__cjforum_topics', 'com_cjforum.item', $data['id']))
			{
				foreach ($associations as $tag => $associated)
				{
					$associations[$tag] = (int) $associated->id;
				}
				
				$data['associations'] = $associations;
			}
		}
		
		if(parent::save($data))
		{
			// add subscription
			$app = JFactory::getApplication();
			$user = JFactory::getUser();
			$db = JFactory::getDbo();
				
			$subscribe = $app->input->getInt('subscribe');
			$id = (int) $this->getState($this->getName() . '.id');
				
			if($id && $subscribe && !$user->guest)
			{
				$query = $db->getQuery(true)
					->insert('#__cjforum_subscribes')
					->columns('subscription_type, subscription_id, subscriber_id')
					->values('1, '.$id.','.$user->id);
			
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
	
	public function isAllowedToCreateTopic()
	{
		$user 			= JFactory::getUser();
		$db 			= JFactory::getDbo();
		$params 		= $this->getState('params');
		$newUserDays	= (int) $params->get('new_user_status_days', 0);
		$maxTopics		= (int) $params->get('max_topics_per_day', 0);
		
		if(strtotime($user->registerDate) >= strtotime('-'.$newUserDays.' days'))
		{
			$maxTopics		= (int) $params->get('max_topics_per_new_user', 0);
		}
		
		if($maxTopics > 0)
		{
			$query = $db->getQuery(true)
				->select('count(*)')
				->from('#__cjforum_topics')
				->where('created_by = '.$user->id)
				->where('created > DATE_SUB(NOW(), INTERVAL 1 DAY)');
			
			$db->setQuery($query);
			$count = (int) $db->loadResult();
			
			return $count < $maxTopics;
		}
		
		return true;
	}
}
