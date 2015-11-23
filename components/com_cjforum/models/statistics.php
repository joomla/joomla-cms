<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class CjForumModelStatistics extends JModelLegacy
{
	public function __construct($config = array())
	{
		parent::__construct($config);
	}
	
	public function getForumStatistics()
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$return = new stdClass();
		
		try
		{
			$query = $db->getQuery(true)->select('count(1)')->from('#__cjforum_topics')->where('state = 1');
			$db->setQuery($query);
			$return->topics = (int) $db->loadResult();
			
			$query = $db->getQuery(true)->select('count(1)')->from('#__cjforum_replies')->where('state = 1');
			$db->setQuery($query);
			$return->replies = (int) $db->loadResult();

			$query = $db->getQuery(true)->select('count(1)')->from('#__users')->where('block = 0');
			$db->setQuery($query);
			$return->users = (int) $db->loadResult();
			
			$query = $db->getQuery(true)->select('id, name, username')->from('#__users')->where('block = 0')->order('id desc');
			$db->setQuery($query, 0, 1);
			$return->latestMember = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			throw $e;
		}
		
		return $return;
	}
	
	public static function getLoggedInUsers()
	{
		$db    = JFactory::getDbo();
		$user  = JFactory::getUser();
		$return = new stdClass();
	
		try
		{
			$query = $db->getQuery(true)
				->select('s.time, s.client_id, u.id, u.name, u.username')
				->from('#__session AS s')
				->join('LEFT', '#__users AS u ON s.userid = u.id')
				->where('s.guest = 0 and s.client_id = 0')
				->group('u.id');
			$db->setQuery($query, 0, 50);
			
			$return->users = $db->loadObjectList();
			
			$query = $db->getQuery(true)
				->select('count(*) count, guest')
				->from('#__session')
				->where('client_id = 0')
				->group('guest');
			$db->setQuery($query);
			
			$userTypes = $db->loadObjectList();
			$return->guests = $return->members = 0;
			
			if(!empty($userTypes))
			{
				foreach ($userTypes as $type)
				{
					if($type->guest != 0)
					{
						$return->guests = $type->count;
					}
					else 
					{
						$return->members = $type->count;
					}
				}
			}
		}
		catch (RuntimeException $e)
		{
			throw $e;
		}
		
		return $return;
	}
}