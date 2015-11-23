<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumHelper extends JHelperContent
{

	public static $extension = 'com_cjforum';

	public static function addSubmenu ($vName)
	{
		JHtmlSidebar::addEntry(JText::_('COM_CJFORUM_MENU_DASHBOARD'), 'index.php?option=com_cjforum&view=dashboard', $vName == 'dashboard');
		JHtmlSidebar::addEntry(JText::_('COM_CJFORUM_MENU_TOPICS'), 'index.php?option=com_cjforum&view=topics', $vName == 'topics');
		JHtmlSidebar::addEntry(JText::_('COM_CJFORUM_MENU_CATEGORIES'), 'index.php?option=com_categories&extension=com_cjforum', $vName == 'categories');
		JHtmlSidebar::addEntry(JText::_('COM_CJFORUM_MENU_RANKS'), 'index.php?option=com_cjforum&view=ranks', $vName == 'ranks');
		JHtmlSidebar::addEntry(JText::_('COM_CJFORUM_MENU_USERS'), 'index.php?option=com_cjforum&view=users', $vName == 'users');
		JHtmlSidebar::addEntry(JText::_('COM_CJFORUM_MENU_ACTIVITIES'), 'index.php?option=com_cjforum&view=activities', $vName == 'activities');
		JHtmlSidebar::addEntry(JText::_('COM_CJFORUM_MENU_POINTS'), 'index.php?option=com_cjforum&view=points', $vName == 'points');
		JHtmlSidebar::addEntry(JText::_('COM_CJFORUM_MENU_ACTIVITY_TYPES'), 'index.php?option=com_cjforum&view=activitytypes', $vName == 'activitytypes');
		JHtmlSidebar::addEntry(JText::_('COM_CJFORUM_MENU_POINTS_RULES'), 'index.php?option=com_cjforum&view=pointsrules', $vName == 'pointsrules');
		JHtmlSidebar::addEntry(JText::_('COM_CJFORUM_MENU_EMAIL_TEMPLATES'), 'index.php?option=com_cjforum&view=emails', $vName == 'emails');
		JHtmlSidebar::addEntry(JText::_('COM_CJFORUM_MENU_MIGRATE'), 'index.php?option=com_cjforum&view=migrate', $vName == 'migrate');
	}
	
	public static function scanRules()
	{
		$components = array();
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true)
			->select('element')
			->from('#__extensions')
			->where($db->qn('type').' = '.$db->q('component'))
			->where($db->qn('enabled').' = 1')
			->where($db->qn('client_id').' = 1');
		$db->setQuery($query);
		
		try 
		{
			$components = $db->loadColumn();
		}
		catch (Exception $e)
		{
			return false;
		}
		
		if(empty($components))
		{
			return false;
		}
		
		foreach ($components as $component)
		{
			$ruleFile = JPATH_ADMINISTRATOR.'/components/'.$component.'/cjforum_rules.xml';
			if(! file_exists($ruleFile))
			{
				$ruleFile = JPATH_ROOT.'/components/'.$component.'/cjforum_rules.xml';
				if(! file_exists($ruleFile))
				{
					continue;
				}
			}

			self::loadActivityRules($ruleFile, $component);
			self::loadPointsRules($ruleFile, $component);
		}
		
		return true;
	}
	
	private static function loadActivityRules($file, $component)
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$rules = simplexml_load_file($file);
		
		if(empty($rules) || empty($rules->activity_type)) 
		{
			return false;
		}
		
		foreach ($rules->activity_type as $rule)
		{
			$alias = CjLibUtils::getUrlSafeString($rule->title);
				
			$query = $db->getQuery(true)
				->insert('#__cjforum_activity_types')
				->columns('title, alias, description, activity_name, app_name, published, access, created_by, created, language')
				->values(
					$db->q($rule->title).','.
					$db->q($alias).','.
					$db->q($rule->description).','.
					$db->q($rule->name).','.
					$db->q($rule->appname).','.
					((int)$rule->state).','.
					((int)$rule->access).','.
					JFactory::getUser()->id.','.
					$db->q(JFactory::getDate()->toSql()).','.
					$db->q($rule->language));
			
			$db->setQuery($query);
			
			try 
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				// ignore if the rule already exists
// 				$app->enqueueMessage($db->getErrorMsg());
			}
		}
		
		return true;
	}
	
	private static function loadPointsRules($file, $component)
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$rules = simplexml_load_file($file);
		
		if(empty($rules) || empty($rules->points_rule)) 
		{
			return false;
		}
		
		foreach ($rules->points_rule as $rule)
		{
		
			$query = $db->getQuery(true)
				->insert('#__cjforum_points_rules')
				->columns('title, description, rule_name, app_name, points, published, auto_approve, access, created_by, created')
				->values(
					$db->q($rule->title).','.
					$db->q($rule->description).','.
					$db->q($rule->name).','.
					$db->q($rule->appname).','.
					((int)$rule->points).','.
					((int)$rule->state).','.
					((int)$rule->auto_approve).','.
					((int)$rule->access).','.
					JFactory::getUser()->id.','.
				$db->q(JFactory::getDate()->toSql()));
				
			$db->setQuery($query);
			
			try 
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				// ignore if the rule already exists
// 				$app->enqueueMessage($db->getErrorMsg());
			}
		}
		
		return true;
	}
	
	public static function uploadFiles($postId, $postType, $fieldName = 'attachment_file')
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		
		jimport('joomla.filesystem.file');
		$files = $app->input->files->get('attachment_file');
		$uploaded = array();
		
		if(!empty($files))
		{
			foreach ($files as $i=>$file)
			{
				if(empty($file['tmp_name']))
				{
					continue;
				}
					
				$filename = $postId.'_'.$postType.'_'.JFile::makeSafe($file['name']);
				$src = $file['tmp_name'];
				$dest = CF_ATTACHMENTS_DIR.$filename;
					
				if(JFile::upload($src, $dest))
				{
					$upload = new stdClass();
					$upload->name = $filename;
					$upload->size = (int) $file['size'];
					$uploaded[] = $upload;
				}
			}
		}
			
		if(!empty($uploaded))
		{
			$query = $db->getQuery(true)
				->insert('#__cjforum_attachments')
				->columns('post_id, post_type, created_by, hash, filesize, folder, filetype, filename');
		
			foreach ($uploaded as $upload)
			{
				$hash = md5_file(CF_ATTACHMENTS_DIR.$upload->name);
				$query->values($postId.','.$postType.','.$user->id.','.$db->q($hash).','.$upload->size.','.$db->q(CF_ATTACHMENTS_PATH).','.$db->q('').','.$db->q($upload->name));
			}
		
			$db->setQuery($query);
		
			try
			{
				$db->execute();
			}
			catch (Exception $e){}
		}
	}
}
