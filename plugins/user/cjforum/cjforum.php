<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();
require_once JPATH_ROOT.'/components/com_cjlib/framework/api.php';

class plgUserCjForum extends JPlugin
{
	public function onUserLogin($user, $options)
	{
		$app = JFactory::getApplication();
		$language = JFactory::getLanguage();
		
		if ($app->isAdmin()) return true;
		
		$userId = intval(JUserHelper::getUserId($user['username']));
		$language->load('com_cjforum');
		$api = new CjLibApi();
		
		$title = $description = JText::sprintf('COM_CJFORUM_POINTS_DAILY_LOGIN', date('F j, Y, g:i a'));
		$options = array('function'=>'com_users.login', 'reference'=>date('Ymd'), 'info'=>$description, 'component'=>'com_users', 'title'=>$title);
		$api->awardPoints('cjforum', $userId, $options);
		
		// update user login status
		try 
		{
			$db = JFactory::getDbo();
			$date = JFactory::getDate()->toSql();
			
			$query = $db->getQuery(true)
				->update('#__cjforum_users')
				->set('last_access_date = current_access_date')
				->set('current_access_date = '.$db->q($date))
				->where('id = '.$userId);
			
			$db->setQuery($query);
			$db->execute();
		}
		catch (Exception $e)
		{
			// don't interupt login process
		}
		
		return true;
	}
	
	public function onUserAfterSave($user, $isnew, $success, $error)
	{
		if($isnew && $success)
		{
			$userId = JArrayHelper::getValue($user, 'id', 0, 'int');
			if($userId > 0)
			{
				try 
				{
					$api = new CjLibApi();
					$db = JFactory::getDbo();
					$language = JFactory::getLanguage();
					$language->load('com_cjforum');

					// get the rank of the next level if any
					$query = $db->getQuery(true)
						->select('id')
						->from('#__cjforum_ranks')
						->where('rank_type = 0')
						->where('min_posts = 0')
						->order('id');

					$db->setQuery($query, 0, 1);
					$rankId = (int) $db->loadResult();
					
					$query = $db->getQuery(true)
						->insert('#__cjforum_users')
						->columns('id, handle, rank')
						->values($userId.','.$db->q(str_replace('-', '_', $user['username'])).', '.$rankId);

					$db->setQuery($query);
					if($db->execute())
					{
						$title = $description = JText::sprintf('COM_CJFORUM_POINTS_FOR_SIGNUP');
						$options = array('function'=>'com_users.signup', 'reference'=>$userId, 'info'=>$description, 'component'=>'com_users', 'title'=>$title);
						$api->awardPoints('cjforum', $userId, $options);
					}
					
				}
				catch (Exception $e)
				{
// 				    var_dump($e);
				}
			}
		}
				
		return true;
	}
}
