<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

require_once JPATH_ROOT.'/components/com_cjlib/framework/api.php';
require_once JPATH_ROOT.'/components/com_cjforum/router.php';
require_once JPATH_ROOT.'/components/com_cjforum/helpers/route.php';

class PlgCjForumTopics extends JPlugin
{
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
		$this->loadLanguage('com_cjforum', JPATH_ROOT);
	}
	
	public function onProfilePrepareForm($form, $data) 
	{
		JForm::addFormPath(__DIR__ . '/forms');
		$form->loadFile('profile', false);
	}
	
	public function onTopicBeforeSave($context, $topic, $isNew)
	{
		return true;
	}

	public function onReplyBeforeSave($context, $reply, $isNew)
	{
		return true;
	}

	public function onTopicAfterDelete($context, $data)
	{
		return true;
	}
	
	public function onTopicBeforeDelete($context, $topic)
	{
		if ($context != 'com_cjforum.form' && $context != 'com_cjforum.topic')
		{
			return true;
		}
		
		$myparams = $this->params;
		if($myparams->get('points_deleted_topic', true))
		{
			$this->awardPoints($topic, 10);
		}
	}

	public function onReplyBeforeDelete($context, $data)
	{
		return true;
	}
	
	public function onReplyAfterDelete($context, $topic)
	{
		if ($context != 'com_cjforum.replyform' && $context != 'com_cjforum.reply')
		{
			return true;
		}
		
		$myparams = $this->params;
		if($myparams->get('points_deleted_reply', true))
		{
			$this->awardPoints($topic, 11);
		}
	}
	
	public function onTopicChangeState($context, $pks, $value)
	{
		if ($context != 'com_cjforum.form' && $context != 'com_cjforum.topic')
		{
			return true;
		}
		
		if($value == 0 || $value == 1 || $value == -2)
		{
			foreach ($pks as $pk)
			{
				$topic = new stdClass();
				$topic->id = $pk;
				
				if($value == 1)
				{
					$this->awardPoints($topic, 1);
				}
				else if($value == -2 || $value == 0)
				{
					$this->awardPoints($topic, 10);
				}
			}
		}
		
		return true;
	}

	public function onReplyChangeState($context, $pks, $value)
	{
		if ($context != 'com_cjforum.replyform' && $context != 'com_cjforum.reply')
		{
			return true;
		}
		
		if($value == 0 || $value == 1 || $value == -2)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('id, topic_id, description, created_by')
				->from('#__cjforum_replies')
				->where('id in ('.implode(',', $pks).')');
			
			try 
			{
				$db->setQuery($query);
				$replies = $db->loadObjectList();
				
				foreach ($replies as $reply)
				{
					if($value == 1)
					{
						$this->awardPoints($reply, 2);
					}
					else if($value == -2 || $value == 0)
					{
						$this->awardPoints($reply, 11);
					}
				}
			}
			catch (Exception $e){}
		}
		
		return true;
	}
	
	public function onTopicAfterSave($context, $topic, $isNew)
	{
		if ($context != 'com_cjforum.form' || ! $isNew || $topic->state != 1)
		{
			return true;
		}
		
		$myparams = $this->params;
		
		if($myparams->get('points_new_topic', true))
		{
			$this->awardPoints($topic, 1);
		}
		
		if($myparams->get('activity_new_topic', true))
		{
			$this->streamActivity($topic, 1);
		}
		
		if($myparams->get('email_new_topic', true))
		{
			$this->sendEmail($topic, 1);
		}
		
		$this->syncPosts(1);
		$this->assignRank();
	}

	public function onReplyAfterSave($context, $reply, $isNew)
	{
		if ($context != 'com_cjforum.replyform' || ! $isNew || $reply->state != 1)
		{
			return true;
		}

		$myparams = $this->params;
		
		if($myparams->get('points_new_reply', true))
		{
			$this->awardPoints($reply, 2);
		}
		
		if($myparams->get('activity_new_reply', true))
		{
			$this->streamActivity($reply, 2);
		}
		
		if($myparams->get('email_new_reply', true))
		{
			$this->sendEmail($reply, 2);
		}

		$this->syncPosts(2);
		$this->assignRank();
	}
	
	public function onTopicAfterLike($context, $rating)
	{
		if ($context != 'com_cjforum.topic' || empty($rating))
		{
			return true;
		}
		
		$type = $rating->action_value == 2 ? 4 : 3;
		$myparams = $this->params;
		
		if(
				($type == 3 && $myparams->get('points_liked_topic', true)) ||
				($type == 4 && $myparams->get('points_disliked_topic', true))
		)
		{
			$this->awardPoints($rating, $type);
		}
		
		if(
				($type == 3 && $myparams->get('activity_liked_topic', true)) ||
				($type == 4 && $myparams->get('activity_disliked_topic', true))
		)
		{
			$this->streamActivity($rating, $type);
		}
		
		if(
				($type == 3 && $myparams->get('email_liked_topic', true)) ||
				($type == 4 && $myparams->get('email_disliked_topic', true))
		)
		{
			$this->sendEmail($rating, $type);
		}
	}

	public function onReplyAfterLike($context, $rating)
	{
		if ($context != 'com_cjforum.reply' || empty($rating))
		{
			return true;
		}
	
		$type = $rating->action_value == 2 ? 6 : 5;
		$myparams = $this->params;
		
		if(
				($type == 5 && $myparams->get('points_liked_reply', true)) ||
				($type == 6 && $myparams->get('points_disliked_reply', true))
		)
		{
			$this->awardPoints($rating, $type);
		}
		
		if(
				($type == 5 && $myparams->get('activity_liked_reply', true)) ||
				($type == 6 && $myparams->get('activity_disliked_reply', true))
		)
		{
			$this->streamActivity($rating, $type);
		}
			
		if(
				($type == 5 && $myparams->get('email_liked_reply', true)) ||
				($type == 6 && $myparams->get('email_disliked_reply', true))
		)
		{
			$this->sendEmail($rating, $type);
		}
	}

	public function onReplyAfterThankyou($context, $thankyou)
	{
		if ($context != 'com_cjforum.reply' || empty($thankyou))
		{
			return true;
		}
		
		if($this->params->get('points_author_thankyou_reply', true))
		{
			$this->awardPoints($thankyou, 7);
		}
		
		if($this->params->get('points_thankyou_reply', true))
		{
			$this->awardPoints($thankyou, 8);
		}
		
		if($this->params->get('activity_thankyou_reply', true))
		{
			$this->streamActivity($thankyou, 8);
		}
			
		if($this->params->get('email_thankyou_reply', true))
		{
			$this->streamActivity($thankyou, 7);
		}
	}
	
	public function onTopicAfterFavored($context, $topic)
	{
		if ($context != 'com_cjforum.form' || empty($topic))
		{
			return true;
		}
		
		if($this->params->get('points_favored_topic', true))
		{
			$this->awardPoints($topic, 9);
		}
		
		if($this->params->get('activity_favored_topic', true))
		{
			$this->streamActivity($topic, 9);
		}
	}
	
	private function syncPosts($postType)
	{
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		
		if($user->guest)
		{
			return;
		}
		
		try 
		{
			switch ($postType)
			{
				case 1: // topic
					$query = 'update #__cjforum_users set topics = (select count(*) from #__cjforum_topics where created_by = '.$user->id.' and state = 1) where id = '.$user->id;
					$db->setQuery($query);
					$db->execute();
					break;
					
				case 2: // replies
					$query = 'update #__cjforum_users set replies = (select count(*) from #__cjforum_replies where created_by = '.$user->id.' and state = 1) where id = '.$user->id;
					$db->setQuery($query);
					$db->execute();
					break;
						
			}
		}
		catch(Exception $e)
		{
			// do nothing
		}
	}
	
	private function assignRank()
	{
		// now assign the rank if any, skip if the user has a special rank
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		
		if($user->guest)
		{
			return;
		}
		
		try 
		{
			$api = CjForumApi::getProfileApi();
			$profile = $api->getUserProfile($user->id, true);

			if($profile['rank_type'] == 1)
			{
				// special rank assigned, no need to touch this
				return;
			}
			
			// get the rank of the next level if any
			$query = $db->getQuery(true)
				->select('id')
				->from('#__cjforum_ranks')
				->where('rank_type = 0')
				->where('min_posts <= (select topics + replies from #__cjforum_users where id = '.$user->id.')')
				->order('min_posts desc');
			
			$db->setQuery($query, 0, 1);
			$rankId = (int) $db->loadResult();
			
			if($rankId != $profile['rank_id'])
			{
				$query = $db->getQuery(true)
					->update('#__cjforum_users')
					->set('rank = '.$rankId)
					->where('id = '.$user->id);
				
				$db->setQuery($query);
				$db->execute();
			}
		}
		catch(Exception $e)
		{
			// nothing to do
		}
	}
	
	private function streamActivity($item, $type)
	{
		$params 		= JComponentHelper::getParams('com_cjforum');
		$streamApp 		= $params->get('stream_component', 'cjforum');
		$displayName	= $params->get('display_name', 'name');
		
		// Activity stream
		if(empty($streamApp) || $streamApp == 'none')
		{
			return true;
		}
		
		$db = JFactory::getDbo();
		$topic = null;
		$topicId = 0;
		
		switch ($type)
		{
			case 1: // new topic
				$topicId = $item->id;
				break;
				
			case 2: // topic reply
				$topicId = $item->topic_id;
				break;
				
			case 3: // like topic
				$topicId = $item->item_id;
				break;
				
			case 4: // dislike topic
				$topicId = $item->item_id;
				break;
				
			case 5: // like reply
				$topicId = $item->topic_id;
				break;
				
			case 6: // dislike reply
				$topicId = $item->topic_id;
				break;

			case 8: // thank you for a reply
				$topicId = $item->topic_id;
				break;

			case 9: // favored topic
				$topicId = $item->id;
				break;
		}
		
		$query = $db->getQuery(true)
			->select('a.id, a.title, a.alias, a.catid, a.introtext')
			->select('c.alias as category_alias')
			->select('u.'.$displayName.' AS author')
			->from('#__cjforum_topics AS a')
			->join('left', '#__categories AS c ON a.catid = c.id')
			->join('left', '#__users AS u on a.created_by = u.id')
			->where('a.id = '.$topicId);
		$db->setQuery($query);
		
		try 
		{
			$topic = $db->loadObject();
		}
		catch (Exception $e)
		{
			return false;
		}
		
		if($topic)
		{
			$user = JFactory::getUser();
			$language = JFactory::getLanguage();
			$language->load('com_cjforum');
			$api = new CjLibApi();
				
			$topic->slug = $topic->alias ? ($topic->id . ':' . $topic->alias) : $topic->id;
			$topic->catslug = !empty($topic->category_alias) ? ($topic->catid . ':' . $topic->category_alias) : $topic->catid;
			
			$profileComponent = $params->get('profile_component', 'cjforum');
			$userName = $api->getUserProfileUrl($profileComponent, $user->id, false, $user->$displayName);
			$topicUrl = CjForumHelperRoute::getTopicRoute($topic->slug, $topic->catslug);
			$topicLink = JHtml::link($topicUrl, JComponentHelper::filterText($topic->title));
			$parentId = 0;
			$itemId = $topicId;
			
			switch ($type)
			{
				case 1: // new topic
					$title = JText::sprintf('COM_CJFORUM_ACTIVITY_NEW_TOPIC', $userName, $topicLink);
					$description = $topic->introtext;
					$function = 'com_cjforum.new_topic';
					break;
			
				case 2: // topic reply
					$title = JText::sprintf('COM_CJFORUM_ACTIVITY_TOPIC_REPLY', $userName, $topicLink);
					$description = $item->description;
					$function = 'com_cjforum.topic_reply';
					$parentId = $topicId;
					$itemId = $item->id;
					break;
			
				case 3: // like topic
					$title = JText::sprintf('COM_CJFORUM_ACTIVITY_USER_LIKED_TOPIC', $userName, $topicLink);
					$description = $topic->introtext;
					$function = 'com_cjforum.liked_topic';
					break;
			
				case 4: // dislike topic
					$title = JText::sprintf('COM_CJFORUM_ACTIVITY_USER_DISLIKED_TOPIC', $userName, $topicLink);
					$description = $topic->introtext;
					$function = 'com_cjforum.disliked_topic';
					break;
			
				case 5: // like reply
					$title = JText::sprintf('COM_CJFORUM_ACTIVITY_USER_LIKED_REPLY', $userName, $topicLink);
					$description = ''; //$item->description;
					$function = 'com_cjforum.liked_reply';
					$parentId = $topicId;
					$itemId = $item->item_id;
					break;

				case 6: // dislike reply
					$title = JText::sprintf('COM_CJFORUM_ACTIVITY_USER_DISLIKED_REPLY', $userName, $topicLink);
					$description = ''; //$item->description;
					$function = 'com_cjforum.disliked_reply';
					$parentId = $topicId;
					$itemId = $item->item_id;
					break;

				case 8: // thank you to a reply
					$assignedTo = $api->getUserProfileUrl($profileComponent, $item->assigned_to, false, JFactory::getUser($item->assigned_to)->name);
					$title = JText::sprintf('COM_CJFORUM_ACTIVITY_USER_THANKYOU_REPLY', $userName, $assignedTo, $topicLink);
					$description = $item->description;
					$function = 'com_cjforum.thankyou_reply';
					$parentId = $topicId;
					$itemId = $item->item_id;
					break;

				case 9: // favored topic
					$title = JText::sprintf('COM_CJFORUM_ACTIVITY_USER_FAVORED_TOPIC', $userName, $topicLink);
					$description = $topic->introtext;
					$function = 'com_cjforum.favored_topic';
					break;
			}
				
			$activity = new stdClass();
			$activity->type = $function;
			$activity->href = $topicUrl;
			$activity->title = $title;
			$activity->description = $description;
			$activity->userId = $user->id;
			$activity->featured = 0;
			$activity->language = $language->getTag();
			$activity->itemId = $itemId;
			$activity->parentId = $parentId;
			$activity->length = $params->get('readmore_limit', 0);

			$api->pushActivity($streamApp, $activity);
		}
		
		return true;
	}
	
	private function awardPoints($item, $type)
	{
		$user 			= JFactory::getUser();
		$api 			= new CjLibApi();
		$params 		= JComponentHelper::getParams('com_cjforum');
		$displayName	= $params->get('display_name', 'name');
		
		$db = JFactory::getDbo();
		$topic = null;
		$topicId = 0;
		
		switch ($type)
		{
			case 1: // new topic
				$topicId = $item->id;
				break;
		
			case 2: // topic reply
				$topicId = $item->topic_id;
				break;
		
			case 3: // like topic
				$topicId = $item->item_id;
				break;
		
			case 4: // dislike topic
				$topicId = $item->item_id;
				break;
		
			case 5: // like reply
				$topicId = $item->topic_id;
				break;

			case 6: // dislike reply
				$topicId = $item->topic_id;
				break;

			case 7: // get thank you for a reply
				$topicId = $item->topic_id;
				break;

			case 8: // said thank you to a reply
				$topicId = $item->topic_id;
				break;

			case 9: // favored topic
				$topicId = $item->id;
				break;
				
			case 10: // delete topic
				$topicId = $item->id;
				break;
				
			case 11: // delete reply
				$topicId = $item->topic_id;
		}
		
		$query = $db->getQuery(true)
			->select('a.id, a.title, a.alias, a.catid, a.introtext, a.language, a.created_by')
			->select('c.alias as category_alias')
			->select('u.'.$displayName.' AS author')
			->from('#__cjforum_topics AS a')
			->join('left', '#__categories AS c ON a.catid = c.id')
			->join('left', '#__users AS u on a.created_by = u.id')
			->where('a.id = '.$topicId);
		$db->setQuery($query);
		
		try
		{
			$topic = $db->loadObject();
		}
		catch (Exception $e)
		{
			return false;
		}
		
		if($topic)
		{
			$pointsComponent = $params->get('points_component', 'cjforum');
			$profileComponent = $params->get('profile_component', 'cjforum');
			
			$topic->slug = $topic->alias ? ($topic->id . ':' . $topic->alias) : $topic->id;
			$topic->catslug = $topic->category_alias ? ($topic->catid . ':' . $topic->category_alias) : $topic->catid;
			$topicLink = JHtml::link(JRoute::_(CjForumHelperRoute::getTopicRoute($topic->slug, $topic->catslug, $topic->language), false), JComponentHelper::filterText($topic->title));
			$info = '';
			$reference = $topicId;
			$awardedTo = $user->id;
			
			switch ($type)
			{
				case 1: // new topic
					$function = 'com_cjforum.new_topic';
					$title = JText::sprintf('COM_CJFORUM_POINTS_NEW_TOPIC', $topicLink);
					$info = $topic->introtext; 
					break;
			
				case 2: // topic reply
					$function = 'com_cjforum.topic_reply';
					$title = JText::sprintf('COM_CJFORUM_POINTS_TOPIC_REPLY', $topicLink);
					$info = $item->description;
					$reference = $item->id;
					break;
			
				case 3: // like topic
					$function = 'com_cjforum.liked_topic';
					$title = JText::sprintf('COM_CJFORUM_POINTS_LIKED_TOPIC', $topicLink);
					$info = $topic->introtext;
					break;
			
				case 4: // dislike topic
					$function = 'com_cjforum.disliked_topic';
					$title = JText::sprintf('COM_CJFORUM_POINTS_DISLIKED_TOPIC', $topicLink);
					$info = $topic->introtext;
					break;
			
				case 5: // like reply
					$function = 'com_cjforum.liked_reply';
					$title = JText::sprintf('COM_CJFORUM_POINTS_LIKED_REPLY', $topicLink);
					$info = $topic->introtext;
					$reference = $item->item_id;
					break;

				case 6: // dislike reply
					$function = 'com_cjforum.disliked_reply';
					$title = JText::sprintf('COM_CJFORUM_POINTS_DISLIKED_REPLY', $topicLink);
					$info = $topic->introtext;
					$reference = $item->item_id;
					break;

				case 7: // get thank you to a reply
					$function = 'com_cjforum.thankyou_reply_author';
					$profileLink = $api->getUserProfileUrl($profileComponent, $user->id, false, $user->$displayName);
					$title = JText::sprintf('COM_CJFORUM_POINTS_THANKYOU_REPLY_AUTHOR', $profileLink, $topicLink);
					$info = $item->description;
					$reference = $item->item_id;
					$awardedTo = $item->assigned_to;
					break;

				case 8: // said thank you to a reply
					$function = 'com_cjforum.thankyou_reply';
					$assignedTo = $api->getUserProfileUrl($profileComponent, $item->assigned_to, false, JFactory::getUser($item->assigned_to)->$displayName);
					$title = JText::sprintf('COM_CJFORUM_POINTS_THANKYOU_REPLY', $assignedTo, $topicLink);
					$info = $item->description;
					$reference = $item->item_id;
					break;

				case 9: // favored topic
					$function = 'com_cjforum.favored_topic';
					$title = JText::sprintf('COM_CJFORUM_POINTS_FAVORED_TOPIC', $topicLink);
					$info = $topic->introtext;
					break;
					
				case 10: // delete topic
					$function = 'com_cjforum.deleted_topic';
					$title = JText::sprintf('COM_CJFORUM_POINTS_DELETED_TOPIC', JComponentHelper::filterText($topic->title));
					$info = $topic->introtext;
					$awardedTo = $topic->created_by;
					$reference = $topic->id;
					break;
					
				case 11: // delete reply
					$function = 'com_cjforum.deleted_reply';
					$title = JText::sprintf('COM_CJFORUM_POINTS_DELETED_REPLY', $topicLink);
					$info = $item->description;
					$awardedTo = $item->created_by;
					$reference = $item->id;
					break;
			}
			
			$options = array('function'=>$function, 'reference'=>$reference, 'info'=>$info, 'component'=>'com_cjforum', 'title'=>$title);
			$api->awardPoints($pointsComponent, $awardedTo, $options);
		}
		
		return true;
	}
	
	private function sendEmail($item, $type)
	{
		$db 			= JFactory::getDbo();
		$params 		= JComponentHelper::getParams('com_cjforum');
		$displayName	= $params->get('display_name', 'name');
		$topic 			= null;
		$topicId 		= 0;
		$emailType 		= null;
	
		switch ($type)
		{
			case 1: // new topic
				$emailType = 'com_cjforum.new_topic';
				$topicId = $item->id;
				break;

			case 2: // new reply
				$emailType = 'com_cjforum.new_reply';
				$topicId = $item->topic_id;
				break;

			case 3: // like topic
				$emailType = 'com_cjforum.like_topic';
				$topicId = $item->item_id;
				break;
					
			case 4: // dislike topic
				$emailType = 'com_cjforum.dislike_topic';
				$topicId = $item->item_id;
				break;
	
			case 5: // like reply
				$emailType = 'com_cjforum.like_reply';
				$topicId = $item->topic_id;
				break;
	
			case 6: // dislike reply
				$emailType = 'com_cjforum.dislike_reply';
				$topicId = $item->topic_id;
				break;

			case 7: // thank you
				$emailType = 'com_cjforum.thank_you';
				$topicId = $item->topic_id;
				break;
		}
		
		$query = $db->getQuery(true)
			->select('a.id, a.title, a.alias, a.catid, a.introtext, a.language, a.created_by')
			->select('c.alias as category_alias, c.title as category_title')
			->select('u.'.$displayName.' AS author, u.email as author_email')
			->from('#__cjforum_topics AS a')
			->join('left', '#__categories AS c ON a.catid = c.id')
			->join('left', '#__users AS u on a.created_by = u.id')
			->where('a.id = '.$topicId);
		
		$db->setQuery($query);
	
		try
		{
			$topic = $db->loadObject();
		}
		catch (Exception $e)
		{
			return false;
		}
		
		if($topic)
		{
			$template = null;
			$tag = JFactory::getLanguage()->getTag();
				
			$query = $db->getQuery(true)
				->select('title, description, language')
				->from('#__cjforum_email_templates')
				->where('email_type = '.$db->q($emailType))
				->where('language in ('.$db->q($tag).','.$db->q('*').')')
				->where('published = 1');
				
			$db->setQuery($query);
			$templates = $db->loadObjectList('language');

			if(isset($templates[$tag]))
			{
				$template = $templates[$tag];
			}
			else if(isset($templates['*']))
			{
				$template = $templates['*'];
			}
			
			if(!empty($template))
			{
				JLoader::import('mail', JPATH_ROOT.'/components/com_cjforum/models');
	
				$user				= JFactory::getUser();
				$config 			= JFactory::getConfig();
				$sitename 			= $config->get('sitename');
				$message 			= new stdClass();
				$mailModel			= JModelLegacy::getInstance( 'mail', 'CjForumModel' );
	
				$topic->slug 		= $topic->alias ? ($topic->id . ':' . $topic->alias) : $topic->id;
				$topic->catslug 	= !empty($topic->category_alias) ? ($topic->catid . ':' . $topic->category_alias) : $topic->catid;
				$topicUrl 			= JRoute::_(CjForumHelperRoute::getTopicRoute($topic->slug, $topic->catslug), false, -1);
	
				$recipients			= array();
				$subject			= str_ireplace('{TOPIC_TITLE}', $topic->title, $template->title);
				$description 		= str_ireplace('{SITENAME}', $sitename, $template->description);
				$description 		= str_ireplace('{TOPIC_TITLE}', $topic->title, $description);
				$description 		= str_ireplace('{TOPIC_URL}', $topicUrl, $description);
				$description 		= str_ireplace('{CATEGORY}', $topic->category_title, $description);
				$description		= str_ireplace('{AUTHOR_NAME}', $user->$displayName, $description);
	
				switch ($type)
				{
					case 1: // new question, mails should go to all subscribers of category & global
						$query = $db->getQuery(true)
							->select('a.subscriber_id AS id, u.'.$displayName.' AS name, u.email AS email')
							->from('#__cjforum_subscribes AS a')
							->join('INNER', '#__users AS u ON a.subscriber_id = u.id')
							->where('(a.subscription_type = 2 and subscription_id = '.$topic->catid.') OR (a.subscription_type = 3)');
	
						try
						{
							$db->setQuery($query);
							$recipients = $db->loadObjectList();

							$message->asset_id = $topic->id;
						}
						catch (Exception $e)
						{
							return false;
						}
						break;
	
					case 2: // new reply, mails should go to all subscribers of topic, category & global
						$query = $db->getQuery(true)
							->select('distinct a.subscriber_id AS id, u.'.$displayName.' AS name, u.email AS email')
							->from('#__cjforum_subscribes AS a')
							->join('INNER', '#__users AS u ON a.subscriber_id = u.id')
							->where('((a.subscription_type = 1 and subscription_id = '.$topic->id.') OR (a.subscription_type = 2 and subscription_id = '.$topic->catid.') OR (a.subscription_type = 3))')
							->where('a.subscriber_id not in ('.$user->id.')');
						
						try
						{
							$db->setQuery($query);
							$recipients = $db->loadObjectList();
								
							$message->asset_id = $topic->id;
						}
						catch (Exception $e)
						{
							return false;
						}
						break;
	
					case 3: // like topic, should be sent to the author only
						$asker = new stdClass();
						$asker->id = $topic->created_by;
						$asker->name = $topic->author;
						$asker->email = $topic->author_email;
						$recipients[] = $asker;
	
						$message->asset_id = $topic->id;
						break;
	
					case 4: // dislike topic, should be sent to the author only
						$asker = new stdClass();
						$asker->id = $topic->created_by;
						$asker->name = $topic->author;
						$asker->email = $topic->author_email;
						$recipients[] = $asker;
	
						$message->asset_id = $topic->id;
						break;

					case 5: // like reply, should be sent to the answerer only
						$message->asset_id = $item->item_id;
						try
						{
							$query = $db->getQuery(true)
								->select('id, name, email')
								->from('#__users AS u')
								->where('id = (select created_by from #__cjforum_replies where id = '.$item->item_id.')');
								
							$db->setQuery($query);
							$answerer = $db->loadObject();
								
							if(!empty($answerer))
							{
								$recipients[] = $answerer;
							}
						}
						catch (Exception $e)
						{
							return false;
						}
						break;
	
					case 6: // dislike reply, should be sent to the answerer only
						$message->asset_id = $item->item_id;
						try
						{
							$query = $db->getQuery(true)
							->select('id, name, email')
							->from('#__users AS u')
							->where('id = (select created_by from #__answers_replies where id = '.$item->item_id.')');
								
							$db->setQuery($query);
							$answerer = $db->loadObject();
								
							if(!empty($answerer))
							{
								$recipients[] = $answerer;
							}
						}
						catch (Exception $e)
						{
							return false;
						}
						break;

					case 7: // thank you, should be sent to the answerer only
						$message->asset_id = $item->item_id;
						try
						{
							$query = $db->getQuery(true)
							->select('id, name, email')
							->from('#__users AS u')
							->where('id = (select created_by from #__cjforum_replies where id = '.$item->item_id.')');
								
							$db->setQuery($query);
							$answerer = $db->loadObject();
								
							if(!empty($answerer))
							{
								$recipients[] = $answerer;
							}
						}
						catch (Exception $e)
						{
							return false;
						}
						break;
				}
				
				if(!empty($recipients) && !empty($message))
				{
					$message->asset_name = $emailType;
					$message->subject = $subject;
					$message->description = $description;
					$mailModel->enqueueMail($message, $recipients, 'none');
				}
			}
		}
	
		return true;
	}
}