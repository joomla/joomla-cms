<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JLoader::register('CjForumHelper', JPATH_ADMINISTRATOR . '/components/com_cjforum/helpers/cjforum.php');
JLoader::import('migrate', JPATH_COMPONENT_ADMINISTRATOR.'/models');
require_once CJLIB_PATH.'/lib/jbbcode/Parser.php';
require_once CJLIB_PATH.'/lib/jbbcode/custom/CjCustomCodeDefinitions.php';

define('CJFORUM_MIGRATION_NUM_USERS', 250);

class CjForumModelMigrateCjBlog extends CjForumModelMigrate
{
	private $_message = null;
	
	public function getMessage()
	{
		return $this->_message;
	}
	
	public function setMessage($message)
	{
		$this->_message = $message;
	}
	
	public function migrate($step = 0)
	{
		$app = JFactory::getApplication();
		$content = file_get_contents(JPATH_ROOT.'/tmp/migrate_cjblog.json');
		
		if(!$content)
		{
			throw new Exception('Migration steps file is missing.');
		}
		
		$steps = json_decode($content);
		
		if(!is_array($steps))
		{
			throw new Exception('Analysis file not found, cannot continue.');
		} 
		else if(!isset($steps[$step]))
		{
			// process completed
			return 1;
		}
		
		$parts = explode('.', $steps[$step]);
		$features = $app->input->get('features', array(), 'array');
		
		if (count($parts) != 2 || !is_numeric($parts[1]))
		{
			throw new Exception('Invalid controller steps file.');
		}
		
		switch ($parts[0])
		{
			case 'users':
				if(in_array('users', $features))
				{
					$migrateAvatars = false;
					if(in_array('avatar', $features))
					{
						$migrateAvatars = true;
					}
					
					$migratePoints = false;
					if(in_array('points', $features))
					{
						$migratePoints = true;
					}
					
					if(! $this->migrateUsers($parts[1], CJFORUM_MIGRATION_NUM_USERS, $migrateAvatars, $migratePoints) )
					{
						return false;
					}
					
					$count = $parts[1] + CJFORUM_MIGRATION_NUM_USERS;
					$this->setMessage('<i class="fa fa-users"></i> Migrating users now.. '.$count.' users migrated.');
				}
				break;
		}
		
		return true;
	}
	
	public function analyse()
	{
		$db = JFactory::getDbo();
		$steps = array();
		
		try 
		{
			$i = 0;
			$features = JFactory::getApplication()->input->getArray(array('features'=>'array'));
			
			if(empty($features['features']))
			{
				return false;
			}
			
			$modules = $tables = array();
			
			foreach ($features['features'] as $feature)
			{
				switch ($feature)
				{
					case 'users':
						
						$steps[$i++] = 'ranks.0';;
						$modules['users'] = CJFORUM_MIGRATION_NUM_USERS;
						$tables['users'] = '#__cjblog_users';
						break;
				}
			}
			
			foreach ($modules as $module=>$limit)
			{
				$query = $db->getQuery(true)->select('count(*)')->from($tables[$module]);
				$db->setQuery($query);
				$count = (int) $db->loadResult();
				
				if ($count > 0)
				{
					$start = 0;
					$steps[$i++] = $module.'.'.$start;
					
					while ($count > $start + $limit)
					{
						$start = $start + $limit;
						$steps[$i++] = $module.'.'.$start;
					}
				}
			}
			
			$json = json_encode($steps);
			if( JFile::write(JPATH_ROOT.'/tmp/migrate_cjblog.json', $json) )
			{
				return true;
			}
		}
		catch (Exception $e)
		{
			return false;
		}
		
		return false;
	}
	
	private function migrateUsers($start, $limit = 250, $migrateAvatars = false, $migratePoints = false)
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		
		require_once CJLIB_PATH.'/framework/class.upload.php';
		$sizes = array(16, 32, 48, 64, 96, 128, 160, 192, 256);
		
		try
		{
			$parser = new JBBCode\Parser();
			$parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
			$parser->addCodeDefinitionSet(new JBBCode\CjCodeDefinitionSet());
			
			$query = $db->getQuery(true)
				->select('u.id, u.username AS handle')
				->select('cb.about, cb.avatar, cb.profile_views hits, cb.points')
				->from('#__users AS u')
				->join('left', '#__cjblog_users AS cb on u.id = cb.id');
			
			$db->setQuery($query, $start, $limit);
			$users = $db->loadObjectList('id');
			
			if (empty($users))
			{
				$app->enqueueMessage($db->getErrorMsg());
				return false;
			}
			
			$query = $db->getQuery(true)
				->insert('#__cjforum_users')
				->columns('id, handle, about, avatar, hits, points');
			
			$userIds = array();
			foreach ($users as $user)
			{
				// no need to migrate avatars as they already at right place in cjblog
				$query->values(
					$user->id.','.
					$db->q($user->handle).','.
					$db->q($user->about).','.
					$db->q($user->avatar).','.
					(int)$user->hits.','.
					(int)$user->points
				);
				
				$userIds[] = $user->id;
			}
			
			$query = $query->__toString();;
			$query = $query . ' on duplicate key update avatar = values (avatar), about = values(about), points = values (points)';
			
			$db->setQuery($query);
			
			if($db->execute())
			{
				if($migratePoints)
				{
					$query = $db->getQuery(true)
						->insert('#__cjforum_points')
						->columns('title, user_id, rule_id, published, points, description, created_by, created, ref_id');
					
					reset($users);
					$created = JFactory::getDate()->toSql();
					$userId = JFactory::getUser()->id;
					
					foreach ($users as $user)
					{
						$query->values(
								$db->q('Archived as on '.$created).','.
								$user->id.','.
								'1, 1, '.
								(int)$user->points.','.
								$db->q('Points archived as on '.$created).','.
								$userId.','.
								$db->q($created).','.
								$user->id
							);
					}
					
					$db->setQuery($query);
					$db->execute();
				}
								
				return true;
			}
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage());
		}
		
		return true;
	}

	public function getTable ($type = 'Topic', $prefix = 'CjForumTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function syncUsers()
	{
		$db = JFactory::getDbo();
		
		try
		{
			// now update posts and replies counts
			$query = $db->getQuery(true)
				->update('#__cjforum_users A')
				->join('inner', '(select created_by, count(*) as topics from #__cjforum_topics group by created_by) B on B.created_by = A.id')
				->set('A.topics = B.topics');
				
			$db->setQuery($query);
			$db->execute();
				
			$query = $db->getQuery(true)
				->update('#__cjforum_users A')
				->join('inner', '(select created_by, count(*) as replies from #__cjforum_replies group by created_by) B on B.created_by = A.id')
				->set('A.replies = B.replies');
				
			$db->setQuery($query);
			$db->execute();
			
			return true;
		}
		catch (Exception $e)
		{
			throw new Exception('Unable to do final steps! Error: '.$db->getErrorMsg());
		}
		
		return false;
	}
	
	public function rebuildAssets()
	{
		JLoader::import('joomla.application.component.model');
		JLoader::import('migrate', JPATH_COMPONENT_ADMINISTRATOR.'/components/com_cjforum/models');
		$model = JModelLegacy::getInstance( 'Migrate', 'CjForumModel' );
		$model->rebuildAssets();
		
		return true;
	}
	
	public function syncTopics()
	{
		return true;
	}
}