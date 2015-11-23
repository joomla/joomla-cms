<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class CjForumPluginCjforum
{
	public function loadActivityDetails(&$activity)
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_cjforum', JPATH_BASE);
		
		switch ($activity->activity_name)
		{
			case 'com_cjforum.new_topic':
				
				try 
				{
					
					
					if(!empty($topic))
					{
						$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
						$item->catslug = $item->category_alias ? ($item->catid . ':' . $item->category_alias) : $item->catid;
						$topicUri = JRoute::_(CjForumHelperRoute::getTopicRoute($topic->slug, $topic->catslug, $topic->language));
						$topicUrl = JHtml::link($topicUri, htmlspecialchars($topic->title, ENT_COMPAT, 'UTF-8'));
						
						$activity->title = JText::sprintf('COM_CJFORUM_ACTIVITY_NEW_TOPIC', $activity->profileLink, $topicUrl);
						$activity->description = $topic->introtext;
					}
				}
				catch (Exception $e)
				{
					return;
				}
				
				break;
				
			case 'com_cjforum.topic_reply':
				break;
				
			case 'com_cjforum.topic_like':
				break;
				
			case 'com_cjforum.topic_dislike':
				break;
				
			case 'com_cjforum.follow_user':
				break;
		}
	}
	
	private function getTopicDetails($id)
	{
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true)
			->select('a.id, a.title, a.alias, a.introtext, a.catid, c.alias as category_alias')
			->from('#__cjforum_topics AS a')
			->join('left', '#__categories AS c on c.id = a.catid')
			->where('id = '.((int) $id));
		
		$db->setQuery($query);
		$topic = $db->loadObject();
	}
}