<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.modellist' );
require_once JPATH_COMPONENT.'/models/topics.php';

class CjForumModelDashboard extends CjForumModelTopics
{
	public function __construct ($config = array())
	{
		parent::__construct($config);
	}

	protected function populateState ($ordering = null, $direction = null)
	{
		parent::populateState('a.created', 'desc');
		$this->setState('list.limit', 5);
	}
	
	public function getTopicCountByDay()
	{
		$db = JFactory::getDbo();
		try 
		{
			$query = $db->getQuery(true)
				->select('count(*) as topics, date(created) as cdate')
				->from('#__cjforum_topics')
				->where('created >= DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR)')
				->group('date(created)')
				->order('created desc');
			
			$db->setQuery($query);
			$topicCounts = $db->loadAssocList('cdate');
			return $topicCounts;
		}
		catch (Exception $e){}
		return false;
	}
	
	public function getReplyCountByDay()
	{
		$db = JFactory::getDbo();
		try
		{
			$query = $db->getQuery(true)
				->select('count(*) as replies, date(created) as cdate')
				->from('#__cjforum_replies')
				->where('created >= DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR)')
				->group('date(created)')
				->order('created desc');
				
			$db->setQuery($query);
			$replyCounts = $db->loadAssocList('cdate');
			return $replyCounts;
		}
		catch (Exception $e){}
		return false;
	}
	
	public function getGeoLocationReport()
	{
		$db = JFactory::getDbo();
		try
		{
			$query = $db->getQuery(true)
				->select('count(*) as posts, a.country, c.country_name')
				->from('#__cjforum_tracking AS a')
				->join('left', '#__corejoomla_countries AS c ON a.country = c.country_code')
				->group('country');
		
			$db->setQuery($query);
			$replyCounts = $db->loadAssocList('country');
			return $replyCounts;
		}
		catch (Exception $e){}
		return false;
	}
}