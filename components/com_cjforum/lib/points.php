<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class CjForumPointsApi
{
	private $_enable_logging = false;
	private $_errors = array();
	
	public function __construct ($config = array())
	{
		if(isset($config['logging']))
		{
			$this->_enable_logging = true;
		}
		
		JFactory::getLanguage()->load('com_cjforum', JPATH_ROOT);
	}
	
	public function awardPoints($ruleId, $userId = 0, $points = 0, $reference = null, $title = null, $description = null)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$points = intval($points);
	
		if($this->_enable_logging)
		{
			JLog::add('CjForumApi.award_points - Rule: '.$ruleId.'| UserID: '.$userId, JLog::DEBUG, 'com_cjforum');
		}
	
		if(strlen($ruleId) < 3) return false;
		if(!$userId && $user->guest) return false;
	
		$userId = $userId > 0 ? $userId : $user->id;
	
		$query = $db->getQuery(true)
			->select('id, title, description, rule_name, app_name, points, published, auto_approve, access')
			->from('#__cjforum_points_rules')
			->where('rule_name='.$db->q($ruleId));
	
		if($db->getErrorNum())
		{
			JLog::add('CjForumApi.award_points - DB Error: '.$db->getErrorMsg(), JLog::ERROR, 'com_cjforum');
		}
	
		$db->setQuery($query);
		$rule = $db->loadObject();
	
		if(!$rule || !$rule->id || ($rule->published != '1') || ($points == 0 && $rule->points == 0)) return false;
		if(!in_array($rule->access, JAccess::getAuthorisedViewLevels($userId))) return false;
	
		if(!$points || $points == 0) $points = $rule->points;
	
		if($reference)
		{
			$query = $db->getQuery(true)
				->select('count(*)')
				->from('#__cjforum_points')
				->where('user_id = '.$userId.' and rule_id='.$rule->id.' and ref_id='.$db->q($reference));
				
			$db->setQuery($query);
			$count = (int)$db->loadResult();
	
			if($db->getErrorNum())
			{
				JLog::add('CjForumApi.award_points - DB Error: '.$db->getErrorMsg(), JLog::ERROR, 'com_cjforum');
			}
	
			if($count > 0) return false;
		}
	
		$reference = !$reference ? 'null' : $db->q($reference);
		$title = empty($title) ? $rule->title : JComponentHelper::filterText($title);
		$description = trim($description);
		$description = empty($description) ? 'null' : JComponentHelper::filterText($description, true);
		$createdate = JFactory::getDate()->toSql();
		$published = $rule->auto_approve == 1 ? 1 : 2;
	
		$query = $db->getQuery(true)
			->insert('#__cjforum_points')
			->columns('title, description, user_id, rule_id, points, ref_id, published, created_by, created, publish_up')
			->values($db->q($title).','.$db->q($description).','.$userId.','.$rule->id.','.$points.','.$reference.','.$published.','.$user->id.','.$db->q($createdate).','.$db->q($createdate));
	
		$db->setQuery($query);
	
		if(!$db->query())
		{
			$this->_errors[] = 'Error: '.$db->getErrorMsg();
	
			if($db->getErrorNum())
			{
				JLog::add('CjForumApi.award_points - DB Error: '.$db->getErrorMsg(), JLog::ERROR, 'com_cjforum');
			}
	
			return false;
		}
	
		if($published == 1)
		{
			$query = $db->getQuery(true)
				->update('#__cjforum_users')
				->set('points = points '.($points > 0 ? '+'.$points : '-'.abs($points)))
				->where('id = '.$userId);
		
			$db->setQuery($query);
		
			if(!$db->query())
			{
				$this->_errors[] = 'Error: '.$db->getErrorMsg();
			}
		}
		
		$params = JComponentHelper::getParams('com_cjforum');
		if($userId && $userId == $user->id && ($params->get('show_points_messages', 0) == 1))
		{
			$message = $points > 0 ? JText::plural('COM_CJFORUM_POINTS_ASSIGNED', $points) : JText::plural('COM_CJFORUM_POINTS_DEDUCTED', $points);
			JFactory::getApplication()->enqueueMessage($message);
		}
	
		return true;
	}
	
	public function getErrors()
	{
		return $this->_errors;
	}
	
	public function getError()
	{
		return array_pop($this->_errors);
	}
}