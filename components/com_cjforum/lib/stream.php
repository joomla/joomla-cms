<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class CjForumStreamApi 
{
	public function push($stream)
	{
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$activity = new stdClass();
		
		try
		{
			$query = $db->getQuery(true)
				->select('id, access')
				->from('#__cjforum_activity_types')
				->where('activity_name = '.$db->q($stream->type));
			
			$db->setQuery($query);
			$type = $db->loadObject();
			
			if(!empty($type))
			{
				$activity->title 			= JComponentHelper::filterText($stream->title);
				$activity->description 		= JComponentHelper::filterText($stream->description);
				$activity->created_by 		= (int) isset($stream->user_id) ? $stream->user_id : $user->id;
				$activity->created 			= JFactory::getDate()->toSql();
				$activity->published 		= 1;
				$activity->featured 		= (int) isset($stream->featured) ? $stream->featured : 0;
				$activity->language 		= isset($stream->language) ? $stream->language : '*';
				$activity->activity_type 	= $type->id;
				$activity->item_id 			= isset($stream->item_id) ? $stream->item_id : 0;
				$activity->parent_id 		= isset($stream->parent_id) ? $stream->parent_id : 0;
				$activity->access 			= $type->access;
				
				if($stream->length > 0)
				{
					$activity->description 	= JHtml::_('string.truncate', $activity->description, $stream->length);
				} 
				
				if($db->insertObject('#__cjforum_activity', $activity, 'id'))
				{
					return true;
				}
			}
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'));
			return false;
		}
		
		return false;
	}
}