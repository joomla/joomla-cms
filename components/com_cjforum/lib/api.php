<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT.'/components/com_cjforum/helpers/constants.php';

class CjForumApi 
{
	public static function getStreamApi($options = null)
	{
		require_once JPATH_ROOT.'/components/com_cjforum/lib/stream.php';
		
		$date = JFactory::getDate()->format('Y.m.d');
		JLog::addLogger(array('text_file' => 'com_cjforum'.'.'.$date.'.log.php'), JLog::ALL, 'com_cjforum');
		
		$streamApi = new CjForumStreamApi($options);
		
		return $streamApi;
	}
	
	public static function getPointsApi($options = null)
	{
		require_once JPATH_ROOT.'/components/com_cjforum/lib/points.php';
		
		$date = JFactory::getDate()->format('Y.m.d');
		JLog::addLogger(array('text_file' => 'com_cjforum'.'.'.$date.'.log.php'), JLog::ALL, 'com_cjforum');
		
		$pointsApi = new CjForumPointsApi($options);
		
		return $pointsApi;
	}
	
	public static function getProfileApi($options = null)
	{
		require_once JPATH_ROOT.'/components/com_cjforum/lib/profile.php';
		
		$date = JFactory::getDate()->format('Y.m.d');
		JLog::addLogger(array('text_file' => 'com_cjforum'.'.'.$date.'.log.php'), JLog::ALL, 'com_cjforum');
		
		$profileApi = new CjForumProfileApi($options);
		
		return $profileApi;
	}
	
	public static function checkMessages($userId)
	{
		$count = 0;
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true)
			->select('count(*)')
			->from('#__cjforum_messages_map')
			->where('receiver_id = '.$userId.' and receiver_state = 0');
		
		$db->setQuery($query);
		
		try
		{
			$count = $db->loadResult();
		}
		catch (Exception $e)
		{
			JLog::add('CjForumApi.check_messages - DB Error: '.$db->getErrorMsg(), JLog::ERROR, 'com_cjforum');
		}
		
		return $count;
	}
	
	public static function getActivityDate($strdate)
	{
		if(empty($strdate) || $strdate == '0000-00-00 00:00:00')
		{
			return JText::_('LBL_NA');
		}
	
		jimport('joomla.utilities.date');
		$user = JFactory::getUser();
	
		// Given time
		$date = new JDate(JHtml::date($strdate, 'Y-m-d H:i:s'));
		$compareTo = new JDate(JHtml::date('now', 'Y-m-d H:i:s'));
		$diff = $compareTo->toUnix() - $date->toUnix();
	
		$diff = abs($diff);
		$dayDiff = floor($diff/86400);
	
		if($dayDiff == 0)
		{
			if($diff < 3600)
			{
				return JText::sprintf('COM_CJFORUM_DATE_FORMAT_MINUTES', floor($diff/60));
			}
			else
			{
				return JText::sprintf('COM_CJFORUM_DATE_FORMAT_HOURS', floor($diff/3600));
			}
		} else
		{
			return $date->format(JText::_('COM_CJFORUM_DATE_FORMAT_FULL_DATE', false, false));
		}
	}
	
	/**
	 * Gets the user rank image or the formatted rank text based on the values passed.
	 *
	 * @param string $profile the name of the rank profile
	 * @param string $image the filename of the rank image
	 * @param string $title rank title
	 *
	 * @return string Image path to the rank or the formatted html text of the rank
	 */
	public static function getUserRankImage($user_id, $profile='default')
	{
		$profileApi = CjForumApi::getProfileApi();
		$profile = $profileApi->getUserProfile($user_id);

		if(empty($profile['rank_image']))
		{
			return '<div class="label label-'.($profile['rank_class'] ? $profile['rank_class'] : 'default').' rank">'.CjLibUtils::escape($profile['rank_title']).'</div>';
		}
		else
		{
			return '<img src="'.CjLibUtils::escape(JUri::root(false).$profile['rank_image']).'" alt="'.$profile['rank_title'].'" title="'.$profile['rank_title'].'" data-toggle="tooltip">';
		}
	}
}